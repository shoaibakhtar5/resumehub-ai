<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function index(Request $request): View
    {
        $templates = Template::query()
            ->with('category')
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('dashboard.templates', [
            'templates' => $templates,
            'categories' => $templates->pluck('category.name')->filter()->unique()->values(),
            'resume' => $request->user()->resumes()->latest('updated_at')->first(),
        ]);
    }

    public function apply(Request $request, Template $template): RedirectResponse
    {
        $this->authorize('apply', $template);

        $resume = $request->user()->resumes()->latest('updated_at')->first();

        if (! $resume) {
            return redirect()->route('resume.builder', ['template' => $template->id]);
        }

        $resume->forceFill(['template_id' => $template->id])->save();

        return redirect()->route('resumes.edit', $resume)->with('status', 'Template applied.');
    }
}
