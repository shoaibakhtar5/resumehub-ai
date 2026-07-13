<x-admin-layout :title="'View '.Str::singular($definition['title'])">
    <div class="mx-auto max-w-4xl">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.'.$resource) }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-600">← Back to {{ $definition['title'] }}</a>
            @unless ($definition['readonly'])
                <a href="{{ route('admin.resources.edit', ['resource' => $resource, 'id' => $record->getKey()]) }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"><x-ui.icon name="pencil-square" class="h-4 w-4" /> Edit</a>
            @endunless
        </div>
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5"><h2 class="font-display text-lg font-semibold">Record details</h2><p class="mt-1 text-sm text-slate-500">Database values for this {{ strtolower(Str::singular($definition['title'])) }}.</p></div>
            <dl class="grid gap-x-8 md:grid-cols-2">
                @foreach ($record->getAttributes() as $key => $value)
                    @continue(in_array($key, ['password', 'remember_token'], true))
                    @php
                        $castValue = data_get($record, $key);
                        if (is_array($castValue)) $castValue = json_encode($castValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        if ($castValue instanceof \Carbon\CarbonInterface) $castValue = $castValue->format('M j, Y g:i A');
                        if (is_bool($castValue)) $castValue = $castValue ? 'Yes' : 'No';
                    @endphp
                    <div class="border-b border-slate-100 px-6 py-4"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ Str::headline($key) }}</dt><dd class="mt-1 whitespace-pre-wrap break-words text-sm text-slate-800">{{ filled($castValue) ? $castValue : '—' }}</dd></div>
                @endforeach
            </dl>
        </section>
    </div>
</x-admin-layout>
