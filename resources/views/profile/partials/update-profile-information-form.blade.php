<section class="p-2">
    <header class="w-full">
        <div class="w-full flex justify-between items-center mb-3">
            <div class="flex flex-col">
                <h2 class="text-sm font-medium text-white">
                    {{ __('Informações de Perfil') }}
                </h2>
                <p class="mt-1 text-sm text-white">
                    {{ __("Atualize as informações do perfil.") }}
                </p>
            </div>

            {{-- Área de foto melhorada --}}
            <div class="flex flex-col items-center gap-1">
                <div class="relative w-24 h-24 rounded-full cursor-pointer group"
                     onclick="document.getElementById('avatar-input').click()">

                    @if($user->imagem)
                        <img src="{{ Storage::url($user->imagem) }}"
                             id="user-avatar"
                             class="w-24 h-24 rounded-full object-cover border-2 border-white">
                    @else
                        <div id="user-avatar"
                             class="w-24 h-24 rounded-full flex flex-col items-center justify-center border-2 border-dashed border-white bg-white/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </div>
                    @endif

                    {{-- Overlay ao passar o mouse --}}
                    <div class="absolute inset-0 rounded-full bg-black/50 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                        </svg>
                        <span class="text-white text-xs mt-1">Alterar</span>
                    </div>
                </div>

                <p class="text-white text-xs text-center">Clique para alterar foto</p>
            </div>
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form id="profileForm" method="post" action="{{ route('profile.update') }}" class="mt-1 space-y-3 w-full" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" class="text-white" :value="__('Nome')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-1" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" class="text-white" :value="__('E-mail')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                :value="old('email', $user->email)" required autocomplete="email" />
            <x-input-error class="mt-1" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="text-sm mt-1 text-yellow-300">
                    {{ __('E-mail não verificado.') }}
                    <button form="send-verification" class="underline text-white hover:text-yellow-200 text-xs">
                        {{ __('Reenviar verificação') }}
                    </button>
                </p>
            @endif
        </div>

        <div>
            <x-input-label for="phone" class="text-white" :value="__('Telefone')" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                :value="old('phone', $user->phone)" required autocomplete="tel" />
            <x-input-error class="mt-1" :messages="$errors->get('phone')" />
        </div>

        {{-- Campo oculto para envio da imagem --}}
        <input type="file" form="profileForm" id="avatar-input" name="imagem" class="hidden" accept="image/*">

        <div class="flex items-center gap-4 pt-1">
            <x-primary-button id="profile-submit-btn" class="w-full text-center flex justify-center items-center gap-2">
                <svg id="profile-submit-spinner" class="hidden animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                <span id="profile-submit-text">Salvar</span>
            </x-primary-button>
        </div>
    </form>
</section>

<script>
document.getElementById('profileForm').addEventListener('submit', function () {
    const btn     = document.getElementById('profile-submit-btn');
    const spinner = document.getElementById('profile-submit-spinner');
    const text    = document.getElementById('profile-submit-text');

    btn.disabled = true;
    spinner.classList.remove('hidden');
    text.textContent = 'Salvando...';
});

document.getElementById('avatar-input').addEventListener('change', function (event) {
    const file = event.target.files[0];
    if (!file) return;

    const url = URL.createObjectURL(file);
    const old = document.getElementById('user-avatar');
    const wrapper = old.parentElement;

    const img = document.createElement('img');
    img.id = 'user-avatar';
    img.src = url;
    img.className = 'w-24 h-24 rounded-full object-cover border-2 border-white';

    wrapper.replaceChild(img, old);
});
</script>
