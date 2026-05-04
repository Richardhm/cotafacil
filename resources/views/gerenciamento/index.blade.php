<x-app-layout>
    <!-- Cabeçalho de valor -->
    <div class="flex p-2 mt-2 justify-between flex-wrap items-center w-full bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] rounded md:w-[99%]">
        <div class="flex items-center flex-wrap gap-4">
            <p class="text-sm text-white text-center">
                Valor por usuário <strong class="text-blue-200 font-bold">R$ 29,90</strong>
            </p>
        </div>
        <div class="flex items-center text-sm gap-2 flex-wrap">
            <p>
                <span class="text-white">📈 Valor Total:</span>
                <strong class="text-red-500 preco_total">R$ {{ number_format($valor, 2, ',', '.') }}</strong>
            </p>
            <a class="p-1 bg-orange-500 text-white rounded" href="{{ route('assinatura.edit') }}">Pagar</a>
        </div>
    </div>

    <!--Modal Editar-->
    <x-modals.edit-user-modal />
    <!--Fim Modal Editar-->

    <!-- Modal Cadastrar -->
    <x-modals.cadastrar-user-modal />
    <!-- Fim Modal Cadastrar -->

    <!-- ═══ Modal de Ativação em Lote ═══ -->
    <div id="ativar-modal" class="fixed inset-0 hidden flex items-center justify-center z-50">
        <div class="bg-[rgba(20,20,20,0.97)] backdrop-blur-[15px] shadow-2xl border border-white/20 rounded-xl p-6 w-[26rem] max-h-[92vh] overflow-auto">

            <div class="flex justify-between items-center mb-4">
                <h2 id="ativar-titulo" class="text-lg font-semibold text-white">Ativar Usuários</h2>
                <button id="ativar-fechar" class="text-white/60 hover:text-white text-xl font-bold leading-none">✕</button>
            </div>

            <div class="mb-4 p-3 bg-white/5 border border-white/10 rounded-lg text-sm">
                <p class="text-white/60">Usuários: <span id="ativar-qtd" class="text-white font-semibold"></span></p>
                <p class="text-white/60 mt-1">Total a pagar: <span id="ativar-total" class="text-yellow-300 font-bold text-base"></span></p>
            </div>

            <!-- Escolha de método -->
            <div id="step-metodo">
                <p class="text-white/70 text-sm mb-3">Escolha a forma de pagamento:</p>
                <div class="flex gap-3 mb-4">
                    <button id="btn-pix"
                            class="flex-1 flex flex-col items-center gap-1 p-4 rounded-xl border-2 border-white/20 bg-white/5 text-white hover:border-green-400 hover:bg-green-500/10 transition">
                        <span class="text-2xl">📱</span>
                        <span class="font-semibold text-sm">PIX</span>
                        <span class="text-white/50 text-xs">Aprovação imediata</span>
                    </button>
                    <button id="btn-cartao"
                            class="flex-1 flex flex-col items-center gap-1 p-4 rounded-xl border-2 border-white/20 bg-white/5 text-white hover:border-blue-400 hover:bg-blue-500/10 transition">
                        <span class="text-2xl">💳</span>
                        <span class="font-semibold text-sm">Cartão</span>
                        <span class="text-white/50 text-xs">Crédito</span>
                    </button>
                </div>
                <button id="btn-cancelar" class="text-white/50 hover:text-white text-sm underline">Cancelar</button>
            </div>

            <!-- PIX: CPF -->
            <div id="step-pix-cpf" class="hidden">
                <p class="text-white/70 text-sm mb-3">Informe seu CPF para gerar a cobrança:</p>
                <input type="text" id="pix-cpf" placeholder="000.000.000-00" maxlength="14"
                       class="w-full rounded-md border border-white/30 bg-white/10 text-white placeholder-white/30 px-3 py-2 text-sm mb-2 focus:outline-none focus:border-green-400">
                <div id="pix-cpf-erro" class="hidden text-red-300 text-xs mb-2"></div>
                <div class="flex justify-between mt-1">
                    <button id="pix-voltar" class="text-white/50 hover:text-white text-sm underline">← Voltar</button>
                    <button id="pix-gerar" class="bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
                        Gerar QR Code
                    </button>
                </div>
            </div>

            <!-- PIX: QR Code -->
            <div id="step-pix-qr" class="hidden">
                <div id="pix-loading" class="text-center py-6">
                    <div class="inline-block w-7 h-7 border-4 border-white/20 border-t-white rounded-full animate-spin mb-2"></div>
                    <p class="text-white/60 text-sm">Gerando QR Code…</p>
                </div>
                <div id="pix-qr-content" class="hidden text-center">
                    <img id="pix-img" src="" alt="QR" class="mx-auto w-44 h-44 mb-3 rounded-lg bg-white p-1">
                    <div class="relative mb-3">
                        <input type="text" id="pix-copia" readonly
                               class="w-full rounded-md border border-white/20 bg-white/5 text-white/70 px-3 py-2 pr-16 text-xs font-mono">
                        <button id="pix-copiar" class="absolute right-2 top-1/2 -translate-y-1/2 text-xs bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded">
                            Copiar
                        </button>
                    </div>
                    <div class="flex items-center justify-center gap-2">
                        <div class="inline-block w-3 h-3 border-2 border-white/20 border-t-white rounded-full animate-spin"></div>
                        <p class="text-white/50 text-xs">Aguardando… expira em <span id="pix-countdown" class="text-yellow-300 font-bold"></span></p>
                    </div>
                </div>
            </div>

            <!-- Cartão -->
            <div id="step-cartao" class="hidden">
                <div class="mb-3">
                    <label class="block text-sm text-white mb-1">Número do cartão</label>
                    <input type="text" id="c-num" placeholder="0000 0000 0000 0000" maxlength="19"
                           class="w-full rounded-md border border-white/30 bg-white/10 text-white placeholder-white/30 px-3 py-2 text-sm focus:outline-none focus:border-blue-400">
                </div>
                <div class="flex gap-3 mb-3">
                    <div class="flex-1">
                        <label class="block text-sm text-white mb-1">Validade</label>
                        <input type="text" id="c-val" placeholder="MM/AA" maxlength="5"
                               class="w-full rounded-md border border-white/30 bg-white/10 text-white placeholder-white/30 px-3 py-2 text-sm focus:outline-none focus:border-blue-400">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm text-white mb-1">CVV</label>
                        <input type="text" id="c-cvv" placeholder="000" maxlength="4"
                               class="w-full rounded-md border border-white/30 bg-white/10 text-white placeholder-white/30 px-3 py-2 text-sm focus:outline-none focus:border-blue-400">
                    </div>
                </div>
                <div id="cartao-erro" class="hidden text-red-300 text-xs mb-2"></div>
                <div class="flex justify-between items-center">
                    <button id="cartao-voltar" class="text-white/50 hover:text-white text-sm underline">← Voltar</button>
                    <button id="cartao-pagar" class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
                        Pagar
                    </button>
                </div>
            </div>

            <!-- Sucesso -->
            <div id="step-sucesso" class="hidden text-center py-4">
                <div class="text-green-400 text-5xl mb-3">✓</div>
                <h3 class="text-white font-bold text-base mb-2">Pagamento confirmado!</h3>
                <p class="text-white/60 text-sm">Os usuários foram ativados e já podem acessar o sistema.</p>
            </div>
        </div>
    </div>
    <!-- ═══ Fim Modal Ativação ═══ -->

    <div class="w-[99%] mx-auto flex flex-wrap justify-between gap-4 mt-3">

        <!-- Coluna esquerda: Layout -->
        <div class="w-full md:w-[32%]">
            <h3 class="text-white font-semibold text-base sm:text-lg md:text-xl lg:text-2xl mb-1">
                Gerenciador de Layout e UF de Referência
            </h3>
            <div class="flex w-full justify-around bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border-white border mt-1 mb-1 rounded p-1 items-center gap-2">
                <label for="regiao" class="block text-sm font-medium text-white w-full md:w-[50%]">
                    Região (UF) de Preferência
                </label>
                <select name="regiao" id="regiao"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700 bg-white w-[40%]">
                    <option value="" disabled selected>UF</option>
                    @foreach($cidades as $uf => $grupo)
                        <option value="{{ $uf }}" {{ auth()->user()->uf_preferencia === $uf ? 'selected' : '' }}>{{ $uf }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($layouts as $layout)
                    <label class="layout relative group flex flex-col items-center border rounded p-2 bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px]">
                        <input type="radio" id="layout_{{ $layout->id }}" name="layout_id" value="{{ $layout->id }}"
                               {{ $layout->id == $user->layout_id ? 'checked' : '' }}
                               class="w-6 h-6 cursor-pointer rounded-full border-4 border-white transition-transform hover:scale-110 focus:ring-2 focus:ring-blue-500" />
                        <div class="w-full h-[150px] flex justify-center">
                            <img src="{{ $folder ? asset($folder.'/'.$layout->imagem) : asset($layout->imagem) }}"
                                 alt="{{ $layout->nome }}" class="w-full max-w-[90%] rounded-lg" />
                        </div>
                        <p class="mt-1 text-center text-sm font-semibold text-white">{{ $layout->nome }}</p>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Coluna direita: Ativos + Inativos -->
        <div class="w-full md:w-[65%] flex flex-col">

            <!-- Usuários Ativos -->
            <div>
                <div class="flex items-center">
                    <h3 class="text-white font-semibold text-base">Usuários Ativos</h3>
                    <button id="toggle-modal"
                            class="w-8 ml-3 h-8 flex items-center justify-center rounded-full bg-green-500 border-white border-4 text-white text-2xl font-bold shadow-lg transition hover:scale-110"
                            title="Adicionar usuário">
                        +
                    </button>
                </div>
                <div id="user-table"
                     class="max-h-[400px] h-[270px] overflow-y-auto rounded-lg scrollbar-thin scrollbar-thumb-yellow-500 scrollbar-track-white/10">
                    @include('partials.user-table', ['users' => $users])
                </div>
            </div>

            <!-- Usuários Inativos -->
            <div id="inativos-section" class="mt-3 {{ $pendentes->isEmpty() ? 'hidden' : '' }}">
                <div class="bg-[rgba(254,254,254,0.10)] backdrop-blur-[15px] border border-white/20 rounded-xl overflow-hidden">
                    <!-- Header -->
                    <div class="px-4 py-3 border-b border-white/10 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span>
                        <h3 class="text-white font-semibold text-sm">Usuários Inativos</h3>
                        <span class="text-white/40 text-xs ml-1">— clique em Ativar para liberar o acesso</span>
                    </div>

                    <!-- Tabela -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-white/10 text-white/50 text-xs uppercase">
                                    <th class="px-4 py-2 text-left">Nome</th>
                                    <th class="px-4 py-2 text-left">E-mail</th>
                                    <th class="px-4 py-2 text-left">Telefone</th>
                                    <th class="px-4 py-2 text-right">Valor</th>
                                    <th class="px-4 py-2 text-center">Remover</th>
                                </tr>
                            </thead>
                            <tbody id="inativos-tbody">
                                @forelse($pendentes as $p)
                                    <tr id="pendente-{{ $p->id }}" class="border-b border-white/10 hover:bg-white/5 transition">
                                        <td class="px-4 py-2 text-white text-sm">{{ $p->name }}</td>
                                        <td class="px-4 py-2 text-white/70 text-sm">{{ $p->email }}</td>
                                        <td class="px-4 py-2 text-white/70 text-sm">{{ $p->phone }}</td>
                                        <td class="px-4 py-2 text-right text-yellow-300 text-sm font-semibold">
                                            R$ {{ number_format($p->valor, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <button onclick="removerPendente({{ $p->id }}, this)"
                                                    class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1 rounded-lg transition">
                                                Remover
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr data-empty>
                                        <td colspan="5" class="px-4 py-4 text-white/40 text-sm text-center">Nenhum usuário inativo.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <!-- Footer com total + botão Ativar -->
                            <tfoot id="inativos-footer">
                                <tr class="border-t border-white/20 bg-white/5">
                                    <td colspan="3" class="px-4 py-3 text-white/60 text-sm">
                                        <span id="inativos-count">{{ $pendentes->count() }}</span>
                                        {{ $pendentes->count() === 1 ? 'usuário' : 'usuários' }} ×
                                        R$ 29,90
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-yellow-300 font-bold" id="inativos-total">
                                            R$ {{ number_format($pendentes->sum('valor'), 2, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button id="btn-ativar-todos"
                                                onclick="abrirModalAtivacao()"
                                                class="bg-green-500 hover:bg-green-600 text-white text-xs px-4 py-2 rounded-lg font-semibold transition {{ $pendentes->isEmpty() ? 'hidden' : '' }}">
                                            Ativar
                                        </button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div><!-- fim coluna direita -->
    </div>

    @section('css')
        <style>
            #user-table::-webkit-scrollbar { width: 8px; }
            #user-table::-webkit-scrollbar-track { background: rgba(255,255,255,0.1); }
            #user-table::-webkit-scrollbar-thumb { background-color: #facc15; border-radius: 8px; }
            #user-table { scrollbar-color: #facc15 rgba(255,255,255,0.1); scrollbar-width: thin; }
        </style>
    @endsection

    @section('scripts')
        <script>
            const routes = {
                storeUser:           "{{ route('storeUser') }}",
                assinaturaEdit:      "{{ route('assinatura.edit') }}",
                gerenciamentoRegiao: "{{ route('gerenciamento.regiao') }}",
                layoutsSelect:       "{{ route('layouts.select') }}",
                usersGet:            "{{ route('users.get') }}",
                usersAlterar:        "{{ route('users.alterar') }}",
                usersUpdate:         "{{ route('users.update') }}",
                deletarUser:         "{{ route('deletar.user') }}",
                equipeRemover:       "{{ url('/gerenciamento/equipe/remover') }}",
                equipePixTodos:      "{{ route('equipe.pix-todos') }}",
                equipeVerificar:     "{{ route('equipe.verificar') }}",
                equipeCartaoTodos:   "{{ route('equipe.cartao-todos') }}",
            };
            const csrfToken = "{{ csrf_token() }}";
        </script>
        <script src="{{ asset('js/gerencimento.js') }}"></script>
        <script type='text/javascript'>
            (function(){
                var gnHost = '{{ config("gerencianet.is_sandbox") ? "cobrancas-h.api.efipay.com.br" : "cobrancas.api.efipay.com.br" }}';
                var gnId   = '{{ config("gerencianet.cdn_account_id") }}';
                var v = parseInt(Math.random()*1000000);
                var s = document.createElement('script');
                s.type='text/javascript'; s.async=false; s.id=gnId;
                s.src='https://'+gnHost+'/v1/cdn/'+gnId+'/'+v;
                if(!document.getElementById(gnId)) document.head.appendChild(s);
                window.$gn={validForm:true,processed:false,done:{},ready:function(fn){$gn.done=fn;}};
            })();
        </script>
        <script>
        // ── Adicionar linha na tabela de inativos ──────────────────
        window.adicionarLinhaInativa = function (u) {
            const section = document.getElementById('inativos-section');
            const tbody   = document.getElementById('inativos-tbody');
            const btnAtivar = document.getElementById('btn-ativar-todos');
            if (!tbody) return;

            tbody.querySelector('[data-empty]')?.remove();

            const tr = document.createElement('tr');
            tr.id        = 'pendente-' + u.id;
            tr.className = 'border-b border-white/10 hover:bg-white/5 transition';
            tr.innerHTML = `
                <td class="px-4 py-2 text-white text-sm">${u.name}</td>
                <td class="px-4 py-2 text-white/70 text-sm">${u.email}</td>
                <td class="px-4 py-2 text-white/70 text-sm">${u.phone}</td>
                <td class="px-4 py-2 text-right text-yellow-300 text-sm font-semibold">${u.valor_fmt}</td>
                <td class="px-4 py-2 text-center">
                    <button onclick="removerPendente(${u.id}, this)"
                            class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1 rounded-lg transition">
                        Remover
                    </button>
                </td>`;
            tbody.appendChild(tr);
            section.classList.remove('hidden');
            btnAtivar?.classList.remove('hidden');
            atualizarFooter();
        };

        // ── Recalcula total e contagem no footer ───────────────────
        function atualizarFooter() {
            const rows = document.querySelectorAll('#inativos-tbody tr:not([data-empty])');
            const count = rows.length;
            const total = Array.from(rows).reduce((sum, tr) => {
                const v = tr.querySelector('td:nth-child(4)')?.textContent.trim()
                    .replace('R$','').replace('.','').replace(',','.').trim();
                return sum + (parseFloat(v) || 0);
            }, 0);

            const countEl = document.getElementById('inativos-count');
            const totalEl = document.getElementById('inativos-total');
            if (countEl) countEl.textContent = count;
            if (totalEl) totalEl.textContent = 'R$ ' + total.toFixed(2).replace('.',',');
        }

        // ── Remover pendente ───────────────────────────────────────
        window.removerPendente = function (id, btn) {
            if (!confirm('Remover este usuário da fila?')) return;
            fetch(routes.equipeRemover + '/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    document.getElementById('pendente-' + id)?.remove();
                    const tbody = document.getElementById('inativos-tbody');
                    if (tbody && tbody.querySelectorAll('tr:not([data-empty])').length === 0) {
                        document.getElementById('inativos-section').classList.add('hidden');
                    } else {
                        atualizarFooter();
                    }
                }
            }).catch(() => alert('Erro de conexão.'));
        };

        // ── EfiPay checkout object (set when SDK ready) ────────────
        let gnCheckout = null;
        $gn.ready(function(checkout){ gnCheckout = checkout; });

        function getBandeira(numero) {
            const n = numero.replace(/\s/g,'');
            if (/^4/.test(n))                                             return 'visa';
            if (/^5[1-5]/.test(n) || /^2[2-7]/.test(n))                 return 'mastercard';
            if (/^3[47]/.test(n))                                         return 'amex';
            if (/^(636368|438935|504175|451416|509\d{3}|5067[0-6]|36297[89])/.test(n)) return 'elo';
            if (/^(606282|637095|637568|637599|637609|637612)/.test(n))  return 'hipercard';
            return null;
        }

        // ── Modal de Ativação ──────────────────────────────────────
        (function () {
            let pollingTimer   = null;
            let countdownTimer = null;
            let pixExpireAt    = null;

            const el = id => document.getElementById(id);

            function steps() {
                return ['step-metodo','step-pix-cpf','step-pix-qr','step-cartao','step-sucesso'];
            }
            function showStep(s) {
                steps().forEach(x => el(x).classList.add('hidden'));
                el(s).classList.remove('hidden');
            }
            function stopPolling()   { if (pollingTimer)   { clearInterval(pollingTimer);   pollingTimer   = null; } }
            function stopCountdown() { if (countdownTimer) { clearInterval(countdownTimer); countdownTimer = null; } }

            function closeModal() {
                stopPolling(); stopCountdown();
                el('ativar-modal').classList.add('hidden');
            }

            function countPendentes() {
                return document.querySelectorAll('#inativos-tbody tr:not([data-empty])').length;
            }
            function totalPendentes() {
                return parseFloat(el('inativos-total')?.textContent
                    .replace('R$','').replace('.','').replace(',','.').trim()) || 0;
            }

            window.abrirModalAtivacao = function () {
                const qtd   = countPendentes();
                const total = totalPendentes();
                if (qtd === 0) return;

                el('ativar-qtd').textContent   = qtd + (qtd === 1 ? ' usuário' : ' usuários');
                el('ativar-total').textContent = 'R$ ' + total.toFixed(2).replace('.',',');
                el('pix-cpf').value = '';
                el('pix-cpf-erro').classList.add('hidden');
                el('pix-loading').classList.remove('hidden');
                el('pix-qr-content').classList.add('hidden');
                ['c-num','c-val','c-cvv'].forEach(id => { if(el(id)) el(id).value=''; });
                el('cartao-erro').classList.add('hidden');
                showStep('step-metodo');
                el('ativar-modal').classList.remove('hidden');
            };

            el('ativar-fechar').addEventListener('click', closeModal);
            el('btn-cancelar').addEventListener('click', closeModal);
            el('ativar-modal').addEventListener('click', e => { if (e.target === el('ativar-modal')) closeModal(); });

            // ── PIX ──
            el('btn-pix').addEventListener('click', () => showStep('step-pix-cpf'));
            el('pix-voltar').addEventListener('click', () => showStep('step-metodo'));

            el('pix-cpf').addEventListener('input', e => {
                let v = e.target.value.replace(/\D/g,'').slice(0,11);
                v = v.replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d{1,2})$/,'$1-$2');
                e.target.value = v;
            });

            el('pix-gerar').addEventListener('click', async () => {
                const cpf = el('pix-cpf').value.replace(/\D/g,'');
                if (cpf.length < 11) {
                    el('pix-cpf-erro').textContent = 'CPF inválido.';
                    el('pix-cpf-erro').classList.remove('hidden');
                    return;
                }
                el('pix-cpf-erro').classList.add('hidden');
                showStep('step-pix-qr');
                el('pix-loading').classList.remove('hidden');
                el('pix-qr-content').classList.add('hidden');

                const body = new FormData();
                body.append('_token', csrfToken);
                body.append('cpf', cpf);

                try {
                    const r = await fetch(routes.equipePixTodos, { method: 'POST', body });
                    const d = await r.json();
                    if (!d.success) {
                        showStep('step-pix-cpf');
                        el('pix-cpf-erro').textContent = d.error ?? 'Erro ao gerar PIX.';
                        el('pix-cpf-erro').classList.remove('hidden');
                        return;
                    }
                    el('pix-img').src         = d.imagem;
                    el('pix-copia').value     = d.copiacola.trim();
                    el('pix-loading').classList.add('hidden');
                    el('pix-qr-content').classList.remove('hidden');
                    pixExpireAt = Date.now() + 30 * 60 * 1000;

                    stopCountdown();
                    countdownTimer = setInterval(() => {
                        const s = Math.max(0, Math.round((pixExpireAt - Date.now()) / 1000));
                        const m = String(Math.floor(s/60)).padStart(2,'0');
                        el('pix-countdown').textContent = m + ':' + String(s%60).padStart(2,'0');
                        if (s === 0) stopCountdown();
                    }, 1000);

                    const txid = d.txid;
                    stopPolling();
                    pollingTimer = setInterval(async () => {
                        try {
                            const pr = await fetch(routes.equipeVerificar + '?txid=' + txid);
                            const pd = await pr.json();
                            if (pd.pago) {
                                stopPolling(); stopCountdown();
                                showStep('step-sucesso');
                                setTimeout(() => { closeModal(); location.reload(); }, 2000);
                            } else if (pd.expirado) {
                                stopPolling(); stopCountdown();
                                showStep('step-pix-cpf');
                                el('pix-cpf-erro').textContent = 'PIX expirado. Tente novamente.';
                                el('pix-cpf-erro').classList.remove('hidden');
                            }
                        } catch (_) {}
                    }, 5000);
                } catch (_) {
                    showStep('step-pix-cpf');
                    el('pix-cpf-erro').textContent = 'Erro de conexão.';
                    el('pix-cpf-erro').classList.remove('hidden');
                }
            });

            el('pix-copiar').addEventListener('click', () => {
                navigator.clipboard.writeText(el('pix-copia').value).then(() => {
                    el('pix-copiar').textContent = 'Copiado!';
                    setTimeout(() => { el('pix-copiar').textContent = 'Copiar'; }, 2000);
                });
            });

            // ── Cartão ──
            el('btn-cartao').addEventListener('click', () => showStep('step-cartao'));
            el('cartao-voltar').addEventListener('click', () => showStep('step-metodo'));

            el('c-num').addEventListener('input', e => {
                e.target.value = e.target.value.replace(/\D/g,'').replace(/(.{4})/g,'$1 ').trim().slice(0,19);
            });
            el('c-val').addEventListener('input', e => {
                let v = e.target.value.replace(/\D/g,'').slice(0,4);
                if (v.length > 2) v = v.slice(0,2)+'/'+v.slice(2);
                e.target.value = v;
            });
            el('cartao-pagar').addEventListener('click', async () => {
                const num  = el('c-num').value.replace(/\s/g,'');
                const val  = el('c-val').value;
                const cvv  = el('c-cvv').value.trim();
                const erro = el('cartao-erro');

                if (num.length < 15)            { erro.textContent='Número inválido.';         erro.classList.remove('hidden'); return; }
                if (!/^\d{2}\/\d{2}$/.test(val)){ erro.textContent='Validade inválida.';       erro.classList.remove('hidden'); return; }
                if (cvv.length < 3)             { erro.textContent='CVV inválido.';            erro.classList.remove('hidden'); return; }
                erro.classList.add('hidden');

                const btn = el('cartao-pagar');
                btn.disabled = true; btn.textContent = 'Processando…';

                try {
                    const [mes, ano] = val.split('/');
                    if (!gnCheckout) throw new Error('SDK EfiPay não carregado. Aguarde e tente novamente.');

                    const bandeira = getBandeira(num);
                    if (!bandeira) throw new Error('Bandeira não reconhecida. Use Visa, Mastercard, Elo ou Amex.');

                    const paymentToken = await new Promise((resolve, reject) => {
                        gnCheckout.getPaymentToken({
                            brand: bandeira,
                            number: num,
                            cvv: cvv,
                            expiration_month: mes,
                            expiration_year: '20' + ano,
                        }, (err, response) => {
                            if (err) reject(new Error(err.error_description ?? 'Erro ao tokenizar cartão.'));
                            else     resolve(response.data.payment_token);
                        });
                    });

                    const body = new FormData();
                    body.append('_token',       csrfToken);
                    body.append('paymentToken', paymentToken);

                    const r = await fetch(routes.equipeCartaoTodos, { method: 'POST', body });
                    const d = await r.json();

                    if (d.success) {
                        showStep('step-sucesso');
                        setTimeout(() => { closeModal(); location.reload(); }, 2000);
                    } else {
                        erro.textContent = d.error ?? 'Pagamento recusado.';
                        erro.classList.remove('hidden');
                    }
                } catch (e) {
                    erro.textContent = e.message ?? 'Erro ao processar.';
                    erro.classList.remove('hidden');
                } finally {
                    btn.disabled = false; btn.textContent = 'Pagar';
                }
            });
        })();
        </script>
    @endsection
</x-app-layout>
