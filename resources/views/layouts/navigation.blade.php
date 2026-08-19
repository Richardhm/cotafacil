<nav x-data="{ open: false }" class="bg-opacity-10 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <style>
        @keyframes hapvidaSsPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(219,39,119,0.6); }
            50% { box-shadow: 0 0 0 6px rgba(219,39,119,0); }
        }
        @keyframes hapvidaSsArrow {
            0%, 100% { transform: translateX(0); opacity: 1; }
            50% { transform: translateX(4px); opacity: 0.5; }
        }
        @keyframes tabelaCompletaPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(37,99,235,0.6); }
            50% { box-shadow: 0 0 0 6px rgba(37,99,235,0); }
        }
        .hapvida-ss-highlight {
            background: linear-gradient(90deg,#7c3aed,#db2777);
            animation: hapvidaSsPulse 1.6s infinite;
        }
        .tabela-completa-highlight {
            background: linear-gradient(90deg,#2563eb,#0891b2);
            animation: tabelaCompletaPulse 1.6s infinite;
        }
        .hapvida-ss-arrow {
            display: inline-block;
            animation: hapvidaSsArrow 0.9s infinite;
        }
        @media (prefers-reduced-motion: reduce), (prefers-reduced-data: reduce) {
            .hapvida-ss-highlight,
            .tabela-completa-highlight,
            .hapvida-ss-arrow {
                animation: none;
            }
        }
    </style>
    <!-- Primary Navigation Menu -->
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                @php
                    $assinaturaId = \Illuminate\Support\Facades\Auth::user()->assinaturas()->first()?->id;
                @endphp

                @if(in_array($assinaturaId, [22,29,77,71]))
                    <div class="shrink-0 flex items-center">
                        <a href="https://select.bmsys.com.br/dashboard" class="h-8">
                            <img src="{{ asset('logo_select.png') }}" alt="Logo" style="width:50px;height:30px;" class="bg-white rounded-lg hover:bg-blue-100 ml-3">
                        </a>
                    </div>
                @endif



            </div>

            @if(Auth::user()->isAdmin() && auth()->user()->estaEmTrial())
                <p class="bg-red-600 border-white border p-2 rounded text-white text-sm">
                    @php
                        $data = Auth::user()->assinaturas()->first()->trial_ends_at;
                    @endphp
                    Seu período de teste se encerra em: {{ \Carbon\Carbon::parse($data)->format('d/m/Y \à\s H:i') }}.
                    <a class="p-1 bg-orange-500 text-white rounded ml-2" href="{{route('assinatura.edit')}}">Pagar</a>
                </p>
            @endif



            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">


{{--                @if(Auth::user()->isAdmin() || Auth::user()->isDesenvolvedor())--}}
{{--                    <a class="text-white relative flex items-center gap-1 {{ !Auth::user()->primeiro_acesso ? 'blink-border shake-arrow' : '' }}"--}}
{{--                       title="Gerenciar Usuários"--}}
{{--                       alt="Gerenciar Usuários"--}}
{{--                       href="{{ route('users.manage') }}">--}}

{{--                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"--}}
{{--                             stroke-width="1.5" stroke="currentColor" class="size-6">--}}
{{--                            <path stroke-linecap="round" stroke-linejoin="round"--}}
{{--                                  d="M15 19.128a9.38 9.38 0 0 0 2.625.372--}}
{{--                  9.337 9.337 0 0 0 4.121-.952--}}
{{--                  4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106--}}
{{--                  A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109--}}
{{--                  a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0--}}
{{--                  3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0--}}
{{--                  2.625 2.625 0 0 1 5.25 0Z" />--}}
{{--                        </svg>--}}

{{--                        @if(!Auth::user()->primeiro_acesso)--}}
{{--                            <span class="animated-arrow text-yellow-400 font-bold text-xl">⬅</span>--}}
{{--                        @endif--}}

{{--                    </a>--}}
{{--                @endif--}}

                    <a href="{{ route('hapvida-ss.index') }}"
                       title="Cotações Empresariais"
                       class="hapvida-ss-highlight text-white flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-bold mr-2">
                        <span class="hapvida-ss-arrow">➜</span>
                        Cotações Empresariais
                        <span class="hapvida-ss-arrow">➜</span>
                    </a>

                    @if (Auth::user()->temAcessoHumana())
                        <a href="{{ route('humanas.index') }}"
                           title="Cotação Humana — Teresina (PI)"
                           class="hapvida-ss-highlight text-white flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-bold mr-2">
                            <span class="hapvida-ss-arrow">➜</span>
                            Humana
                            <span class="hapvida-ss-arrow">➜</span>
                        </a>
                    @endif

                    <a href="{{ route('tabela_completa.index') }}"
                       title="Tabela Completa"
                       class="tabela-completa-highlight text-white flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-bold mr-2">
                        <span class="hapvida-ss-arrow">➜</span>
                        Tabela Completa
                        <span class="hapvida-ss-arrow">➜</span>
                    </a>

                    @if(Auth::user()->isAdmin())
                        <a class="text-white relative flex items-center gap-1" title="Configurações" alt="Configurações" href="{{ route('gerenciamento.index') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </a>
                    @else
                        <a class="text-white ml-2" title="Editar Seu Perfil" alt="Editar Seu Perfil" href="{{route('profile.edit')}}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </a>
                    @endif


{{--                    <a class="text-white ml-2" title="Editar Seu Perfil" alt="Editar Seu Perfil" href="{{route('profile.edit')}}">--}}
{{--                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">--}}
{{--                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />--}}
{{--                        </svg>--}}
{{--                    </a>--}}

{{--                    <a class="text-white ml-2" title="Escolher o seu Layout" alt="Editar Seu Perfil" href="{{route('layouts.index')}}">--}}
{{--                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">--}}
{{--                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />--}}
{{--                        </svg>--}}
{{--                    </a>--}}

                    @if(Auth::user()->isAdmin() && !auth()->user()->estaEmTrial())

                        @if(\Illuminate\Support\Facades\Auth::user()->assinaturas()->first()->tipo == "PIX")

                            <a class="text-white ml-2" title="Historico Financeiro" alt="Editar Seu Perfil" href="{{route('assinatura.historico.pix')}}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                </svg>
                            </a>




                        @else

                            <a class="text-white ml-2" title="Historico Financeiro" alt="Editar Seu Perfil" href="{{route('assinatura.historico')}}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                </svg>
                            </a>


                        @endif




                    @endif


                    @if(Auth::check() && Auth::user()->isDesenvolvedor())
                        <a class="text-white ml-2" title="Configuração do Administrador" alt="Configuração do Administrador" href="{{route('configuracoes.index')}}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                            </svg>
                        </a>
                        <a class="text-white ml-2" title="Financeiro" alt="Financeiro" href="{{route('financeiro.index')}}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </a>
                        <a class="text-white ml-2" title="Logins Compartilhados" alt="Logins Compartilhados" href="{{route('gerenciamento.logins-compartilhados')}}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                        </a>
                    @endif
                    <div class="px-1 py-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full text-white flex items-center gap-2 p-1 rounded-lg hover:bg-red-700 transition">
                                Sair
                            </button>
                        </form>
                    </div>
            </div>



            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-white dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="open ? 'block' : 'hidden'"
         class="fixed top-0 left-0 w-64 h-full bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] text-black shadow-lg transform transition-transform duration-300 sm:hidden z-50">

        <div class="flex flex-col h-full">
            <!-- Usuário -->
            <div class="px-6 py-4 border-b border-gray-700">
                <div class="text-lg font-semibold text-white">{{ Auth::user()->name }}</div>
                <div class="text-sm text-gray-400 text-white">{{ Auth::user()->email }}</div>
            </div>

            <!-- Links -->
            <nav class="flex-1 px-6 py-4 space-y-2">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    📊 Dashboard
                </x-responsive-nav-link>

                <a href="{{ route('hapvida-ss.index') }}" class="hapvida-ss-highlight text-white flex items-center gap-2 p-3 rounded-lg font-bold">
                    <span class="hapvida-ss-arrow">➜</span> Cotações Empresariais <span class="hapvida-ss-arrow">➜</span>
                </a>

                @if (Auth::user()->temAcessoHumana())
                    <a href="{{ route('humanas.index') }}" class="hapvida-ss-highlight text-white flex items-center gap-2 p-3 rounded-lg font-bold">
                        <span class="hapvida-ss-arrow">➜</span> Humana <span class="hapvida-ss-arrow">➜</span>
                    </a>
                @endif

                <a href="{{ route('tabela_completa.index') }}" class="tabela-completa-highlight text-white flex items-center gap-2 p-3 rounded-lg font-bold">
                    <span class="hapvida-ss-arrow">➜</span> Tabela Completa <span class="hapvida-ss-arrow">➜</span>
                </a>

                @if(Auth::user()->isAdmin())
                    <a class="text-white relative flex items-center gap-1" title="Configurações" alt="Configurações" href="{{ route('gerenciamento.index') }}">
                        👥 Gerenciar Usuários
                    </a>
                @else
                    <a class="text-white ml-2" title="Editar Seu Perfil" alt="Editar Seu Perfil" href="{{route('profile.edit')}}">
                        ✏️ Editar Perfil
                    </a>
                @endif

                @if(Auth::check() && Auth::user()->isDesenvolvedor())
                    <a href="{{route('gerenciamento.logins-compartilhados')}}" class="text-white flex items-center gap-2 p-3 rounded-lg hover:bg-gray-700 transition">
                        🛡️ Logins Compartilhados
                    </a>
                @endif
            </nav>

            <!-- Logout -->
            <div class="px-6 py-4 border-t border-gray-700">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full text-white flex items-center gap-2 p-3 bg-red-600 rounded-lg hover:bg-red-700 transition">
                        🚪 Sair
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
