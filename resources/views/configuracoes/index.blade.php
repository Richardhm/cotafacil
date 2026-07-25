<x-app-layout>
    <div class="py-6">
        <div class="w-[95%] mx-auto sm:px-6 lg:px-8">
            <div class="bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] overflow-hidden shadow-sm sm:rounded-lg">
                <!-- Container das Abas -->
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-3" aria-label="Tabs">
                        <!-- Itens das Abas -->
                        <a href="#tab0" class="tab-button whitespace-nowrap py-3 px-2 border-b-2 font-medium text-sm text-white hover:bg-white/10 transition-all border-transparent active rounded-tl-lg rounded-tr-lg">Tabelas</a>
                        <a href="#tab1" class="tab-button whitespace-nowrap py-3 px-2 border-b-2 font-medium text-sm text-white hover:bg-white/10 transition-all border-transparent">Coparticipações</a>
                        <a href="#tab2" class="tab-button whitespace-nowrap py-3 px-2 border-b-2 font-medium text-sm text-white hover:bg-white/10 transition-all border-transparent">Cidades</a>
                        <a href="#tab3" class="tab-button whitespace-nowrap py-3 px-2 border-b-2 font-medium text-sm text-white hover:bg-white/10 transition-all border-transparent">Assinaturas/Cidade</a>
                        <a href="#tab4" class="tab-button whitespace-nowrap py-3 px-2 border-b-2 font-medium text-sm text-white hover:bg-white/10 transition-all border-transparent">Administradora</a>
                        <a href="#tab5" class="tab-button whitespace-nowrap py-3 px-2 border-b-2 font-medium text-sm text-white hover:bg-white/10 transition-all border-transparent rounded-tr-lg">Planos</a>
                        <a href="#tab6" class="tab-button whitespace-nowrap py-3 px-2 border-b-2 font-medium text-sm text-white hover:bg-white/10 transition-all border-transparent rounded-tr-lg">Desconto</a>
                        <a href="#tab7" class="tab-button whitespace-nowrap py-3 px-2 border-b-2 font-medium text-sm text-white hover:bg-white/10 transition-all border-transparent rounded-tr-lg">Assinaturas/User</a>
                        <a href="#tab8" class="tab-button whitespace-nowrap py-3 px-2 border-b-2 font-medium text-sm text-white hover:bg-white/10 transition-all border-transparent rounded-tr-lg">Administradora/Plano/Cidade</a>
                        <a href="#tab9" class="tab-button whitespace-nowrap py-3 px-2 border-b-2 font-medium text-sm text-white hover:bg-white/10 transition-all border-transparent rounded-tr-lg">Cupons</a>
                        <a href="#tab10" class="tab-button whitespace-nowrap py-3 px-2 border-b-2 font-medium text-sm text-white hover:bg-white/10 transition-all border-transparent rounded-tr-lg">Carencia</a>
                        <a href="#tab11" class="tab-button whitespace-nowrap py-3 px-2 border-b-2 font-medium text-sm text-white hover:bg-white/10 transition-all border-transparent rounded-tr-lg">PDF Exceção</a>
                        <a href="#tab12" class="tab-button whitespace-nowrap py-3 px-2 border-b-2 font-medium text-sm text-white hover:bg-white/10 transition-all border-transparent rounded-tr-lg">📥 Importar PDF</a>

                    </nav>
                </div>

                <!-- Conteúdo das Abas -->
                <div class="p-6">
                    <!-- Conteúdos mantidos igual -->
                    <div id="tab0" class="tab-content active">
                        <x-configuracoes.tabelas-tab />
                    </div>
                    <div id="tab1" class="tab-content hidden">
                        <x-configuracoes.pdf-tab />
                    </div>
                    <div id="tab2" class="tab-content hidden">
                        <x-configuracoes.cidades-tab />
                    </div>
                    <div id="tab3" class="tab-content hidden">
                        <x-configuracoes.assinaturas-cidade-tab />
                    </div>
                    <div id="tab4" class="tab-content hidden">
                        <x-configuracoes.administradoras-tab />
                    </div>
                    <div id="tab5" class="tab-content hidden">
                        <x-configuracoes.planos-tab />
                    </div>
                    <div id="tab6" class="tab-content hidden">
                        <x-configuracoes.desconto-tab />
                    </div>
                    <div id="tab7" class="tab-content hidden">
                        <x-configuracoes.assinaturas-user :perPage="15" />
                    </div>
                    <div id="tab8" class="tab-content hidden">
                        <x-configuracoes.administradora-plano-cidade />
                    </div>
                    <div id="tab9" class="tab-content hidden">
                        <x-configuracoes.cupons-tab />
                    </div>
                    <div id="tab10" class="tab-content hidden">
                        <x-configuracoes.carencia-tab />
                    </div>
                    <div id="tab11" class="tab-content hidden">
                        <x-configuracoes.pdf-excecao-tab />
                    </div>
                    <div id="tab12" class="tab-content hidden">
                        <x-configuracoes.importar-tabelas-tab />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .assinatura-item.active {
            background-color: rgba(59, 130, 246, 0.8);
            border-left: 4px solid #3B82F6;
        }

        .tab-button.active {
            background: rgba(255, 255, 255, 0.25);
            border-top: 2px solid #6366f1;
            border-left: 2px solid #6366f1;
            border-right: 2px solid #6366f1;
            border-bottom: 2px solid transparent !important;
            color: white;
            position: relative;
            top: 0px;
            border-radius: 8px 8px 0 0;
        }

        .tab-button {
            margin-bottom: -2px;
            transition: all 0.2s ease;
        }
    </style>

    @section('scripts')
        <script>
            $(document).ready(function() {


                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $('#formPlanos').on('submit', function(e) {
                    e.preventDefault();

                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            window.location.reload();
                            //toastr.success(response.success);
                            // Atualiza a lista
                            //location.reload();
                            //$('#tab9').load(location.href + ' #tab9');
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON.message || 'Erro ao salvar!');
                        }
                    });
                });









                $('input[name*="valor_apartamento"]').mask("#.##0,00", {reverse: true});
                $('input[name*="valor_enfermaria"]').mask("#.##0,00", {reverse: true});
                $('input[name*="valor_ambulatorial"]').mask("#.##0,00", {reverse: true});


                $('input[name="desconto_plano"]').mask("#.##0,00", {reverse: true});
                $('input[name="desconto_extra"]').mask("#.##0,00", {reverse: true});




                // Ativa aba inicial
                $('.tab-button:first').addClass('active');
                $('.tab-button').click(function (e) {
                    e.preventDefault();
                    // Remove todas as classes ativas
                    $('.tab-button').removeClass('active');
                    $('.tab-content').hide();

                    // Adiciona classe ativa no item clicado
                    $(this).addClass('active');

                    // Mostra o conteúdo correspondente
                    $($(this).attr('href')).show();
                });


                var valores = [];

                $("body").on('change', 'select[class^="tabela"]', function (e) {
                    let todosPreenchidos = true;
                    if ($('select[name="administradora"]').val() == '' ||
                        $('select[name="planos"]').val() == '' ||
                        $('select[name="tabela_origem"]').val() == '' ||
                        $('select[name="coparticipacao"]').val() == '' ||
                        $('select[name="odonto"]').val() == '')
                    {
                        todosPreenchidos = false;
                        return false;
                    } else {

                        var valores = {
                            "administradora" : $('select[name="administradora"]').val(),
                            "planos" : $('select[name="planos"]').val(),
                            "tabela_origem" : $('select[name="tabela_origem"]').val(),
                            "coparticipacao" : $('select[name="coparticipacao"]').val(),
                            "odonto" : $('select[name="odonto"]').val()
                        };
                        //valores.push($(this).val());
                        $(".alert-danger").remove();

                        if(todosPreenchidos) {
                            $('input[name*="valor_apartamento"]').prop('disabled',false);
                            $('input[name*="valor_enfermaria"]').prop('disabled',false);
                            $('input[name*="valor_ambulatorial"]').prop('disabled',false);
                            $('#overlay').removeClass("ocultar");

                            let administradora = $('select[name="administradora"]').val();
                            let planos  = $('select[name="planos"]').val()
                            let tabela_origem = $('select[name="tabela_origem"]').val();
                            let coparticipacao = $('select[name="coparticipacao"]').val();
                            let odonto = $('select[name="odonto"]').val();

                            //let plano = $("#planos").val();
                            //let cidade = $("#tabela_origem").val();
                            $('.valor').removeAttr('disabled');

                            $.ajax({
                                url:"{{route('tabelas.verificar')}}",
                                method:"POST",
                                data: {
                                    "administradora" : administradora,
                                    "planos" : planos,
                                    "tabela_origem" : tabela_origem,
                                    "coparticipacao" : coparticipacao,
                                    "odonto" : odonto,

                                },
                                success:function(res) {

                                    // $('#overlay').addClass('ocultar');
                                    if(res != "empty") {

                                        $('input[name="valor_apartamento[]"]').each(function(index) {
                                            $(this).addClass('valor');
                                            if (res[index] && res[index].acomodacao_id == 1) {
                                                $(this).val(res[index].valor_formatado).attr('data-id',res[index].id);
                                            }
                                        });
                                        $('input[name="valor_enfermaria[]"]').each(function(index) {
                                            $(this).addClass('valor');
                                            if (res[index+10] && res[index+10].acomodacao_id == 2) {
                                                $(this).val(res[index+10].valor_formatado).attr('data-id',res[index+10].id);
                                            }
                                        });
                                        $('input[name="valor_ambulatorial[]"]').each(function(index) {
                                            $(this).addClass('valor');
                                            if (res[index+20] && res[index+20].acomodacao_id == 3) {
                                                $(this).val(res[index+20].valor_formatado).attr('data-id',res[index+20].id)
                                            }
                                        });
                                        $("#container_btn_cadastrar").slideUp('slow').html('');
                                    } else {
                                        $('input[name="valor_apartamento[]"]')
                                            .val('')
                                            .removeClass('valor');

                                        $('input[name="valor_enfermaria[]"]')
                                            .val('')
                                            .removeClass('valor');

                                        $('input[name="valor_ambulatorial[]"]')
                                            .val('')
                                            .removeClass('valor');

                                        $("#container_btn_cadastrar")
                                            .html(`<button type="button" class="btn_cadastrar text-white w-full bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">Cadastrar</button>`)
                                            .hide()
                                            .slideDown('slow');

                                        $("#container_alert_cadastrar")
                                            .html(`<div class="bg-blue-100 border border-blue-300 text-blue-800 text-2xl px-4 py-3 rounded">
                                                    <h4 class="font-semibold">Essa tabela não existe, para inserir os dados clicar no botão cadastrar abaixo, após preencher todos os campos.</h4>
                                                </div>`)
                                            .hide()
                                            .slideDown('slow');
                                    }
                                }
                            });
                            return false;
                        }
                        return false;
                    }
                    return false;
                });

                function verificarCamposPreenchidos(tipoCampo) {
                    var todosPreenchidos = true;
                    $("input[name='" + tipoCampo + "']").each(function () {
                        if ($(this).val() === "") {
                            todosPreenchidos = false;
                            return false; // Encerra o loop se encontrar um campo não preenchido
                        }
                    });
                    return todosPreenchidos;
                }

                // Função para obter os valores dos campos de um determinado tipo
                function obterValoresDosCampos(tipoCampo) {
                    var valores = [];
                    $("input[name='" + tipoCampo + "']").each(function () {
                        valores.push($(this).val());
                    });
                    return valores;
                }





                $("body").on('click','.btn_cadastrar',function(){

                    let load = $(".ajax_load");
                    let camposApartamentoPreenchidos  = verificarCamposPreenchidos("valor_apartamento[]");
                    let camposEnfermariaPreenchidos   = verificarCamposPreenchidos("valor_enfermaria[]");
                    let camposAmbulatorialPreenchidos = verificarCamposPreenchidos("valor_ambulatorial[]");

                    if (camposApartamentoPreenchidos && camposEnfermariaPreenchidos && camposAmbulatorialPreenchidos) {

                        let valoresApartamento = obterValoresDosCampos("valor_apartamento[]");
                        let valoresEnfermaria = obterValoresDosCampos("valor_enfermaria[]");
                        let valoresAmbulatorial = obterValoresDosCampos("valor_ambulatorial[]");

                        // Preparar os dados para enviar ao backend (você pode ajustar de acordo com suas necessidades)
                        let dados = {
                            valoresApartamento: valoresApartamento,
                            valoresEnfermaria: valoresEnfermaria,
                            valoresAmbulatorial: valoresAmbulatorial,
                            administradora : $('select[name="administradora"]').val(),
                            planos : $('select[name="planos"]').val(),
                            tabela_origem : $('select[name="tabela_origem"]').val(),
                            coparticipacao : $('select[name="coparticipacao"]').val(),
                            odonto : $('select[name="odonto"]').val(),
                        };

                        $.ajax({
                            url:"{{route('tabelas.salvar')}}",
                            method:"POST",
                            data:dados,
                            beforeSend: function () {
                                load.fadeIn(100).css("display", "flex");
                            },
                            success:function(res) {
                                if(res == "sucesso") {
                                    load.fadeOut(300);
                                    toastr["success"]("Tabela cadastrada com sucesso")
                                    toastr.options = {
                                        "closeButton": false,
                                        "debug": false,
                                        "newestOnTop": false,
                                        "progressBar": false,
                                        "positionClass": "toast-top-right",
                                        "preventDuplicates": false,
                                        "onclick": null,
                                        "showDuration": "300",
                                        "hideDuration": "1000",
                                        "timeOut": "5000",
                                        "extendedTimeOut": "1000",
                                        "showEasing": "swing",
                                        "hideEasing": "linear",
                                        "showMethod": "fadeIn",
                                        "hideMethod": "fadeOut"
                                    }
                                    $("#container_btn_cadastrar").html('');
                                    $("#container_alert_cadastrar").html('');





                                } else {
                                    alert("Erro ao cadastrar a tabela")

                                }
                            }
                        });


                    } else {
                        toastr["error"]("Todos os campos são obrigatório")
                        toastr.options = {
                            "closeButton": false,
                            "debug": false,
                            "newestOnTop": false,
                            "progressBar": false,
                            "positionClass": "toast-top-right",
                            "preventDuplicates": false,
                            "onclick": null,
                            "showDuration": "300",
                            "hideDuration": "1000",
                            "timeOut": "5000",
                            "extendedTimeOut": "1000",
                            "showEasing": "swing",
                            "hideEasing": "linear",
                            "showMethod": "fadeIn",
                            "hideMethod": "fadeOut"
                        }
                        return false; // Impede o envio do formulário se algum campo estiver em branco
                    }
                    return false; // Isso impede o envio do formulário para evitar que a página seja recarregada
                });










                $('body').on('change','.valor',function(){
                    let valor = $(this).val();
                    let id = $(this).attr('data-id');



                    $.ajax({
                        url:"{{route('corretora.mudar.valor.tabela')}}",
                        method:"POST",
                        data:"id="+id+"&valor="+valor,
                        success:function(res) {
                            console.log(res);
                        }
                    });
                });
            });

            document.querySelector('form[name="cupon-form"]').addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const successMessage = document.getElementById('success-message');

                try {
                    const response = await fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        //console.log(data);
                        // Exibir mensagem de sucesso
                        document.getElementById('success-codigo').textContent = data.codigo;
                        document.getElementById('success-validade').textContent = data.validade;
                        document.getElementById('success-plano').textContent = parseFloat(data.valor_plano).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                        successMessage.classList.remove('hidden');

                        // Rolagem suave para a mensagem
                        successMessage.scrollIntoView({ behavior: 'smooth' });

                        // Resetar formulário
                        this.reset();
                    } else {
                        alert('Erro: ' + (data.message || 'Falha ao cadastrar'));
                    }

                } catch (error) {
                    console.error('Erro:', error);
                    alert('Erro na comunicação com o servidor');
                }
            });




            const URL_VINCULOS = "{{ route('admin-planos.vinculos', ['assinatura' => '__ID__']) }}";
            const URL_EXCLUIR_VINCULO = "{{ route('admin-planos.destroy', ['id' => '__ID__']) }}";

            function escapeHtml(texto) {
                return $('<div>').text(texto == null ? '' : texto).html();
            }

            // Estado do modal de vínculos
            let vinculosAssinaturaId = null;   // assinatura aberta no momento
            let vinculosDados = null;          // { total, cidades: [...] } devolvido pelo servidor
            let cidadesAbertas = new Set();    // cidades expandidas dentro do modal

            function abrirModalVinculos(assinaturaId, titulo) {
                const modal = document.getElementById('modal-vinculos');
                if (!modal) {
                    return;
                }

                vinculosAssinaturaId = assinaturaId;
                vinculosDados = null;
                cidadesAbertas = new Set();

                document.getElementById('modal-vinculos-titulo').textContent = titulo;
                document.getElementById('modal-vinculos-subtitulo').textContent = 'Carregando...';
                document.getElementById('modal-vinculos-busca').value = '';
                document.getElementById('modal-vinculos-corpo').innerHTML =
                    '<p class="px-4 py-4 text-white text-sm opacity-80">Carregando...</p>';

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');

                $.getJSON(URL_VINCULOS.replace('__ID__', assinaturaId))
                    .done(function (data) {
                        vinculosDados = data;
                        renderVinculos();
                    })
                    .fail(function () {
                        document.getElementById('modal-vinculos-subtitulo').textContent = '';
                        document.getElementById('modal-vinculos-corpo').innerHTML =
                            '<p class="px-4 py-4 text-red-300 text-sm">Erro ao carregar os vínculos.</p>';
                    });
            }

            function fecharModalVinculos() {
                const modal = document.getElementById('modal-vinculos');
                if (!modal) {
                    return;
                }

                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
                vinculosAssinaturaId = null;
                vinculosDados = null;
            }

            function toggleCidadeVinculos(cidadeId) {
                if (cidadesAbertas.has(cidadeId)) {
                    cidadesAbertas.delete(cidadeId);
                } else {
                    cidadesAbertas.add(cidadeId);
                }

                renderVinculos();
            }

            function renderVinculos() {
                if (!vinculosDados) {
                    return;
                }

                const corpo = document.getElementById('modal-vinculos-corpo');
                const filtro = (document.getElementById('modal-vinculos-busca').value || '').trim().toLowerCase();
                const todas = vinculosDados.cidades || [];
                const cidades = filtro
                    ? todas.filter(c => (c.cidade || '').toLowerCase().indexOf(filtro) !== -1)
                    : todas;

                document.getElementById('modal-vinculos-subtitulo').textContent =
                    todas.length + ' cidade(s) · ' + (vinculosDados.total || 0) + ' vínculo(s)';

                if (!cidades.length) {
                    corpo.innerHTML = '<p class="px-4 py-4 text-white text-sm opacity-80">'
                        + (todas.length ? 'Nenhuma cidade com esse nome.' : 'Nenhum vínculo cadastrado.')
                        + '</p>';
                    return;
                }

                corpo.innerHTML = cidades.map(montarBlocoCidade).join('');
            }

            function montarBlocoCidade(cidade) {
                const aberta = cidadesAbertas.has(cidade.cidade_id);

                return '<div class="border-b border-white/10">'
                    + '<button type="button" onclick="toggleCidadeVinculos(' + cidade.cidade_id + ')" '
                    + 'class="w-full flex items-center justify-between gap-3 px-4 py-2 text-left text-white hover:bg-white/10">'
                    + '<span class="font-medium text-sm">'
                    + '<span class="inline-block w-3 opacity-70">' + (aberta ? '▾' : '▸') + '</span> '
                    + escapeHtml(cidade.cidade) + '</span>'
                    + '<span class="text-[11px] opacity-70 whitespace-nowrap">' + cidade.total + ' vínculo(s)</span>'
                    + '</button>'
                    + (aberta
                        ? '<div class="bg-black/25">' + cidade.itens.map(montarItemVinculo).join('') + '</div>'
                        : '')
                    + '</div>';
            }

            function montarItemVinculo(item) {
                return '<div class="flex items-center justify-between gap-3 pl-10 pr-4 py-1.5 '
                    + 'text-white text-xs border-t border-white/5">'
                    + '<span>' + escapeHtml(item.administradora)
                    + ' <span class="opacity-50">/</span> ' + escapeHtml(item.plano) + '</span>'
                    + '<button type="button" onclick="excluirVinculo(' + item.id + ')" '
                    + 'class="text-red-400 hover:text-red-300 whitespace-nowrap">Excluir</button>'
                    + '</div>';
            }

            function excluirVinculo(vinculoId) {
                if (!confirm('Excluir este vínculo?')) {
                    return;
                }

                $.ajax({
                    url: URL_EXCLUIR_VINCULO.replace('__ID__', vinculoId),
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        _method: 'DELETE',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    }
                })
                    .done(function () {
                        // Tira o vínculo do estado local e some com a cidade que ficou vazia
                        vinculosDados.cidades.forEach(function (cidade) {
                            cidade.itens = cidade.itens.filter(item => item.id !== vinculoId);
                            cidade.total = cidade.itens.length;
                        });
                        vinculosDados.cidades = vinculosDados.cidades.filter(cidade => cidade.total > 0);
                        vinculosDados.total = vinculosDados.cidades.reduce((soma, c) => soma + c.total, 0);

                        atualizarContadorAssinatura();
                        renderVinculos();
                    })
                    .fail(function () {
                        alert('Erro ao excluir o vínculo.');
                    });
            }

            function atualizarContadorAssinatura() {
                const botao = document.getElementById('btn-vinculos-' + vinculosAssinaturaId);
                if (botao) {
                    botao.textContent = 'Ver Vínculos (' + (vinculosDados.total || 0) + ')';
                }
            }

            document.addEventListener('keydown', function (evento) {
                if (evento.key === 'Escape') {
                    fecharModalVinculos();
                }
            });

            $(function () {
                // A aba mora dentro de um container com backdrop-blur + overflow-hidden, que vira
                // containing block de position:fixed e cortaria o modal. Solta ele no body.
                const modal = document.getElementById('modal-vinculos');
                if (modal && modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
            });






            function copiarCodigo() {
                const codigo = document.getElementById('codigo-input').value;
                navigator.clipboard.writeText(codigo).then(() => {
                    alert('Código copiado para a área de transferência!');
                });
            }





            let itemAtivo = null;
            function carregarCidades(assinaturaId,elemento) {
                if (itemAtivo) {
                    itemAtivo.classList.remove('active');
                }

                // Adicionar classe ativa ao novo item
                elemento.classList.add('active');
                itemAtivo = elemento;
                fetch(`{{ route('assinaturas.cidades', ':assinatura') }}`.replace(':assinatura', assinaturaId))
                    .then(response => response.json())
                    .then(data => {
                        let html = '';
                        data.cidades.forEach(cidade => {
                            html += `
                <div class="flex items-center justify-between p-3 hover:bg-white/10 rounded-lg">
                    <span class="text-white">${cidade.nome} - ${cidade.uf}</span>
                    <input type="checkbox" ${cidade.vinculado ? 'checked' : ''}
                           onchange="toggleVinculo(${assinaturaId}, ${cidade.id}, this.checked)"
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                </div>`;
                        });
                        document.getElementById('cidades-list').innerHTML = html;
                    });
            }

            function toggleVinculo(assinaturaId, cidadeId, vincular) {
                const url = vincular ? "{{ route('assinaturas.vincular') }}" : "{{ route('assinaturas.desvincular') }}";
                const formData = new FormData();
                formData.append('assinatura_id', assinaturaId);
                formData.append('cidade_id', cidadeId);

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            alert('Ocorreu um erro!');
                            location.reload();
                        }
                    });
            }

            // let editMode = false;
            //
            // async function editarConfig(id) {
            //     try {
            //         const response = await fetch(`/pdf/${id}/edit`);
            //         const data = await response.json();
            //         console.log(data);
            //         // Preencher formulário
            //         document.getElementById('config_id').value = data.id;
            //         document.querySelector('[name="plano_id"]').value = data.plano_id;
            //         document.querySelector('[name="tabela_origens_id"]').value = data.tabela_origens_id || '';
            //         document.querySelector('[name="linha01"]').value = data.linha01 || '';
            //
            //         // Split linha02
            //         const parts = data.linha02?.split('|') || [];
            //         document.querySelector('[name="linha02_part1"]').value = parts[0] || '';
            //         document.querySelector('[name="linha02_part2"]').value = parts[1] || '';
            //
            //         document.querySelector('[name="linha03"]').value = data.linha03 || '';
            //
            //         // Preencher campos dinâmicos
            //         const campos = ['consultas_eletivas', 'consultas_de_urgencia', 'exames_simples',
            //             'exames_complexos', 'terapias_especiais', 'demais_terapias',
            //             'internacoes', 'cirurgia'];
            //
            //         campos.forEach(campo => {
            //             document.querySelector(`[name="${campo}_total"]`).value = data[`${campo}_total`] || '';
            //             document.querySelector(`[name="${campo}_parcial"]`).value = data[`${campo}_parcial`] || '';
            //         });
            //
            //         // Alterar para modo edição
            //         editMode = true;
            //         document.getElementById('submit-button').textContent = 'Atualizar Configuração';
            //         document.getElementById('cancel-edit').classList.remove('hidden');
            //         window.scrollTo({ top: 0, behavior: 'smooth' });
            //
            //     } catch (error) {
            //         console.error('Erro:', error);
            //         alert('Erro ao carregar dados para edição');
            //     }
            // }
            //
            // function cancelarEdicao() {
            //     editMode = false;
            //     document.getElementById('config_id').value = '';
            //     document.getElementById('submit-button').textContent = 'Salvar Configuração';
            //     document.getElementById('cancel-edit').classList.add('hidden');
            //     document.querySelector('form[name="store_edit_pdf"]').reset();
            // }
            //
            // // Atualizar o formulário para enviar para a rota correta
            // document.querySelector('form[name="store_edit_pdf"]').addEventListener('submit', function(e) {
            //     if (editMode) {
            //         const id = document.getElementById('config_id').value;
            //         this.action = `/pdf/${id}`;
            //         this.method = 'POST'; // Usaremos method spoofing
            //         const methodInput = document.createElement('input');
            //         methodInput.type = 'hidden';
            //         methodInput.name = '_method';
            //         methodInput.value = 'PUT';
            //         this.appendChild(methodInput);
            //     }
            // });

            function updateCharCounter(input, counterId) {
                const counter = document.getElementById(counterId);
                if (counter) {
                    counter.textContent = input.value.length;

                    // Mudar cor se atingir o limite
                    if (input.value.length >= 35) {
                        counter.classList.add('text-red-400');
                        counter.classList.remove('text-gray-300');
                    } else {
                        counter.classList.remove('text-red-400');
                        counter.classList.add('text-gray-300');
                    }
                }
            }

            document.querySelectorAll('input[maxlength="35"]').forEach(input => {
                const counterId = input.name + '-counter';
                input.setAttribute('oninput', `updateCharCounter(this, '${counterId}')`);
                // Disparar evento inicial
                const event = new Event('input');
                input.dispatchEvent(event);
            });

            document.getElementById('formDesconto').addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                try {
                    const response = await fetch('{{ route("descontos.store") }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Erro: ' + (data.message || 'Falha ao salvar descontos'));
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    alert('Erro ao salvar descontos');
                }
            });

            $('#bloco-planos').hide();
            $('#bloco-tabelas').hide();

            // Quando usuário seleciona administradoras
            $("input[name='administradora_id']").on('change', function(){
                //var adminSelecionados = $("input[name='administradora_id']:checked").map(function(){return this.value;}).get();
                let adminSelecionados = $(this).val();
                $('#bloco-tabelas').hide(); // Esconde tabelas
                $('#bloco-planos').hide(); // Esconde blocos até carregar

                if(adminSelecionados.length > 0) {
                    $.ajax({
                        url: "{{ route('filtrar.planos.admin') }}",
                        type: "POST",
                        data: {
                            administradora_id: adminSelecionados,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(planos) {
                            console.log(planos);
                            $("#lista-planos").empty();
                            $.each(planos, function(i, plano){
                                $("#lista-planos").append(
                                    `<label class="flex items-center space-x-1 text-white text-sm">
                            <input type="radio" name="plano_id" value="${plano.id}" class="rounded border-gray-300 plano-checkbox-js">
                            <span>${plano.nome}</span>
                          </label>`
                                );
                            });
                            $('#bloco-planos').show();
                        }
                    });
                }
            });

            // Quando usuário seleciona um plano
            $(document).on('change', ".plano-checkbox-js", function(){
                var adminSelecionados = $("input[name='administradora_id']:checked").val();
                var planosSelecionados = $(this).val();

                $('#bloco-tabelas').hide();

                if(planosSelecionados && adminSelecionados) {
                    $.ajax({
                        url: "{{ route('filtrar.tabelas.adminplano') }}",
                        type: "POST",
                        data: {
                            administradora_id: adminSelecionados,
                            plano_id: planosSelecionados,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(tabelas){
                            //window.location.reload();
                            // if(res.success) {
                            //     document.getElementById('success-codigo').classList.remove('hidden');
                            //
                            // }
                            $("#lista-tabelas").empty();
                            $.each(tabelas, function(i, tabela){
                                $("#lista-tabelas").append(
                                    `<label class="flex items-center space-x-1 text-white text-sm">
                            <input type="checkbox" name="tabela_origem_id[]" value="${tabela.id}" class="rounded border-gray-300">
                            <span>${tabela.nome}</span>
                          </label>`
                                );
                            });
                            $('#bloco-tabelas').show();
                        }
                    });
                }
            });

            // Sempre que mudar administradora, limpa planos e tabelas
            $("input[name='administradora_id[]']").on('change', function(){
                $("#lista-planos").empty();
                $("#lista-tabelas").empty();
                $('#bloco-planos').hide();
                $('#bloco-tabelas').hide();
            });

            // Sempre que mudar planos, limpa tabelas
            $(document).on('change', ".plano-checkbox-js", function(){
                $("#lista-tabelas").empty();
                $('#bloco-tabelas').hide();
            });

            // Opcional: ao mudar assinatura, limpa tudo
            $("input[name='assinatura_id[]']").on('change', function(){
                $("input[name='administradora_id[]']").prop('checked', false);
                $("#lista-planos").empty(); $("#lista-tabelas").empty();
                $('#bloco-planos').hide(); $('#bloco-tabelas').hide();
            });









        </script>
    @endsection
</x-app-layout>
