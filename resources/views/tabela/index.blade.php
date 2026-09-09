<x-app-layout>
    <div class="max-w-full mx-auto sm:px-6 lg:px-8 flex flex-col lg:flex-row gap-x-4 px-4" style="align-items: flex-start;">
        <x-informacoes-tabela :estados="$estados" :ufpreferencia="$uf_preferencia" class="sm:mx-5"></x-informacoes-tabela>
        <x-operadoras-tabela :operadoras="$administradoras" class="sm:mx-5"></x-operadoras-tabela>
        <x-planos-tabela :planos="$planos" class="sm:mx-5"></x-planos-tabela>
        <div class="p-1 rounded mt-2 hidden bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border w-full lg:w-[30%] sm:mx-5" id="resultado"></div>
    </div>



    {{-- Modal de opções do Gerar Imagem (mesma lógica do modal do /dashboard) --}}
    <style>
        /* Celular: modal compacta (CSS puro — classes novas do Tailwind não existem no build de produção) */
        @media (max-width: 480px) {
            #modalTabelaCompleta > div { width: 92% !important; max-width: 21rem; padding: 12px 16px !important; border-width: 2px !important; }
            #modalTabelaCompleta h2 { font-size: 1rem !important; margin-bottom: 8px !important; }
            #modalTabelaCompleta fieldset { padding: 8px 10px !important; margin-top: 8px !important; border-width: 2px !important; }
            #modalTabelaCompleta legend { font-size: .9rem !important; }
            #modalTabelaCompleta span.font-semibold { font-size: .85rem !important; }
            #modalTabelaCompleta .flex.justify-center { margin-top: 10px !important; }
            #modalTabelaCompleta #gerarTabelaCompletaBtn { padding: 8px 16px !important; font-size: 1rem !important; }
        }
    </style>
    <div id="modalTabelaCompleta" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden" style="z-index:9998;">
        <div class="bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] px-6 py-10 rounded-lg shadow-lg w-96 text-white border-white border-4">
            <div class="flex justify-between">
                <h2 class="text-lg font-bold mb-4 mx-auto">Escolha a Opção</h2>
                <svg xmlns="http://www.w3.org/2000/svg" id="fecharModalTC" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-6 border-white border-4 rounded hover:cursor-pointer">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </div>

            <div class="space-y-2">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" id="comCoparticipacaoTC" checked="checked" class="form-checkbox">
                    <span class="font-semibold">Com Coparticipação</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" id="semCoparticipacaoTC" checked="checked" class="form-checkbox">
                    <span class="font-semibold">Sem Coparticipação</span>
                </label>
            </div>

            <fieldset class="border-4 border-gray-300 rounded-lg p-4 mt-4">
                <legend class="text-lg font-semibold px-2 mx-auto">Acomodação</legend>
                <div class="space-y-2">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" id="apartamentoTC" checked class="form-checkbox">
                        <span class="font-semibold">Apartamento</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" id="enfermariaTC" checked class="form-checkbox">
                        <span class="font-semibold">Enfermaria</span>
                    </label>
                </div>
            </fieldset>

            <div class="flex justify-center mt-3">
                <button id="gerarTabelaCompletaBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full w-full text-lg">Gerar</button>
            </div>
        </div>
    </div>

    @section('scripts')
        <script>

            $(document).ready(function(){
                function scrollToBottom() {
                    if (window.innerWidth <= 768) { // Aplica apenas para mobile
                        // Espera o conteúdo carregado via AJAX aparecer antes de rolar,
                        // e calcula a altura na hora do scroll (não na do clique)
                        setTimeout(function () {
                            $('html, body').stop(true).animate({
                                scrollTop: $(document).height()
                            }, 1200, 'swing');
                        }, 400);
                    }
                }

                // Delegado no body: pega também os radios de plano injetados via AJAX
                $("body").on('change', "input[name='operadoras']", scrollToBottom);
                $("body").on('click', "input[name='planos-radio']", scrollToBottom);

                $("input[type='text']").on('input', function(){
                    // Quando o usuário digitar algo, o scroll segue o progresso
                    scrollToBottom();
                });

                function ultimaEtapa() {
                    scrollToBottom();
                }

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                // UF -> Cidade pelos vínculos da assinatura (mapa montado no servidor, sem AJAX)
                const cidadesPorUf = @json($cidadesPorUf);

                function preencherCidades(disparaChange) {
                    let uf = $('#estado').val();
                    let $cid = $('#cidade');
                    $cid.empty().append('<option value="" class="text-xs text-black">Escolher Cidade</option>');
                    (cidadesPorUf[uf] || []).forEach(function (c) {
                        $cid.append($('<option>', { value: c.id, text: c.nome, 'class': 'text-black' }));
                    });
                    if (disparaChange) {
                        $cid.trigger('change'); // roda os resets que a página já tem no change da cidade
                    }
                }

                $('#estado').on('change', function () { preencherCidades(true); });

                // UF preferida já vem selecionada do servidor: carrega as cidades dela
                if ($('#estado').val()) { preencherCidades(false); }

                function filtrarPlanosPorCidadeEOperadora() {
                    let valor = $("input[name='operadoras']:checked").val();
                    let cidade = $("#cidade").val();

                    if (!valor || !cidade) {
                        return;
                    }

                    if($("#resultado").is(":visible")){
                        $("input[name='planos-radio']").prop('checked', false);
                        $("#resultado").hide().empty();
                    }
                    $.ajax({
                        url: '{{route('buscar_planos')}}',  // URL da rota que irá processar a requisição
                        type: 'POST',
                        data: {
                            administradora_id: valor,
                            tabela_origens_id: cidade
                        },
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest', // Define como uma requisição AJAX
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Inclui o CSRF token
                        },
                        success: function(response) {
                            // Monta a lista de ids de planos disponíveis para essa cidade + administradora
                            let planosPermitidos = [];
                            $.each(response.planos_por_grupo, function(grupoNome, planos) {
                                $.each(planos, function(i, plano) {
                                    planosPermitidos.push(Number(plano.id));
                                });
                            });
                            $.each(response.planos_sem_grupo, function(i, plano) {
                                planosPermitidos.push(Number(plano.id));
                            });
                            if (response.planos_ambulatoriais && response.planos_ambulatoriais.length) {
                                $.each(response.planos_ambulatoriais, function(i, plano) {
                                    planosPermitidos.push(Number(plano.id));
                                });
                            } else if (response.tem_ambulatorial && response.plano_ambulatorial_id) {
                                planosPermitidos.push(Number(response.plano_ambulatorial_id));
                            }

                            // Atualiza a lista de planos com os dados recebidos
                            $('#planos').removeClass('hidden').find('div[data-plano]').each(function() {
                                let planoId = Number($(this).data('plano'));
                                if (planosPermitidos.includes(planoId)) {
                                    $(this).show();  // Mostra o plano
                                } else {
                                    $(this).hide();  // Esconde o plano
                                }
                            });

                            // Opções Ambulatoriais como no dashboard: um radio por plano com
                            // tabela ambulatorial nesta operadora+cidade (substitui o antigo
                            // botão verde "Ambulatorial" que ficava embaixo do resultado)
                            $('#planos div[data-plano-ambulatorial]').remove();
                            let planosAmbTC = response.planos_ambulatoriais || [];
                            $.each(planosAmbTC, function (i, plano) {
                                let rotuloAmb = planosAmbTC.length > 1 ? plano.nome + ' - Ambulatorial' : 'Ambulatorial';
                                $('#planos').append(`
                                    <div data-plano-ambulatorial="${plano.id}" class="py-1 w-full px-1 me-2 mb-2 text-sm font-medium text-white focus:outline-none rounded-lg bg-gray-500 bg-opacity-10 dark:hover:text-gray-900" style="border:2px solid white;">
                                        <label class="flex justify-between items-center">
                                            <div class="flex w-[100%] p-3">
                                                <input type="radio" value="${plano.id}" name="planos-radio" data-ambulatorial="1" class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300">
                                                <span class="ms-2 text-white flex justify-between text-sm font-medium">${rotuloAmb}</span>
                                            </div>
                                        </label>
                                    </div>`);
                            });
                        },
                        error: function() {
                            alert('Erro ao buscar os planos. Tente novamente.');
                        }
                    });
                }

                function resetSelecao() {
                    $("input[name='operadoras']").prop('checked', false);
                    $("input[name='planos-radio']").prop('checked', false);
                    $("#resultado").hide().empty();
                    $("#planos").addClass('hidden').find('div[data-plano]').show();
                }

                $("body").on('change touchstart',"input[name='operadoras']",function(e){
                    e.preventDefault();
                    filtrarPlanosPorCidadeEOperadora();
                    return false;
                })

                $('#cidade').on('change', function(){
                    resetSelecao();
                    filtrarPlanosPorCidadeEOperadora();
                })



                /*****************verificar se cidade e minus estão preenchidos para aparecer administradoras*******/
                function checkFields() {
                    var hasValue = false;
                    // Verifica se algum campo de texto tem valor diferente de vazio ou zero
                    $('input[type="text"]').each(function() {

                        if ($(this).val().trim() !== '' && $(this).val() !== '0') {
                            hasValue = true;
                        }
                    });
                    // Verifica se o select está preenchido
                    var cidadeSelected = $('#cidade').val() !== '';
                    console.log(cidadeSelected);

                    // Se ambas as condições forem verdadeiras, remova a classe 'hidden'
                    //if (hasValue && cidadeSelected) {
                    $('#operadoras').removeClass('hidden');
                    //} else {
                    //$('#operadoras').addClass('hidden');
                    //}

                    if($("#planos").is(":visible") && $("#operadoras").is(":visible") && $("#resultado").is(":visible")) {
                        atualizarResultado();
                    }
                }

                $('input[type="text"]').on('input', checkFields);
                $('#cidade').on('change', checkFields);
                /*****************verificar se cidade e minus estão preenchidos para aparecer administradoras*******/

                /***********Incrementar valores aos input*****************************/
                let counterInput = $("input[type='text']");
                let incrementButton = $("button:contains('+')");
                let decrementButton = $("button:contains('-')");
                incrementButton.click(function() {
                    let inputField = $(this).siblings("input[type='text']");
                    let currentValue = parseInt(inputField.val()) || 0;
                    if (getTotal() < 8) {
                        inputField.val(currentValue + 1);
                        inputField.trigger('input'); // Dispara o evento 'input' no campo de texto
                    }
                });

                // Adiciona evento de clique para decremento
                decrementButton.click(function() {
                    let inputField = $(this).siblings("input[type='text']");
                    let currentValue = parseInt(inputField.val()) || 0;
                    if (currentValue > 0) {
                        inputField.val(currentValue - 1);
                        inputField.trigger('input'); // Dispara o evento 'input' no campo de texto
                    }
                });


                function getTotal() {
                    let total = 0;
                    $("input[type='text']").each(function() {
                        total += parseInt($(this).val()) || 0;
                    });
                    return total;
                }
                /***********Incrementar valores aos input*****************************/


                function atualizarResultado(ambulatorial = 0) {

                    setTimeout(()=>{
                        let cidade = "";
                        let plano = "";
                        let operadora = "";
                        let faixas = [];
                        let status_carencia = "";

                        cidade = $("#cidade").val();
                        plano = $("input[name='planos-radio']:checked").val();
                        operadora = $("input[name='operadoras']:checked").val();
                        faixas = [{

                            '1' : 1,
                            '2' : 1,
                            '3' : 1,
                            '4' : 1,
                            '5' : 1,
                            '6' : 1,
                            '7' : 1,
                            '8' : 1,
                            '9' : 1,
                            '10' : 1

                        }];


                        $.ajax({
                            url: "{{ route('orcamento.tabela.montarOrcamento') }}",
                            method: "POST",
                            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                            data: {
                                "tabela_origem": cidade,
                                "plano": plano,
                                "operadora": operadora,
                                "_token": "{{ csrf_token() }}",
                                "ambulatorial" : ambulatorial,
                                "faixas": faixas
                            },
                            success: function(res) {
                                console.log(res);
                                $("#resultado").removeClass('hidden').slideDown('slow').html(res);
                                // //interacaoContador++;
                                return false;
                            }
                        });


                    },0.1);


                    return false;

                }

                $("body").on('click',"input[name='planos-radio']",function(){
                    let valor = $(this).val();
                    //console.log(valor);
                    atualizarResultado($(this).data('ambulatorial') ? 1 : 0);
                });




                $("body").on('click',".downloadLink",function(e){
                    let load = $(".ajax_load");
                    e.preventDefault();
                    let linkUrl = $(this).attr("href");

                    let cidade = "";
                    let plano = "";
                    let operadora = "";
                    let faixas = [];
                    let odonto = "";
                    //let status_carencia = "";



                    cidade = $("#cidade").val();
                    plano = $("input[name='planos-radio']:checked").val();
                    operadora = $("input[name='operadoras']:checked").val();
                    let status_carencia = $("input[name='status_carencia']").is(':checked');
                    let status_desconto = $("input[name='status_desconto']").is(':checked');

                    // Exibe o valor booleano no console
                    odonto = $(this).attr('data-odonto');
                    faixas = [{
                        '1' : $("body").find("#input_0_18").val(),
                        '2' : $("body").find('#input_19_23').val(),
                        '3' : $("body").find('#input_24_28').val(),
                        '4' : $("body").find('#input_29_33').val(),
                        '5' : $("body").find('#input_34_38').val(),
                        '6' : $("body").find('#input_39_43').val(),
                        '7' : $("body").find('#input_44_48').val(),
                        '8' : $("body").find('#input_49_53').val(),
                        '9' : $("body").find('#input_54_58').val(),
                        '10' : $("body").find('#input_59').val()
                    }];

                    $.ajax({
                        url: "{{route('gerar.imagem')}}",
                        method: "POST",
                        data: {
                            "tabela_origem": cidade,
                            "plano": plano,
                            "operadora": operadora,
                            "faixas": faixas,
                            "odonto" : odonto,
                            "status_carencia" : status_carencia,
                            "status_desconto" : status_desconto,
                            "ambulatorial": 0
                            //"cliente" : cliente,
                            //"_token": "{{ csrf_token() }}"
                        },
                        xhrFields: {
                            responseType: 'blob'
                        },
                        beforeSend: function () {
                            load.fadeIn(100).css("display", "flex");
                        },
                        success:function(blob,status,xhr,ppp) {
                            if(blob.size && blob.size != undefined) {

                                var filename = "";
                                var disposition = xhr.getResponseHeader('Content-Disposition');
                                if (disposition && disposition.indexOf('attachment') !== -1) {
                                    var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                                    var matches = filenameRegex.exec(disposition);
                                    if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                                }
                                if (typeof window.navigator.msSaveBlob !== 'undefined') {
                                    window.navigator.msSaveBlob(blob, filename);
                                } else {
                                    var URL = window.URL || window.webkitURL;
                                    var downloadUrl = URL.createObjectURL(blob);
                                    if (filename) {
                                        var a = document.createElement("a");
                                        if (typeof a.download === 'undefined') {
                                            window.location.href = downloadUrl;
                                        } else {
                                            a.href = downloadUrl;
                                            a.download = filename;
                                            document.body.appendChild(a);
                                            a.click();
                                        }
                                    } else {
                                        window.location.href = downloadUrl;
                                    }
                                    setTimeout(function () {
                                        URL.revokeObjectURL(downloadUrl);
                                    },100);
                                    load.fadeOut(100).css("display", "none");
                                }
                            }
                        }
                    });
                    return false;
                });

                $("body").on('click',".downloadLinkAmbulatorial",function(e){
                    let load = $(".ajax_load");
                    e.preventDefault();
                    let linkUrl = $(this).attr("href");
                    let cidade = "";
                    let plano = "";
                    let operadora = "";
                    let faixas = [];
                    let odonto = "";
                    let status_carencia = $("input[name='status_carencia_ambulatorial']").is(':checked');
                    let status_desconto = $("input[name='status_desconto_ambulatorial']").is(':checked');
                    cidade = $("#cidade").val();
                    plano = $("input[name='planos-radio']:checked").val();
                    operadora = $("input[name='operadoras']:checked").val();
                    // Exibe o valor booleano no console
                    odonto = $(this).attr('data-odonto');
                    faixas = [{
                        '1' : $("body").find("#input_0_18").val(),
                        '2' : $("body").find('#input_19_23').val(),
                        '3' : $("body").find('#input_24_28').val(),
                        '4' : $("body").find('#input_29_33').val(),
                        '5' : $("body").find('#input_34_38').val(),
                        '6' : $("body").find('#input_39_43').val(),
                        '7' : $("body").find('#input_44_48').val(),
                        '8' : $("body").find('#input_49_53').val(),
                        '9' : $("body").find('#input_54_58').val(),
                        '10' : $("body").find('#input_59').val()
                    }];
                    $.ajax({
                        url: "{{route('gerar.imagem')}}",
                        method: "POST",
                        data: {
                            "tabela_origem": cidade,
                            "plano": plano,
                            "operadora": operadora,
                            "faixas": faixas,
                            "odonto" : odonto,
                            "status_carencia" : status_carencia,
                            "status_desconto" : status_desconto,
                            "ambulatorial": 1
                            //"cliente" : cliente,
                            //"_token": "{{ csrf_token() }}"
                        },
                        xhrFields: {
                            responseType: 'blob'
                        },
                        beforeSend: function () {
                            load.fadeIn(100).css("display", "flex");
                        },
                        success:function(blob,status,xhr,ppp) {
                            if(blob.size && blob.size != undefined) {

                                var filename = "";
                                var disposition = xhr.getResponseHeader('Content-Disposition');
                                if (disposition && disposition.indexOf('attachment') !== -1) {
                                    var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                                    var matches = filenameRegex.exec(disposition);
                                    if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                                }
                                if (typeof window.navigator.msSaveBlob !== 'undefined') {
                                    window.navigator.msSaveBlob(blob, filename);
                                } else {
                                    var URL = window.URL || window.webkitURL;
                                    var downloadUrl = URL.createObjectURL(blob);
                                    if (filename) {
                                        var a = document.createElement("a");
                                        if (typeof a.download === 'undefined') {
                                            window.location.href = downloadUrl;
                                        } else {
                                            a.href = downloadUrl;
                                            a.download = filename;
                                            document.body.appendChild(a);
                                            a.click();
                                        }
                                    } else {
                                        window.location.href = downloadUrl;
                                    }
                                    setTimeout(function () {
                                        URL.revokeObjectURL(downloadUrl);
                                    },100);
                                    load.fadeOut(100).css("display", "none");
                                }
                            }
                        }
                    });
                    return false;
                });


                $("body").on('click','.gerar_imagem_ambulatorial',function(){
                    let load = $(".ajax_load");
                    let odonto = $(this).data('odonto');
                    let cidade = "";
                    let plano = "";
                    let operadora = "";
                    let faixas = [];
                    let status_carencia = "";

                    faixas = [{

                        '1' : 1,
                        '2' : 1,
                        '3' : 1,
                        '4' : 1,
                        '5' : 1,
                        '6' : 1,
                        '7' : 1,
                        '8' : 1,
                        '9' : 1,
                        '10' : 1

                    }];

                    cidade = $("#cidade").val();
                    plano = $("input[name='planos-radio']:checked").val();
                    operadora = $("input[name='operadoras']:checked").val();

                    $.ajax({
                        url: "{{route('tabela.gerar-ambulatorial')}}",
                        method: "POST",
                        xhrFields: {
                            responseType: 'blob'
                        },
                        data: {
                            faixas: faixas,
                            cidade,
                            plano,
                            operadora,
                            odonto
                        },
                        beforeSend: function () {
                            load.fadeIn(100).css("display", "flex");
                        },
                        success:function(blob,status,xhr,ppp) {
                            if (blob.size && blob.size != undefined) {
                                var filename = "";
                                var disposition = xhr.getResponseHeader('Content-Disposition');
                                if (disposition && disposition.indexOf('attachment') !== -1) {
                                    var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                                    var matches = filenameRegex.exec(disposition);
                                    if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                                }
                                if (typeof window.navigator.msSaveBlob !== 'undefined') {
                                    window.navigator.msSaveBlob(blob, filename);
                                } else {
                                    var URL = window.URL || window.webkitURL;
                                    var downloadUrl = URL.createObjectURL(blob);
                                    if (filename) {
                                        var a = document.createElement("a");
                                        if (typeof a.download === 'undefined') {
                                            window.location.href = downloadUrl;
                                        } else {
                                            a.href = downloadUrl;
                                            a.download = filename;
                                            document.body.appendChild(a);
                                            a.click();
                                        }
                                    } else {
                                        window.location.href = downloadUrl;
                                    }
                                    setTimeout(function () {
                                        URL.revokeObjectURL(downloadUrl);
                                    }, 100);
                                    load.fadeOut(100).css("display", "none");
                                }
                            }
                        }
                    })









                });





                // Gerar Imagem abre o modal de opções; a geração acontece no Gerar do modal
                let odontoTabelaSelecionado = 0;
                $("body").on('click','.gerar_imagem',function() {
                    odontoTabelaSelecionado = $(this).data('odonto');
                    $("#modalTabelaCompleta").removeClass("hidden");
                });

                $("#fecharModalTC").on("click", function () {
                    $("#modalTabelaCompleta").addClass("hidden");
                });
                $("#modalTabelaCompleta").on("click", function (event) {
                    if (event.target === this) { $(this).addClass("hidden"); }
                });

                $("body").on('click','#gerarTabelaCompletaBtn',function() {
                    $("#modalTabelaCompleta").addClass("hidden");
                    let load = $(".ajax_load");
                    let odonto = odontoTabelaSelecionado;
                    let cidade = "";
                    let plano = "";
                    let operadora = "";
                    let faixas = [];
                    let status_carencia = "";

                    faixas = [{

                        '1' : 1,
                        '2' : 1,
                        '3' : 1,
                        '4' : 1,
                        '5' : 1,
                        '6' : 1,
                        '7' : 1,
                        '8' : 1,
                        '9' : 1,
                        '10' : 1

                    }];

                    cidade = $("#cidade").val();
                    plano = $("input[name='planos-radio']:checked").val();
                    operadora = $("input[name='operadoras']:checked").val();

                    $.ajax({
                        url: "{{route('tabela.gerar')}}",
                        method: "POST",
                        xhrFields: {
                            responseType: 'blob'
                        },
                        data: {
                            faixas: faixas,
                            cidade,
                            plano,
                            operadora,
                            odonto,
                            comcoparticipacao: $("#comCoparticipacaoTC").is(":checked") ? "true" : "false",
                            semcoparticipacao: $("#semCoparticipacaoTC").is(":checked") ? "true" : "false",
                            mostrar_apartamento: $("#apartamentoTC").is(":checked") ? "true" : "false",
                            mostrar_enfermaria: $("#enfermariaTC").is(":checked") ? "true" : "false",
                            tipo_documento: "imagem"

                        },
                        beforeSend: function () {
                            load.fadeIn(100).css("display", "flex");
                        },
                        success:function(blob,status,xhr,ppp) {
                            if (blob.size && blob.size != undefined) {
                                var filename = "";
                                var disposition = xhr.getResponseHeader('Content-Disposition');
                                if (disposition && disposition.indexOf('attachment') !== -1) {
                                    var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                                    var matches = filenameRegex.exec(disposition);
                                    if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                                }
                                if (typeof window.navigator.msSaveBlob !== 'undefined') {
                                    window.navigator.msSaveBlob(blob, filename);
                                } else {
                                    var URL = window.URL || window.webkitURL;
                                    var downloadUrl = URL.createObjectURL(blob);
                                    if (filename) {
                                        var a = document.createElement("a");
                                        if (typeof a.download === 'undefined') {
                                            window.location.href = downloadUrl;
                                        } else {
                                            a.href = downloadUrl;
                                            a.download = filename;
                                            document.body.appendChild(a);
                                            a.click();
                                        }
                                    } else {
                                        window.location.href = downloadUrl;
                                    }
                                    setTimeout(function () {
                                        URL.revokeObjectURL(downloadUrl);
                                    }, 100);
                                    load.fadeOut(100).css("display", "none");
                                }
                            }
                        }
                    })
                });









                $("body").on('click','.btn_ambulatorial',function(){
                    $("#resultado").slideUp("slow");
                    $("#resultado").empty();
                    atualizarResultado(1)

                });

                $("body").on('click','.btn_normal',function(){
                    $("#resultado").slideUp("slow");
                    $("#resultado").empty();
                    atualizarResultado(0)
                });


            });
        </script>
    @endsection
</x-app-layout>
