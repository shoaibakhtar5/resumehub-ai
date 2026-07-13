@php
    $editing = (bool) $record;
    $formTitle = ($editing ? 'Edit ' : 'Create ').Str::singular($definition['title']);
    $selectedRelations = [
        'role_ids' => $record?->roles?->pluck('id')->map(fn ($id) => (string) $id)->all() ?? [],
        'permission_ids' => $record?->permissions?->pluck('id')->map(fn ($id) => (string) $id)->all() ?? [],
    ];
@endphp
<x-admin-layout :title="$formTitle">
    <div class="mx-auto max-w-4xl">
        <div class="mb-5 flex items-center justify-between">
            <a href="{{ route('admin.'.$resource) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-indigo-600">← Back to {{ $definition['title'] }}</a>
        </div>
        <form method="POST" action="{{ $editing ? route('admin.resources.update', ['resource' => $resource, 'id' => $record->getKey()]) : route('admin.resources.store', ['resource' => $resource]) }}" class="rounded-xl border border-slate-200 bg-white shadow-sm" x-data="{ saving: false }" @submit="saving = true">
            @csrf
            @if ($editing) @method('PATCH') @endif
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="font-display text-lg font-semibold">{{ $formTitle }}</h2>
                <p class="mt-1 text-sm text-slate-500">Fields marked with an asterisk are required.</p>
            </div>
            <div class="grid gap-5 p-6 md:grid-cols-2">
                @foreach ($definition['fields'] as $name => $field)
                    @php
                        $type = $field['type'];
                        $required = ($field['required'] ?? false) || ($resource === 'users' && $name === 'password' && ! $editing);
                        $rawValue = old($name, data_get($record, $name));
                        if ($name === 'body') $rawValue = old($name, $record?->content);
                        if ($name === 'price') $rawValue = old($name, $record ? $record->price_cents / 100 : null);
                        if ($name === 'amount') $rawValue = old($name, $record ? $record->amount_cents / 100 : null);
                        if ($resource === 'settings' && $name === 'value') $rawValue = old($name, data_get($record?->value, 'text'));
                        if ($resource === 'notifications') {
                            if ($name === 'user_id') $rawValue = old($name, $record?->notifiable_id);
                            if ($name === 'title') $rawValue = old($name, data_get($record?->data, 'title'));
                            if ($name === 'message') $rawValue = old($name, data_get($record?->data, 'message'));
                            if ($name === 'status') $rawValue = old($name, $record?->read_at ? 'read' : 'unread');
                        }
                        if ($rawValue instanceof \Carbon\CarbonInterface && $type === 'datetime-local') $rawValue = $rawValue->format('Y-m-d\TH:i');
                    @endphp
                    <label class="{{ in_array($type, ['textarea', 'multiselect'], true) ? 'md:col-span-2' : '' }} {{ $type === 'checkbox' ? 'flex items-center gap-3 rounded-lg border border-slate-200 p-4' : 'block' }}">
                        @if ($type === 'checkbox')
                            <input type="hidden" name="{{ $name }}" value="0">
                            <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, (bool) $rawValue)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-semibold text-slate-700">{{ $field['label'] }}</span>
                        @else
                            <span class="mb-1.5 block text-sm font-semibold text-slate-700">{{ $field['label'] }} @if ($required)<span class="text-rose-500">*</span>@endif</span>
                            @if ($type === 'textarea')
                                <textarea name="{{ $name }}" rows="5" @required($required) class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">{{ $rawValue }}</textarea>
                            @elseif ($type === 'select')
                                <select name="{{ $name }}" @required($required) class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                                    <option value="">Select {{ strtolower($field['label']) }}</option>
                                    @foreach (($field['options'] ?? $options[$name] ?? []) as $optionValue => $optionLabel)<option value="{{ $optionValue }}" @selected((string) $rawValue === (string) $optionValue)>{{ $optionLabel }}</option>@endforeach
                                </select>
                            @elseif ($type === 'multiselect')
                                <select name="{{ $name }}[]" multiple size="6" class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                                    @foreach (($options[$name] ?? []) as $optionValue => $optionLabel)<option value="{{ $optionValue }}" @selected(in_array((string) $optionValue, old($name, $selectedRelations[$name] ?? []), true))>{{ $optionLabel }}</option>@endforeach
                                </select>
                                <span class="mt-1 block text-xs text-slate-500">Hold Ctrl (Windows) or Command (Mac) to select multiple options.</span>
                            @else
                                <input name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' ? '' : $rawValue }}" @required($required && !($editing && $type === 'password')) step="{{ in_array($name, ['price', 'amount'], true) ? '0.01' : '1' }}" class="w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                            @endif
                            @error($name)<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                        @endif
                    </label>
                @endforeach
            </div>
            <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">
                <a href="{{ route('admin.'.$resource) }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" :disabled="saving" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60"><span x-show="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span><span x-text="saving ? 'Saving...' : 'Save record'">Save record</span></button>
            </div>
        </form>
    </div>
</x-admin-layout>
