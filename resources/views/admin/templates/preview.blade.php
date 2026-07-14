<x-admin-layout :title="$template->name.' Preview'">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3"><div><a href="{{ route('admin.templates') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-600">← Back to Templates</a><p class="mt-2 text-sm text-slate-500">Demo rendering · Version {{ $template->version }} · {{ Str::headline($template->status) }}</p></div><div class="flex gap-2"><a href="{{ route('admin.templates.edit',$template) }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold">Edit</a><form method="POST" action="{{ route('admin.templates.duplicate',$template) }}">@csrf<button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Duplicate</button></form></div></div>
        <section class="rounded-xl border border-slate-200 bg-slate-100 p-4 shadow-sm"><div class="mx-auto aspect-[210/297] w-full max-w-[794px] overflow-hidden bg-white shadow-xl"><iframe title="{{ $template->name }} preview" srcdoc="{{ e($renderedHtml) }}" class="h-full w-full border-0"></iframe></div></section>
    </div>
</x-admin-layout>
