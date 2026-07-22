@props(['user', 'size' => 'h-12 w-12', 'textSize' => 'text-base'])

<span {{ $attributes->merge(['class' => "relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-indigo-100 to-violet-100 text-indigo-700 ring-1 ring-indigo-100 $size"]) }}>
    <span class="font-display font-bold {{ $textSize }}">{{ strtoupper(mb_substr($user->name ?: 'U', 0, 1)) }}</span>
    @if ($user->profile_photo_url)
        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="absolute inset-0 h-full w-full object-cover" onerror="this.remove()">
    @endif
</span>
