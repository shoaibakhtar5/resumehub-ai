<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTemplateRequest;
use App\Http\Requests\Admin\UpdateTemplateRequest;
use App\Models\ResumeDownload;
use App\Models\Template;
use App\Models\TemplateCategory;
use App\Services\TemplateRenderingService;
use App\Services\TemplateUploadService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function __construct(
        private readonly TemplateUploadService $uploads,
        private readonly TemplateRenderingService $renderer,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Template::class);
        $query = Template::query()->with(['category', 'creator', 'previewMedia'])->withCount('resumes');

        if ($search = trim($request->string('search')->toString())) {
            $query->where(fn (Builder $builder) => $builder
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('slug', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%'));
        }
        if ($category = $request->integer('category')) {
            $query->where('template_category_id', $category);
        }
        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($request->filled('featured')) {
            $query->where('config->is_featured', $request->boolean('featured'));
        }

        $templates = $query->orderBy('sort_order')->orderByDesc('updated_at')->paginate(8)->withQueryString();
        $downloadCounts = ResumeDownload::query()
            ->join('resumes', 'resumes.id', '=', 'resume_downloads.resume_id')
            ->whereIn('resumes.template_id', $templates->pluck('id'))
            ->selectRaw('resumes.template_id, COUNT(*) as aggregate')
            ->groupBy('resumes.template_id')->pluck('aggregate', 'resumes.template_id');

        return view('admin.templates.index', [
            'templates' => $templates,
            'categories' => TemplateCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'downloadCounts' => $downloadCounts,
            'stats' => [
                'total' => Template::query()->count(),
                'active' => Template::query()->published()->count(),
                'featured' => Template::query()->where('config->is_featured', true)->count(),
                'downloads' => ResumeDownload::query()->whereHas('resume', fn ($q) => $q->whereNotNull('template_id'))->count(),
                'categories' => TemplateCategory::query()->where('is_active', true)->count(),
            ],
            'placeholders' => $this->renderer->placeholderLabels(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Template::class);

        return $this->form();
    }

    public function store(StoreTemplateRequest $request): RedirectResponse
    {
        $template = DB::transaction(function () use ($request): Template {
            $template = Template::query()->create($this->payload($request->validated(), $request->user()->id));
            $this->uploads->storeSource($template, $request->file('template_file'));
            if ($request->hasFile('thumbnail')) {
                $this->uploads->storeThumbnail($template, $request->file('thumbnail'), $request->user());
            }
            return $template;
        });

        return redirect()->route('admin.templates.preview', $template)->with('status', 'Template uploaded and registered successfully.');
    }

    public function edit(Template $template): View
    {
        $this->authorize('update', $template);

        return $this->form($template);
    }

    public function update(UpdateTemplateRequest $request, Template $template): RedirectResponse
    {
        DB::transaction(function () use ($request, $template): void {
            $template->update($this->payload($request->validated(), $template->created_by_user_id, $request->user()->id));
            if ($request->hasFile('template_file')) {
                $this->uploads->storeSource($template, $request->file('template_file'));
            }
            if ($request->hasFile('thumbnail')) {
                $this->uploads->storeThumbnail($template, $request->file('thumbnail'), $request->user());
            }
        });

        return redirect()->route('admin.templates')->with('status', 'Template updated.');
    }

    public function preview(Template $template): View
    {
        $this->authorize('view', $template);

        return view('admin.templates.preview', [
            'template' => $template->load(['category', 'creator']),
            'renderedHtml' => $this->renderer->render($template, $this->renderer->demoPayload()),
        ]);
    }

    public function duplicate(Template $template, Request $request): RedirectResponse
    {
        $this->authorize('duplicate', $template);
        $copy = DB::transaction(function () use ($template, $request): Template {
            $copy = $template->replicate(['slug', 'package_path', 'preview_media_id', 'preview_path']);
            $copy->name = $template->name.' Copy';
            $copy->slug = $this->uniqueSlug($template->slug.'-copy');
            $copy->status = 'draft';
            $copy->created_by_user_id = $request->user()->id;
            $copy->config = array_merge($template->config ?? [], ['updated_by_user_id' => $request->user()->id, 'is_featured' => false]);
            $copy->save();
            if ($template->package_path && Storage::disk('local')->exists($template->package_path)) {
                $path = 'templates/'.$copy->id.'/'.$copy->version.'/resume.html';
                Storage::disk('local')->put($path, Storage::disk('local')->get($template->package_path));
                $copy->update(['package_path' => $path]);
            }
            return $copy;
        });

        return redirect()->route('admin.templates.edit', $copy)->with('status', 'Template duplicated as a draft.');
    }

    public function status(Template $template): RedirectResponse
    {
        $this->authorize('update', $template);
        $template->update(['status' => $template->status === 'published' ? 'disabled' : 'published']);

        return back()->with('status', 'Template status updated.');
    }

    public function featured(Template $template, Request $request): RedirectResponse
    {
        $this->authorize('update', $template);
        $template->update(['config' => array_merge($template->config ?? [], [
            'is_featured' => ! $template->is_featured,
            'updated_by_user_id' => $request->user()->id,
        ])]);

        return back()->with('status', 'Featured status updated.');
    }

    public function destroy(Template $template): RedirectResponse
    {
        $this->authorize('delete', $template);
        if ($template->resumes()->exists()) {
            return back()->withErrors(['delete' => 'This template is in use. Disable it instead of deleting it.']);
        }
        $this->uploads->deleteSource($template);
        $template->delete();

        return redirect()->route('admin.templates')->with('status', 'Template deleted.');
    }

    private function form(?Template $template = null): View
    {
        return view('admin.templates.form', [
            'template' => $template,
            'categories' => TemplateCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'sections' => collect($this->renderer->placeholderLabels())->only(['summary', 'experiences', 'education', 'skills', 'projects', 'certifications', 'languages', 'awards', 'references']),
            'placeholders' => $this->renderer->placeholderLabels(),
        ]);
    }

    private function payload(array $data, ?int $createdBy, ?int $updatedBy = null): array
    {
        return [
            'template_category_id' => $data['template_category_id'] ?? null,
            'created_by_user_id' => $createdBy,
            'name' => $data['name'], 'slug' => Str::slug($data['slug']),
            'description' => $data['description'] ?? null, 'status' => $data['status'],
            'is_premium' => (bool) ($data['is_premium'] ?? false), 'sort_order' => $data['sort_order'],
            'config' => [
                'primary_color' => $data['primary_color'], 'font_family' => $data['font_family'],
                'supported_sections' => array_values($data['supported_sections']),
                'preview_images' => array_values(array_filter($data['preview_images'] ?? [])),
                'is_featured' => (bool) ($data['is_featured'] ?? false),
                'updated_by_user_id' => $updatedBy ?? $createdBy,
            ],
        ];
    }

    private function uniqueSlug(string $base): string
    {
        $slug = Str::slug($base); $candidate = $slug; $index = 2;
        while (Template::withTrashed()->where('slug', $candidate)->exists()) {
            $candidate = $slug.'-'.$index++;
        }
        return $candidate;
    }
}
