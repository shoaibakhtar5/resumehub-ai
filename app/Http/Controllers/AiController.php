<?php

namespace App\Http\Controllers;

use App\Http\Requests\AiActionRequest;
use App\Models\AiHistory;
use App\Models\Resume;
use App\Services\Ai\ResumeAiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiController extends Controller
{
    public function __construct(private readonly ResumeAiService $ai)
    {
    }

    public function studio(Request $request): View
    {
        return view('dashboard.page', [
            'mode' => 'user',
            'page' => $this->page('ai-resume-studio', $request),
            'aiHistories' => AiHistory::query()->where('user_id', $request->user()->id)->latest()->limit(10)->get(),
            'resumes' => $request->user()->resumes()->latest('updated_at')->get(),
        ]);
    }

    public function tool(Request $request, string $page): View
    {
        return view('dashboard.page', [
            'mode' => 'user',
            'page' => $this->page($page, $request),
            'aiHistories' => AiHistory::query()->where('user_id', $request->user()->id)->where('feature', $page)->latest()->limit(10)->get(),
            'resumes' => $request->user()->resumes()->latest('updated_at')->get(),
        ]);
    }

    public function generate(AiActionRequest $request): RedirectResponse
    {
        $history = $this->ai->generate($request->user(), $request->validated());

        return back()->with('status', 'AI suggestion generated.')->with('ai_output', $history->output);
    }

    private function page(string $key, Request $request): array
    {
        $page = config("resumehub.user_pages.{$key}", config('resumehub.user_pages.ai-resume-studio'));
        $historyCount = AiHistory::query()->where('user_id', $request->user()->id)->count();
        $resumeCount = Resume::query()->where('user_id', $request->user()->id)->count();

        $page['stats'] = [
            ['label' => 'AI generations', 'value' => (string) $historyCount, 'icon' => 'sparkles', 'tone' => 'ai'],
            ['label' => 'Resumes available', 'value' => (string) $resumeCount, 'icon' => 'document-text'],
            ['label' => 'Last run', 'value' => optional(AiHistory::query()->where('user_id', $request->user()->id)->latest()->first())->created_at?->diffForHumans() ?? 'None', 'icon' => 'clock'],
        ];

        return $page;
    }
}
