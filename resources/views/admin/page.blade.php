@php
    $definition = $definition ?? null;
    $records = $records ?? null;
    $resource = $resource ?? null;
@endphp

<x-app-layout :title="$page['title']" mode="admin">
    <div class="space-y-8">
        <x-ui.page-header :eyebrow="$page['eyebrow']" :title="$page['title']" :description="$page['description']">
            <x-ui.button href="{{ route('admin.analytics') }}" icon="presentation-chart-line">View Analytics</x-ui.button>
            <x-ui.button href="{{ route('admin.logs') }}" variant="secondary" icon="server-stack">Open Logs</x-ui.button>
        </x-ui.page-header>

        @if (session('status'))
            <x-ui.card class="border-success/30 bg-success/10 text-on-surface">{{ session('status') }}</x-ui.card>
        @endif

        <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($page['stats'] as $stat)
                <x-ui.stat-card
                    :label="$stat['label']"
                    :value="$stat['value']"
                    :icon="$stat['icon']"
                    :trend="$stat['trend'] ?? null"
                    :tone="$stat['tone'] ?? 'primary'"
                />
            @endforeach
        </section>

        @if ($definition && ! ($definition['readonly'] ?? false))
            <x-ui.card>
                <h2 class="font-display text-headline-md text-on-surface">Create {{ str($resource)->replace('-', ' ')->headline() }}</h2>
                <form method="POST" action="{{ route('admin.resources.store', $resource) }}" enctype="multipart/form-data" class="mt-6 grid gap-5 lg:grid-cols-2">
                    @csrf
                    @foreach ($definition['fields'] as $field => $type)
                        @if ($type === 'textarea')
                            <x-ui.textarea :label="str($field)->replace('_', ' ')->headline()" :name="$field">{{ old($field) }}</x-ui.textarea>
                        @elseif ($type === 'checkbox')
                            <label class="mt-8 inline-flex items-center gap-2 text-body-sm text-on-surface-variant">
                                <input type="checkbox" name="{{ $field }}" value="1" class="rounded border-border-light text-primary focus:ring-primary" @checked(old($field))>
                                {{ str($field)->replace('_', ' ')->headline() }}
                            </label>
                        @else
                            <x-ui.input :label="str($field)->replace('_', ' ')->headline()" :name="$field" :type="$type" :value="old($field)" />
                        @endif
                    @endforeach
                    <div class="lg:col-span-2">
                        <x-ui.button type="submit" icon="plus-circle">Create</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        @endif

        @if ($records)
            <section class="grid gap-5">
                @forelse ($records as $record)
                    <x-ui.card>
                        <div class="grid gap-4 xl:grid-cols-[1fr_1.2fr]">
                            <div>
                                <h2 class="font-display text-headline-md text-on-surface">{{ $record->title ?? $record->name ?? $record->email ?? $record->original_name ?? 'Record #'.$record->id }}</h2>
                                <dl class="mt-5 grid gap-3 sm:grid-cols-2">
                                    @foreach ($definition['columns'] as $column)
                                        @php
                                            $value = data_get($record, $column);
                                            if ($value instanceof \Illuminate\Support\Carbon) {
                                                $value = $value->diffForHumans();
                                            } elseif (is_bool($value)) {
                                                $value = $value ? 'Yes' : 'No';
                                            } elseif (is_array($value)) {
                                                $value = json_encode($value);
                                            }
                                        @endphp
                                        <div>
                                            <dt class="text-label-sm uppercase text-on-surface-variant">{{ str($column)->replace('_', ' ')->headline() }}</dt>
                                            <dd class="mt-1 text-body-sm text-on-surface">{{ filled($value) ? $value : 'None' }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>

                            @if (! ($definition['readonly'] ?? false))
                                <div class="rounded-lg border border-border-light p-4">
                                    <form method="POST" action="{{ route('admin.resources.update', [$resource, $record->id]) }}" class="grid gap-4 sm:grid-cols-2">
                                        @csrf
                                        @method('PATCH')
                                        @foreach ($definition['fields'] as $field => $type)
                                            @php($value = old($field, $field === 'password' ? '' : data_get($record, $field)))
                                            @if ($type === 'file')
                                                @continue
                                            @elseif ($type === 'textarea')
                                                <x-ui.textarea :label="str($field)->replace('_', ' ')->headline()" :name="$field">{{ is_array($value) ? json_encode($value) : $value }}</x-ui.textarea>
                                            @elseif ($type === 'checkbox')
                                                <label class="mt-8 inline-flex items-center gap-2 text-body-sm text-on-surface-variant">
                                                    <input type="checkbox" name="{{ $field }}" value="1" class="rounded border-border-light text-primary focus:ring-primary" @checked((bool) $value)>
                                                    {{ str($field)->replace('_', ' ')->headline() }}
                                                </label>
                                            @else
                                                <x-ui.input :label="str($field)->replace('_', ' ')->headline()" :name="$field" :type="$type" :value="$value" />
                                            @endif
                                        @endforeach
                                        <div class="flex flex-wrap gap-2 sm:col-span-2">
                                            <x-ui.button type="submit" size="sm" icon="check">Save</x-ui.button>
                                        </div>
                                    </form>
                                    <form method="POST" action="{{ route('admin.resources.destroy', [$resource, $record->id]) }}" class="mt-3">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" size="sm" variant="danger" icon="trash">Delete</x-ui.button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </x-ui.card>
                @empty
                    <x-ui.empty-state icon="inbox-stack" title="No records yet" description="Create the first record above or seed baseline content." />
                @endforelse
            </section>

            @if (method_exists($records, 'links'))
                <div>{{ $records->links() }}</div>
            @endif
        @else
            <section class="grid gap-5 lg:grid-cols-3">
                @foreach ($page['cards'] as $card)
                    <x-ui.card interactive>
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-ai-accent/10 text-ai-accent">
                            <x-ui.icon :name="$card['icon']" class="h-5 w-5" />
                        </span>
                        <h2 class="mt-5 font-display text-headline-md text-on-surface">{{ $card['title'] }}</h2>
                        <p class="mt-3 text-body-md text-on-surface-variant">{{ $card['body'] }}</p>
                    </x-ui.card>
                @endforeach
            </section>

            <x-ui.table :headers="$page['table']['headers']" :rows="$page['table']['rows']" />
        @endif
    </div>
</x-app-layout>
