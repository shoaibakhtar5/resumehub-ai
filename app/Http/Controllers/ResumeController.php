<?php

namespace App\Http\Controllers;

use App\Http\Requests\Resume\ResumeAutosaveRequest;
use App\Http\Requests\Resume\ResumeShareRequest;
use App\Http\Requests\Resume\ResumeStoreRequest;
use App\Http\Requests\Resume\ResumeUpdateRequest;
use App\Models\Resume;
use App\Models\ResumeShare;
use App\Models\Template;
use App\Services\ResumeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ResumeController extends Controller
{
    public function __construct(private readonly ResumeService $resumes)
    {
    }

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
        $resume = null;

        return view('dashboard.builder', [
            'resume' => $resume,
            'templates' => Template::query()->where('status', 'published')->orderBy('sort_order')->get(),
            'selectedTemplate' => $request->integer('template') ?: null,
        ]);
    }

    public function store(ResumeStoreRequest $request): RedirectResponse
    {
        $resume = $this->resumes->create($request->user(), $request->validated());

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

        return view('dashboard.builder', [
            'resume' => $resume->load(['profile', 'experiences', 'educations', 'template']),
            'templates' => Template::query()->where('status', 'published')->orderBy('sort_order')->get(),
            'selectedTemplate' => $resume->template_id,
        ]);
    }

    public function update(ResumeUpdateRequest $request, Resume $resume): RedirectResponse
    {
        $this->resumes->update($resume, $request->user(), $request->validated());

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
        $resume = $this->resumes->update($resume, $request->user(), $request->validated(), 'autosave');

        return [
            'saved' => true,
            'updated_at' => $resume->updated_at?->toIso8601String(),
            'completion_score' => $resume->completion_score,
        ];
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

    public function preview(Request $request, ?Resume $resume = null): View
    {
        $resume ??= $request->user()->resumes()->with(['profile', 'experiences', 'educations', 'template', 'shares'])->latest('updated_at')->first();

        if ($resume) {
            $this->authorize('view', $resume);
            $resume->load(['profile', 'experiences', 'educations', 'template', 'shares']);
        }

        $latestReport = $resume?->atsReports()->latest('scanned_at')->first();

        return view('dashboard.preview', [
            'resume' => $resume,
            'latestReport' => $latestReport,
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
            ->with(['resume.profile', 'resume.experiences', 'resume.educations', 'resume.template', 'resume.shares'])
            ->where('token', $token)
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->firstOrFail();

        $share->forceFill(['last_accessed_at' => now()])->save();

        return view('dashboard.preview', [
            'resume' => $share->resume,
            'latestReport' => $share->resume->atsReports()->latest('scanned_at')->first(),
            'sharedView' => true,
        ]);
    }

    public function download(Request $request, Resume $resume): Response
    {
        $this->authorize('download', $resume);
        $format = $request->string('format', 'txt')->toString();
        $this->resumes->recordDownload($resume, $request->user(), $format);
        $resume->load(['profile', 'experiences', 'educations']);

        return response($this->downloadText($resume), 200, [
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

        $active = $user->resumes()->where('is_archived', false)->count();
        $favorites = $user->resumes()->where('is_favorite', true)->count();
        $archived = $user->resumes()->where('is_archived', true)->count();
        $downloads = $user->resumes()->withCount('downloads')->get()->sum('downloads_count');
        $score = (int) round($user->resumes()->avg('completion_score') ?: 0);

        $base['stats'] = [
            ['label' => 'Active resumes', 'value' => (string) $active, 'icon' => 'document-text'],
            ['label' => 'Average score', 'value' => $score.'%', 'icon' => 'chart-bar', 'tone' => $score >= 80 ? 'success' : 'warning'],
            ['label' => 'Favorites', 'value' => (string) $favorites, 'icon' => 'heart'],
            ['label' => 'Downloads', 'value' => (string) $downloads, 'icon' => 'arrow-down-tray'],
        ];

        if ($page === 'archived-resumes') {
            $base['stats'][0] = ['label' => 'Archived', 'value' => (string) $archived, 'icon' => 'archive-box'];
        }

        $recent = $this->resumes->recentFor($user, 3);
        $base['cards'] = $recent->map(fn (Resume $resume) => [
            'icon' => $resume->is_favorite ? 'heart' : 'document-text',
            'title' => $resume->title,
            'body' => trim(($resume->target_role ?: 'Untargeted').' - '.$resume->completion_score.'% complete'),
        ])->values()->all() ?: $base['cards'];

        $base['table'] = [
            'headers' => ['Resume', 'Target Role', 'Score', 'Updated'],
            'rows' => $this->resumes->recentFor($user, 5)->map(fn (Resume $resume) => [
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

    private function downloadText(Resume $resume): string
    {
        $settings = $resume->settings ?? [];
        $lines = [
            $resume->profile?->full_name ?: $resume->title,
            $resume->profile?->headline ?: $resume->target_role,
            trim(implode(' | ', array_filter([$resume->profile?->email, $resume->profile?->phone, $resume->profile?->location]))),
            '',
            'Profile',
            $settings['summary'] ?? '',
            '',
            'Experience',
        ];

        foreach ($resume->experiences as $experience) {
            $lines[] = "{$experience->position}, {$experience->company}";
            $lines[] = $experience->description;
        }

        $lines[] = '';
        $lines[] = 'Education';
        foreach ($resume->educations as $education) {
            $lines[] = trim($education->degree.' '.$education->field_of_study.', '.$education->institution);
        }

        $lines[] = '';
        $lines[] = 'Skills';
        $lines[] = implode(', ', $settings['skills'] ?? []);

        return implode("\n", array_filter($lines, fn ($line) => $line !== null));
    }
}
