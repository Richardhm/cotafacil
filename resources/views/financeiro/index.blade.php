<x-app-layout>
    @section('css')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .card-stat { background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; padding: 1.2rem 1.5rem; }
        .card-stat .label { font-size: 0.8rem; font-weight: 600; color: rgba(255,255,255,0.7); margin-bottom: 4px; }
        .card-stat .value { font-size: 2rem; font-weight: 700; }
        .badge-tipo { font-size: 0.72rem; padding: 2px 8px; border-radius: 20px; font-weight: 600; }
        .badge-cartao  { background:#3b82f6; color:#fff; }
        .badge-pix     { background:#10b981; color:#fff; }
        .badge-pixauto { background:#8b5cf6; color:#fff; }
        .badge-trial   { background:#f59e0b; color:#fff; }
        .badge-status-ativo    { background:#10b981; color:#fff; }
        .badge-status-trial    { background:#f59e0b; color:#fff; }
        .badge-status-inativo  { background:#ef4444; color:#fff; }
        .badge-status-pendente { background:#d97706; color:#fff; }
        table thead th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: rgba(255,255,255,0.85); font-weight: 700; }
        table tbody td { color: #fff; }

        /* Seta expansível */
        .btn-expand { cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
        .btn-expand .seta { transition: transform .2s ease; display:inline-block; }
        .btn-expand.aberto .seta { transform: rotate(90deg); }

        /* Linha de usuários */
        .linha-usuarios { display:none; }
        .linha-usuarios.aberta { display:table-row; }
        .usuarios-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(220px,1fr)); gap:8px; padding:10px 0; }
        .usuario-card { background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; padding:8px 12px; }
        .usuario-card .u-nome { font-weight:600; color:#fff; font-size:0.82rem; }
        .usuario-card .u-tel  { color:rgba(255,255,255,0.7); font-size:0.78rem; margin-top:2px; }
        .usuario-card .u-email { color:rgba(255,255,255,0.55); font-size:0.74rem; }
        .crown { color:#fbbf24; font-size:0.7rem; margin-left:4px; }

        /* Modal */
        .modal-overlay { display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.7); align-items:center; justify-content:center; }
        .modal-overlay.aberto { display:flex; }
        .modal-box { background:#1e293b; border-radius:16px; width:100%; max-width:720px; max-height:85vh; display:flex; flex-direction:column; overflow:hidden; }
        .modal-box-lg { max-width:900px; }
        .modal-header { padding:1.25rem 1.5rem; border-bottom:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:space-between; }
        .modal-body { padding:1.25rem 1.5rem; overflow-y:auto; flex:1; }

        /* ---- Impressão ---- */
        .print-section { display: none; }
        @media print {
            @page { margin: 1.5cm 2cm; }
            body * { visibility: hidden; }
            .print-section { display: block !important; }
            .print-section, .print-section * { visibility: visible; }
            .print-section { position: absolute; left: 0; top: 0; width: 100%; }
            .ps-titulo    { font-family: Arial, sans-serif; font-size: 15px; font-weight: bold; color: #111; margin: 0 0 2px; }
            .ps-sub       { font-family: Arial, sans-serif; font-size: 10px; color: #777; margin: 0 0 12px; }
            .ps-hr        { border: none; border-top: 1px solid #bbb; margin: 0 0 10px; }
            .ps-assinante { font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; color: #111; margin: 8px 0 1px; }
            .ps-meta      { font-family: Arial, sans-serif; font-size: 10px; color: #555; margin: 0 0 2px; }
            .ps-usuario   { font-family: Arial, sans-serif; font-size: 11px; color: #333; margin: 1px 0 1px 2em; }
            .ps-usuario::before { content: "↳ "; color: #999; }
        }
    </style>
    @endsection

    <div class="py-8 px-4 max-w-7xl mx-auto bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] rounded-lg mt-5">

        {{-- Cabeçalho --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Dashboard Financeiro</h1>
                <p class="text-sm text-white/70 mt-1">Visão geral das assinaturas · {{ now()->format('d/m/Y H:i') }}</p>
            </div>
            <button onclick="imprimirTabela()"
                class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Exportar / Imprimir
            </button>
        </div>

        {{-- Cards de métricas --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="card-stat">
                <p class="label">Assinaturas Ativas</p>
                <p class="value text-emerald-400">{{ $totalAtivas }}</p>
            </div>
            <div class="card-stat">
                <p class="label">Receita Mensal Estimada</p>
                <p class="value text-blue-300">R$ {{ number_format($receitaMensal, 2, ',', '.') }}</p>
            </div>
            <div class="card-stat">
                <p class="label">Novas este mês</p>
                <p class="value text-indigo-300">{{ $novasEsteMes }}</p>
            </div>
            <div class="card-stat">
                <p class="label">Dias até fim do mês</p>
                <p class="value text-yellow-300">{{ floor($diasAteFimMes) }}</p>
            </div>
        </div>

        @if($vencendoEm7Dias > 0)
        <div class="bg-yellow-900/40 border border-yellow-600 text-yellow-300 rounded-xl px-4 py-3 mb-6 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <span><strong>{{ $vencendoEm7Dias }}</strong> assinatura(s) PIX vencem nos próximos 7 dias.</span>
        </div>
        @endif

        {{-- Gráfico de evolução --}}
        <div class="bg-black/20 border border-white/10 rounded-2xl p-5 mb-8 no-print">
            <h2 class="text-white font-bold mb-4">Evolução — Últimos 12 meses</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-white/70 text-xs mb-2 uppercase tracking-wide font-semibold">Novas Assinaturas</p>
                    <canvas id="chartNovas" height="120"></canvas>
                </div>
                <div>
                    <p class="text-white/70 text-xs mb-2 uppercase tracking-wide font-semibold">Receita (R$)</p>
                    <canvas id="chartReceita" height="120"></canvas>
                </div>
            </div>
        </div>

        {{-- Tabela de contas --}}
        <div class="bg-black/20 border border-white/10 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-white font-bold">Todas as Contas</h2>
                <input id="busca" type="text" placeholder="Buscar por nome ou e-mail..."
                    class="no-print bg-white/10 text-white text-sm rounded-lg px-3 py-1.5 border border-white/20 outline-none w-64
                           placeholder-gray-500 focus:border-blue-400">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-300" id="tabela-contas">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="pb-2 pr-4">Admin</th>
                            <th class="pb-2 pr-4">E-mail</th>
                            <th class="pb-2 pr-4 text-center">Usuários</th>
                            <th class="pb-2 pr-4 text-center">Tipo</th>
                            <th class="pb-2 pr-4 text-center">Status</th>
                            <th class="pb-2 pr-4 text-right">Valor</th>
                            <th class="pb-2 pr-4 text-center">Próx. Cobrança</th>
                            <th class="pb-2 pr-4 text-center">Cadastro</th>
                            <th class="pb-2 pr-4 text-center" style="display:none">Histórico</th>
                            <th class="pb-2 pr-4 text-center">Free</th>
                            <th class="pb-2 text-center col-acesso">Acesso</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contas as $i => $conta)

                        {{-- Linha principal --}}
                        <tr class="border-b border-white/5 hover:bg-white/5 transition linha-conta"
                            data-nome="{{ strtolower($conta['admin_nome']) }}"
                            data-email="{{ strtolower($conta['admin_email']) }}">

                            {{-- Nome com seta --}}
                            <td class="py-2 pr-4">
                                <span class="flex items-center gap-1.5">
                                    <span class="btn-expand font-medium text-white" data-target="usuarios-{{ $i }}">
                                        <span class="seta text-gray-400">&#9654;</span>
                                        {{ $conta['admin_nome'] }}
                                    </span>
                                    @php
                                        $phone = preg_replace('/\D/', '', $conta['admin_phone'] ?? '');
                                        if ($phone && strlen($phone) <= 11) { $phone = '55' . $phone; }
                                    @endphp
                                    @if($phone)
                                    <a href="https://wa.me/{{ $phone }}" target="_blank" title="WhatsApp: {{ $conta['admin_phone'] }}"
                                       class="shrink-0 text-green-400 hover:text-green-300 transition-colors">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                        </svg>
                                    </a>
                                    @endif
                                </span>
                            </td>

                            <td class="py-2 pr-4 text-white/75">{{ $conta['admin_email'] }}</td>
                            <td class="py-2 pr-4 text-center font-semibold">{{ $conta['total_users'] }}</td>

                            <td class="py-2 pr-4 text-center">
                                @php
                                    $tipo = strtolower($conta['tipo'] ?? 'cartao');
                                    $badgeTipo = match($tipo) {
                                        'pix'            => 'badge-pix',
                                        'pix_automatico' => 'badge-pixauto',
                                        'cartao'         => 'badge-cartao',
                                        default          => 'badge-cartao',
                                    };
                                    $labelTipo = match($tipo) {
                                        'pix'            => 'PIX',
                                        'pix_automatico' => 'PIX Auto',
                                        'cartao'         => 'Cartão',
                                        default          => ucfirst($tipo),
                                    };
                                @endphp
                                <span class="badge-tipo {{ $badgeTipo }}">{{ $labelTipo }}</span>
                            </td>

                            <td class="py-2 pr-4 text-center">
                                @php
                                    $status = strtolower($conta['status'] ?? 'inativo');
                                    $emAtraso = $conta['next_charge']
                                        && $status !== 'trial'
                                        && \Carbon\Carbon::parse($conta['next_charge'])->isPast();
                                    if ($emAtraso) {
                                        $badgeStatus = 'badge-status-pendente';
                                        $labelStatus = 'Pendente';
                                    } else {
                                        $badgeStatus = match($status) {
                                            'ativo'  => 'badge-status-ativo',
                                            'trial'  => 'badge-status-trial',
                                            default  => 'badge-status-inativo',
                                        };
                                        $labelStatus = ucfirst($conta['status']);
                                    }
                                @endphp
                                <span class="badge-tipo {{ $badgeStatus }}">{{ $labelStatus }}</span>
                            </td>

                            <td class="py-2 pr-4 text-right">R$ {{ number_format($conta['preco_total'] ?? 0, 2, ',', '.') }}</td>

                            <td class="py-2 pr-4 text-center text-white/80">
                                @if($conta['next_charge'])
                                    {{ \Carbon\Carbon::parse($conta['next_charge'])->format('d/m/Y') }}
                                @elseif($conta['trial_ends_at'])
                                    {{ \Carbon\Carbon::parse($conta['trial_ends_at'])->format('d/m/Y') }}
                                    <span class="text-yellow-300">(trial)</span>
                                @else
                                    —
                                @endif
                            </td>

                            <td class="py-2 pr-4 text-center text-white/80">
                                {{ $conta['created_at'] ? \Carbon\Carbon::parse($conta['created_at'])->format('d/m/Y') : '—' }}
                            </td>

                            {{-- Botão Histórico (oculto) --}}
                            <td class="py-2 pr-4 text-center" style="display:none">
                                <button
                                    class="btn-historico text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-lg transition"
                                    data-id="{{ $conta['id'] }}"
                                    data-subscription-id="{{ $conta['subscription_id'] }}"
                                    data-nome="{{ $conta['admin_nome'] }}">
                                    Histórico
                                </button>
                            </td>

                            {{-- Toggle Free --}}
                            <td class="py-2 pr-4 text-center">
                                <button
                                    class="btn-toggle-free text-xs px-3 py-1 rounded-lg transition font-semibold {{ $conta['free'] ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-white/10 hover:bg-white/20 text-white/60' }} text-white"
                                    data-assinatura-id="{{ $conta['id'] }}"
                                    data-free="{{ $conta['free'] ? '1' : '0' }}"
                                    title="{{ $conta['free'] ? 'Conta Free — clique para remover' : 'Clique para marcar como Free' }}">
                                    {{ $conta['free'] ? '★ Free' : 'Free' }}
                                </button>
                            </td>

                            {{-- Botão Ativar/Desativar --}}
                            <td class="py-2 text-center col-acesso">
                                <button
                                    class="btn-toggle-status text-xs px-3 py-1 rounded-lg transition font-semibold {{ $conta['users_ativo'] ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white"
                                    data-assinatura-id="{{ $conta['id'] }}"
                                    data-ativo="{{ $conta['users_ativo'] ? '1' : '0' }}">
                                    {{ $conta['users_ativo'] ? 'Desativar' : 'Ativar' }}
                                </button>
                            </td>
                        </tr>

                        {{-- Linha expansível com os usuários --}}
                        <tr class="linha-usuarios border-b border-white/5" id="usuarios-{{ $i }}">
                            <td colspan="10" class="pb-3 pt-0 px-4 bg-white/[0.02]">
                                <div class="usuarios-grid">
                                    @foreach($conta['usuarios'] as $u)
                                    @php
                                        $uPhone = preg_replace('/\D/', '', $u['telefone'] ?? '');
                                        if ($uPhone && strlen($uPhone) <= 11) { $uPhone = '55' . $uPhone; }
                                    @endphp
                                    <div class="usuario-card" style="position:relative">
                                        @if($uPhone)
                                        <a href="https://wa.me/{{ $uPhone }}" target="_blank"
                                           title="WhatsApp: {{ $u['telefone'] }}"
                                           style="position:absolute; top:8px; right:8px; color:#22c55e;"
                                           class="hover:opacity-80 transition-opacity">
                                            <svg style="width:14px;height:14px" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                            </svg>
                                        </a>
                                        @endif
                                        <p class="u-nome">
                                            {{ $u['nome'] }}
                                            @if($u['admin'])
                                                <span class="crown" title="Administrador">&#9733; admin</span>
                                            @endif
                                        </p>
                                        <p class="u-tel">{{ $u['telefone'] }}</p>
                                        <p class="u-email">{{ $u['email'] }}</p>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="10" class="py-6 text-center text-white/60">Nenhuma assinatura encontrada.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL DE HISTÓRICO --}}
    <div id="modalHistorico" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <div>
                    <h3 class="text-white font-semibold text-base" id="modalNome">Histórico Financeiro</h3>
                    <p class="text-gray-400 text-xs mt-0.5">Pagamentos registrados</p>
                </div>
                <button id="fecharModal" class="text-gray-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div id="modalConteudo"></div>
            </div>
        </div>
    </div>

    @section('scripts')
    <script>
        // ---- Impressão ----
        function imprimirTabela() { window.print(); }

        // ---- Dados de histórico (agrupados por id / subscription_id) ----
        const historicoPix    = @json($historicoPix);
        const historicoCartao = @json($historicoCartao);

        // ---- Modal ----
        const modal         = document.getElementById('modalHistorico');
        const modalNome     = document.getElementById('modalNome');
        const modalConteudo = document.getElementById('modalConteudo');

        function buildTabelaPix(pagamentos) {
            if (!pagamentos || pagamentos.length === 0) return '';
            let html = `
                <h4 class="text-white text-sm font-semibold mb-2 mt-4">PIX</h4>
                <table class="w-full text-sm text-left text-gray-300 mb-4">
                    <thead>
                        <tr class="border-b border-white/10 text-xs uppercase tracking-wide opacity-60">
                            <th class="pb-2 pr-4">Tipo</th>
                            <th class="pb-2 pr-4">Valor</th>
                            <th class="pb-2 pr-4">Pago em</th>
                            <th class="pb-2">TxID</th>
                        </tr>
                    </thead>
                    <tbody>`;
            pagamentos.forEach(p => {
                const badgeClass = p.tipo === 'PIX' ? 'badge-pix' : 'badge-pixauto';
                const label      = p.tipo === 'PIX' ? 'PIX' : 'PIX Auto';
                const valor      = parseFloat(p.valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                html += `
                    <tr class="border-b border-white/5">
                        <td class="py-2 pr-4"><span class="badge-tipo ${badgeClass}">${label}</span></td>
                        <td class="py-2 pr-4 text-green-400 font-semibold">${valor}</td>
                        <td class="py-2 pr-4 text-gray-300">${p.pago_em}</td>
                        <td class="py-2 text-gray-500 text-xs font-mono">${p.txid}</td>
                    </tr>`;
            });
            html += '</tbody></table>';
            return html;
        }

        function buildTabelaCartao(cobranças) {
            if (!cobranças || cobranças.length === 0) return '';
            let html = `
                <h4 class="text-white text-sm font-semibold mb-2 mt-4">Cartão de Crédito (recorrências)</h4>
                <table class="w-full text-sm text-left text-gray-300 mb-4">
                    <thead>
                        <tr class="border-b border-white/10 text-xs uppercase tracking-wide opacity-60">
                            <th class="pb-2 pr-4">Status</th>
                            <th class="pb-2 pr-4">Valor</th>
                            <th class="pb-2 pr-4">Pago em</th>
                            <th class="pb-2">Charge ID</th>
                        </tr>
                    </thead>
                    <tbody>`;
            cobranças.forEach(c => {
                const statusClass = c.status === 'paid' ? 'badge-status-ativo' : 'badge-status-inativo';
                const statusLabel = c.status === 'paid' ? 'Pago' : c.status;
                const valor       = parseFloat(c.valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                const pagoEm      = c.pago_em || '—';
                html += `
                    <tr class="border-b border-white/5">
                        <td class="py-2 pr-4"><span class="badge-tipo ${statusClass}">${statusLabel}</span></td>
                        <td class="py-2 pr-4 text-green-400 font-semibold">${valor}</td>
                        <td class="py-2 pr-4 text-gray-300">${pagoEm}</td>
                        <td class="py-2 text-gray-500 text-xs font-mono">${c.charge_id}</td>
                    </tr>`;
            });
            html += '</tbody></table>';
            return html;
        }

        document.querySelectorAll('.btn-historico').forEach(btn => {
            btn.addEventListener('click', () => {
                const id             = btn.dataset.id;
                const subscriptionId = btn.dataset.subscriptionId;
                const nome           = btn.dataset.nome;

                const pagamentosPix    = historicoPix[id]          || [];
                const cobrancasCartao  = historicoCartao[subscriptionId] || [];

                modalNome.textContent = nome + ' — Histórico Financeiro';

                const htmlPix    = buildTabelaPix(pagamentosPix);
                const htmlCartao = buildTabelaCartao(cobrancasCartao);

                if (!htmlPix && !htmlCartao) {
                    modalConteudo.innerHTML = '<p class="text-gray-500 text-sm text-center py-6">Nenhum pagamento registrado para este cliente.</p>';
                } else {
                    modalConteudo.innerHTML = htmlPix + htmlCartao;
                }

                modal.classList.add('aberto');
            });
        });

        document.getElementById('fecharModal').addEventListener('click', () => modal.classList.remove('aberto'));
        modal.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('aberto'); });

        // ---- Toggle Ativar/Desativar usuários ----
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        document.querySelectorAll('.btn-toggle-status').forEach(btn => {
            btn.addEventListener('click', () => {
                const assinaturaId = btn.dataset.assinaturaId;
                const ativo        = btn.dataset.ativo === '1';

                const confirmMsg = ativo
                    ? 'Desativar o acesso de todos os usuários desta conta?'
                    : 'Ativar o acesso de todos os usuários desta conta?';

                if (!confirm(confirmMsg)) return;

                btn.disabled = true;
                btn.textContent = '...';

                fetch('{{ route("financeiro.toggle.status") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ assinatura_id: assinaturaId }),
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const novoAtivo = res.ativo;
                        btn.dataset.ativo  = novoAtivo ? '1' : '0';
                        btn.textContent    = novoAtivo ? 'Desativar' : 'Ativar';
                        btn.className      = btn.className.replace(
                            novoAtivo ? /bg-green-\d00 hover:bg-green-\d00/ : /bg-red-\d00 hover:bg-red-\d00/,
                            ''
                        );
                        if (novoAtivo) {
                            btn.classList.remove('bg-green-600', 'hover:bg-green-700');
                            btn.classList.add('bg-red-600', 'hover:bg-red-700');
                        } else {
                            btn.classList.remove('bg-red-600', 'hover:bg-red-700');
                            btn.classList.add('bg-green-600', 'hover:bg-green-700');
                        }
                    } else {
                        alert(res.message || 'Erro ao alterar status.');
                    }
                })
                .catch(() => alert('Erro de comunicação.'))
                .finally(() => { btn.disabled = false; });
            });
        });

        // ---- Toggle Free ----
        document.querySelectorAll('.btn-toggle-free').forEach(btn => {
            btn.addEventListener('click', () => {
                const assinaturaId = btn.dataset.assinaturaId;
                const isFree       = btn.dataset.free === '1';
                const msg = isFree
                    ? 'Remover condição Free desta conta? Voltará a cobrar normalmente.'
                    : 'Marcar esta conta como Free? Usuários poderão ser adicionados sem cobrança extra.';
                if (!confirm(msg)) return;

                btn.disabled = true;
                fetch('{{ route("financeiro.toggle.free") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ assinatura_id: assinaturaId }),
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const novo = res.free;
                        btn.dataset.free = novo ? '1' : '0';
                        btn.textContent  = novo ? '★ Free' : 'Free';
                        btn.title        = novo ? 'Conta Free — clique para remover' : 'Clique para marcar como Free';
                        btn.className = btn.className
                            .replace(/bg-emerald-\d+\s*hover:bg-emerald-\d+/, '')
                            .replace(/bg-white\/\d+\s*hover:bg-white\/\d+\s*text-white\/\d+/, '');
                        if (novo) {
                            btn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
                            btn.classList.remove('bg-white/10', 'hover:bg-white/20', 'text-white/60');
                        } else {
                            btn.classList.add('bg-white/10', 'hover:bg-white/20', 'text-white/60');
                            btn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
                        }
                    }
                })
                .catch(() => alert('Erro de comunicação.'))
                .finally(() => { btn.disabled = false; });
            });
        });

        // ---- Expansão de usuários ----
        document.querySelectorAll('.btn-expand').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = document.getElementById(btn.dataset.target);
                if (!target) return;
                const aberto = target.classList.toggle('aberta');
                btn.classList.toggle('aberto', aberto);
            });
        });

        // ---- Busca na tabela ----
        document.getElementById('busca').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.linha-conta').forEach(row => {
                const nome  = row.dataset.nome  || '';
                const email = row.dataset.email || '';
                const visivel = nome.includes(q) || email.includes(q);
                row.style.display = visivel ? '' : 'none';

                const btn = row.querySelector('.btn-expand');
                if (btn) {
                    const linhaUsuarios = document.getElementById(btn.dataset.target);
                    if (linhaUsuarios) linhaUsuarios.style.display = visivel ? '' : 'none';
                }
            });
        });

        // ---- Gráficos ----
        const labels   = @json($meses->pluck('mes'));
        const totais   = @json($meses->pluck('total'));
        const receitas = @json($meses->pluck('receita'));

        const gridColor = 'rgba(255,255,255,0.15)';
        const tickColor = 'rgba(255,255,255,0.8)';
        const baseOpts  = {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: tickColor } },
                y: { grid: { color: gridColor }, ticks: { color: tickColor }, beginAtZero: true }
            }
        };

        new Chart(document.getElementById('chartNovas'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{ data: totais, backgroundColor: 'rgba(99,102,241,0.7)', borderRadius: 4 }]
            },
            options: baseOpts,
        });

        new Chart(document.getElementById('chartReceita'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    data: receitas,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.15)',
                    pointBackgroundColor: '#10b981',
                    tension: 0.3,
                    fill: true,
                }]
            },
            options: {
                ...baseOpts,
                scales: {
                    ...baseOpts.scales,
                    y: {
                        ...baseOpts.scales.y,
                        ticks: {
                            color: tickColor,
                            callback: v => 'R$ ' + v.toLocaleString('pt-BR', { minimumFractionDigits: 2 })
                        }
                    }
                }
            },
        });
    </script>
    @endsection

    {{-- SEÇÃO EXCLUSIVA PARA IMPRESSÃO --}}
    <div class="print-section">
        <p class="ps-titulo">Dashboard Financeiro</p>
        <p class="ps-sub">Gerado em {{ now()->format('d/m/Y \à\s H:i') }} &nbsp;·&nbsp; {{ $contas->count() }} conta(s)</p>
        <hr class="ps-hr">

        @foreach($contas as $conta)
        @php
            $labelTipoPs = match(strtolower($conta['tipo'] ?? '')) {
                'pix'            => 'PIX',
                'pix_automatico' => 'PIX Auto',
                default          => 'Cartão',
            };
            $proximaPs = $conta['next_charge']
                ? \Carbon\Carbon::parse($conta['next_charge'])->format('d/m/Y')
                : ($conta['trial_ends_at'] ? \Carbon\Carbon::parse($conta['trial_ends_at'])->format('d/m/Y').' (trial)' : '—');
            $membros = collect($conta['usuarios'])->filter(fn($u) => !$u['admin']);
        @endphp

        <p class="ps-assinante">{{ $conta['admin_nome'] }}</p>
        <p class="ps-meta">{{ $conta['admin_email'] }} &nbsp;·&nbsp; {{ $labelTipoPs }} &nbsp;·&nbsp; R$ {{ number_format($conta['preco_total'] ?? 0, 2, ',', '.') }} &nbsp;·&nbsp; Próx. cobrança: {{ $proximaPs }}</p>

        @foreach($membros as $u)
            <p class="ps-usuario">{{ $u['nome'] }}{{ ($u['telefone'] ?? '—') !== '—' ? ' · '.$u['telefone'] : '' }} · {{ $u['email'] }}</p>
        @endforeach

        @endforeach
    </div>
</x-app-layout>
