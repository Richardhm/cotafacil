<x-app-layout>
<div class="p-4 max-w-7xl mx-auto space-y-6">

    {{-- Cabeçalho --}}
    <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Logins Compartilhados</h1>
            <p class="text-sm text-gray-400 mt-1">
                Monitoramento de dispositivos e acessos dos usuários
            </p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <label class="text-sm text-gray-300">Período:</label>
            <select name="horas" onchange="this.form.submit()"
                class="bg-white/10 text-white text-sm rounded-lg px-3 py-1.5 border border-white/20 focus:outline-none focus:ring-1 focus:ring-white/30 backdrop-blur">
                <option value="hoje" {{ $horas === 'hoje' ? 'selected' : '' }} class="bg-gray-800">Hoje</option>
                @foreach([24 => 'Últimas 24h', 48 => 'Últimas 48h', 72 => 'Últimas 72h', 168 => 'Últimos 7 dias'] as $val => $label)
                    <option value="{{ $val }}" {{ $horas == $val ? 'selected' : '' }} class="bg-gray-800">{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Flashes --}}
    @foreach([
        'desativado'          => ['border-green-500/40',   'text-green-400',   'text-green-300'],
        'ativado'             => ['border-emerald-500/40', 'text-emerald-400', 'text-emerald-300'],
        'limpo'               => ['border-blue-500/40',    'text-blue-400',    'text-blue-300'],
        'ip_bloqueado'        => ['border-red-500/40',     'text-red-400',     'text-red-300'],
        'ip_desbloqueado'     => ['border-green-500/40',   'text-green-400',   'text-green-300'],
        'dispositivo_removido'=> ['border-blue-500/40',    'text-blue-400',    'text-blue-300'],
        'dispositivo_bloqueado'=> ['border-red-500/40',    'text-red-400',     'text-red-300'],
        'titular_liberado'    => ['border-amber-500/40',  'text-amber-400',   'text-amber-300'],
    ] as $key => [$border, $icon, $text])
        @if(session($key))
            <div style="background: rgba(254,254,254,0.18)" class="backdrop-blur-[15px] rounded-2xl border {{ $border }} px-5 py-3 flex items-center gap-3">
                <svg class="w-5 h-5 {{ $icon }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="{{ $text }} text-sm">{{ session($key) }}</p>
            </div>
        @endif
    @endforeach
    @if(session('usuario_ip_bloqueado'))
        <div style="background: rgba(254,254,254,0.18)" class="backdrop-blur-[15px] rounded-2xl border border-orange-500/40 px-5 py-3 flex items-center gap-3">
            <svg class="w-5 h-5 text-orange-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
            </svg>
            <p class="text-orange-300 text-sm">{{ session('usuario_ip_bloqueado') }}</p>
        </div>
    @endif
    @if(session('usuario_ip_desbloqueado'))
        <div style="background: rgba(254,254,254,0.18)" class="backdrop-blur-[15px] rounded-2xl border border-green-500/40 px-5 py-3 flex items-center gap-3">
            <svg class="w-5 h-5 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-green-300 text-sm">{{ session('usuario_ip_desbloqueado') }}</p>
        </div>
    @endif

    @if($suspeitos->isEmpty())
        <div style="background: rgba(254,254,254,0.18)" class="backdrop-blur-[15px] rounded-2xl border border-green-500/30 p-8 text-center">
            <svg class="w-10 h-10 text-green-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-green-300 font-semibold text-lg">Nenhum acesso registrado</p>
            <p class="text-gray-400 text-sm mt-1">
                Nenhum usuário com dispositivo ativo
                @if($horas === 'hoje') hoje.
                @else nas últimas {{ $horas }}h.
                @endif
            </p>
            <p class="text-gray-500 text-xs mt-2">
                O monitoramento está ativo — os dados são coletados automaticamente a cada novo login.
            </p>
        </div>
    @else

        {{-- Cards de resumo --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div style="background: rgba(254,254,254,0.18)" class="backdrop-blur-[15px] rounded-2xl border border-red-500/30 p-4 text-center">
                <p id="stat-usuarios" class="text-3xl font-bold text-red-300">{{ $suspeitos->count() }}</p>
                <p class="text-xs text-gray-400 mt-1 uppercase tracking-wide">Usuários com dispositivos ativos</p>
            </div>
            <div style="background: rgba(254,254,254,0.18)" class="backdrop-blur-[15px] rounded-2xl border border-yellow-500/30 p-4 text-center">
                <p id="stat-dispositivos" class="text-3xl font-bold text-yellow-300">{{ $suspeitos->sum('dispositivos_distintos') }}</p>
                <p class="text-xs text-gray-400 mt-1 uppercase tracking-wide">Dispositivos ativos</p>
            </div>
            <div style="background: rgba(254,254,254,0.18)" class="backdrop-blur-[15px] rounded-2xl border border-blue-500/30 p-4 text-center">
                <p id="stat-logins" class="text-3xl font-bold text-blue-300">{{ $suspeitos->sum('total_logins') }}</p>
                <p class="text-xs text-gray-400 mt-1 uppercase tracking-wide">Logins no período</p>
            </div>
        </div>

        {{-- Lista de usuários --}}
        <div class="space-y-3">
            @foreach($suspeitos as $s)
                @php
                    $dispositivos = $detalhesPorUsuario[$s->user_id];
                    $cidades      = $dispositivos->pluck('city')->filter()->reject(fn($c) => $c === 'Local')->unique()->values();
                    $multiCidade  = $cidades->count() > 1;
                    $desativado   = $s->status == 2;
                    $tentativas   = $tentativasBloqueadasPorUsuario[$s->user_id] ?? collect();
                @endphp

                <div id="user-card-{{ $s->user_id }}"
                     data-devices="{{ $s->dispositivos_distintos }}"
                     data-logins="{{ $s->total_logins }}"
                     style="background: rgba(254,254,254,0.18)" class="backdrop-blur-[15px] rounded-2xl border {{ $multiCidade ? 'border-red-400/50' : 'border-white/15' }} overflow-hidden">

                    {{-- Linha clicável --}}
                    @php
                        $emailAssin = $emailAssins[$s->user_id] ?? null;
                        $eTitular   = $emailAssin && $emailAssin->is_administrador;
                        $titular    = (!$eTitular && $emailAssin)
                                        ? ($titularesPorAssinatura[$emailAssin->assinatura_id] ?? null)
                                        : null;
                    @endphp
                    <div class="flex items-center gap-4 px-5 py-4 cursor-pointer hover:bg-white/5 transition-colors select-none"
                         onclick="toggleDetalhes({{ $s->user_id }})">

                        {{-- Avatar --}}
                        <div class="w-11 h-11 rounded-full flex items-center justify-center text-base font-bold shrink-0
                            {{ $s->dispositivos_distintos >= 4 ? 'bg-red-600/50 text-red-200' : ($s->dispositivos_distintos == 3 ? 'bg-orange-600/50 text-orange-200' : 'bg-green-700/50 text-green-200') }}">
                            {{ strtoupper(substr($s->name, 0, 1)) }}
                        </div>

                        {{-- Nome + email --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-white font-bold text-base truncate">{{ $s->name }}</p>
                            <p class="text-gray-400 text-sm truncate">{{ $s->email }}</p>
                        </div>

                        {{-- Titular / Assinante --}}
                        @if($eTitular)
                            <div class="shrink-0 text-right hidden sm:block">
                                <p class="text-yellow-300 font-bold text-base leading-tight">⭐ Titular</p>
                                <p class="text-yellow-400/60 text-[10px] uppercase tracking-wide">da assinatura</p>
                            </div>
                        @elseif($titular)
                            <div class="shrink-0 text-right hidden sm:block">
                                <p class="text-yellow-400/60 text-[10px] uppercase tracking-wide">Assinante</p>
                                <p class="text-yellow-300 font-bold text-sm leading-tight" title="{{ $titular->email }}">{{ $titular->name }}</p>
                            </div>
                        @endif

                        {{-- Botões de ação do usuário --}}
                        <div class="flex items-center gap-1.5 shrink-0" onclick="event.stopPropagation()">
                            @if($desativado)
                                <span class="text-xs px-2.5 py-0.5 rounded-full border border-red-400/60 bg-red-900/50 text-red-300 font-semibold whitespace-nowrap">
                                    🔒 Desativado
                                </span>
                                <form method="POST" action="{{ route('gerenciamento.ativar-usuario', $s->user_id) }}"
                                      onsubmit="return confirm('Reativar o acesso de \"{{ addslashes($s->name) }}\"?')">
                                    @csrf
                                    <input type="hidden" name="horas" value="{{ $horas }}">
                                    <button type="submit"
                                        class="text-xs px-2.5 py-0.5 rounded-full border border-emerald-500/60 bg-emerald-900/40 text-emerald-300 hover:bg-emerald-700/60 transition-colors whitespace-nowrap">
                                        Ativar
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('gerenciamento.desativar-login-compartilhado', $s->user_id) }}"
                                      onsubmit="return confirm('Desativar \"{{ addslashes($s->name) }}\"?\n\nO usuário será deslogado agora.')">
                                    @csrf
                                    <input type="hidden" name="horas" value="{{ $horas }}">
                                    <button type="submit"
                                        class="text-xs px-2.5 py-0.5 rounded-full border border-red-500/50 bg-red-900/40 text-red-300 hover:bg-red-700/60 transition-colors whitespace-nowrap">
                                        Desativar
                                    </button>
                                </form>
                            @endif

                            <button onclick="liberarTitular({{ $s->user_id }}, '{{ addslashes($s->name) }}')"
                                class="text-xs px-2.5 py-0.5 rounded-full border border-amber-500/50 bg-amber-900/40 text-amber-300 hover:bg-amber-700/60 transition-colors whitespace-nowrap">
                                Liberar
                            </button>

                            <button onclick="limparUsuario({{ $s->user_id }}, '{{ addslashes($s->name) }}')"
                                class="text-xs px-2.5 py-0.5 rounded-full border border-gray-500/50 bg-gray-800/50 text-gray-400 hover:bg-gray-700/60 transition-colors whitespace-nowrap">
                                Limpar
                            </button>
                        </div>

                        {{-- Chevron --}}
                        <svg id="chevron-{{ $s->user_id }}" class="w-4 h-4 text-gray-500 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>

                    {{-- Detalhes expansíveis --}}
                    <div id="detalhes-{{ $s->user_id }}" class="hidden border-t border-white/10 px-5 pt-4 pb-5 space-y-4">

                        {{-- Badges de resumo --}}
                        <div class="flex flex-wrap items-center gap-2">
                            @php
                                $desktopCount   = $dispositivos->where('device_type', 'desktop')->count();
                                $mobileAppCount = $dispositivos->where('device_type', 'mobile_app')->count();
                                $acessoNormal   = $desktopCount <= 1 && $mobileAppCount <= 1;
                            @endphp
                            @if($acessoNormal)
                                <span class="bg-green-700/50 text-green-100 text-xs px-3 py-1 rounded-full border border-green-500/40 font-semibold">✅ Acesso Normal</span>
                            @elseif($desktopCount > 1 || $mobileAppCount > 1)
                                <span class="bg-red-700/70 text-red-100 text-xs px-3 py-1 rounded-full border border-red-500/40 font-semibold">🔴 Compartilhando Login</span>
                            @else
                                <span class="bg-yellow-700/70 text-yellow-100 text-xs px-3 py-1 rounded-full border border-yellow-500/40 font-semibold">🟡 Verificar</span>
                            @endif

                            <span class="text-xs text-gray-300 bg-white/5 px-3 py-1 rounded-full border border-white/10">
                                💻 <span class="font-bold {{ $desktopCount > 1 ? 'text-red-400' : 'text-green-400' }}">{{ $desktopCount }}</span>/1 desktop
                            </span>
                            <span class="text-xs text-gray-300 bg-white/5 px-3 py-1 rounded-full border border-white/10">
                                📱 <span class="font-bold {{ $mobileAppCount > 1 ? 'text-red-400' : 'text-green-400' }}">{{ $mobileAppCount }}</span>/1 app
                            </span>
                            <span class="text-xs text-gray-300 bg-white/5 px-3 py-1 rounded-full border border-white/10">
                                {{ $s->total_logins }} logins
                            </span>
                            <span class="text-xs text-gray-300 bg-white/5 px-3 py-1 rounded-full border border-white/10">
                                último: {{ \Carbon\Carbon::parse($s->ultimo_login)->format('d/m H:i') }}
                            </span>

                            @foreach($cidades as $cidade)
                                <span class="text-xs px-3 py-1 rounded-full {{ $multiCidade ? 'bg-red-700/60 text-red-200 border border-red-500/40' : 'bg-blue-800/50 text-blue-200 border border-blue-500/30' }}">
                                    📍 {{ $cidade }}
                                </span>
                            @endforeach
                            @if($multiCidade)
                                <span class="text-xs px-3 py-1 rounded-full bg-red-900/60 text-red-300 border border-red-600/40 font-semibold">
                                    ⚠ cidades diferentes
                                </span>
                            @endif
                        </div>

                        {{-- Tabela de dispositivos --}}
                        <div>
                            <p class="text-xs text-gray-400 font-semibold uppercase mb-2 tracking-wide">
                                Dispositivos registrados de <span class="text-white">{{ $s->name }}</span>
                            </p>
                            <div class="overflow-x-auto rounded-xl border border-white/10">
                                <table class="w-full text-xs text-left">
                                    <thead>
                                        <tr class="bg-white/10 text-gray-400 uppercase tracking-wide text-[10px]">
                                            <th class="px-3 py-2.5 whitespace-nowrap">#</th>
                                            <th class="px-3 py-2.5 whitespace-nowrap">Tipo</th>
                                            <th class="px-3 py-2.5 whitespace-nowrap">Navegador / SO</th>
                                            <th class="px-3 py-2.5 whitespace-nowrap">IP</th>
                                            <th class="px-3 py-2.5 whitespace-nowrap">Localização</th>
                                            <th class="px-3 py-2.5 whitespace-nowrap">Resolução</th>
                                            <th class="px-3 py-2.5 whitespace-nowrap">GPU</th>
                                            <th class="px-3 py-2.5 whitespace-nowrap">Último uso</th>
                                            <th class="px-3 py-2.5 whitespace-nowrap">Status</th>
                                            <th class="px-3 py-2.5 whitespace-nowrap">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/5">
                                        @foreach($dispositivos as $i => $dev)
                                            @php
                                                $gpu = $dev->gpu_renderer ?? '';
                                                if ($gpu) {
                                                    if (preg_match('/ANGLE\s*\([^,]+,\s*(.+?)\s*(?:\(0x[0-9a-f]+\)|\s+Direct3D)/i', $gpu, $gm)) {
                                                        $gpu = trim($gm[1]);
                                                    }
                                                    $gpu = preg_replace('/\s*\((R|TM)\)\s*/i', ' ', $gpu);
                                                    $gpu = trim(preg_replace('/\s+/', ' ', $gpu));
                                                }
                                            @endphp
                                            <tr id="device-row-{{ $dev->device_id }}"
                                                class="hover:bg-white/5 transition-colors {{ $dev->is_blocked ? 'bg-red-900/10' : (!$dev->is_active ? 'opacity-50' : '') }}">
                                                <td class="px-3 py-2.5 text-gray-500 font-mono">{{ $i + 1 }}</td>
                                                <td class="px-3 py-2.5 whitespace-nowrap">
                                                    @if(($dev->device_type ?? 'desktop') === 'mobile_app')
                                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-violet-700/60 text-violet-200 border border-violet-500/40 font-semibold whitespace-nowrap">📱 App</span>
                                                    @else
                                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-700/50 text-blue-200 border border-blue-500/30 font-semibold whitespace-nowrap">💻 Desktop</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2.5 whitespace-nowrap">
                                                    <p class="text-yellow-300 font-semibold">{{ $dev->browser ?: '—' }}</p>
                                                    <p class="text-white/60 text-[11px]">{{ $dev->device_model ?: ($dev->os ?: '—') }}</p>
                                                </td>
                                                <td class="px-3 py-2.5 text-yellow-200/80 font-mono whitespace-nowrap">{{ $dev->ip_address ?: '—' }}</td>
                                                <td class="px-3 py-2.5 whitespace-nowrap">
                                                    @if($dev->city && $dev->city !== 'Local')
                                                        <span class="text-emerald-300">📍 {{ $dev->city }}{{ $dev->country && $dev->country !== 'Dev' ? ', '.$dev->country : '' }}</span>
                                                    @elseif($dev->city === 'Local')
                                                        <span class="text-gray-400">🏠 Rede local</span>
                                                    @else
                                                        <span class="text-gray-600">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2.5 text-yellow-200 whitespace-nowrap">{{ $dev->screen_resolution ?: '—' }}</td>
                                                <td class="px-3 py-2.5 text-cyan-300 whitespace-nowrap max-w-[160px] truncate" title="{{ $dev->gpu_renderer ?? '' }}">
                                                    {{ $gpu ?: '—' }}
                                                </td>
                                                <td class="px-3 py-2.5 text-white/75 whitespace-nowrap">
                                                    {{ $dev->last_seen_at?->format('d/m H:i') ?? '—' }}
                                                </td>
                                                <td class="px-3 py-2.5 whitespace-nowrap device-status">
                                                    @if($dev->is_blocked)
                                                        <span class="text-red-300 font-semibold text-[10px]">🔒 Bloqueado</span>
                                                    @elseif(!$dev->is_active)
                                                        <span class="text-gray-500 text-[10px]">○ Removido</span>
                                                    @else
                                                        <span class="text-green-400 text-[10px]">✓ Ativo</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2.5 whitespace-nowrap">
                                                    <div class="flex gap-1">
                                                        @if($dev->is_active && !$dev->is_blocked)
                                                        <button onclick="bloquearDevice({{ $dev->device_id }}, '{{ addslashes($s->name) }}')"
                                                            class="btn-bloquear text-[10px] px-2 py-0.5 rounded-full border border-red-500/50 bg-red-900/40 text-red-300 hover:bg-red-700/60 transition-colors whitespace-nowrap">
                                                            Bloquear
                                                        </button>
                                                        @endif
                                                        @if($dev->is_blocked)
                                                        <button onclick="desbloquearDevice({{ $dev->device_id }}, '{{ addslashes($s->name) }}')"
                                                            class="btn-desbloquear text-[10px] px-2 py-0.5 rounded-full border border-sky-500/50 bg-sky-900/40 text-sky-300 hover:bg-sky-700/60 transition-colors whitespace-nowrap">
                                                            Desbloquear
                                                        </button>
                                                        @endif
                                                        @if($dev->is_active)
                                                        <button onclick="removerDevice({{ $dev->device_id }}, '{{ addslashes($s->name) }}', {{ $i + 1 }})"
                                                            class="btn-remover text-[10px] px-2 py-0.5 rounded-full border border-gray-500/50 bg-gray-800/50 text-gray-400 hover:bg-gray-700/60 transition-colors whitespace-nowrap">
                                                            Remover
                                                        </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tentativas bloqueadas --}}
                        @if($tentativas->isNotEmpty())
                            <div>
                                <p class="text-xs text-red-400 font-semibold uppercase mb-2 tracking-wide">
                                    ⚠ Tentativas bloqueadas — limite de 3 dispositivos atingido
                                </p>
                                <div class="overflow-x-auto rounded-xl border border-red-500/20">
                                    <table class="w-full text-xs text-left">
                                        <thead>
                                            <tr class="bg-red-900/20 text-gray-400 uppercase tracking-wide text-[10px]">
                                                <th class="px-3 py-2.5 whitespace-nowrap">Navegador / SO</th>
                                                <th class="px-3 py-2.5 whitespace-nowrap">IP</th>
                                                <th class="px-3 py-2.5 whitespace-nowrap">Localização</th>
                                                <th class="px-3 py-2.5 whitespace-nowrap">Resolução</th>
                                                <th class="px-3 py-2.5 whitespace-nowrap">Data / hora</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-white/5">
                                            @foreach($tentativas as $tent)
                                                <tr class="bg-red-900/10 hover:bg-red-900/20 transition-colors">
                                                    <td class="px-3 py-2.5 whitespace-nowrap">
                                                        <p class="text-yellow-300 font-semibold">{{ $tent->browser ?: '—' }}</p>
                                                        <p class="text-white/60 text-[11px]">{{ $tent->device_model ?: ($tent->os ?: '—') }}{{ $tent->is_mobile ? ' 📱' : '' }}</p>
                                                    </td>
                                                    <td class="px-3 py-2.5 text-yellow-200/80 font-mono whitespace-nowrap">{{ $tent->ip_address ?: '—' }}</td>
                                                    <td class="px-3 py-2.5 whitespace-nowrap">
                                                        @if($tent->city && $tent->city !== 'Local')
                                                            <span class="text-emerald-300">📍 {{ $tent->city }}{{ $tent->country && $tent->country !== 'Dev' ? ', '.$tent->country : '' }}</span>
                                                        @elseif($tent->city === 'Local')
                                                            <span class="text-gray-400">🏠 Rede local</span>
                                                        @else
                                                            <span class="text-gray-600">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2.5 text-yellow-200 whitespace-nowrap">{{ $tent->screen_resolution ?: '—' }}</td>
                                                    <td class="px-3 py-2.5 text-white/75 whitespace-nowrap">
                                                        {{ \Carbon\Carbon::parse($tent->logged_in_at)->format('d/m H:i') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            @endforeach
        </div>

        <p class="text-xs text-gray-500 text-center pt-2">
            Clique em qualquer usuário para expandir os dispositivos.
            Remover libera uma vaga. Bloquear impede o dispositivo de se registrar novamente.
        </p>
    @endif

    <div class="pt-2">
        <a href="{{ route('gerenciamento.index') }}"
            class="text-sm text-gray-400 hover:text-white transition-colors">
            ← Voltar ao gerenciamento
        </a>
    </div>
</div>

<script>
function toggleDetalhes(userId) {
    const row     = document.getElementById('detalhes-' + userId);
    const chevron = document.getElementById('chevron-' + userId);
    row.classList.toggle('hidden');
    chevron.style.transform = row.classList.contains('hidden') ? '' : 'rotate(180deg)';
}

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

async function devicePost(url) {
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf(),
        },
        body: '_token=' + encodeURIComponent(csrf()),
    });
    return res.ok;
}

