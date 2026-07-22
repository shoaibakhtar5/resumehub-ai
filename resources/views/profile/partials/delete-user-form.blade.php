<div class="p-6">
    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-100 text-rose-600"><x-ui.icon name="trash" class="h-5 w-5" /></span>
    <h2 class="mt-4 font-display text-lg font-semibold text-rose-950">Delete account</h2>
    <p class="mt-2 text-sm leading-6 text-rose-800/80">Permanently remove your account and associated workspace data. This cannot be undone.</p>
    <button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" class="mt-5 inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">Delete my account</button>
</div>

<x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
    <form method="POST" action="{{ route('profile.destroy') }}" class="p-6 sm:p-7">
        @csrf
        @method('DELETE')
        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-rose-100 text-rose-600"><x-ui.icon name="exclamation-triangle" class="h-5 w-5" /></span>
        <h2 class="mt-4 font-display text-xl font-semibold text-slate-900">Confirm account deletion</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">Enter your password to permanently delete your account and all associated data.</p>
        <div class="mt-6"><x-ui.input label="Password" name="password" type="password" autocomplete="current-password" :error="$errors->userDeletion->first('password')" /></div>
        <div class="mt-6 flex justify-end gap-3">
            <x-secondary-button type="button" x-on:click="$dispatch('close')">Cancel</x-secondary-button>
            <x-danger-button>Delete account</x-danger-button>
        </div>
    </form>
</x-modal>
