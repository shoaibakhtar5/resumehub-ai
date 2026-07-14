@php($sharedView = $sharedView ?? false)
<x-app-layout title="Resume Preview" mode="user">
    @if (! $resume)
        <x-ui.empty-state icon="document-text" title="No resume to preview" description="Create your first resume, then return here to export, share, and review it.">
            <x-ui.button href="{{ route('resume.builder') }}" icon="plus-circle">Create Resume</x-ui.button>
        </x-ui.empty-state>
    @else
        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
            <section class="rounded-2xl border border-slate-200 bg-slate-100 p-3 shadow-sm sm:p-5">
                <div class="mx-auto aspect-[210/297] w-full max-w-[794px] overflow-hidden bg-white shadow-xl">
                    <iframe title="{{ $resume->title }} preview" srcdoc="{{ e($renderedHtml) }}" class="h-full w-full border-0"></iframe>
                </div>
            </section>
            <aside class="space-y-4">
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-display text-lg font-semibold text-slate-900">{{ $resume->title }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $resume->template?->name ?? 'Default Professional' }}</p>
                    @unless($sharedView)
                        <div class="mt-5 grid gap-2">
                            <a href="{{ route('resumes.edit',$resume) }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white">Continue editing</a>
                            <form method="POST" action="{{ route('resumes.download',$resume) }}">@csrf<input type="hidden" name="format" value="pdf"><button class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700">Download PDF</button></form>
                        </div>
                    @endunless
                </section>
                @if($latestReport)<section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Latest ATS score</p><p class="mt-2 text-3xl font-bold text-slate-900">{{ $latestReport->overall_score ?? 0 }}%</p></section>@endif
            </aside>
        </div>
    @endif
</x-app-layout>
