<div class="border-b border-slate-100 px-6 py-5 sm:px-7">
    <div class="flex items-center gap-3">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><x-ui.icon name="user" class="h-5 w-5" /></span>
        <div><h2 class="font-display text-lg font-semibold text-slate-900">Personal information</h2><p class="text-sm text-slate-500">Update your identity and contact details.</p></div>
    </div>
</div>

<form id="send-verification" method="POST" action="{{ route('verification.send') }}">@csrf</form>

<form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="p-6 sm:p-7" x-data="{ preview: null, fileName: '' }">
    @csrf
    @method('PATCH')

    <div class="grid gap-7 lg:grid-cols-[180px_minmax(0,1fr)]">
        <div>
            <p class="mb-3 text-sm font-semibold text-slate-800">Profile photo</p>
            <div class="relative w-fit">
                <x-ui.avatar :user="$user" size="h-32 w-32" text-size="text-3xl" class="ring-4 ring-slate-100" />
                <template x-if="preview"><img :src="preview" alt="Selected profile photo" class="absolute inset-0 h-32 w-32 rounded-full object-cover ring-4 ring-indigo-100"></template>
                <label for="profile_photo" class="absolute bottom-0 right-0 flex h-10 w-10 cursor-pointer items-center justify-center rounded-full border-4 border-white bg-indigo-600 text-white shadow-lg transition hover:bg-indigo-700" title="Choose profile photo"><x-ui.icon name="photo" class="h-4 w-4" /></label>
            </div>
            <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="const file = $event.target.files[0]; if (file) { preview = URL.createObjectURL(file); fileName = file.name }">
            <p class="mt-4 break-words text-xs text-slate-500" x-text="fileName || 'JPG, PNG or WEBP · Max 2MB'"></p>
            <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2"><x-ui.input label="Full name" name="name" type="text" :value="old('name', $user->name)" required autocomplete="name" :error="$errors->first('name')" /></div>
            <div class="sm:col-span-2"><x-ui.input label="Email address" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" :error="$errors->first('email')" /></div>
            <x-ui.input label="Phone number" name="phone" type="tel" :value="old('phone', $user->phone)" autocomplete="tel" :error="$errors->first('phone')" />
            <x-ui.select label="Language" name="locale" :selected="old('locale', $user->locale ?: 'en')" :options="['en' => 'English', 'en-GB' => 'English (UK)', 'ur' => 'Urdu']" />
            <div class="sm:col-span-2">
                <x-ui.input label="Timezone" name="timezone" type="text" list="timezone-options" :value="old('timezone', $user->timezone ?: 'UTC')" required :error="$errors->first('timezone')" />
                <datalist id="timezone-options">@foreach(['UTC','Asia/Karachi','Asia/Dubai','Asia/Kolkata','Europe/London','Europe/Berlin','America/New_York','America/Chicago','America/Denver','America/Los_Angeles','Australia/Sydney'] as $timezone)<option value="{{ $timezone }}"></option>@endforeach</datalist>
            </div>
        </div>
    </div>

    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            Your email address is not verified.
            <button form="send-verification" class="ml-1 font-semibold underline underline-offset-2">Send another verification email</button>
            @if (session('status') === 'verification-link-sent')<p class="mt-2 font-semibold text-emerald-700">A new verification link has been sent.</p>@endif
        </div>
    @endif

    <div class="mt-7 flex flex-wrap items-center gap-4 border-t border-slate-100 pt-6">
        <x-ui.button type="submit" icon="check">Save profile</x-ui.button>
        @if (session('status') === 'profile-updated')<p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Profile updated successfully.</p>@endif
    </div>
</form>
