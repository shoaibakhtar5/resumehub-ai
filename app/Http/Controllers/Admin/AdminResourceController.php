<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminResourceRequest;
use App\Models\ActivityLog;
use App\Models\AnalyticsEvent;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\ContactMessage;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Resume;
use App\Models\Role;
use App\Models\SeoSetting;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\Template;
use App\Models\TemplateCategory;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminResourceController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.page', [
            'mode' => 'admin',
            'page' => $this->page('dashboard'),
            'adminStats' => $this->stats(),
        ]);
    }

    public function index(string $resource): View
    {
        $definition = $this->definition($resource);
        abort_unless($definition, 404);

        $query = $definition['model']::query();
        $query = in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($definition['model']), true)
            ? $query->withoutTrashed()
            : $query;

        $records = $query->latest('id')->paginate(15);

        return view('admin.page', [
            'mode' => 'admin',
            'page' => $this->page($definition['page'] ?? $resource),
            'resource' => $resource,
            'definition' => $definition,
            'records' => $records,
            'adminStats' => $this->stats(),
        ]);
    }

    public function store(AdminResourceRequest $request, string $resource, MediaService $media): RedirectResponse
    {
        $definition = $this->definition($resource);
        abort_unless($definition && ! ($definition['readonly'] ?? false), 404);

        if ($resource === 'media-library' && $request->hasFile('file')) {
            $media->store($request->file('file'), 'admin-media', $request->user(), null, ['alt_text' => $request->string('name')->toString()]);

            return back()->with('status', 'Media uploaded.');
        }

        if ($resource === 'media-library') {
            return back()->withErrors(['file' => 'Choose a file to upload.']);
        }

        $payload = $this->payload($request, $resource, $definition);
        $definition['model']::query()->create($payload);

        return back()->with('status', Str::headline($resource).' created.');
    }

    public function update(AdminResourceRequest $request, string $resource, int $id): RedirectResponse
    {
        $definition = $this->definition($resource);
        abort_unless($definition && ! ($definition['readonly'] ?? false), 404);
        $record = $definition['model']::query()->findOrFail($id);
        $record->fill($this->payload($request, $resource, $definition, $record))->save();

        return back()->with('status', Str::headline($resource).' updated.');
    }

    public function destroy(string $resource, int $id): RedirectResponse
    {
        $definition = $this->definition($resource);
        abort_unless($definition && ! ($definition['readonly'] ?? false), 404);
        $definition['model']::query()->findOrFail($id)->delete();

        return back()->with('status', Str::headline($resource).' deleted.');
    }

    private function payload(AdminResourceRequest $request, string $resource, array $definition, ?Model $record = null): array
    {
        $data = $request->validated();
        $payload = Arr::only($data, $definition['fillable']);

        if (in_array('slug', $definition['fillable'], true) && blank($payload['slug'] ?? null)) {
            $payload['slug'] = Str::slug($payload['title'] ?? $payload['name'] ?? Str::random(8));
        }

        if ($resource === 'users') {
            if (blank($payload['password'] ?? null)) {
                $payload = Arr::except($payload, ['password']);
            } else {
                $payload['password'] = Hash::make($payload['password']);
            }

            $payload['is_admin'] = (bool) ($data['is_admin'] ?? false);
            $payload['status'] = $payload['status'] ?? 'active';
        }

        if (in_array($resource, ['website-settings', 'ai-settings'], true)) {
            $payload['group'] = $resource === 'ai-settings' ? 'ai' : 'site';
            $payload['type'] = 'string';
            $payload['value'] = ['text' => $data['value'] ?? ''];
            $payload['updated_by_user_id'] = $request->user()->id;
        }

        if ($resource === 'seo-settings') {
            $payload['schema_json'] = [];
        }

        if (in_array($resource, ['templates', 'template-upload'], true)) {
            $payload['status'] = $payload['status'] ?? 'published';
            $payload['version'] = '1.0.0';
            $payload['entry_html'] = 'resume.html';
            $payload['entry_css'] = 'style.css';
            $payload['is_premium'] = (bool) ($data['is_premium'] ?? false);
        }

        if ($resource === 'blog') {
            $payload['content'] = $data['body'] ?? $data['description'] ?? $data['excerpt'] ?? '';
            $payload['author_user_id'] = $request->user()->id;
            $payload['published_at'] = ($payload['status'] ?? null) === 'published' ? now() : null;
        }

        if ($resource === 'contact-messages' && ($payload['status'] ?? null) === 'responded') {
            $payload['responded_at'] = now();
        }

        return $payload;
    }

    private function definition(string $resource): ?array
    {
        $definitions = [
            'users' => [
                'model' => User::class,
                'columns' => ['name', 'email', 'status', 'is_admin'],
                'fields' => ['name' => 'text', 'email' => 'email', 'password' => 'password', 'status' => 'text', 'is_admin' => 'checkbox'],
                'fillable' => ['name', 'email', 'password', 'status', 'is_admin'],
            ],
            'resumes' => [
                'model' => Resume::class,
                'columns' => ['title', 'target_role', 'status', 'completion_score'],
                'fields' => ['title' => 'text', 'target_role' => 'text', 'status' => 'text'],
                'fillable' => ['title', 'target_role', 'status'],
                'readonly' => true,
            ],
            'templates' => [
                'model' => Template::class,
                'columns' => ['name', 'slug', 'status', 'is_premium'],
                'fields' => ['name' => 'text', 'slug' => 'text', 'description' => 'textarea', 'status' => 'text', 'is_premium' => 'checkbox'],
                'fillable' => ['name', 'slug', 'description', 'status', 'is_premium'],
            ],
            'template-upload' => [
                'model' => Template::class,
                'page' => 'template-upload',
                'columns' => ['name', 'slug', 'status', 'is_premium'],
                'fields' => ['name' => 'text', 'slug' => 'text', 'description' => 'textarea', 'status' => 'text', 'is_premium' => 'checkbox'],
                'fillable' => ['name', 'slug', 'description', 'status', 'is_premium'],
            ],
            'blog' => [
                'model' => Blog::class,
                'columns' => ['title', 'slug', 'status', 'published_at'],
                'fields' => ['title' => 'text', 'slug' => 'text', 'excerpt' => 'textarea', 'body' => 'textarea', 'status' => 'text'],
                'fillable' => ['title', 'slug', 'excerpt', 'status'],
            ],
            'categories' => [
                'model' => TemplateCategory::class,
                'columns' => ['name', 'slug', 'is_active', 'sort_order'],
                'fields' => ['name' => 'text', 'slug' => 'text', 'description' => 'textarea', 'is_active' => 'checkbox'],
                'fillable' => ['name', 'slug', 'description', 'is_active'],
            ],
            'tags' => [
                'model' => BlogTag::class,
                'columns' => ['name', 'slug', 'description'],
                'fields' => ['name' => 'text', 'slug' => 'text', 'description' => 'textarea'],
                'fillable' => ['name', 'slug', 'description'],
            ],
            'team' => [
                'model' => TeamMember::class,
                'columns' => ['name', 'role', 'email', 'is_active'],
                'fields' => ['name' => 'text', 'role' => 'text', 'email' => 'email', 'bio' => 'textarea', 'is_active' => 'checkbox'],
                'fillable' => ['name', 'role', 'email', 'bio', 'is_active'],
            ],
            'website-settings' => [
                'model' => Setting::class,
                'columns' => ['group', 'key', 'value', 'is_public'],
                'fields' => ['key' => 'text', 'value' => 'textarea'],
                'fillable' => ['key', 'value'],
            ],
            'seo-settings' => [
                'model' => SeoSetting::class,
                'columns' => ['page_key', 'title', 'robots'],
                'fields' => ['page_key' => 'text', 'title' => 'text', 'description' => 'textarea', 'keywords' => 'textarea', 'robots' => 'text'],
                'fillable' => ['page_key', 'title', 'description', 'keywords', 'robots'],
            ],
            'ai-settings' => [
                'model' => Setting::class,
                'columns' => ['group', 'key', 'value', 'is_public'],
                'fields' => ['key' => 'text', 'value' => 'textarea'],
                'fillable' => ['key', 'value'],
            ],
            'analytics' => [
                'model' => AnalyticsEvent::class,
                'columns' => ['event_name', 'event_category', 'occurred_at'],
                'fields' => [],
                'fillable' => [],
                'readonly' => true,
            ],
            'contact-messages' => [
                'model' => ContactMessage::class,
                'columns' => ['name', 'email', 'subject', 'status'],
                'fields' => ['name' => 'text', 'email' => 'email', 'subject' => 'text', 'message' => 'textarea', 'status' => 'text'],
                'fillable' => ['name', 'email', 'subject', 'message', 'status'],
            ],
            'media-library' => [
                'model' => Media::class,
                'columns' => ['original_name', 'mime_type', 'size_bytes', 'disk'],
                'fields' => ['name' => 'text', 'file' => 'file'],
                'fillable' => [],
            ],
            'roles' => [
                'model' => Role::class,
                'columns' => ['name', 'guard_name', 'is_system'],
                'fields' => ['name' => 'text', 'description' => 'textarea'],
                'fillable' => ['name', 'description'],
            ],
            'permissions' => [
                'model' => Permission::class,
                'columns' => ['name', 'guard_name', 'is_system'],
                'fields' => ['name' => 'text', 'description' => 'textarea'],
                'fillable' => ['name', 'description'],
            ],
            'logs' => [
                'model' => ActivityLog::class,
                'columns' => ['event', 'description', 'created_at'],
                'fields' => [],
                'fillable' => [],
                'readonly' => true,
            ],
        ];

        return $definitions[$resource] ?? null;
    }

    private function page(string $key): array
    {
        $page = config("resumehub.admin_pages.{$key}", config('resumehub.admin_pages.dashboard'));
        $stats = $this->stats();
        $page['stats'] = [
            ['label' => 'Users', 'value' => (string) $stats['users'], 'icon' => 'users'],
            ['label' => 'Resumes', 'value' => (string) $stats['resumes'], 'icon' => 'document-text'],
            ['label' => 'Templates', 'value' => (string) $stats['templates'], 'icon' => 'squares-2x2'],
            ['label' => 'Open messages', 'value' => (string) $stats['messages'], 'icon' => 'inbox-stack', 'tone' => $stats['messages'] ? 'warning' : 'success'],
        ];

        return $page;
    }

    private function stats(): array
    {
        return [
            'users' => User::query()->count(),
            'resumes' => Resume::query()->count(),
            'templates' => Template::query()->count(),
            'messages' => ContactMessage::query()->where('status', 'new')->count(),
        ];
    }
}
