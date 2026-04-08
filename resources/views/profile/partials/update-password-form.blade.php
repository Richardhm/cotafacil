<section class="p-2">
    <header>
        <h2 class="text-lg font-medium text-white">
            {{ __('Alterar Senha') }}
        </h2>

        <p class="mt-1 text-sm text-white">
            {{ __('Certifique-se de que sua conta esteja usando uma senha longa e aleatória para permanecer segura.') }}
        </p>
    </header>

    <form  id="passwordForm" method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" class="text-white" :value="__('Senha Atual')" />
            <x-text-input id="update_password_current_password" name="current_password" required type="password" class="mt-1 block w-full" autocomplete="current-password" />

        </div>

        <div>
            <x-input-label for="update_password_password" class="text-white" :value="__('Nova Senha')" />
            <x-text-input id="update_password_password" name="password" type="password" required class="mt-1 block w-full" autocomplete="new-password" />

        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" class="text-white" :value="__('Confirmar nova Senha')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" required type="password" class="mt-1 block w-full" autocomplete="new-password" />

        </div>

        <div class="flex items-center gap-4">
            <x-primary-button id="password-submit-btn" class="w-full text-center flex justify-center items-center gap-2">
                <svg id="password-submit-spinner" class="hidden animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                <span id="password-submit-text">{{ __('Salvar') }}</span>
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Salvar.') }}</p>
            @endif
        </div>
    </form>
</section>

<script>
document.getElementById('passwordForm').addEventListener('submit', function () {
    const btn     = document.getElementById('password-submit-btn');
    const spinner = document.getElementById('password-submit-spinner');
    const text    = document.getElementById('password-submit-text');

    btn.disabled = true;
    spinner.classList.remove('hidden');
    text.textContent = 'Salvando...';
});
</script>
