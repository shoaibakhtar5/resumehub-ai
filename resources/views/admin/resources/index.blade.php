<x-admin-layout :title="$definition['title']">
    <div x-data="{ selected: [], allIds: @js($records->pluck('id')->map(fn ($id) => (string) $id)->values()) }">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-slate-500">Search, filter, sort, and manage {{ strtolower($definition['title']) }}.</p>
            </div>
            @unless ($definition['readonly'])
                <a href="{{ route('admin.resources.create', ['resource' => $resource]) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                    <x-ui.icon name="plus" class="h-4 w-4" /> Create {{ Str::singular($definition['title']) }}
                </a>
            @endunless
        </div>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <form method="GET" class="grid gap-3 border-b border-slate-200 p-4 md:grid-cols-[minmax(220px,1fr)_180px_100px_auto]" x-data="{ filtering: false }" @submit="filtering = true">
                <label class="flex items-center rounded-lg border border-slate-200 px-3 focus-within:border-indigo-400 focus-within:ring-2 focus-within:ring-indigo-100">
                    <x-ui.icon name="magnifying-glass" class="h-4 w-4 text-slate-400" />
                    <input name="search" value="{{ request('search') }}" type="search" class="w-full border-0 bg-transparent px-2 py-2 text-sm focus:ring-0" placeholder="Search {{ strtolower($definition['title']) }}...">
                </label>
                <select name="status" class="rounded-lg border-slate-200 py-2 text-sm focus:border-indigo-400 focus:ring-indigo-200">
                    <option value="">All statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="published" @selected(request('status') === 'published')>Published</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <select name="per_page" class="rounded-lg border-slate-200 py-2 text-sm focus:border-indigo-400 focus:ring-indigo-200">
                    @foreach ([10, 15, 25, 50] as $size)<option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }} rows</option>@endforeach
                </select>
                <button :disabled="filtering" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-60"><span x-text="filtering ? 'Loading...' : 'Apply'">Apply</span></button>
            </form>

            @unless ($definition['readonly'])
                <form x-show="selected.length" x-cloak method="POST" action="{{ route('admin.resources.bulk', ['resource' => $resource]) }}" class="flex flex-wrap items-center gap-3 border-b border-indigo-100 bg-indigo-50 px-4 py-3" @submit="if (!confirm('Apply this action to the selected records?')) $event.preventDefault()">
                    @csrf
                    <template x-for="id in selected" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
                    <span class="text-sm font-semibold text-indigo-800"><span x-text="selected.length"></span> selected</span>
                    <select name="action" required class="rounded-lg border-indigo-200 py-1.5 text-sm focus:border-indigo-400 focus:ring-indigo-200">
                        <option value="">Bulk action</option><option value="activate">Activate</option><option value="deactivate">Deactivate</option><option value="delete">Delete</option>
                    </select>
                    <button class="rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-700">Apply</button>
                </form>
            @endunless

            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left">
                    <thead class="border-b border-slate-200 bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                        <tr>
                            @unless ($definition['readonly'])
                                <th class="w-12 px-4 py-3"><input type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @change="selected = $event.target.checked ? [...allIds] : []" :checked="allIds.length && selected.length === allIds.length" aria-label="Select all rows"></th>
                            @endunless
                            @foreach ($definition['columns'] as $column)
                                @php $sortable = ! str_contains($column, '.'); $nextDirection = request('sort') === $column && request('direction') === 'asc' ? 'desc' : 'asc'; @endphp
                                <th class="px-4 py-3 font-semibold">
                                    @if ($sortable)
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDirection]) }}" class="inline-flex items-center gap-1 hover:text-indigo-600">
                                            {{ Str::headline($column) }}
                                            @if (request('sort') === $column)<span>{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>@endif
                                        </a>
                                    @else
                                        {{ Str::headline(last(explode('.', $column))) }}
                                    @endif
                                </th>
                            @endforeach
                            <th class="px-4 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($records as $record)
                            <tr class="transition hover:bg-slate-50/80">
                                @unless ($definition['readonly'])
                                    <td class="px-4 py-3"><input type="checkbox" value="{{ $record->getKey() }}" x-model="selected" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" aria-label="Select row"></td>
                                @endunless
                                @foreach ($definition['columns'] as $column)
                                    @php $value = data_get($record, $column); @endphp
                                    <td class="max-w-[260px] px-4 py-3 text-slate-600">
                                        @if (is_bool($value))
                                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $value ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $value ? 'Yes' : 'No' }}</span>
                                        @elseif ($value instanceof \Carbon\CarbonInterface)
                                            <span title="{{ $value->toDateTimeString() }}">{{ $value->diffForHumans() }}</span>
                                        @elseif (in_array($column, ['price_cents', 'amount_cents'], true))
                                            <span class="font-semibold text-slate-800">${{ number_format(((int) $value) / 100, 2) }}</span>
                                        @elseif (is_array($value))
                                            <span class="block truncate">{{ data_get($value, 'title', json_encode($value)) }}</span>
                                        @elseif (in_array($column, ['status'], true))
                                            <span class="rounded-full bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700">{{ Str::headline((string) $value) }}</span>
                                        @else
                                            <span class="block truncate {{ $loop->first ? 'font-semibold text-slate-800' : '' }}">{{ filled($value) ? $value : '—' }}</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.resources.show', ['resource' => $resource, 'id' => $record->getKey()]) }}" class="rounded-lg p-2 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600" title="View"><x-ui.icon name="eye" class="h-4 w-4" /></a>
                                        @unless ($definition['readonly'])
                                            <a href="{{ route('admin.resources.edit', ['resource' => $resource, 'id' => $record->getKey()]) }}" class="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-blue-600" title="Edit"><x-ui.icon name="pencil-square" class="h-4 w-4" /></a>
                                            <form method="POST" action="{{ route('admin.resources.destroy', ['resource' => $resource, 'id' => $record->getKey()]) }}" onsubmit="return confirm('Delete this record?')">@csrf @method('DELETE')<button class="rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-600" title="Delete"><x-ui.icon name="trash" class="h-4 w-4" /></button></form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($definition['columns']) + ($definition['readonly'] ? 1 : 2) }}" class="px-6 py-16 text-center"><span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500"><x-ui.icon name="inbox-stack" /></span><p class="mt-3 font-semibold text-slate-700">No records found</p><p class="mt-1 text-sm text-slate-500">Try a different search or create the first record.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($records->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">{{ $records->links() }}</div>
            @endif
        </section>
    </div>
</x-admin-layout>
