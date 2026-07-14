<?php

namespace App\Http\Controllers;

use App\Http\Requests\Resume\ResumeAutosaveRequest;
use App\Http\Requests\Resume\ResumeImportRequest;
use App\Http\Requests\Resume\ResumeShareRequest;
use App\Http\Requests\Resume\ResumeStoreRequest;
use App\Http\Requests\Resume\ResumeUpdateRequest;
use App\Models\Resume;
use App\Models\ResumeDownload;
use App\Models\ResumeShare;
use App\Models\ResumeVersion;
use App\Models\Template;
use App\Services\MediaService;
use App\Services\PreviewService;
use App\Services\ResumeBuilderService;
use App\Services\ResumeImportService;
use App\Services\ResumeService;
use App\Services\TemplateRenderingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ResumeController extends Controller
{
    public function __construct(
        private readonly ResumeService $resumes,
        private readonly ResumeBuilderService $builder,
        private readonly PreviewService $preview,
        private readonly ResumeImportService $imports,
        private readonly MediaService $media,
        private readonly TemplateRenderingService $templateRenderer,
    ) {}

    public function index(Request $request): View
    {
        $resumes = $request->user()->resumes()
            ->with(['template', 'profile'])
            ->where('is_archived', false)
            ->latest('updated_at')
            ->paginate(12);

        return view('dashboard.page', [
            'mode' => 'user',
            'page' => $this->libraryPage('my-resumes', $request),
            'records' => $resumes,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Resume::class);

        $resume = null;

        return view('dashboard.builder', [
            'resume' => $resume,
            'templates' => Template::query()->published()->orderBy('sort_order')->orderBy('name')->get(),
            'selectedTemplate' => $request->integer('template') ?: null,
            'latestReport' => null,
        ]);
    }

    public function store(ResumeStoreRequest $request): RedirectResponse
    {
        $data = $this->resumeData($request);
        $resume = $this->builder->saveDraft(null, $request->user(), $this->builder->buildPayload($data));

        return redirect()->route('resumes.edit', $resume)->with('status', 'Resume created.');
    }

    public function show(Request $request, Resume $resume): View
    {
        $this->authorize('view', $resume);

        return $this->preview($request, $resume);
    }

    public function edit(Resume $resume): View
    {
        $this->authorize('update', $resume);
        $resume->load([...$this->preview->relations(), 'versions']);

        return view('dashboard.builder', [
            'resume' => $resume,
            'templates' => Template::query()->published()->orderBy('sort_order')->orderBy('name')->get(),
            'selectedTemplate' => $resume->template_id,
            'latestReport' => $resume->atsReports()->with(['keywords', 'issues'])->latest('scanned_at')->first(),
        ]);
    }

    public function update(ResumeUpdateRequest $request, Resume $resume): RedirectResponse
    {
        $data = $this->resumeData($request, $resume);
        $this->builder->saveDraft($resume, $request->user(), $this->builder->buildPayload($data), 'manual');

        return redirect()->route('resumes.edit', $resume)->with('status', 'Resume saved.');
    }

    public function destroy(Request $request, Resume $resume): RedirectResponse
    {
        $this->authorize('delete', $resume);
        $resume->delete();

        return redirect()->route('resumes.index')->with('status', 'Resume deleted.');
    }

    public function autosave(ResumeAutosaveRequest $request, Resume $resume): array
    {
        $data = $this->resumeData($request, $resume);
        $resume = $this->resumes->update($resume, $request->user(), $data, 'autosave');

        return [
            'saved' => true,
            'updated_at' => $resume->updated_at?->toIso8601String(),
            'completion_score' => $resume->completion_score,
            'photo_url' => $resume->profile?->photo_path,
        ];
    }

    public function import(ResumeImportRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $file = $data['resume_file'];
        unset($data['resume_file']);

        $resume = $this->imports->import($request->user(), $file, $data);

        return redirect()->route('resumes.edit', $resume)->with('status', 'Resume imported. Review the extracted sections before exporting.');
    }

    public function duplicate(Request $request, Resume $resume): RedirectResponse
    {
        $this->authorize('view', $resume);
        $copy = $this->resumes->duplicate($resume, $request->user());

        return redirect()->route('resumes.edit', $copy)->with('status', 'Resume duplicated.');
    }

    public function favorite(Resume $resume): RedirectResponse
    {
        $this->authorize('update', $resume);
        $this->resumes->toggleFavorite($resume);

        return back()->with('status', 'Favorite status updated.');
    }

    public function archive(Resume $resume): RedirectResponse
    {
        $this->authorize('delete', $resume);
        $this->resumes->setArchived($resume, true);

        return back()->with('status', 'Resume archived.');
    }

    public function restore(Resume $resume): RedirectResponse
    {
        $this->authorize('restore', $resume);
        $this->resumes->setArchived($resume, false);

        return back()->with('status', 'Resume restored.');
    }

    public function restoreVersion(Request $request, Resume $resume, ResumeVersion $version): RedirectResponse
    {
        $this->authorize('update', $resume);
        $this->resumes->restoreVersion($resume, $version, $request->user());

        return redirect()->route('resumes.edit', $resume)->with('status', 'Resume version restored.');
    }

    public function preview(Request $request, ?Resume $resume = null): View
    {
        $resume ??= $request->user()->resumes()->with($this->preview->relations())->latest('updated_at')->first();

        if ($resume) {
            $this->authorize('view', $resume);
            $resume->loadMissing($this->preview->relations());
        }

        $latestReport = $resume?->atsReports()->latest('scanned_at')->first();

        return view('dashboard.preview', [
            'resume' => $resume,
            'latestReport' => $latestReport,
            'renderedHtml' => $resume ? $this->templateRenderer->render($resume->template, $resume) : null,
        ]);
    }

    public function share(ResumeShareRequest $request, Resume $resume): RedirectResponse
    {
        $share = $this->resumes->share($resume, $request->validated());

        return back()->with('status', 'Share link created: '.route('resume.shared', $share->token));
    }

    public function shared(string $token): View
    {
        $share = ResumeShare::query()
            ->with(['resume' => fn ($query) => $query->with($this->preview->relations())])
            ->where('token', $token)
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->firstOrFail();

        $share->forceFill(['last_accessed_at' => now()])->save();

        return view('dashboard.preview', [
            'resume' => $share->resume,
            'latestReport' => $share->resume->atsReports()->latest('scanned_at')->first(),
            'sharedView' => true,
            'renderedHtml' => $this->templateRenderer->render($share->resume->template, $share->resume),
        ]);
    }

    public function download(Request $request, Resume $resume): Response
    {
        $this->authorize('download', $resume);
        $format = $request->string('format', 'txt')->toString();
        $this->resumes->recordDownload($resume, $request->user(), $format);
        $resume->load($this->preview->relations());

        if ($format === 'pdf') {
            $resume->forceFill(['last_exported_at' => now()])->save();

            return Pdf::loadView('dashboard.resume-pdf', [
                'resume' => $resume,
                'settings' => $resume->settings ?? [],
                'renderedHtml' => $this->templateRenderer->render($resume->template, $resume, true),
            ])->setPaper(($resume->settings['theme']['page_size'] ?? 'a4') === 'letter' ? 'letter' : 'a4')
                ->download(str($resume->slug ?: 'resume')->slug().'.pdf');
        }

        return response($this->resumes->plainText($resume), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.str($resume->slug ?: 'resume')->slug().'.txt"',
        ]);
    }

    public function library(Request $request, string $page): View
    {
        return view('dashboard.page', [
            'mode' => 'user',
            'page' => $this->libraryPage($page, $request),
            'records' => $this->recordsFor($request, $page),
        ]);
    }

    private function libraryPage(string $page, Request $request): array
    {
        $user = $request->user();
        $base = config("resumehub.user_pages.{$page}", config('resumehub.user_pages.my-resumes'));

        $stats = $user->resumes()
            ->selectRaw('SUM(CASE WHEN is_archived = 0 THEN 1 ELSE 0 END) as active_count')
            ->selectRaw('SUM(CASE WHEN is_favorite = 1 THEN 1 ELSE 0 END) as favorite_count')
            ->selectRaw('SUM(CASE WHEN is_archived = 1 THEN 1 ELSE 0 END) as archived_count')
            ->selectRaw('AVG(completion_score) as average_score')
            ->first();

        $active = (int) ($stats->active_count ?? 0);
        $favorites = (int) ($stats->favorite_count ?? 0);
        $archived = (int) ($stats->archived_count ?? 0);
        $downloads = ResumeDownload::query()
            ->whereIn('resume_id', $user->resumes()->select('id'))
            ->count();
        $score = (int) round($stats->average_score ?? 0);

        $base['stats'] = [
            ['label' => 'Active resumes', 'value' => (string) $active, 'icon' => 'document-text'],
            ['label' => 'Average score', 'value' => $score.'%', 'icon' => 'chart-bar', 'tone' => $score >= 80 ? 'success' : 'warning'],
            ['label' => 'Favorites', 'value' => (string) $favorites, 'icon' => 'heart'],
            ['label' => 'Downloads', 'value' => (string) $downloads, 'icon' => 'arrow-down-tray'],
        ];

        if ($page === 'archived-resumes') {
            $base['stats'][0] = ['label' => 'Archived', 'value' => (string) $archived, 'icon' => 'archive-box'];
        }

        $recent = $this->resumes->recentFor($user, 5);
        $base['cards'] = $recent->take(3)->map(fn (Resume $resume) => [
            'icon' => $resume->is_favorite ? 'heart' : 'document-text',
            'title' => $resume->title,
            'body' => trim(($resume->target_role ?: 'Untargeted').' - '.$resume->completion_score.'% complete'),
        ])->values()->all() ?: $base['cards'];

        $base['table'] = [
            'headers' => ['Resume', 'Target Role', 'Score', 'Updated'],
            'rows' => $recent->map(fn (Resume $resume) => [
                $resume->title,
                $resume->target_role ?: 'General',
                $resume->completion_score.'%',
                $resume->updated_at?->diffForHumans() ?? 'New',
            ])->values()->all(),
        ];

        return $base;
    }

    private function recordsFor(Request $request, string $page)
    {
        $query = $request->user()->resumes()->with(['template', 'profile'])->latest('updated_at');

        return match ($page) {
            'favorite-resumes' => $query->where('is_favorite', true)->paginate(12),
            'archived-resumes' => $query->where('is_archived', true)->paginate(12),
            'downloads' => $request->user()->resumes()->with('downloads')->has('downloads')->paginate(12),
            'shared-resumes' => $request->user()->resumes()->with('shares')->has('shares')->paginate(12),
            'version-history' => $request->user()->resumes()->with('versions')->has('versions')->paginate(12),
            default => $query->where('is_archived', false)->paginate(12),
        };
    }

    private function resumeData(ResumeStoreRequest $request, ?Resume $resume = null): array
    {
        $data = $request->validated();

        if ($request->hasFile('profile_photo')) {
            $stored = $this->media->store(
                $request->file('profile_photo'),
                'resume-photos',
                $request->user(),
                $resume ?? $request->user(),
                ['alt_text' => ($data['profile']['full_name'] ?? $request->user()->name).' profile photo'],
            );

            $path = $stored->metadata['path'] ?? null;
            $data['profile']['photo_path'] = $path
                ? '/storage/'.ltrim($path, '/')
                : ($stored->metadata['url'] ?? null);
        }

        unset($data['profile_photo']);

        return $data;
    }
}
