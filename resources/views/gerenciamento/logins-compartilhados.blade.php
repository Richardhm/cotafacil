<x-app-layout>
<div class="p-4 max-w-7xl mx-auto space-y-6">

    {{-- Cabeçalho --}}
    <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Logins Compartilhados</h1>
            <p class="text-sm text-gray-400 mt-1">
                Monitoramento de credenciais utilizadas em múltiplos dispositivos distintos
            </p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <label class="text-sm text-gray-300">Período:</label>
            <select name="horas" onchange="this.form.submit()"
                class="bg-white/10 text-white text-sm rounded-lg px-3 py-1.5 border border-white/20 focus:outline-none focus:ring-1 focus:ring-white/30 backdrop-blur">
                @foreach([24 => 'Últimas 24h', 48 => 'Últimas 48h', 72 => 'Últimas 72h', 168 => 'Últimos 7 dias'] as $val => $label)
                    <option value="{{ $val }}" {{ $horas == $val ? 'selected' : '' }} class="bg-gray-800">{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Painel de explicação --}}
    <div style="background: rgba(254,254,254,0.18)" class="backdrop-blur-[15px] rounded-2xl border border-white/20 p-5 space-y-3 hidden">
        <div class="flex items-center gap-2 mb-1">
            <svg class="w-5 h-5 text-blue-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
            </svg>
            <h2 class="text-white font-semibold text-sm uppercase tracking-wide">O que esta página detecta?</h2>
        </div>

        <div class="grid md:grid-cols-2 gap-4 text-sm text-gray-200 leading-relaxed">
            <div class="space-y-2">
                <p>
                    <span class="text-white font-medium">Contexto:</span>
                    O CotaFácil cobra <span class="text-green-300 font-semibold">R$ 29,90 por login (assinatura)</span>.
                    Usuários que compartilham a mesma senha com outras pessoas geram prejuízo direto ao modelo de negócio,
                    pois múltiplas pessoas usam o sistema pagando como se fosse apenas uma.
                </p>
                <p>
                    <span class="text-white font-medium">Como funciona a detecção:</span>
                    A cada login, o sistema registra um <em>device fingerprint</em> — uma impressão digital do dispositivo
                    formada por: <span class="text-blue-300">User-Agent + prefixo de IP (/16) + idioma + plataforma + fuso horário + resolução de tela</span>.
                    Se o mesmo usuário aparece com 2 ou mais fingerprints distintos no período selecionado, ele é listado aqui.
                </p>
            </div>
            <div class="space-y-2">
                <p>
                    <span class="text-white font-medium">O que é coletado por sessão:</span>
                    IP público, <span class="text-yellow-300">cidade e estado via geolocalização</span>, navegador, sistema operacional,
                    tipo de dispositivo (mobile/desktop), <span class="text-violet-300">fuso horário do navegador</span>,
                    <span class="text-gray-300">resolução de tela</span>, data/hora do login, último acesso ativo e
                    se a sessão foi encerrada por novo acesso simultâneo (<em>was_displaced</em>).
                </p>
                <p>
                    <span class="text-white font-medium">Nível de risco:</span>
                    <span class="text-yellow-300">Baixo</span> = 2 dispositivos distintos,
                    <span class="text-orange-300">Médio</span> = 3 dispositivos,
                    <span class="text-red-300">Alto</span> = 4 ou mais. Cidades diferentes em sessões simultâneas
                    são o sinal mais forte de compartilhamento real.
                </p>
                <p class="text-gray-400 text-xs mt-1">
                    ⚠ Fingerprints iguais = mesmo dispositivo. O sistema não bloqueia acesso ainda — apenas monitora para futura tomada de decisão (alerta, cobrança extra ou bloqueio).
                </p>
            </div>
        </div>
    </div>

    {{-- Flashes --}}
    @if(session('desativado'))
        <div style="background: rgba(254,254,254,0.18)" class="backdrop-blur-[15px] rounded-2xl border border-green-500/40 px-5 py-3 flex items-center gap-3">
            <svg class="w-5 h-5 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-green-300 text-sm">{{ session('desativado') }}</p>
        </div>
    @endif
    @if(session('ip_bloqueado'))
        <div style="background: rgba(254,254,254,0.18)" class="backdrop-blur-[15px] rounded-2xl border border-red-500/40 px-5 py-3 flex items-center gap-3">
            <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
            <p class="text-red-300 text-sm">{{ session('ip_bloqueado') }}</p>
        </div>
    @endif
    @if(session('ip_desbloqueado'))
        <div style="background: rgba(254,254,254,0.18)" class="backdrop-blur-[15px] rounded-2xl border border-green-500/40 px-5 py-3 flex items-center gap-3">
            <svg class="w-5 h-5 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-green-300 text-sm">{{ session('ip_desbloqueado') }}</p>
        </div>
    @endif
    @if(session('limpo'))
        <div style="background: rgba(254,254,254,0.18)" class="backdrop-blur-[15px] rounded-2xl border border-blue-500/40 px-5 py-3 flex items-center gap-3">
            <svg class="w-5 h-5 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            <p class="text-blue-300 text-sm">{{ session('limpo') }}</p>
        </div>
    @endif
    @if(session('ativado'))
        <div style="background: rgba(254,254,254,0.18)" class="backdrop-blur-[15px] rounded-2xl border border-emerald-500/40 px-5 py-3 flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-emerald-300 text-sm">{{ session('ativado') }}</p>
        </div>
    @endif
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
            <p class="text-green-300 font-semibold text-lg">Nenhum login suspeito detectado</p>
            <p class="text-gray-400 text-sm mt-1">
                Nenhum usuário utilizou o mesmo login em dispositivos diferentes nas últimas {{ $horas }}h.
            </p>
            <p class="text-gray-500 text-xs mt-2">
                O monitoramento está ativo — os dados são coletados automaticamente a cada novo login.
            </p>
        </div>
    @else

        {{-- Cards de resumo --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div style="background: rgba(254,254,254,0.18)" class="backdrop-blur-[15px] rounded-2xl border border-red-500/30 p-4 text-center">
                <p class="text-3xl font-bold text-red-300">{{ $suspeitos->count() }}</p>
                <p class="text-xs text-gray-400 mt-1 uppercase tracking-wide">Logins suspeitos</p>
            </div>
            <div style="background: rgba(254,254,254,0.18)" class="backdrop-blur-[15px] rounded-2xl border border-yellow-500/30 p-4 text-center">
                <p class="text-3xl font-bold text-yellow-300">{{ $suspeitos->sum('dispositivos_distintos') }}</p>
                <p class="text-xs text-gray-400 mt-1 uppercase tracking-wide">Dispositivos distintos</p>
            </div>
            <div style="background: rgba(254,254,254,0.18)" class="backdrop-blur-[15px] rounded-2xl border border-blue-500/30 p-4 text-center">
                <p class="text-3xl font-bold text-blue-300">{{ $suspeitos->sum('total_logins') }}</p>
                <p class="text-xs text-gray-400 mt-1 uppercase tracking-wide">Acessos no período</p>
            </div>
        </div>

        {{-- Lista de usuários suspeitos --}}
        <div class="space-y-3">
            @foreach($suspeitos as $s)
                @php
                    $sessoes     = $detalhesPorUsuario[$s->user_id];
                    $cidades     = $sessoes->pluck('city')->filter()->reject(fn($c) => $c === 'Local')->unique()->values();
                    $multiCidade = $cidades->count() > 1;
                    $desativado  = $s->status == 2;
                @endphp

                {{-- Card do usuário --}}
                <div style="background: rgba(254,254,254,0.18)" class="backdrop-blur-[15px] rounded-2xl border {{ $multiCidade ? 'border-red-400/50' : 'border-white/15' }} overflow-hidden">

                    {{-- Linha clicável --}}
                    <div class="flex flex-wrap items-center gap-3 px-5 py-4 cursor-pointer hover:bg-white/5 transition-colors select-none"
                         onclick="toggleDetalhes({{ $s->user_id }})">

                        {{-- Avatar / ícone --}}
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold shrink-0
                            {{ $s->dispositivos_distintos >= 4 ? 'bg-red-600/50 text-red-200' : ($s->dispositivos_distintos == 3 ? 'bg-orange-600/50 text-orange-200' : 'bg-yellow-600/50 text-yellow-200') }}">
                            {{ strtoupper(substr($s->name, 0, 1)) }}
                        </div>

                        {{-- Nome + email --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-white font-semibold text-sm truncate">{{ $s->name }}</p>
                            <p class="text-gray-400 text-xs truncate">{{ $s->email }}</p>
                        </div>

                        {{-- Cidades detectadas --}}
                        @if($cidades->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach($cidades as $cidade)
                                    <span class="text-[11px] px-2 py-0.5 rounded-full {{ $multiCidade ? 'bg-red-700/60 text-red-200 border border-red-500/40' : 'bg-blue-800/50 text-blue-200 border border-blue-500/30' }}">
                                        📍 {{ $cidade }}
                                    </span>
                                @endforeach
                                @if($multiCidade)
                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-red-900/60 text-red-300 border border-red-600/40 font-semibold">
                                        ⚠ cidades diferentes
                                    </span>
                                @endif
                            </div>
                        @endif

                        {{-- Métricas --}}
                        <div class="flex items-center gap-4 text-xs text-gray-400 shrink-0">
                            <span>
                                <span class="font-bold {{ $s->dispositivos_distintos >= 3 ? 'text-red-400' : 'text-yellow-400' }} text-sm">
                                    {{ $s->dispositivos_distintos }}
                                </span>
                                dispositivos
                            </span>
                            <span>{{ $s->total_logins }} logins</span>
                            <span class="hidden sm:block">{{ \Carbon\Carbon::parse($s->ultimo_login)->format('d/m H:i') }}</span>
                        </div>

                        {{-- Badge de risco --}}
                        @if($s->dispositivos_distintos >= 4)
                            <span class="shrink-0 bg-red-700/70 text-red-100 text-xs px-2.5 py-0.5 rounded-full border border-red-500/40">Alto</span>
                        @elseif($s->dispositivos_distintos == 3)
                            <span class="shrink-0 bg-orange-700/70 text-orange-100 text-xs px-2.5 py-0.5 rounded-full border border-orange-500/40">Médio</span>
                        @else
                            <span class="shrink-0 bg-yellow-700/70 text-yellow-100 text-xs px-2.5 py-0.5 rounded-full border border-yellow-500/40">Baixo</span>
                        @endif

                        {{-- Botões de ação --}}
                        <div class="flex items-center gap-1.5 shrink-0" onclick="event.stopPropagation()">

                            @if($desativado)
                                {{-- Badge: já desativado --}}
                                <span class="text-xs px-2.5 py-0.5 rounded-full border border-red-400/60 bg-red-900/50 text-red-300 font-semibold whitespace-nowrap">
                                    🔒 Desativado
                                </span>

                                {{-- Ativar --}}
                                <form method="POST"
                                      action="{{ route('gerenciamento.ativar-usuario', $s->user_id) }}"
                                      onsubmit="return confirm('Reativar o acesso de \"{{ addslashes($s->name) }}\"?')">
                                    @csrf
                                    <input type="hidden" name="horas" value="{{ $horas }}">
                                    <button type="submit"
                                        class="text-xs px-2.5 py-0.5 rounded-full border border-emerald-500/60 bg-emerald-900/40 text-emerald-300 hover:bg-emerald-700/60 hover:text-emerald-100 transition-colors whitespace-nowrap">
                                        Ativar
                                    </button>
                                </form>
                            @else
                                {{-- Desativar --}}
                                <form method="POST"
                                      action="{{ route('gerenciamento.desativar-login-compartilhado', $s->user_id) }}"
                                      onsubmit="return confirm('Desativar \"{{ addslashes($s->name) }}\"?\n\nO usuário será deslogado agora e verá a mensagem:\n\n\"Acesso bloqueado: login compartilhado em mais de 1 dispositivo.\"')">
                                    @csrf
                                    <input type="hidden" name="horas" value="{{ $horas }}">
                                    <button type="submit"
                                        class="text-xs px-2.5 py-0.5 rounded-full border border-red-500/50 bg-red-900/40 text-red-300 hover:bg-red-700/60 hover:text-red-100 transition-colors whitespace-nowrap">
                                        Desativar
                                    </button>
                                </form>
                            @endif

                            {{-- Limpar histórico --}}
                            <form method="POST"
                                  action="{{ route('gerenciamento.limpar-sessoes', $s->user_id) }}"
                                  onsubmit="return confirm('Limpar todo o histórico de sessões de \"{{ addslashes($s->name) }}\"?\n\nIsso apagará todos os registros da tabela e o usuário sumirá desta lista.')">
                                @csrf
                                <input type="hidden" name="horas" value="{{ $horas }}">
                                <button type="submit"
                                    class="text-xs px-2.5 py-0.5 rounded-full border border-gray-500/50 bg-gray-800/50 text-gray-400 hover:bg-gray-700/60 hover:text-gray-200 transition-colors whitespace-nowrap">
                                    Limpar
                                </button>
                            </form>

                        </div>

                        {{-- Chevron --}}
                        <svg id="chevron-{{ $s->user_id }}" class="w-4 h-4 text-gray-500 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>

                    {{-- Detalhes expansíveis --}}
                    <div id="detalhes-{{ $s->user_id }}" class="hidden border-t border-white/10 px-5 py-4">
                        <p class="text-xs text-gray-400 font-semibold uppercase mb-3 tracking-wide">
                            Sessões de <span class="text-white">{{ $s->name }}</span> no período:
                        </p>

                        <div class="overflow-x-auto rounded-xl border border-white/10">
                            <table class="w-full text-xs text-left">
                                <thead>
                                    <tr class="bg-white/10 text-gray-400 uppercase tracking-wide text-[10px]">
                                        <th class="px-3 py-2.5 whitespace-nowrap">Navegador</th>
                                        <th class="px-3 py-2.5 whitespace-nowrap">Dispositivo</th>
                                        <th class="px-3 py-2.5 whitespace-nowrap">IP</th>
                                        <th class="px-3 py-2.5 whitespace-nowrap">Localização</th>
                                        <th class="px-3 py-2.5 whitespace-nowrap">Resolução</th>
                                        <th class="px-3 py-2.5 whitespace-nowrap">GPU</th>
                                        <th class="px-3 py-2.5 whitespace-nowrap">Hardware</th>
                                        <th class="px-3 py-2.5 whitespace-nowrap">Canvas</th>
                                        <th class="px-3 py-2.5 whitespace-nowrap">Login</th>
                                        <th class="px-3 py-2.5 whitespace-nowrap">Último acesso</th>
                                        <th class="px-3 py-2.5 whitespace-nowrap">Status</th>
                                        <th class="px-3 py-2.5 whitespace-nowrap">IP</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    @foreach($sessoes as $sess)
                                        @php
                                            $gpu = $sess->gpu_renderer ?? '';
                                            if ($gpu) {
                                                // Extrai o modelo do wrapper ANGLE: "ANGLE (Vendor, Modelo (0xHEX) Direct3D...)"
                                                if (preg_match('/ANGLE\s*\([^,]+,\s*(.+?)\s*(?:\(0x[0-9a-f]+\)|\s+Direct3D)/i', $gpu, $gm)) {
                                                    $gpu = trim($gm[1]);
                                                }
                                                $gpu = preg_replace('/\s*\((R|TM)\)\s*/i', ' ', $gpu);
                                                $gpu = trim(preg_replace('/\s+/', ' ', $gpu));
                                            }
                                        @endphp
                                        <tr class="hover:bg-white/5 transition-colors {{ $sess->was_displaced ? 'bg-red-900/20' : '' }}">
                                            <td class="px-3 py-2.5 text-yellow-300 font-semibold whitespace-nowrap">{{ $sess->browser ?: '—' }}</td>
                                            <td class="px-3 py-2.5 text-white font-medium whitespace-nowrap">
                                                {{ $sess->device_model ?: ($sess->os ?: '—') }}{{ $sess->is_mobile ? ' 📱' : '' }}
                                            </td>
                                            <td class="px-3 py-2.5 text-yellow-200/80 font-mono whitespace-nowrap">{{ $sess->ip_address }}</td>
                                            <td class="px-3 py-2.5 text-emerald-300 whitespace-nowrap">
                                                @if($sess->city && $sess->city !== 'Local')
                                                    📍 {{ $sess->city }}{{ $sess->country && $sess->country !== 'Dev' ? ', '.$sess->country : '' }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="px-3 py-2.5 text-yellow-200 whitespace-nowrap">{{ $sess->screen_resolution ?: '—' }}</td>
                                            <td class="px-3 py-2.5 text-cyan-300 whitespace-nowrap max-w-[180px] truncate" title="{{ $sess->gpu_renderer }}">
                                                {{ $gpu ?: '—' }}
                                            </td>
                                            <td class="px-3 py-2.5 text-purple-300 whitespace-nowrap">
                                                {{ implode(' · ', array_filter([
                                                    $sess->cpu_cores   ? $sess->cpu_cores.'c'       : null,
                                                    $sess->device_memory ? $sess->device_memory.'GB' : null,
                                                ])) ?: '—' }}
                                            </td>
                                            <td class="px-3 py-2.5 font-mono text-yellow-100/50 text-[10px] whitespace-nowrap">
                                                {{ $sess->canvas_hash ?: '—' }}
                                            </td>
                                            <td class="px-3 py-2.5 text-white/75 whitespace-nowrap">{{ $sess->logged_in_at->format('d/m H:i') }}</td>
                                            <td class="px-3 py-2.5 text-white/75 whitespace-nowrap">{{ $sess->last_seen_at?->format('d/m H:i') ?? '—' }}</td>
                                            <td class="px-3 py-2.5 whitespace-nowrap">
                                                @if($sess->was_displaced)
                                                    <span class="text-red-300 font-semibold">⚡ deslogado</span>
                                                @else
                                                    <span class="text-green-400">ativo</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2.5 whitespace-nowrap">
                                                @if($sess->ip_address)
                                                    @php
                                                        $totalNesseIp    = $sessoesPorIp[$sess->ip_address] ?? 1;
                                                        $chaveUserIp     = "{$s->user_id}_{$sess->ip_address}";
                                                        $userIpBloqueado = isset($userIpsBlockeados[$chaveUserIp]);
                                                    @endphp
                                                    <div class="flex flex-col gap-1">

                                                        {{-- Bloquear/Desbloquear IP inteiro --}}
                                                        @if(isset($ipsBlockeados[$sess->ip_address]))
                                                            <form method="POST" action="{{ route('gerenciamento.desbloquear-ip') }}">
                                                                @csrf
                                                                <input type="hidden" name="ip_address" value="{{ $sess->ip_address }}">
                                                                <input type="hidden" name="horas" value="{{ $horas }}">
                                                                <button type="submit" class="text-[10px] px-2 py-0.5 rounded-full border border-green-500/50 bg-green-900/40 text-green-300 hover:bg-green-700/60 transition-colors whitespace-nowrap">
                                                                    Desbloquear IP
                                                                </button>
                                                            </form>
                                                        @else
                                                            <form method="POST" action="{{ route('gerenciamento.bloquear-ip') }}"
                                                                  onsubmit="return confirmarBloqueioIp('{{ $sess->ip_address }}', {{ $totalNesseIp }})">
                                                                @csrf
                                                                <input type="hidden" name="ip_address" value="{{ $sess->ip_address }}">
                                                                <input type="hidden" name="horas" value="{{ $horas }}">
                                                                <button type="submit" class="text-[10px] px-2 py-0.5 rounded-full border border-red-500/50 bg-red-900/40 text-red-300 hover:bg-red-700/60 transition-colors whitespace-nowrap">
                                                                    Bloquear IP{{ $totalNesseIp > 1 ? " ($totalNesseIp usuários)" : '' }}
                                                                </button>
                                                            </form>
                                                        @endif

                                                        {{-- Bloquear/Desbloquear só este usuário neste IP --}}
                                                        @if($userIpBloqueado)
                                                            <form method="POST" action="{{ route('gerenciamento.desbloquear-usuario-ip') }}">
                                                                @csrf
                                                                <input type="hidden" name="user_id" value="{{ $s->user_id }}">
                                                                <input type="hidden" name="ip_address" value="{{ $sess->ip_address }}">
                                                                <input type="hidden" name="horas" value="{{ $horas }}">
                                                                <button type="submit" class="text-[10px] px-2 py-0.5 rounded-full border border-sky-500/50 bg-sky-900/40 text-sky-300 hover:bg-sky-700/60 transition-colors whitespace-nowrap">
                                                                    Liberar só ele
                                                                </button>
                                                            </form>
                                                        @else
                                                            <form method="POST" action="{{ route('gerenciamento.bloquear-usuario-ip') }}"
                                                                  onsubmit="return confirm('Bloquear APENAS {{ addslashes($s->name) }} neste IP?\n\nOs outros usuários do mesmo IP continuarão acessando normalmente.')">
                                                                @csrf
                                                                <input type="hidden" name="user_id" value="{{ $s->user_id }}">
                                                                <input type="hidden" name="ip_address" value="{{ $sess->ip_address }}">
                                                                <input type="hidden" name="horas" value="{{ $horas }}">
                                                                <button type="submit" class="text-[10px] px-2 py-0.5 rounded-full border border-orange-500/50 bg-orange-900/40 text-orange-300 hover:bg-orange-700/60 transition-colors whitespace-nowrap">
                                                                    Bloquear só ele
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <p class="text-xs text-gray-500 text-center pt-2">
            Clique em qualquer usuário para expandir as sessões. Fingerprints iguais = mesmo dispositivo.
            Cidades diferentes em sessões próximas são o indicador mais confiável de compartilhamento.
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

function confirmarBloqueioIp(ip, totalUsuarios) {
    if (totalUsuarios > 1) {
        return confirm(
            '⚠️ ATENÇÃO — BLOQUEIO DE IP COMPARTILHADO\n\n' +
            'O IP ' + ip + ' possui ' + totalUsuarios + ' usuários ativos.\n\n' +
            'Bloquear este IP irá DESCONECTAR TODOS eles, incluindo usuários legítimos do mesmo escritório/rede.\n\n' +
            'Se quiser bloquear apenas uma pessoa, use o botão "Bloquear só ele".\n\n' +
            'Tem certeza que deseja bloquear o IP inteiro?'
        );
    }
    return confirm('Bloquear o IP ' + ip + '?\n\nO usuário será desconectado imediatamente e não conseguirá mais acessar por esse IP.');
}
</script>
</x-app-layout>
