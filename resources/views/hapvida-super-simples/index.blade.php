<x-app-layout>
    <div id="loading-cidades" style="display:none; position:absolute; left:0; right:0; margin:auto; top:0; bottom:0; z-index:9999; background:rgba(0,0,0,0.2); width:100%; height:100%; text-align:center;">
        <div style="position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);">
            <div class="jumping-dots-loader">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto sm:px-6 lg:px-8 px-4">

        {{-- Cabeçalho identificador do plano --}}
        <div class="mb-2 p-2 rounded bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border border-white text-white text-center font-bold text-sm uppercase">
            Hapvida &mdash; <span id="titulo-plano">{{ $planos->firstWhere('id', $planoSelecionado)->nome ?? 'Super Simples' }}</span>
            <span class="block text-xs font-normal opacity-75 mt-0.5">Cotação sem limite de vidas</span>
        </div>

        {{-- Breadcrumb dinâmico (estilo chevron encadeado — tudo inline) --}}
        <div id="breadcrumb" class="mb-2 px-1 py-2">
            <div style="display:flex;align-items:stretch;gap:0;flex-wrap:nowrap;overflow-x:auto;">
                {{-- Item 1: Hapvida (sem notch esquerdo) --}}
                <span style="display:inline-flex;align-items:center;background:rgba(255,255,255,0.38);color:white;font-size:.8rem;font-weight:800;padding:8px 28px 8px 16px;clip-path:polygon(0 0, calc(100% - 16px) 0, 100% 50%, calc(100% - 16px) 100%, 0 100%);white-space:nowrap;letter-spacing:.06em;text-transform:uppercase;position:relative;z-index:30;flex-shrink:0;">
                    HAPVIDA
                </span>
                {{-- Item 2: Super Simples --}}
                <span id="breadcrumb-plano" style="display:inline-flex;align-items:center;background:rgba(255,255,255,0.28);color:white;font-size:.8rem;font-weight:800;padding:8px 28px 8px 30px;clip-path:polygon(0 0, calc(100% - 16px) 0, 100% 50%, calc(100% - 16px) 100%, 0 100%, 16px 50%);white-space:nowrap;letter-spacing:.06em;text-transform:uppercase;position:relative;z-index:20;flex-shrink:0;margin-left:-14px;">
                    {{ $planos->firstWhere('id', $planoSelecionado)->nome ?? 'Super Simples' }}
                </span>
                {{-- Item 3: Localidade — clip-path muda via JS quando cenários aparecem --}}
                <span id="breadcrumb-localidade" style="display:inline-flex;align-items:center;background:rgba(255,255,255,0.12);color:rgba(255,255,255,0.5);font-size:.8rem;font-weight:600;padding:8px 20px 8px 30px;clip-path:polygon(0 0, 100% 0, 100% 100%, 0 100%, 16px 50%);white-space:nowrap;letter-spacing:.04em;font-style:italic;position:relative;z-index:10;flex-shrink:0;margin-left:-14px;">
                    Selecione a localidade
                </span>
                {{-- Cenários: injetados inline no mesmo flex row --}}
                <div id="breadcrumb-cenarios" style="display:none;align-items:stretch;gap:0;flex-shrink:0;"></div>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-x-4">

            {{-- Painel esquerdo: localidade + faixas etárias --}}
            <div class="mt-2 rounded p-3 bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border border-white w-full lg:w-[22%]">
                <button class="py-2 w-full px-1 me-2 mb-2 text-sm font-medium text-white rounded-lg border border-white">
                    Tabela de Origem
                </button>

                {{-- Seletores Plano / UF / Cidade --}}
                <form id="form-localidade">
                    <div class="w-full px-1">
                        <label for="plano" class="text-white text-sm">Plano</label>
                        <select id="plano" class="py-2 text-black w-full text-xs px-1 me-2 mb-2 font-medium rounded-lg dark:bg-transparent dark:text-white dark:border-white">
                            @foreach($planos as $plano)
                                <option value="{{ $plano->id }}" {{ $planoSelecionado == $plano->id ? 'selected' : '' }}>{{ $plano->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full flex">
                        <div class="ml-1 w-[32%]">
                            <label for="estado" class="text-white text-sm">UF</label>
                            <select id="estado" class="py-2 text-black w-full text-xs px-1 me-2 mb-2 font-medium rounded-lg dark:bg-transparent dark:text-white dark:border-white">
                                <option value="">Escolher UF</option>
                                @foreach($estados as $uf)
                                    <option value="{{ $uf->uf }}" {{ $ufpreferencia == $uf->uf ? 'selected' : '' }}>{{ $uf->uf }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ml-1 w-[65%]">
                            <label for="cidade" class="text-white text-sm">Cidade</label>
                            <select id="cidade" class="py-2 text-black w-full text-xs px-1 me-2 mb-2 font-medium rounded-lg dark:bg-transparent dark:text-white dark:border-white">
                                <option value="">Escolher Cidade</option>
                            </select>
                        </div>
                    </div>
                </form>

                {{-- Faixas etárias --}}
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

                {{-- Total de vidas (informativo) --}}
                <div class="mt-2 text-center text-white text-xs">
                    Total de vidas: <span id="total-vidas" class="font-bold">0</span>
                </div>

                {{-- Botão calcular (primeira vez) --}}
                <button id="btn-calcular"
                        class="mt-3 w-full py-2 px-4 bg-blue-500 hover:bg-blue-700 text-white font-bold rounded-lg text-sm disabled:opacity-50"
                        disabled>
                    Calcular
                </button>

                {{-- Indicador de recálculo automático --}}
                <div id="status-calculo" class="mt-1 text-center text-xs text-white opacity-0 transition-opacity duration-300">
                    Atualizando...
                </div>
            </div>

            {{-- Painel direito: resultado (cards) --}}
            <div id="resultado"
                 class="p-1 rounded mt-2 hidden bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border w-full lg:w-[78%]">
            </div>

        </div>
    </div>

    {{-- Modal: gerar imagem/PDF por cenário --}}
    <div id="modalGerarImagem" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
        <div class="bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] px-6 py-10 rounded-lg shadow-lg w-96 text-white border-white border-4">
            <div class="flex justify-between mb-4">
                <h2 class="text-lg font-bold mx-auto">Gerar Documento</h2>
                <svg xmlns="http://www.w3.org/2000/svg" id="fecharModalGerar" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                     class="size-6 border-white border-4 rounded hover:cursor-pointer">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </div>

            <fieldset class="border-4 border-gray-300 rounded-lg p-4">
                <legend class="text-lg font-semibold px-2 mx-auto">Tipo</legend>
                <div class="flex justify-between items-center">
                    <label class="flex items-center space-x-2">
                        <input type="radio" name="tipo_gerar" value="jpg" checked>
                        <span class="font-semibold">Imagem (JPG)</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="radio" name="tipo_gerar" value="pdf">
                        <span class="font-semibold">PDF</span>
                    </label>
                </div>
            </fieldset>

            <div class="flex justify-center mt-4">
                <button id="confirmarGerar"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full w-full text-lg">
                    Gerar
                </button>
            </div>
        </div>
    </div>

    @section('scripts')
    <script>
        $(document).ready(function () {

            let cenarioSelecionado = { copart: 0, odonto: 0, label: '' };
            let calcTimer    = null;
            let carouselGoTo = null;

            // ------- Breadcrumb -------
            function updateBreadcrumbLocalidade() {
                const uf     = $('#estado').val();
                const cidade = $('#cidade').val();
                const $el    = $('#breadcrumb-localidade');
                if (!uf) {
                    $el.text('Selecione a localidade')
                       .css({ background: 'rgba(255,255,255,0.12)', color: 'rgba(255,255,255,0.5)', fontStyle: 'italic', fontWeight: '600', letterSpacing: '.04em' });
                } else if (!cidade) {
                    $el.text($('#estado option:selected').text().trim())
                       .css({ background: 'rgba(255,255,255,0.25)', color: 'white', fontStyle: 'normal', fontWeight: '800', letterSpacing: '.06em' });
                } else {
                    const ufText     = $('#estado option:selected').text().trim();
                    const cidadeText = $('#cidade option:selected').text().trim();
                    $el.text(ufText + ' — ' + cidadeText)
                       .css({ background: 'rgba(255,255,255,0.38)', color: 'white', fontStyle: 'normal', fontWeight: '800', letterSpacing: '.06em' });
                }
            }

            function buildBreadcrumbCenarios(labels) {
                // Localidade vira item do meio (com seta direita) quando cenários aparecem
                $('#breadcrumb-localidade').css('clip-path', 'polygon(0 0, calc(100% - 16px) 0, 100% 50%, calc(100% - 16px) 100%, 0 100%, 16px 50%)');
                const $container = $('#breadcrumb-cenarios').empty();
                const total = labels.length;
                labels.forEach(function (label, i) {
                    const isFirst = i === 0;
                    const isLast  = i === total - 1;
                    const clipPath = isFirst
                        ? 'polygon(0 0, calc(100% - 16px) 0, 100% 50%, calc(100% - 16px) 100%, 0 100%)'
                        : isLast
                            ? 'polygon(0 0, 100% 0, 100% 100%, 0 100%, 16px 50%)'
                            : 'polygon(0 0, calc(100% - 16px) 0, 100% 50%, calc(100% - 16px) 100%, 0 100%, 16px 50%)';
                    const paddingLeft  = isFirst ? '14px' : '28px';
                    const paddingRight = isLast  ? '14px' : '28px';
                    const zIndex = total - i;
                    $('<span>')
                        .addClass('breadcrumb-cenario')
                        .attr('data-index', i)
                        .attr('data-clip', clipPath)
                        .text(label)
                        .css({
                            display:       'inline-flex',
                            alignItems:    'center',
                            padding:       '8px ' + paddingRight + ' 8px ' + paddingLeft,
                            clipPath:      clipPath,
                            fontSize:      '.78rem',
                            cursor:        'pointer',
                            fontWeight:    '700',
                            letterSpacing: '.04em',
                            textTransform: 'uppercase',
                            transition:    'background .2s, color .2s',
                            background:    'rgba(255,255,255,0.18)',
                            color:         'rgba(255,255,255,0.8)',
                            userSelect:    'none',
                            whiteSpace:    'nowrap',
                            position:      'relative',
                            zIndex:        zIndex,
                            flexShrink:    '0',
                            marginLeft:    isFirst ? '0' : '-14px',
                        })
                        .appendTo($container);
                });
                $container.css('display', 'inline-flex');
                updateBreadcrumbCenario(0);
            }

            function updateBreadcrumbCenario(idx) {
                $('#breadcrumb-cenarios .breadcrumb-cenario').each(function (i) {
                    if (i === idx) {
                        $(this).css({ background: 'rgba(255,255,255,0.95)', color: '#1e3a8a' });
                    } else {
                        $(this).css({ background: 'rgba(255,255,255,0.18)', color: 'rgba(255,255,255,0.8)' });
                    }
                });
            }

            function hideBreadcrumbCenarios() {
                // Localidade volta a ser o último item (sem seta direita)
                $('#breadcrumb-localidade').css('clip-path', 'polygon(0 0, 100% 0, 100% 100%, 0 100%, 16px 50%)');
                $('#breadcrumb-cenarios').css('display', 'none').empty();
                carouselGoTo = null;
            }

            $('#breadcrumb').on('click', '.breadcrumb-cenario', function () {
                const idx = parseInt($(this).attr('data-index'));
                if (carouselGoTo) carouselGoTo(idx);
            });

            function planoAtual() {
                return $('#plano').val();
            }

            // ------- Plano → UFs -------
            // Cada plano atende um conjunto diferente de cidades, então trocar o plano
            // obriga a recarregar as UFs e zerar tudo que vem depois.
            $('#plano').on('change', function () {
                const nomePlano = $(this).find('option:selected').text().trim();
                $('#titulo-plano').text(nomePlano);
                $('#breadcrumb-plano').text(nomePlano);

                $('#estado').empty().append('<option value="">Escolher UF</option>');
                $('#cidade').empty().append('<option value="">Escolher Cidade</option>');
                $('#resultado').addClass('hidden').empty();
                hideBreadcrumbCenarios();
                updateBreadcrumbLocalidade();
                atualizarTotalVidas();

                $('#loading-cidades').fadeIn(150);
                $.ajax({
                    url: '{{ route('hapvida-ss.estados') }}',
                    type: 'POST',
                    dataType: 'json',
                    data: { plano_id: planoAtual(), _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function (data) {
                        $.each(data, function (i, v) {
                            $('#estado').append('<option value="' + v.uf + '">' + v.uf + '</option>');
                        });
                    },
                    error: function () {
                        alert('Erro ao carregar as UFs desse plano. Tente novamente.');
                    },
                    complete: function () {
                        $('#loading-cidades').fadeOut(150);
                    }
                });
            });

            // ------- UF → Cidades -------
            $('#estado').on('change', function () {
                let uf = $(this).val();
                $('#cidade').empty().append('<option value="">Escolher Cidade</option>');
                $('#resultado').addClass('hidden').empty();
                hideBreadcrumbCenarios();
                updateBreadcrumbLocalidade();
                atualizarTotalVidas();
                if (!uf) return;

                $('#loading-cidades').fadeIn(150);
                $.ajax({
                    url: '{{ route('hapvida-ss.cidades') }}',
                    type: 'POST',
                    dataType: 'json',
                    data: { uf: uf, plano_id: planoAtual(), _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function (data) {
                        $.each(data, function (i, v) {
                            $('#cidade').append('<option value="' + v.id + '">' + v.nome + '</option>');
                        });
                    },
                    complete: function () {
                        $('#loading-cidades').fadeOut(150);
                    }
                });
            });

            @if($ufpreferencia)
                $('#estado').trigger('change');
            @endif

            // ------- Botões + / - -------
            $('.faixa-etaria-buttons').each(function () {
                const $container = $(this);
                const $input     = $container.find('.faixa-etaria-input');
                const $plusBtn   = $container.find('.bg-green-400');
                const $minusBtn  = $container.find('.bg-red-400');

                const updateState = () => {
                    $minusBtn.prop('disabled', (parseInt($input.val()) || 0) <= 0);
                    atualizarTotalVidas();
                };

                $plusBtn.on('click', function (e) {
                    e.preventDefault();
                    $input.val((i, val) => {
                        const n = parseInt(val) + 1;
                        return isNaN(n) ? 1 : n;
                    }).trigger('input');
                    updateState();
                });

                $minusBtn.on('click', function (e) {
                    e.preventDefault();
                    $input.val((i, val) => {
                        const n = parseInt(val) - 1;
                        return n < 0 ? 0 : n;
                    }).trigger('input');
                    updateState();
                });

                $input.on('input', function () {
                    let v = parseInt($(this).val()) || 0;
                    if (v < 0) v = 0;
                    $(this).val(v);
                    updateState();
                });

                updateState();
            });

            function getTotalVidas() {
                let total = 0;
                $('.faixa-etaria-input').each(function () { total += parseInt($(this).val()) || 0; });
                return total;
            }

            function atualizarTotalVidas() {
                const total = getTotalVidas();
                $('#total-vidas').text(total);
                $('#btn-calcular').prop('disabled', total === 0 || !$('#cidade').val());
                if ($('#resultado').is(':visible') && $('#cidade').val() && total > 0) {
                    clearTimeout(calcTimer);
                    calcTimer = setTimeout(function () { carregarCenarios(false); }, 600);
                }
            }

            $('#cidade').on('change', function () {
                $('#resultado').addClass('hidden').empty();
                hideBreadcrumbCenarios();
                updateBreadcrumbLocalidade();
                atualizarTotalVidas();
            });

            $('#btn-calcular').on('click', function () {
                carregarCenarios(true);
            });

            function getFaixas() {
                return [{
                    '1':  $('#input_0_18').val()  || '0',
                    '2':  $('#input_19_23').val() || '0',
                    '3':  $('#input_24_28').val() || '0',
                    '4':  $('#input_29_33').val() || '0',
                    '5':  $('#input_34_38').val() || '0',
                    '6':  $('#input_39_43').val() || '0',
                    '7':  $('#input_44_48').val() || '0',
                    '8':  $('#input_49_53').val() || '0',
                    '9':  $('#input_54_58').val() || '0',
                    '10': $('#input_59').val()    || '0',
                }];
            }

            function carregarCenarios(primeiraVez) {
                if (primeiraVez === undefined) primeiraVez = true;
                const cidade = $('#cidade').val();
                const faixas = getFaixas();

                $.ajax({
                    url: '{{ route('hapvida-ss.cotacao') }}',
                    type: 'POST',
                    data: {
                        tabela_origem: cidade,
                        plano_id: planoAtual(),
                        faixas: faixas,
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function () {
                        if (primeiraVez) {
                            $('#btn-calcular').prop('disabled', true).text('Calculando...');
                        } else {
                            $('#status-calculo').css('opacity', 1);
                        }
                    },
                    success: function (html) {
                        if (primeiraVez) {
                            $('#resultado').removeClass('hidden').slideDown('fast').html(html);
                        } else {
                            $('#resultado').html(html);
                        }
                        initCarousel();
                        // Reconstrói as pills do breadcrumb com os cenários retornados
                        const labels = [];
                        $('#resultado .carousel-slide').each(function () {
                            const lbl = $(this).attr('data-label');
                            if (lbl) labels.push(lbl);
                        });
                        if (labels.length) buildBreadcrumbCenarios(labels);
                    },
                    error: function () {
                        alert('Erro ao calcular. Verifique os campos e tente novamente.');
                    },
                    complete: function () {
                        if (primeiraVez) {
                            $('#btn-calcular').prop('disabled', false).text('Calcular');
                        } else {
                            $('#status-calculo').css('opacity', 0);
                        }
                    }
                });
            }

            // ------- Carrossel -------
            function initCarousel() {
                let current = 0;
                const $res   = $('#resultado');
                const $track = $res.find('.carousel-track');
                const total  = $res.find('.carousel-slide').length;

                if (total <= 1) {
                    carouselGoTo = null;
                    return;
                }

                function goTo(idx) {
                    current = (idx + total) % total;
                    $track.css('transform', 'translateX(-' + (current * 100) + '%)');
                    $res.find('.carousel-dot').css('background', 'rgba(255,255,255,0.3)');
                    $res.find('.carousel-dot').eq(current).css('background', 'rgba(255,255,255,1)');
                    $res.find('.carousel-counter').text((current + 1) + ' / ' + total);
                    updateBreadcrumbCenario(current);
                }

                carouselGoTo = goTo;

                $res.off('click', '.carousel-prev').on('click', '.carousel-prev', function () {
                    goTo(current - 1);
                });
                $res.off('click', '.carousel-next').on('click', '.carousel-next', function () {
                    goTo(current + 1);
                });
                $res.off('click', '.carousel-dot').on('click', '.carousel-dot', function () {
                    goTo(parseInt($(this).data('index')));
                });

                let touchStartX = 0;
                $res.off('touchstart').on('touchstart', function (e) {
                    touchStartX = e.originalEvent.touches[0].clientX;
                });
                $res.off('touchend').on('touchend', function (e) {
                    const diff = touchStartX - e.originalEvent.changedTouches[0].clientX;
                    if (Math.abs(diff) > 40) goTo(diff > 0 ? current + 1 : current - 1);
                });
            }

            // ------- Gerar Imagem por cenário -------
            $('body').on('click', '.btn-gerar-cenario', function (e) {
                e.preventDefault();
                cenarioSelecionado.copart = $(this).data('coparticipacao');
                cenarioSelecionado.odonto = $(this).data('odonto');
                cenarioSelecionado.label  = $(this).data('label');
                $('#modalGerarImagem').removeClass('hidden');
            });

            $('#fecharModalGerar').on('click', function () {
                $('#modalGerarImagem').addClass('hidden');
            });

            $('#modalGerarImagem').on('click', function (e) {
                if (e.target === this) $(this).addClass('hidden');
            });

            $('#confirmarGerar').on('click', function (e) {
                e.preventDefault();
                let tipo   = $("input[name='tipo_gerar']:checked").val();
                let faixas = getFaixas();
                let cidade = $('#cidade').val();
                let copart = cenarioSelecionado.copart;
                let odonto = cenarioSelecionado.odonto;
                let load   = $(".ajax_load");

                $.ajax({
                    url: '{{ route('hapvida-ss.gerar') }}',
                    method: 'POST',
                    data: {
                        coparticipacao: copart,
                        tabela_origem:  cidade,
                        plano_id:       planoAtual(),
                        faixas:         faixas,
                        odonto:         odonto,
                        tipo_documento: tipo,
                        _token:         '{{ csrf_token() }}'
                    },
                    xhrFields: { responseType: 'blob' },
                    beforeSend: function () {
                        load.fadeIn(100).css('display', 'flex');
                        $('#modalGerarImagem').addClass('hidden');
                    },
                    success: function (blob, status, xhr) {
                        if (blob.size) {
                            let filename = '';
                            let disp = xhr.getResponseHeader('Content-Disposition');
                            if (disp && disp.indexOf('attachment') !== -1) {
                                let m = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disp);
                                if (m && m[1]) filename = m[1].replace(/['"]/g, '');
                            }
                            let url = (window.URL || window.webkitURL).createObjectURL(blob);
                            let a = document.createElement('a');
                            a.href = url;
                            a.download = filename || ('hapvida-ss-' + Date.now() + '.' + tipo);
                            document.body.appendChild(a);
                            a.click();
                            setTimeout(function () { (window.URL || window.webkitURL).revokeObjectURL(url); }, 100);
                        }
                        load.fadeOut(100).css('display', 'none');
                    },
                    error: function () {
                        load.fadeOut(100).css('display', 'none');
                        alert('Erro ao gerar o documento. Tente novamente.');
                    }
                });
            });

        });
    </script>
    @endsection
</x-app-layout>
