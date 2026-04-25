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
    </style>
    @endsection

    <div class="py-8 px-4 max-w-7xl mx-auto bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] rounded-lg mt-5">

        {{-- Cabeçalho --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Dashboard Financeiro</h1>
                <p class="text-sm text-white/70 mt-1">Visão geral das assinaturas · {{ now()->format('d/m/Y H:i') }}</p>
            </div>
            <button onclick="window.print()"
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
            <div class="card-stat cursor-pointer hover:ring-2 hover:ring-emerald-500/50 transition"
                 onclick="document.getElementById('modalAtivas').classList.add('aberto')"
                 title="Clique para ver os assinantes ativos">
                <p class="label">Assinaturas Ativas</p>
                <p class="value text-emerald-400">{{ $totalAtivas }}</p>
                <p class="text-white/50 text-xs mt-1">clique para detalhes →</p>
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
        <div class="bg-black/20 border border-white/10 rounded-2xl p-5 mb-8">
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
                    class="bg-white/10 text-white text-sm rounded-lg px-3 py-1.5 border border-white/20 outline-none w-64
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
                            <th class="pb-2 pr-4 text-center">Histórico</th>
                            <th class="pb-2 text-center">Acesso</th>
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
                                <span class="btn-expand font-medium text-white" data-target="usuarios-{{ $i }}">
                                    <span class="seta text-gray-400">&#9654;</span>
                                    {{ $conta['admin_nome'] }}
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
                                    $badgeStatus = match($status) {
                                        'ativo'  => 'badge-status-ativo',
                                        'trial'  => 'badge-status-trial',
                                        default  => 'badge-status-inativo',
                                    };
                                @endphp
                                <span class="badge-tipo {{ $badgeStatus }}">{{ ucfirst($conta['status']) }}</span>
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

                            {{-- Botão Histórico --}}
                            <td class="py-2 pr-4 text-center">
                                <button
                                    class="btn-historico text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-lg transition"
                                    data-id="{{ $conta['id'] }}"
                                    data-subscription-id="{{ $conta['subscription_id'] }}"
                                    data-nome="{{ $conta['admin_nome'] }}">
                                    Histórico
                                </button>
                            </td>

                            {{-- Botão Ativar/Desativar --}}
                            <td class="py-2 text-center">
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
                                    <div class="usuario-card">
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

    {{-- MODAL ASSINATURAS ATIVAS --}}
    <div id="modalAtivas" class="modal-overlay">
        <div class="modal-box modal-box-lg">
            <div class="modal-header">
                <div>
                    <h3 class="text-white font-semibold text-base">Assinantes Ativos</h3>
                    <p class="text-gray-400 text-xs mt-0.5">{{ $contasAtivas->count() }} conta(s) ativas</p>
                </div>
                <button onclick="document.getElementById('modalAtivas').classList.remove('aberto')" class="text-gray-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <table class="w-full text-sm text-left text-gray-300">
                    <thead>
                        <tr class="border-b border-white/10 text-xs uppercase tracking-wide opacity-60">
                            <th class="pb-2 pr-4">Admin</th>
                            <th class="pb-2 pr-4">E-mail</th>
                            <th class="pb-2 pr-4 text-center">Usuários</th>
                            <th class="pb-2 pr-4 text-center">Tipo</th>
                            <th class="pb-2 pr-4 text-right">Valor</th>
                            <th class="pb-2 text-center">Próx. Cobrança</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contasAtivas as $ativa)
                        @php
                            $tipo = strtolower($ativa['tipo'] ?? 'cartao');
                            $badgeTipo = match($tipo) {
                                'pix'            => 'badge-pix',
                                'pix_automatico' => 'badge-pixauto',
                                default          => 'badge-cartao',
                            };
                            $labelTipo = match($tipo) {
                                'pix'            => 'PIX',
                                'pix_automatico' => 'PIX Auto',
                                default          => 'Cartão',
                            };
                        @endphp
                        <tr class="border-b border-white/5 hover:bg-white/5 transition">
                            <td class="py-2 pr-4 font-medium text-white">{{ $ativa['admin_nome'] }}</td>
                            <td class="py-2 pr-4 text-white/75">{{ $ativa['admin_email'] }}</td>
                            <td class="py-2 pr-4 text-center">{{ $ativa['total_users'] }}</td>
                            <td class="py-2 pr-4 text-center">
                                <span class="badge-tipo {{ $badgeTipo }}">{{ $labelTipo }}</span>
                            </td>
                            <td class="py-2 pr-4 text-right text-green-400 font-semibold">
                                R$ {{ number_format($ativa['preco_total'] ?? 0, 2, ',', '.') }}
                            </td>
                            <td class="py-2 text-center text-white/80">
                                {{ $ativa['next_charge'] ? \Carbon\Carbon::parse($ativa['next_charge'])->format('d/m/Y') : '—' }}
                            </td>
                        </tr>
                        @endforeach
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

        // Fecha modal de ativos ao clicar no fundo
        const modalAtivas = document.getElementById('modalAtivas');
        modalAtivas.addEventListener('click', e => { if (e.target === modalAtivas) modalAtivas.classList.remove('aberto'); });

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
</x-app-layout>
