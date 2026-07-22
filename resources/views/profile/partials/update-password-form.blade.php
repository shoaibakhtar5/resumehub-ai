<div id="security" class="scroll-mt-24 border-b border-slate-100 px-6 py-5 sm:px-7">
    <div class="flex items-center gap-3">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600"><x-ui.icon name="lock-closed" class="h-5 w-5" /></span>
        <div><h2 class="font-display text-lg font-semibold text-slate-900">Password and security</h2><p class="text-sm text-slate-500">Use a strong, unique password for this account.</p></div>
    </div>
</div>

<form method="POST" action="{{ route('password.update') }}" class="grid gap-5 p-6 sm:grid-cols-2 sm:p-7">
    @csrf
    @method('PUT')
    <div class="sm:col-span-2"><x-ui.input label="Current password" name="current_password" id="update_password_current_password" type="password" autocomplete="current-password" :error="$errors->updatePassword->first('current_password')" /></div>
    <x-ui.input label="New password" name="password" id="update_password_password" type="password" autocomplete="new-password" :error="$errors->updatePassword->first('password')" />
    <x-ui.input label="Confirm new password" name="password_confirmation" id="update_password_password_confirmation" type="password" autocomplete="new-password" />
    <div class="flex flex-wrap items-center gap-4 border-t border-slate-100 pt-6 sm:col-span-2">
        <x-ui.button type="submit" icon="shield-check">Update password</x-ui.button>
        @if (session('status') === 'password-updated')<p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="text-sm font-semibold text-emerald-600">Password updated.</p>@endif
    </div>
</form>