function setDeviceActions(row, id, nome, state) {
    const safe = nome.replace(/'/g, "\\'");
    const actionsDiv = row.querySelector('td:last-child div');
    actionsDiv.innerHTML = '';
    if (state === 'ativo') {
        actionsDiv.innerHTML =
            `<button onclick="bloquearDevice(${id},'${safe}')" class="btn-bloquear text-[10px] px-2 py-0.5 rounded-full border border-red-500/50 bg-red-900/40 text-red-300 hover:bg-red-700/60 transition-colors whitespace-nowrap">Bloquear</button>` +
            `<button onclick="removerDevice(${id},'${safe}','')" class="btn-remover text-[10px] px-2 py-0.5 rounded-full border border-gray-500/50 bg-gray-800/50 text-gray-400 hover:bg-gray-700/60 transition-colors whitespace-nowrap">Remover</button>`;
    } else if (state === 'bloqueado') {
        actionsDiv.innerHTML =
            `<button onclick="desbloquearDevice(${id},'${safe}')" class="btn-desbloquear text-[10px] px-2 py-0.5 rounded-full border border-sky-500/50 bg-sky-900/40 text-sky-300 hover:bg-sky-700/60 transition-colors whitespace-nowrap">Desbloquear</button>`;
    }
    // state === 'removido': sem botões
}

async function bloquearDevice(id, nome) {
    if (!confirm(`Bloquear este dispositivo de "${nome}"?\n\nEle será desconectado e não poderá se registrar novamente.`)) return;
    const ok = await devicePost(`/gerenciamento/bloquear-dispositivo/${id}`);
    if (!ok) return;
    const row = document.getElementById(`device-row-${id}`);
    row.classList.add('bg-red-900/10');
    row.classList.remove('opacity-50');
    row.querySelector('.device-status').innerHTML = '<span class="text-red-300 font-semibold text-[10px]">🔒 Bloqueado</span>';
    setDeviceActions(row, id, nome, 'bloqueado');
}

async function desbloquearDevice(id, nome) {
    if (!confirm(`Desbloquear este dispositivo de "${nome}"?`)) return;
    const ok = await devicePost(`/gerenciamento/desbloquear-dispositivo/${id}`);
    if (!ok) return;
    const row = document.getElementById(`device-row-${id}`);
    row.classList.remove('bg-red-900/10', 'opacity-50');
    row.querySelector('.device-status').innerHTML = '<span class="text-green-400 text-[10px]">✓ Ativo</span>';
    setDeviceActions(row, id, nome, 'ativo');
}

async function removerDevice(id, nome, num) {
    if (!confirm(`Remover dispositivo de "${nome}"?\n\nUma vaga será liberada. O dispositivo poderá se registrar novamente no próximo login.`)) return;
    const ok = await devicePost(`/gerenciamento/remover-dispositivo/${id}`);
    if (!ok) return;
    const row = document.getElementById(`device-row-${id}`);
    if (!row) return;
    row.classList.add('opacity-50');
    row.querySelector('.device-status').innerHTML = '<span class="text-gray-500 text-[10px]">○ Removido</span>';
    setDeviceActions(row, id, nome, 'removido');
}

async function liberarTitular(userId, nome) {
    if (!confirm(`Liberar todos os dispositivos ativos de "${nome}"?\n\nTodos serão desconectados agora e as vagas liberadas. O titular poderá se logar novamente.`)) return;
    const ok = await devicePost(`/gerenciamento/liberar-titular/${userId}`);
    if (!ok) { alert('Erro ao liberar. Tente novamente.'); return; }
    document.querySelectorAll(`#user-card-${userId} tr[id^="device-row-"]`).forEach(row => {
        if (row.querySelector('.device-status')?.innerHTML.includes('✓ Ativo')) {
            const id = row.id.replace('device-row-', '');
            row.classList.add('opacity-50');
            row.querySelector('.device-status').innerHTML = '<span class="text-gray-500 text-[10px]">○ Removido</span>';
            setDeviceActions(row, id, nome, 'removido');
        }
    });
}

async function limparUsuario(userId, nome) {
    if (!confirm(`Limpar todo o histórico de "${nome}"?\n\nApaga sessões, logins e dispositivos registrados.`)) return;
    const ok = await devicePost(`/gerenciamento/limpar-sessoes/${userId}`);
    if (!ok) return;
    const card = document.getElementById(`user-card-${userId}`);
    if (!card) return;
    const devices = parseInt(card.dataset.devices) || 0;
    const logins  = parseInt(card.dataset.logins)  || 0;
    card.remove();
    const elU = document.getElementById('stat-usuarios');
    const elD = document.getElementById('stat-dispositivos');
    const elL = document.getElementById('stat-logins');
    if (elU) elU.textContent = Math.max(0, parseInt(elU.textContent) - 1);
    if (elD) elD.textContent = Math.max(0, parseInt(elD.textContent) - devices);
    if (elL) elL.textContent = Math.max(0, parseInt(elL.textContent) - logins);
}
</script>
</x-app-layout>
