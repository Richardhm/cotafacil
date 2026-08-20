{{-- Cotação Humana — Teresina (PI). Tela única: contratação + linha à esquerda,
     vidas por faixa, e resultado com pills instantâneas de acomodação × copay.
     Os preços da linha chegam num único POST /humanas/precos (cacheado por
     plano no JS) — alternar pill não faz nova requisição. --}}
<x-app-layout>
    <div class="max-w-full mx-auto sm:px-6 lg:px-8 px-4">

        {{-- Cabeçalho --}}
        <div class="mb-2 p-2 rounded bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border border-white text-white text-center font-bold text-sm uppercase">
            Humana &mdash; <span id="titulo-plano">Teresina (PI)</span>
            <span class="block text-xs font-normal opacity-75 mt-0.5" id="subtitulo-plano">Escolha a contratação e a linha para cotar</span>
        </div>

        <div class="flex flex-col lg:flex-row gap-x-4">

            {{-- Painel esquerdo: contratação, linha e vidas --}}
            <div class="mt-2 rounded p-3 bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border border-white w-full lg:w-[22%]">

                <button class="py-2 w-full px-1 me-2 mb-2 text-sm font-medium text-white rounded-lg border border-white">
                    Contratação
                </button>
                <div class="flex gap-2 mb-3" id="botoes-contratacao">
                    <button data-contratacao="pf"
                            class="btn-contratacao flex-1 py-2 rounded-lg text-sm font-bold border border-white text-white bg-blue-500">
                        Pessoa Física
                    </button>
                    <button data-contratacao="pme"
                            class="btn-contratacao flex-1 py-2 rounded-lg text-sm font-bold border border-white text-white bg-transparent">
                        PME
                    </button>
                </div>

                <div class="w-full px-1 mb-2">
                    <label for="plano" class="text-white text-sm">Linha</label>
                    <select id="plano" class="py-2 text-black w-full text-xs px-1 me-2 mb-1 font-medium rounded-lg dark:bg-transparent dark:text-white dark:border-white">
                    </select>
                    <div id="badges-plano" class="text-[11px] text-white opacity-80 leading-4"></div>
                </div>

                {{-- Faixas etárias (mesmo componente do Hapvida SS) --}}
                <button class="py-2 w-full px-1 me-2 mb-2 text-sm font-medium text-white rounded-lg border border-white">
                    Quantidade de Vidas por Faixa
                </button>

                <div class="flex flex-wrap justify-around" id="faixa_etarias">
                    <div class="flex flex-col w-[45%]">
                        @php
                            $faixasEsquerda = [
                                ['id' => 'input_0_18',  'label' => '0 - 18'],
                                ['id' => 'input_24_28', 'label' => '24 - 28'],
                                ['id' => 'input_34_38', 'label' => '34 - 38'],
                                ['id' => 'input_44_48', 'label' => '44 - 48'],
                                ['id' => 'input_54_58', 'label' => '54 - 58'],
                            ];
                        @endphp
                        @foreach($faixasEsquerda as $f)
                        <div class="mb-2 w-full">
                            <span class="text-white text-sm">{{ $f['label'] }}</span>
                            <div class="flex items-center text-center">
                                <div class="flex rounded overflow-hidden border border-white mx-auto faixa-etaria-buttons h-8 w-full">
                                    <button class="bg-red-400 text-white flex-grow" style="width:33%;">-</button>
                                    <input id="{{ $f['id'] }}" name="{{ $f['id'] }}" type="text" value="0"
                                           class="text-xs text-center flex-grow border-none dark:bg-opacity-20 dark:bg-gray-300 dark:text-white text-black faixa-etaria-input"
                                           style="width:33%;">
                                    <button class="bg-green-400 text-white flex-grow" style="width:33%;">+</button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="flex flex-col w-[45%]">
                        @php
                            $faixasDireita = [
                                ['id' => 'input_19_23', 'label' => '19 - 23'],
                                ['id' => 'input_29_33', 'label' => '29 - 33'],
                                ['id' => 'input_39_43', 'label' => '39 - 43'],
                                ['id' => 'input_49_53', 'label' => '49 - 53'],
                                ['id' => 'input_59',    'label' => 'Acima 59+'],
                            ];
                        @endphp
                        @foreach($faixasDireita as $f)
                        <div class="mb-2 w-full">
                            <span class="text-white text-sm">{{ $f['label'] }}</span>
                            <div class="flex items-center text-center">
                                <div class="flex rounded overflow-hidden border border-white mx-auto faixa-etaria-buttons h-8 w-full">
                                    <button class="bg-red-400 text-white flex-grow" style="width:33%;">-</button>
                                    <input id="{{ $f['id'] }}" name="{{ $f['id'] }}" type="text" value="0"
                                           class="text-xs text-center flex-grow border-none dark:bg-opacity-20 dark:bg-gray-300 dark:text-white text-black faixa-etaria-input"
                                           style="width:33%;">
                                    <button class="bg-green-400 text-white flex-grow" style="width:33%;">+</button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-2 text-center text-white text-xs">
                    Total de vidas: <span id="total-vidas" class="font-bold">0</span>
                </div>

                <div id="status-calculo" class="mt-1 text-center text-xs text-white opacity-0 transition-opacity duration-300">
                    Atualizando...
                </div>
            </div>

            {{-- Painel direito: resultado --}}
            <div id="resultado"
                 class="p-3 rounded mt-2 bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border border-white w-full lg:w-[78%] text-white">
                <p class="text-sm opacity-80">Escolha a linha e informe as vidas para ver a cotação.</p>
            </div>

        </div>
    </div>

    @section('scripts')
    <script>
        // ---------- Dados embutidos (as 12 linhas comerciais) ----------
        const PLANOS = @json($planosPf->concat($planosPme)->values());
        const URL_PRECOS = "{{ route('humanas.precos') }}";
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        const BRL = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

        // Grade de coparticipação (páginas 3 dos PDFs). Grupo 'vital' = VITAL e
        // AMBULATORIAL; 'superior' = IDEAL, SUPERIOR, SUPERIOR R2 e PREMIUM.
        // Básica é igual para todos. REFERÊNCIA não tem copay (grade oculta).
        const COPAY_GRADES = {
            completa: {
                vital: [
                    ['Consulta eletiva', 'R$ 20,00'],
                    ['Consulta em hospital (pronto-socorro)', 'R$ 30,00'],
                    ['Exames simples', '25% com limitador de R$ 25,00'],
                    ['Exames especiais', '25% com limitador de R$ 80,00'],
                    ['Terapias — Grupo 1', '25% com limitador de R$ 30,00'],
                    ['Terapias — Grupo 2', 'Isento'],
                    ['Terapias — Grupo 3', '40% com limitador de R$ 70,00'],
                    ['Internação', 'Isento'],
                ],
                superior: [
                    ['Consulta eletiva', 'R$ 20,00'],
                    ['Consulta em hospital (pronto-socorro)', 'R$ 40,00'],
                    ['Exames simples', '30% com limitador de R$ 25,00'],
                    ['Exames especiais', '30% com limitador de R$ 80,00'],
                    ['Terapias — Grupo 1', '30% com limitador de R$ 30,00'],
                    ['Terapias — Grupo 2', 'Isento'],
                    ['Terapias — Grupo 3', '40% com limitador de R$ 70,00'],
                    ['Internação', 'Isento'],
                ],
            },
            basica: [
                ['Consulta eletiva', 'Isento'],
                ['Consulta em hospital (pronto-socorro)', 'Isento'],
                ['Exames simples', 'Isento'],
                ['Exames especiais', 'Isento'],
                ['Terapias — Grupo 1', 'Isento'],
                ['Terapias — Grupo 2', 'Isento'],
                ['Terapias — Grupo 3', '40% com limitador de R$ 70,00'],
                ['Internação', 'Isento'],
            ],
        };

        function grupoCopay(linha) {
            if (linha === 'REFERENCIA') return null;
            return (linha === 'VITAL' || linha === 'AMBULATORIAL') ? 'vital' : 'superior';
        }

        // ---------- Estado ----------
        let contratacao = 'pf';
        let planoAtual = null;            // resposta de /humanas/precos do plano escolhido
        let selAcomodacao = null;         // pill ativa
        let selCopay = null;              // pill ativa
        let selProduto = 'todos';         // pill ativa: todos | saude | essencial | pleno

        const PRODUTOS = [
            ['todos', 'Todos'],
            ['saude', 'Valor Saúde'],
            ['essencial', 'Combo Essencial'],
            ['pleno', 'Combo Odonto Pleno'],
        ];
        const cachePrecos = {};           // plano_id -> resposta

        const MAPA_INPUTS = {
            1: 'input_0_18', 2: 'input_19_23', 3: 'input_24_28', 4: 'input_29_33',
            5: 'input_34_38', 6: 'input_39_43', 7: 'input_44_48', 8: 'input_49_53',
            9: 'input_54_58', 10: 'input_59',
        };

        function getVidas() {
            const vidas = {};
            for (const [faixaId, inputId] of Object.entries(MAPA_INPUTS)) {
                vidas[faixaId] = parseInt($('#' + inputId).val()) || 0;
            }
            return vidas;
        }

        function atualizarTotalVidas() {
            const total = Object.values(getVidas()).reduce((a, b) => a + b, 0);
            $('#total-vidas').text(total);
        }

        // ---------- Contratação e linha ----------
        function preencherPlanos() {
            const $sel = $('#plano').empty();
            PLANOS.filter(p => p.contratacao === contratacao).forEach(p => {
                $sel.append($('<option>').val(p.id).text(p.nome));
            });
            $sel.trigger('change');
        }

        $('.btn-contratacao').on('click', function (e) {
            e.preventDefault();
            contratacao = $(this).data('contratacao');
            $('.btn-contratacao').removeClass('bg-blue-500').addClass('bg-transparent');
            $(this).removeClass('bg-transparent').addClass('bg-blue-500');
            preencherPlanos();
        });

        $('#plano').on('change', function () {
            const planoId = parseInt($(this).val());
            if (!planoId) return;
            selAcomodacao = null;
            selCopay = null;
            carregarPrecos(planoId);
        });

        function carregarPrecos(planoId) {
            if (cachePrecos[planoId]) {
                planoAtual = cachePrecos[planoId];
                renderizar();
                return;
            }
            $('#status-calculo').css('opacity', 1);
            $.ajax({
                url: URL_PRECOS,
                method: 'POST',
                data: { plano_id: planoId },
                headers: { 'X-CSRF-TOKEN': CSRF },
                success: function (res) {
                    cachePrecos[planoId] = res;
                    planoAtual = res;
                    renderizar();
                },
                error: function () {
                    $('#resultado').html('<p class="text-sm text-red-200">Não foi possível carregar os preços. Tente novamente.</p>');
                },
                complete: function () {
                    $('#status-calculo').css('opacity', 0);
                },
            });
        }

        // ---------- Render ----------
        function tabelaSelecionada() {
            if (!planoAtual) return null;
            const tabelas = planoAtual.tabelas;
            // garante seleção válida (default: primeira combinação existente)
            if (!tabelas.some(t => t.acomodacao === selAcomodacao)) selAcomodacao = tabelas[0].acomodacao;
            const daAcomodacao = tabelas.filter(t => t.acomodacao === selAcomodacao);
            if (!daAcomodacao.some(t => t.coparticipacao === selCopay)) selCopay = daAcomodacao[0].coparticipacao;
            return daAcomodacao.find(t => t.coparticipacao === selCopay);
        }

        function pillHtml(ativo, texto, tipo, valor) {
            const cls = ativo
                ? 'bg-blue-500 text-white border-white'
                : 'bg-transparent text-white border-white opacity-70 hover:opacity-100';
            return '<button type="button" class="pill px-3 py-1 rounded-full text-xs font-bold border ' + cls + '" ' +
                   'data-tipo="' + tipo + '" data-valor="' + valor + '">' + texto + '</button>';
        }

        function renderizar() {
            if (!planoAtual) return;
            const plano = planoAtual.plano;
            const tabela = tabelaSelecionada();

            $('#titulo-plano').text((contratacao === 'pf' ? 'PF' : 'PME') + ' — ' + plano.nome);
            $('#subtitulo-plano').text(plano.segmentacao + ' • ' + plano.abrangencia);
            $('#badges-plano').html(
                plano.segmentacao + '<br>' + plano.abrangencia +
                (plano.obstetricia === false ? '<br>Sem obstetrícia' : '')
            );

            let html = '';

            // Pills de acomodação (só quando há escolha)
            const acomodacoes = [...new Set(planoAtual.tabelas.map(t => t.acomodacao))];
            const copays = [...new Set(planoAtual.tabelas
                .filter(t => t.acomodacao === selAcomodacao)
                .map(t => t.coparticipacao))];

            html += '<div class="flex flex-wrap items-center gap-2 mb-3">';
            if (acomodacoes.length > 1 || acomodacoes[0] !== 'nenhuma') {
                html += '<span class="text-xs opacity-75">Acomodação:</span>';
                acomodacoes.forEach(a => {
                    const t = planoAtual.tabelas.find(x => x.acomodacao === a);
                    html += pillHtml(a === selAcomodacao, t.acomodacao_label, 'acomodacao', a);
                });
            }
            if (copays.length > 1 || copays[0] !== 'nao_se_aplica') {
                html += '<span class="text-xs opacity-75 ml-2">Coparticipação:</span>';
                copays.forEach(c => {
                    const t = planoAtual.tabelas.find(x => x.acomodacao === selAcomodacao && x.coparticipacao === c);
                    html += pillHtml(c === selCopay, t.coparticipacao_label, 'copay', c);
                });
            }
            // Pills de produto (pedido da usuária) — só onde os combos existem
            const temCombosPlano = tabela.precos['1'].essencial !== null;
            if (temCombosPlano) {
                html += '<span class="text-xs opacity-75 ml-2">Produto:</span>';
                PRODUTOS.forEach(([valor, rotulo]) => {
                    html += pillHtml(valor === selProduto, rotulo, 'produto', valor);
                });
            }
            html += '</div>';

            // Linha de identificação da tabela ativa
            html += '<div class="text-[11px] opacity-75 mb-2">ANS nº ' + (tabela.registro_ans || '—') +
                    (tabela.vigencia_inicio ? ' • Vigência ' + tabela.vigencia_inicio + ' a ' + tabela.vigencia_fim : '') +
                    '</div>';

            // Tabela de valores
            const vidas = getVidas();
            const faixasComVidas = planoAtual.faixas.filter(f => vidas[f.id] > 0);
            const temCombos = tabela.precos['1'].essencial !== null;

            if (faixasComVidas.length === 0) {
                html += '<p class="text-sm opacity-80 mt-2">Informe a quantidade de vidas por faixa etária para calcular.</p>';
            } else {
                let colunas = temCombos
                    ? [['saude', 'Valor Saúde'], ['essencial', 'Combo Essencial'], ['pleno', 'Combo Odonto Pleno']]
                    : [['saude', 'Valor Saúde']];
                if (temCombos && selProduto !== 'todos') {
                    colunas = colunas.filter(([chave]) => chave === selProduto);
                }

                html += '<div class="overflow-x-auto"><table class="w-full text-sm">';
                html += '<thead><tr class="border-b border-white/50 text-left">' +
                        '<th class="py-2 pr-2">Faixa</th><th class="py-2 pr-2 text-center">Vidas</th>';
                colunas.forEach(([, titulo]) => {
                    html += '<th class="py-2 px-2 text-right">' + titulo + '</th>';
                });
                html += '</tr></thead><tbody>';

                const totais = {};
                colunas.forEach(([chave]) => totais[chave] = 0);

                faixasComVidas.forEach(f => {
                    const qtd = vidas[f.id];
                    const preco = tabela.precos[f.id];
                    html += '<tr class="border-b border-white/20">' +
                            '<td class="py-1.5 pr-2">' + f.nome + '</td>' +
                            '<td class="py-1.5 pr-2 text-center">' + qtd + '</td>';
                    colunas.forEach(([chave]) => {
                        const subtotal = preco[chave] * qtd;
                        totais[chave] += subtotal;
                        html += '<td class="py-1.5 px-2 text-right whitespace-nowrap">' +
                                '<span class="opacity-60 text-xs">' + qtd + ' × ' + BRL.format(preco[chave]) + '</span><br>' +
                                '<span class="font-semibold">' + BRL.format(subtotal) + '</span></td>';
                    });
                    html += '</tr>';
                });

                html += '<tr class="font-bold text-base"><td class="py-2 pr-2">TOTAL</td>' +
                        '<td class="py-2 pr-2 text-center">' + Object.values(vidas).reduce((a, b) => a + b, 0) + '</td>';
                colunas.forEach(([chave]) => {
                    html += '<td class="py-2 px-2 text-right">' + BRL.format(totais[chave]) + '</td>';
                });
                html += '</tr></tbody></table></div>';

                if (temCombos) {
                    html += '<p class="text-[11px] opacity-70 mt-2">' +
                            'Valor Saúde = plano sem odonto • Combo Essencial = saúde + odonto de urgência/emergência e prevenção • ' +
                            'Combo Odonto Pleno = saúde + odonto completo.</p>';
                }

                html += '<div class="flex flex-wrap gap-2 mt-3">' +
                        '<button type="button" id="btn-gerar-pdf" class="py-2 px-4 bg-blue-500 hover:bg-blue-700 text-white font-bold rounded-lg text-sm">Gerar PDF</button>' +
                        '<button type="button" id="btn-gerar-jpg" class="py-2 px-4 bg-blue-500 hover:bg-blue-700 text-white font-bold rounded-lg text-sm">Gerar Imagem</button>';
                // Comparativo Completa × Básica: só onde existem as duas copays
                const copaysDaAcomodacao = planoAtual.tabelas
                    .filter(t => t.acomodacao === selAcomodacao)
                    .map(t => t.coparticipacao);
                if (copaysDaAcomodacao.includes('completa') && copaysDaAcomodacao.includes('basica')) {
                    html += '<button type="button" id="btn-comparativo-pdf" class="py-2 px-4 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-lg text-sm">Comparativo PDF</button>' +
                            '<button type="button" id="btn-comparativo-jpg" class="py-2 px-4 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-lg text-sm">Comparativo Imagem</button>';
                }
                html += '</div>';
            }

            // Grade de coparticipação
            const grupo = grupoCopay(plano.linha);
            if (grupo && selCopay !== 'nao_se_aplica') {
                const grade = selCopay === 'completa' ? COPAY_GRADES.completa[grupo] : COPAY_GRADES.basica;
                html += '<details class="mt-3 text-xs"><summary class="cursor-pointer font-semibold opacity-90">' +
                        'Taxas de coparticipação (' + (selCopay === 'completa' ? 'Completa' : 'Básica') + ')</summary>' +
                        '<table class="mt-2 w-full max-w-xl">';
                grade.forEach(([proc, valor]) => {
                    const valorFinal = (plano.linha === 'AMBULATORIAL' && proc === 'Internação') ? 'Não se aplica' : valor;
                    html += '<tr class="border-b border-white/20"><td class="py-1 pr-2">' + proc + '</td>' +
                            '<td class="py-1 text-right">' + valorFinal + '</td></tr>';
                });
                html += '</table></details>';
            }

            $('#resultado').html(html);

            $('#resultado .pill').on('click', function (e) {
                e.preventDefault();
                const tipo = $(this).data('tipo');
                if (tipo === 'acomodacao') {
                    selAcomodacao = $(this).data('valor');
                } else if (tipo === 'copay') {
                    selCopay = $(this).data('valor');
                } else {
                    selProduto = $(this).data('valor');
                }
                renderizar();   // instantâneo: os preços já estão no cliente
            });

            $('#btn-gerar-pdf').on('click', function (e) { e.preventDefault(); gerarDocumento('pdf', false); });
            $('#btn-gerar-jpg').on('click', function (e) { e.preventDefault(); gerarDocumento('jpg', false); });
            $('#btn-comparativo-pdf').on('click', function (e) { e.preventDefault(); gerarDocumento('pdf', true); });
            $('#btn-comparativo-jpg').on('click', function (e) { e.preventDefault(); gerarDocumento('jpg', true); });
        }

        // Download via AJAX + blob (mesmo padrão do dashboard): gerenciadores de
        // download interceptam form POST e tentam rebaixar a URL com GET — que
        // não existe nesta rota. Com blob o arquivo nasce no próprio navegador.
        function gerarDocumento(tipo, comparativo) {
            const tabela = tabelaSelecionada();
            if (!tabela) return;

            const faixas = {};
            for (const [faixaId, qtd] of Object.entries(getVidas())) {
                if (qtd > 0) faixas[faixaId] = qtd;
            }

            const $botoes = $('#btn-gerar-pdf, #btn-gerar-jpg, #btn-comparativo-pdf, #btn-comparativo-jpg');

            $.ajax({
                url: "{{ route('humanas.gerar') }}",
                method: 'POST',
                data: {
                    plano_id: planoAtual.plano.id,
                    acomodacao: tabela.acomodacao,
                    coparticipacao: tabela.coparticipacao,
                    tipo_documento: tipo,
                    produto: selProduto,
                    comparativo: comparativo ? 1 : 0,
                    faixas: faixas,
                },
                headers: { 'X-CSRF-TOKEN': CSRF },
                xhrFields: { responseType: 'blob' },
                timeout: 120000,
                beforeSend: function () {
                    $botoes.prop('disabled', true).addClass('opacity-60');
                    $('#status-calculo').text('Gerando documento...').css('opacity', 1);
                },
                complete: function () {
                    $botoes.prop('disabled', false).removeClass('opacity-60');
                    $('#status-calculo').css('opacity', 0);
                },
                error: function () {
                    alert('Não foi possível gerar o documento. Tente novamente.');
                },
                success: function (blob, status, xhr) {
                    if (!blob.size) return;
                    let filename = 'cotacao-humana.' + (tipo === 'pdf' ? 'pdf' : 'png');
                    const disposition = xhr.getResponseHeader('Content-Disposition');
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                        if (matches && matches[1]) filename = matches[1].replace(/['"]/g, '');
                    }
                    const downloadUrl = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    setTimeout(() => URL.revokeObjectURL(downloadUrl), 100);
                },
            });
        }

        // ---------- Vidas: botões +/- e recálculo automático ----------
        let debounceRender = null;
        function agendarRender() {
            clearTimeout(debounceRender);
            debounceRender = setTimeout(() => { if (planoAtual) renderizar(); }, 250);
        }

        $('.faixa-etaria-buttons').each(function () {
            const $container = $(this);
            const $input = $container.find('.faixa-etaria-input');
            const $plusBtn = $container.find('.bg-green-400');
            const $minusBtn = $container.find('.bg-red-400');

            $plusBtn.on('click', function (e) {
                e.preventDefault();
                const n = (parseInt($input.val()) || 0) + 1;
                $input.val(n);
                atualizarTotalVidas();
                agendarRender();
            });

            $minusBtn.on('click', function (e) {
                e.preventDefault();
                const n = Math.max(0, (parseInt($input.val()) || 0) - 1);
                $input.val(n);
                atualizarTotalVidas();
                agendarRender();
            });

            $input.on('input', function () {
                this.value = this.value.replace(/\D/g, '');
                atualizarTotalVidas();
                agendarRender();
            });
        });

        // ---------- Boot ----------
        preencherPlanos();
    </script>
    @endsection
</x-app-layout>
