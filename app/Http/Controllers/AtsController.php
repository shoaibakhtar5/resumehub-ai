<?php

namespace App\Http\Controllers;

use App\Http\Requests\AtsReportRequest;
use App\Models\AtsReport;
use App\Services\Ats\AtsCheckerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AtsController extends Controller
{
    public function __construct(private readonly AtsCheckerService $ats)
    {
    }

    public function index(Request $request): View
    {
        $reports = AtsReport::query()
            ->where('user_id', $request->user()->id)
            ->with(['resume', 'keywords', 'issues'])
            ->latest('scanned_at')
            ->limit(10)
            ->get();

        return view('dashboard.page', [
            'mode' => 'user',
            'page' => $this->page($request, $reports->first()),
            'reports' => $reports,
            'resumes' => $request->user()->resumes()->latest('updated_at')->get(),
        ]);
    }

    public function store(AtsReportRequest $request): RedirectResponse
    {
        $report = $this->ats->scan($request->user(), $request->validated());

        return redirect()->route('ats.reports.show', $report)->with('status', 'ATS scan complete.');
    }

    public function show(Request $request, AtsReport $report): View
    {
        abort_unless($report->user_id === $request->user()->id || $request->user()->is_admin, 403);

        return view('dashboard.page', [
            'mode' => 'user',
            'page' => $this->page($request, $report->load(['keywords', 'issues', 'resume'])),
            'reports' => collect([$report]),
            'resumes' => $request->user()->resumes()->latest('updated_at')->get(),
        ]);
    }

    private function page(Request $request, ?AtsReport $report): array
    {
        $page = config('resumehub.user_pages.ats-checker');
        $scanCount = AtsReport::query()->where('user_id', $request->user()->id)->count();

        $page['stats'] = [
            ['label' => 'Latest ATS score', 'value' => $report ? round($report->ats_score).'%' : '0%', 'icon' => 'shield-check', 'tone' => $report && $report->ats_score >= 80 ? 'success' : 'warning'],
            ['label' => 'Scans run', 'value' => (string) $scanCount, 'icon' => 'document-magnifying-glass'],
            ['label' => 'Keyword score', 'value' => $report ? round($report->keyword_score).'%' : '0%', 'icon' => 'command-line'],
        ];

        return $page;
    }
}
