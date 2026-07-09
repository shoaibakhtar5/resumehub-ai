@props([
    'headers' => [],
    'rows' => [],
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-border-light bg-white shadow-soft']) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-border-light text-left">
            <thead class="bg-surface-subtle">
                <tr>
                    @foreach ($headers as $header)
                        <th scope="col" class="whitespace-nowrap px-5 py-4 text-label-sm uppercase text-on-surface-variant">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-border-light">
                @foreach ($rows as $row)
                    <tr class="transition hover:bg-surface-subtle">
                        @foreach ($row as $cell)
                            <td class="whitespace-nowrap px-5 py-4 text-body-sm text-on-surface">{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
