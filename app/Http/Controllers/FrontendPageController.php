<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactMessageRequest;
use App\Models\AiHistory;
use App\Models\Blog;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FrontendPageController extends Controller
{
    public function home(): View
    {
        return view('landing.home');
    }

    public function about(): View
    {
        return view('landing.show', [
            'page' => [
                'title' => 'About ResumeHub AI',
                'eyebrow' => 'Built for modern job search',
                'description' => 'ResumeHub AI turns resume creation into a focused product workflow: structured content, premium presentation, ATS confidence, and AI support that stays practical.',
                'sections' => [
                    ['icon' => 'sparkles', 'title' => 'AI where it helps', 'body' => 'Our product guides job seekers through clearer positioning, stronger bullets, and role-specific language without making every resume sound the same.'],
                    ['icon' => 'shield-check', 'title' => 'Designed for trust', 'body' => 'The interface emphasizes accessible forms, transparent scoring, controlled sharing, and dependable export behavior.'],
                    ['icon' => 'squares-2x2', 'title' => 'Templates with restraint', 'body' => 'Every template balances visual polish with parser safety, consistent hierarchy, and readable spacing.'],
                ],
            ],
        ]);
    }

    public function features(): View
    {
        return view('landing.features');
    }

    public function pricing(): View
    {
        return view('landing.pricing');
    }

    public function contact(): View
    {
        return view('landing.contact');
    }

    public function contactSubmit(ContactMessageRequest $request)
    {
        $validated = $request->validated();

        ContactMessage::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['topic'],
            'message' => $validated['message'],
            'source' => 'contact-page',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => ['url' => $request->fullUrl()],
        ]);

        return back()->with('status', 'Your message has been received by ResumeHub AI.');
    }

    public function faq(): View
    {
        return view('landing.faq');
    }

    public function terms(): View
    {
        return view('landing.legal', [
            'page' => [
                'title' => 'Terms of Service',
                'eyebrow' => 'Legal',
                'description' => 'Clear terms for using ResumeHub AI, managing your account, creating content, and exporting career documents.',
                'updated' => 'Updated July 9, 2026',
            ],
        ]);
    }

    public function privacy(): View
    {
        return view('landing.legal', [
            'page' => [
                'title' => 'Privacy Policy',
                'eyebrow' => 'Privacy',
                'description' => 'How ResumeHub AI handles resume content, account information, analytics, AI processing, and shared links.',
                'updated' => 'Updated July 9, 2026',
            ],
        ]);
    }

    public function blogIndex(): View
    {
        $posts = Blog::query()
            ->with('category')
            ->where('status', 'published')
            ->latest('published_at')
            ->get()
            ->map(fn (Blog $post) => [
                'slug' => $post->slug,
                'title' => $post->title,
                'excerpt' => $post->excerpt,
                'date' => $post->published_at?->format('M j, Y') ?? $post->created_at->format('M j, Y'),
                'read' => max(3, (int) ceil(str_word_count(strip_tags($post->content)) / 220)).' min read',
                'category' => $post->category?->name ?? 'Guides',
            ])
            ->values()
            ->all();

        return view('landing.blog-index', [
            'posts' => $posts ?: config('resumehub.blog_posts'),
        ]);
    }

    public function blogShow(string $slug): View
    {
        $blog = Blog::query()
            ->with('category')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        $post = $blog ? [
            'slug' => $blog->slug,
            'title' => $blog->title,
            'excerpt' => $blog->excerpt,
            'date' => $blog->published_at?->format('M j, Y') ?? $blog->created_at->format('M j, Y'),
            'read' => max(3, (int) ceil(str_word_count(strip_tags($blog->content)) / 220)).' min read',
            'category' => $blog->category?->name ?? 'Guides',
            'content' => $blog->content,
        ] : collect(config('resumehub.blog_posts'))->firstWhere('slug', $slug);

        abort_unless($post, 404);

        return view('landing.blog-show', ['post' => $post]);
    }

    public function dashboard(): View
    {
        $user = request()->user();
        $resumes = $user->resumes()->with(['profile', 'template'])->latest('updated_at')->limit(5)->get();
        $latestReport = $user->resumes()
            ->join('ats_reports', 'resumes.id', '=', 'ats_reports.resume_id')
            ->where('ats_reports.user_id', $user->id)
            ->orderByDesc('ats_reports.scanned_at')
            ->value('ats_reports.ats_score');

        return view('dashboard.index', [
            'resumes' => $resumes,
            'stats' => [
                'resume_score' => (int) round($user->resumes()->avg('completion_score') ?: 0),
                'ats_score' => (int) round($latestReport ?: 0),
                'ai_rewrites' => AiHistory::query()->where('user_id', $user->id)->count(),
                'recruiter_opens' => $user->resumes()->withCount('shares')->get()->sum('shares_count'),
            ],
        ]);
    }

    public function templates(): View
    {
        return view('dashboard.templates');
    }

    public function builder(): View
    {
        return view('dashboard.builder');
    }

    public function preview(): View
    {
        return view('dashboard.preview');
    }

    public function userPage(string $page): View
    {
        $pages = config('resumehub.user_pages');

        abort_unless(isset($pages[$page]), 404);

        return view('dashboard.page', [
            'mode' => 'user',
            'page' => $pages[$page],
        ]);
    }

    public function adminPage(string $page = 'dashboard'): View
    {
        $pages = config('resumehub.admin_pages');

        abort_unless(isset($pages[$page]), 404);

        return view('admin.page', [
            'mode' => 'admin',
            'page' => $pages[$page],
        ]);
    }

    public function otp(): View
    {
        return view('auth.otp-verification');
    }

    public function twoFactor(): View
    {
        return view('auth.two-factor');
    }

    public function notFound(): Response
    {
        return response()->view('errors.404', [], 404);
    }
}
