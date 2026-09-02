<x-app-layout>
    @if(session('success'))
        <div
            id="alert-success"
            class="flex items-center justify-between bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded relative mb-4"
            role="alert"
        >
        <span class="block font-bold mr-6">
            {{ session('success') }}
        </span>
            <button
                type="button"
                class="text-green-500 hover:text-green-700 text-xl font-bold focus:outline-none"
                onclick="document.getElementById('alert-success').style.display='none'"
            >
                &times;
            </button>
        </div>
    @endif




    <div id="loading-cidades" style="display:none; position: absolute; left:0; right:0; margin:auto; top:0; bottom:0; z-index:9999; background:rgba(0,0,0,0.2); width:100%; height:100%; text-align:center;">
        <div style="position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);">
            <div class="jumping-dots-loader">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>

        </div>

    </div>


    @if(Auth::user()->isAdmin() && !Auth::user()->primeiro_acesso)
        <div class="bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] flex flex-col items-center justify-center text-center w-full text-white rounded py-2 mb-4 relative">
            <div class="mb-2">
                <strong>Bem-vindo!</strong> Para cadastrar usuários da sua equipe, acesse o link:
                <a href="{{ route('gerenciamento.index') }}" style="border: 2px solid #ffcc00;border-radius: 5px;"
                   class="inline-flex ml-2 p-1 items-center gap-1 underline text-white font-bold">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </a>
                , ou clique acima
            </div>

            <!-- Nova linha de texto -->
            <p class="text-sm text-yellow-200 mt-1 px-4">
                Caso deseje, entre em contato com o suporte para adicionarmos sua logo à cotação.
            </p>


        </div>
    @endif

        <div class="whatsapp-container">
            <span class="suporte-texto">Suporte</span>
            <a href="https://wa.me/5562982686918" target="_blank" class="whatsapp-button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40">
                    <path fill="white" d="M19.077 4.928C17.191 3.041 14.683 2.001 12.011 2c-5.506 0-9.987 4.479-9.989 9.985-.001 1.76.459 3.478 1.333 4.992L2 22l5.233-1.237c1.37.751 2.945 1.148 4.565 1.148h.004c5.505 0 9.986-4.48 9.989-9.985.001-2.677-1.057-5.215-2.964-7.062zm-7.066 14.31h-.004c-1.37 0-2.697-.37-3.84-1.075l-.275-.163-2.865.758.764-2.853-.18-.278a8.3 8.3 0 0 1-1.274-4.383c.002-4.584 3.735-8.317 8.317-8.317 2.223 0 4.313.866 5.885 2.437a8.284 8.284 0 0 1 2.433 5.89c-.002 4.583-3.735 8.316-8.317 8.316zm4.538-6.213c-.246-.123-1.457-.719-1.684-.8-.226-.084-.392-.123-.557.123-.166.247-.643.8-.788.968-.145.164-.29.185-.538.062-.247-.123-1.043-.384-1.987-1.226-.734-.654-1.23-1.462-1.374-1.71-.144-.247-.015-.381.108-.504.112-.112.247-.289.37-.434.124-.145.165-.247.247-.412.082-.164.041-.309-.009-.434-.05-.123-.557-1.345-.763-1.84-.204-.495-.408-.428-.557-.433-.144-.005-.31-.005-.476-.005-.164 0-.431.061-.657.308-.226.246-.864.845-.864 2.058 0 1.213.883 2.387 1.006 2.55.123.164 1.746 2.666 4.236 3.727.59.255 1.05.409 1.407.522.59.185 1.126.159 1.551.097.473-.069 1.457-.594 1.662-1.168.204-.574.204-1.066.143-1.168-.062-.102-.226-.164-.472-.287z"/>
                </svg>
            </a>
        </div>


    <input type="hidden" id="odonto_resultado" />
    <div class="max-w-full mx-auto sm:px-6 lg:px-8 flex flex-col lg:flex-row gap-x-4 px-4">
        <x-informacoes :cidades="$cidades" :estados="$estados" :ufpreferencia="$uf_preferencia" class="sm:mx-5"></x-informacoes>
        <x-operadoras :operadoras="$administradoras" class="sm:mx-5"></x-operadoras>
        <x-planos :planos="$planos" class="sm:mx-5"></x-planos>
        <div class="p-1 rounded mt-2 hidden bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border w-full lg:w-[30%] sm:mx-5" id="resultado"></div>
    </div>

        <div id="modalPlanoAmbulatorial" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
            <div class="bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] px-6 py-10 rounded-lg shadow-lg w-96 text-white border-white border-4">
                <div class="flex justify-between">
                    <h2 class="text-lg font-bold mb-4 mx-auto">Escolha a Opção</h2>
                    <svg xmlns="http://www.w3.org/2000/svg" id="fecharModalAmbulatorial" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-6 border-white border-4 rounded hover:cursor-pointer">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </div>

                <fieldset class="border-4 border-gray-300 rounded-lg p-4 mt-4">
                    <legend class="text-lg font-semibold px-2 mx-auto">Coparticipação</legend>
                    <div class="space-y-2">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" id="comCoparticipacaoAmb" checked class="form-checkbox">
                            <span class="font-semibold">Com Coparticipação</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" id="semCoparticipacaoAmb" checked class="form-checkbox">
                            <span class="font-semibold">Sem Coparticipação</span>
                        </label>
                    </div>
                </fieldset>

                <fieldset class="border-4 border-gray-300 rounded-lg p-4 mt-4">
                    <legend class="text-lg font-semibold px-2 mx-auto">Tipo Documento</legend>
                    <div class="flex justify-between items-center">
                        <label class="flex items-center space-x-2">
                            <input type="radio" name="tipo_cotacao_ambulatorial" value="imagem" checked>
                            <span class="font-semibold">Imagem</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="radio" name="tipo_cotacao_ambulatorial" value="pdf">
                            <span class="font-semibold">PDF</span>
                        </label>
                    </div>
                </fieldset>


                <div class="flex justify-center mt-3">
                    <button id="gerarImagemAmbulatorial" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full w-full text-lg">Gerar</button>
                </div>
            </div>
        </div>











    <div id="modalPlano" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] px-6 py-10 rounded-lg shadow-lg w-96 text-white border-white border-4">
            <div class="flex justify-between">
                <h2 class="text-lg font-bold mb-4 mx-auto">Escolha a Opção</h2>
                <svg xmlns="http://www.w3.org/2000/svg" id="fecharModal" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-6 border-white border-4 rounded hover:cursor-pointer">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
              </div>

            <div class="space-y-2">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" id="comCoparticipacao" checked="checked" class="form-checkbox">
                    <span class="font-semibold">Com Coparticipação</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" id="semCoparticipacao" checked="checked" class="form-checkbox">
                    <span class="font-semibold">Sem Coparticipação</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" id="apenasValores" class="form-checkbox">
                    <span class="font-semibold">Apenas Valores</span>
                </label>
            </div>

            <fieldset class="border-4 border-gray-300 rounded-lg p-4 mt-4">
                <legend class="text-lg font-semibold px-2 mx-auto">Acomodação</legend>
                <div class="space-y-2">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" id="apartamento" checked class="form-checkbox">
                        <span class="font-semibold">Apartamento</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" id="enfermaria" checked class="form-checkbox">
                        <span class="font-semibold">Enfermaria</span>
                    </label>
                </div>
            </fieldset>

            <fieldset class="border-4 border-gray-300 rounded-lg p-4 mt-4">
                <legend class="text-lg font-semibold px-2 mx-auto">Tipo Documento</legend>
                <div class="flex justify-between items-center">
                    <label class="flex items-center space-x-2">
                        <input type="radio" name="tipo_cotacao" value="imagem" checked>
                        <span class="font-semibold">Imagem</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="radio" name="tipo_cotacao" value="pdf">
                        <span class="font-semibold">PDF</span>
                    </label>
                </div>
            </fieldset>


            <div class="flex justify-center mt-3">
                <button id="gerarImagem" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full w-full text-lg">Gerar</button>
            </div>
        </div>
    </div>

   @section('css')
       <style>
           @keyframes blink {
               0% { border-color: #ffcc00; }
               50% { border-color: transparent; }
               100% { border-color: #ffcc00; }
           }

           /* Aplicar borda piscante */
           .blink-border {
               border: 2px solid #ffcc00;
               padding: 4px 8px;
               border-radius: 5px;
               animation: blink 1s infinite;
           }

           @keyframes blink {
               0%, 100% { border-color: #facc15; } /* amarelo */
               50% { border-color: transparent; }
           }

           @keyframes pulseArrow {
               0%, 100% { transform: translateX(0); opacity: 1; }
               50% { transform: translateX(-5px); opacity: 0.6; }
           }

           .blink-border {
               border: 2px solid #facc15;
               padding: 5px;
               border-radius: 6px;
               animation: blink 1.2s infinite;
           }

           .animated-arrow {
               animation: pulseArrow 0.8s infinite;
           }

           .jumping-dots-loader {
               display: flex;
               align-items: center;
               justify-content: center;
               height: 40px;
               gap: 8px;
           }

           .jumping-dots-loader .dot {
               width: 15px;
               height: 15px;
               background: orange;
               border-radius: 50%;
               margin: 0 3px;
               animation: jumping 0.6s infinite alternate;
               display: inline-block;
           }

           .jumping-dots-loader .dot:nth-child(2) {
               animation-delay: 0.2s;
           }
           .jumping-dots-loader .dot:nth-child(3) {
               animation-delay: 0.4s;
           }

           @keyframes jumping {
               to {
                   transform: translateY(-16px);
               }
           }

       </style>
   @endsection

    @section('scripts')
       <script>
           $(document).ready(function(){
               // Guarda a cidade selecionada para saber se um change é troca ou primeira escolha.
               // Declarado aqui em cima porque mudarEstado() já é chamado logo abaixo.
               let cidadeAnterior = $('#cidade').val() || '';

               let ufPreferida = "{{ $uf_preferencia }}";

               if (ufPreferida) {
                   mudarEstado()
                   //$('#estado').val(ufPreferida).trigger('change'); // dispara o evento para carregar cidades
               }



               // $('#cidade').on('focus', function() {
               //     if($('#estado').val() === '' || $('#estado').val() == null) {
               //         //alert("OPSSSSS");
               //         toastr.error('Escolha o estado primeiro','error');
               //     }
               // });


               // Zera tudo que vem depois da cidade: vidas, operadoras, planos e resultado.
               // Usada tanto na troca de UF quanto na troca de cidade — trocar qualquer um
               // dos dois invalida os preços das etapas seguintes.
               function resetarEtapas() {
                   $('.faixa-etaria-input').val(0);
                   desmarcarPlanos();
                   verificarVisibilidade();
               }

               function mudarEstado() {
                   resetarEtapas();
                   cidadeAnterior = '';
                   let estado_id = $("#estado").val();
                   if(estado_id) {
                       $('#loading-cidades').fadeIn(150);
                       $.ajax({
                           url: '{{route('cidades.origem')}}', // endpoint para post
                           type: "POST",
                           dataType: "json",
                           data: {
                               uf: estado_id, // 'uf' ou 'id', conforme seu campo
                               _token: $('meta[name="csrf-token"]').attr('content') // CSRF Token Laravel
                           },
                           success:function(data) {
                               $('#cidade').empty();
                               $('#cidade').append('<option value="">Escolher Cidade</option>');
                               $.each(data, function(key, value) {
                                   $('#cidade').append('<option value="'+ value.id +'">' + value.nome + '</option>');
                               });
                           },
                           complete: function() {
                               // Esconde loader sempre ao finalizar
                               $('#loading-cidades').fadeOut(150);

                               verificarVisibilidade();

                               desmarcarPlanos()

                           }
                       });
                   } else {
                       $('#cidade').empty();
                       $('#cidade').append('<option value="">Escolher Cidade</option>');

                       verificarVisibilidade();

                       desmarcarPlanos();
                   }
               }

               function verificarVisibilidade() {
                   if ($('#operadoras').is(':visible')) {
                       $('#operadoras').addClass('hidden');
                   } else {
                       console.log('#operadoras não está visível');
                   }

                   if ($('#planos').is(':visible')) {
                       $("#planos").addClass('hidden');
                   } else {
                       console.log('#planos não está visível');
                   }

                   if ($('#resultado').is(':visible')) {
                       $("#resultado").addClass('hidden').empty();
                   } else {
                       console.log('#resultado não está visível');
                   }
               }

               function desmarcarPlanos() {
                   $("input[name='planos-radio']").prop('checked', false);
               }



               $('#estado').on('change',mudarEstado);

               function scrollToBottom() {
                   if (window.innerWidth <= 768) { // Aplica apenas para mobile
                       $('html, body').animate({
                           scrollTop: $(document).height() // Define o scroll para o final do documento
                       },1500,'swing'); // Tempo da animação (1 segundo)
                   }
               }

               // Exemplo de onde você pode chamar o scrollToBottom:
               $("input[name='operadoras']").on('change', function(){
                   // Lógica para mostrar operadoras
                   scrollToBottom(); // Chama o scroll para o bottom após a mudança de etapa
               });

               $("input[name='planos-radio']").on('click', function(){
                   // Lógica para selecionar um plano
                   scrollToBottom(); // Chama o scroll para o bottom após a seleção do plano
               });

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

               function handleOperadoraChange(e) {
                   e.preventDefault();

                   let valor = $(this).val(); // ID da administradora
                   let cidade = $("#cidade").val(); // ID da tabela de origem

                   if ($("#resultado").is(":visible")) {
                       $("input[name='planos-radio']").prop("checked", false);
                       $("#resultado").hide().empty();
                   }

                   $.ajax({
                       url: '{{route('buscar_planos')}}',
                       type: 'POST',
                       data: {
                           administradora_id: valor,
                           tabela_origens_id: cidade
                       },
                       headers: {
                           "X-Requested-With": "XMLHttpRequest",
                           "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                       },
                       success: function (response) {
                           // Limpa o contêiner dos planos
                           let planosContainer = $("#planos");
                           planosContainer.empty();

                           // Adiciona o botão "Planos" no topo
                           planosContainer.append(`
                <button id="show-planos-button" class="py-1.5 w-full px-1 me-2 mb-2 text-sm font-medium dark:text-white text-white bg-white rounded-lg border border-white dark:border-gray-200 bg-gray-500 bg-opacity-10">
                    Planos
                </button>
            `);

                           // Adiciona o fundo translúcido ao contêiner principal
                           planosContainer.addClass("bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] p-3 rounded-lg");

                           // 1. Renderiza os planos organizados por grupos
                           let grupos = response.planos_por_grupo;
                           $.each(grupos, function (grupoNome, planos) {
                               // Ignorar grupos vazios por segurança
                               if (!grupoNome) return;

                               // Criar o título do accordion
                               let accordionHeader = `
                    <button class="accordion-header flex justify-between items-center w-full p-2 text-sm rounded-lg font-bold bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] text-white transition focus:outline-none">
                        <span>${grupoNome}</span>
                        <svg class="accordion-icon w-4 h-4 transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>`;

                               // Criar o corpo do accordion com os planos
                               let accordionBody = `<div class="accordion-body hidden space-y-3 p-3 divide-y divide-transparent">`;

                               // Configurando os sub-itens/planos
                               $.each(planos, function (i, plano) {
                                   accordionBody += `
                                        <label class="flex items-center p-2 rounded-lg w-full" style="border:2px solid #3b82f6;">
                                            <input type="radio" name="planos-radio" value="${plano.id}" class="w-4 h-4 text-purple-600 border-gray-300">
                                            <span class="ml-2 text-white text-sm">${plano.nome}</span>
                                        </label>`;
                               });

                               accordionBody += `</div>`;

                               // Adicionar o bloco completo do Accordion (header + body)
                               planosContainer.append(`
                    <div class="border border-gray-300 rounded-lg shadow-sm mb-3">
                        ${accordionHeader}
                        ${accordionBody}
                    </div>`);
                           });

                           // 2. Renderiza planos sem grupo diretamente
                           let planosSemGrupo = response.planos_sem_grupo;
                           if (planosSemGrupo.length > 0) {


                               $.each(planosSemGrupo, function (i, plano) {
                                   planosContainer.append(`
                        <label class="flex items-center p-2 border border-gray-300 rounded-lg mb-2">
                            <input type="radio" name="planos-radio" value="${plano.id}" class="w-4 h-4 text-purple-600 border-gray-300">
                            <span class="ml-2 text-white text-sm">${plano.nome}</span>
                        </label>`);
                               });
                           }

                           // Inicializa interatividade dos Accordions
                           initAccordion();

                           // Opções Ambulatoriais: um radio por plano que tenha tabela
                           // ambulatorial nesta operadora+cidade (ex.: Porto Alegre tem
                           // Individual E Super Simples). Com um plano só, mantém o rótulo
                           // "Ambulatorial" de sempre; com mais de um, "Nome - Ambulatorial".
                           let planosAmb = response.planos_ambulatoriais || [];
                           $.each(planosAmb, function (i, plano) {
                               let rotuloAmb = planosAmb.length > 1 ? `${plano.nome} - Ambulatorial` : 'Ambulatorial';
                               planosContainer.append(`
                                   <label class="flex items-center p-2 rounded-lg w-full mb-2" style="border:2px solid white;">
                                       <input type="radio" name="planos-radio"
                                              value="${plano.id}"
                                              class="w-4 h-4 text-purple-600 border-gray-300"
                                              data-ambulatorial="1">
                                       <span class="ml-2 text-white text-sm">${rotuloAmb}</span>
                                   </label>`);
                           });

                           planosContainer.removeClass("hidden");
                       },
                       error: function () {
                           alert("Erro ao buscar os planos. Tente novamente.");
                       }
                   });

                   return false;
               }













               function handleOperadoraChangeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee(e) {
                   e.preventDefault();

                   let valor = $(this).val(); // ID da administradora
                   let cidade = $("#cidade").val(); // ID da tabela de origem

                   if ($("#resultado").is(":visible")) {
                       $("input[name='planos-radio']").prop("checked", false);
                       $("#resultado").hide().empty();
                   }

                   $.ajax({
                       url: '{{route('buscar_planos')}}',
                       type: 'POST',
                       data: {
                           administradora_id: valor,
                           tabela_origens_id: cidade
                       },
                       headers: {
                           "X-Requested-With": "XMLHttpRequest",
                           "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                       },
                       success: function (response) {
                           // Limpa o contêiner dos planos
                           let planosContainer = $("#planos");
                           planosContainer.empty();

                           // Adiciona o botão "Planos" no topo
                           planosContainer.append(`
                <button id="show-planos-button" class="py-1.5 w-full px-1 me-2 mb-2 text-sm font-medium dark:text-white text-white bg-white rounded-lg border border-white dark:border-gray-200 bg-gray-500 bg-opacity-10">
                    Planos
                </button>
            `);

                           // 1. Renderiza os planos organizados por grupos
                           let grupos = response.planos_por_grupo;
                           $.each(grupos, function (grupoNome, planos) {
                               // Ignorar grupos vazios por segurança
                               if (!grupoNome) return;

                               // Criar o título do accordion
                               let accordionHeader = `
                    <button class="accordion-header flex justify-between items-center w-full p-2 text-sm rounded-lg font-bold bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] text-white transition focus:outline-none">
                        <span>${grupoNome}</span>
                        <svg class="accordion-icon w-4 h-4 transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>`;

                               // Criar o corpo do accordion com os planos
                               let accordionBody = `<div class="accordion-body bg-white hidden divide-y divide-gray-200">`; // Sem fundo para o container principal

                               $.each(planos, function (i, plano) {
                                   accordionBody += `
                        <label class="flex items-center p-2 mb-2 rounded-lg bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px]">
                            <input type="radio" name="planos-radio" value="${plano.id}" class="w-4 h-4 text-purple-600 border-gray-300">
                            <span class="ml-2 text-white text-sm">${plano.nome}</span>
                        </label>`;
                               });

                               accordionBody += `</div>`;

                               // Adicionar o bloco completo do Accordion (header + body)
                               planosContainer.append(`
                    <div class="border border-gray-300 rounded-lg shadow-sm mb-3">
                        ${accordionHeader}
                        ${accordionBody}
                    </div>`);
                           });

                           // 2. Renderiza planos sem grupo diretamente
                           let planosSemGrupo = response.planos_sem_grupo;
                           if (planosSemGrupo.length > 0) {


                               $.each(planosSemGrupo, function (i, plano) {
                                   planosContainer.append(`
                        <label class="flex items-center p-2 bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] mb-2 rounded-lg">
                            <input type="radio" name="planos-radio" value="${plano.id}" class="w-4 h-4 text-purple-600 border-gray-300">
                            <span class="ml-2 text-white text-sm">${plano.nome}</span>
                        </label>`);
                               });
                           }

                           // Inicializa interatividade dos Accordions
                           initAccordion();

                           // Adiciona opção Ambulatorial se disponível para essa operadora+cidade
                           if (response.tem_ambulatorial && response.plano_ambulatorial_id) {
                               planosContainer.append(`
                                   <label class="flex items-center p-2 rounded-lg w-full" style="border:2px solid white;">
                                       <input type="radio" name="planos-radio"
                                              value="${response.plano_ambulatorial_id}"
                                              class="w-4 h-4 text-purple-600 border-gray-300"
                                              data-ambulatorial="1">
                                       <span class="ml-2 text-white text-sm">Ambulatorial</span>
                                   </label>`);
                           }

                           planosContainer.removeClass("hidden");
                       },
                       error: function () {
                           alert("Erro ao buscar os planos. Tente novamente.");
                       }
                   });

                   return false;
               }








               function handleOperadoraChangeTheLast(e) {
                   e.preventDefault();

                   let valor = $(this).val(); // ID da administradora
                   let cidade = $("#cidade").val(); // ID da tabela de origem

                   if ($("#resultado").is(":visible")) {
                       $("input[name='planos-radio']").prop("checked", false);
                       $("#resultado").hide().empty();
                   }

                   $.ajax({
                       url: '{{route('buscar_planos')}}',
                       type: 'POST',
                       data: {
                           administradora_id: valor,
                           tabela_origens_id: cidade
                       },
                       headers: {
                           "X-Requested-With": "XMLHttpRequest",
                           "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                       },
                       success: function (response) {
                           // Limpa o contêiner dos planos
                           let planosContainer = $("#planos");
                           planosContainer.empty();

                           // Adiciona o botão "Planos" no topo
                           planosContainer.append(`
                                <button id="show-planos-button" class="py-1.5 w-full px-1 me-2 mb-2 text-sm font-medium dark:text-white text-white bg-white rounded-lg border border-white dark:border-gray-200 bg-gray-500 bg-opacity-10">
                                    Planos
                                </button>
                           `);

                           // 1. Renderiza planos agrupados em Accordions
                           let grupos = response.planos_por_grupo;
                           $.each(grupos, function (grupoNome, planos) {
                               // Ignorar grupos vazios por segurança
                               if (!grupoNome) return;

                               // Criar o título do accordion
                               let accordionHeader = `
                    <button class="accordion-header flex justify-between items-center w-full p-2 text-sm rounded-lg font-bold bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] text-white transition focus:outline-none">
                        <span>${grupoNome}</span>
                        <svg class="accordion-icon w-4 h-4 transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>`;

                               // Criar o corpo do accordion com os planos
                               let accordionBody = `<div class="accordion-body hidden bg-gray-50 divide-y divide-gray-200">`;
                               $.each(planos, function (i, plano) {
                                   accordionBody += `
                                         <label class="flex items-center p-2 mb-2 rounded-lg bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px]">
                                            <input type="radio" name="planos-radio" value="${plano.id}" class="w-4 h-4 text-purple-600 border-gray-300">
                                            <span class="ml-2 text-white text-sm">${plano.nome}</span>
                                        </label>`;
                                   });
                                   accordionBody += `</div>`;

                               // Adicionar o bloco completo do Accordion (header + body)
                               planosContainer.append(`
                                    <div class="border border-gray-300 rounded-lg shadow-sm mb-3">
                                        ${accordionHeader}
                                        ${accordionBody}
                                    </div>`);
                               });

                                // 2. Renderiza planos sem grupo diretamente
                                let planosSemGrupo = response.planos_sem_grupo;
                                   if (planosSemGrupo.length > 0) {

                                       $.each(planosSemGrupo, function (i, plano) {
                                           planosContainer.append(`
                                <label class="flex items-center p-2 bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] mb-2 rounded-lg">
                                    <input type="radio" name="planos-radio" value="${plano.id}" class="w-4 h-4 text-purple-600 border-gray-300">
                                    <span class="ml-2 text-white text-sm">${plano.nome}</span>
                                </label>`);
                                       });
                           }

                           // Inicializa interatividade dos Accordions
                           initAccordion();
                           planosContainer.removeClass("hidden");
                       },
                       error: function () {
                           alert("Erro ao buscar os planos. Tente novamente.");
                       }
                   });

                   return false;
               }

// Iniciar Accordion
               function initAccordion() {
                   // Adicione o evento de clique para os Accordions
                   $(".accordion-header").off("click").on("click", function () {
                       let body = $(this).next(".accordion-body"); // Próximo corpo do Accordion
                       let icon = $(this).find(".accordion-icon"); // Ícone do Accordion

                       // Fechar todos os outros Accordions
                       $(".accordion-body").not(body).slideUp(); // Esconde outros corpos
                       $(".accordion-icon").not(icon).removeClass("rotate-180"); // Restaura o estado de outros ícones

                       // Alternar o estado do Accordion clicado
                       if (body.is(":visible")) {
                           body.slideUp(); // Esconde caso visível
                           icon.removeClass("rotate-180"); // Ícone volta ao estado inicial
                       } else {
                           body.slideDown(); // Exibe accordion clicado
                           icon.addClass("rotate-180"); // Ícone rotaciona
                       }
                   });
               }















               function handleOperadoraChangeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee(e) {
                   e.preventDefault();

                   let valor = $(this).val(); // ID da administradora
                   let cidade = $("#cidade").val(); // ID da tabela de origem

                   if ($("#resultado").is(":visible")) {
                       $("input[name='planos-radio']").prop("checked", false);
                       $("#resultado").hide().empty();
                   }

                   $.ajax({
                       url: '{{route('buscar_planos')}}',
                       type: 'POST',
                       data: {
                           administradora_id: valor,
                           tabela_origens_id: cidade
                       },
                       headers: {
                           "X-Requested-With": "XMLHttpRequest",
                           "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                       },
                       success: function (response) {
                           // Limpa o contêiner dos planos
                           let planosContainer = $("#planos");
                           planosContainer.empty();

                           // 1. Renderiza planos agrupados em Accordions
                           let grupos = response.planos_por_grupo;
                           $.each(grupos, function (grupoNome, planos) {
                               // Criar o título do accordion
                               let accordionHeader = `
                    <button class="accordion-header flex justify-between items-center w-full p-4 text-lg font-bold bg-white text-gray-800 hover:bg-gray-100 transition focus:outline-none">
                        <span>${grupoNome}</span>
                        <svg class="accordion-icon w-6 h-6 transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>`;

                               // Criar o corpo do accordion com os planos
                               let accordionBody = `<div class="accordion-body hidden bg-gray-50 divide-y divide-gray-200">`;
                               $.each(planos, function (i, plano) {
                                   accordionBody += `
                        <label class="flex items-center p-4">
                            <input type="radio" name="planos-radio" value="${plano.id}" class="w-4 h-4 text-purple-600 border-gray-300">
                            <span class="ml-2 text-gray-700">${plano.nome}</span>
                        </label>`;
                               });
                               accordionBody += `</div>`;

                               // Adicionar o bloco completo do Accordion (header + body)
                               planosContainer.append(`
                    <div class="border border-gray-300 rounded-lg shadow-sm mb-3">
                        ${accordionHeader}
                        ${accordionBody}
                    </div>`);
                           });

                           // 2. Renderiza planos sem grupo diretamente
                           let planosSemGrupo = response.planos_sem_grupo;
                           if (planosSemGrupo.length > 0) {
                               let semGrupoHeader = `<div class="text-lg font-bold text-gray-800 mt-4">Outros Planos</div>`;
                               let semGrupoBody = `<div class="bg-gray-50 divide-y divide-gray-200">`;

                               $.each(planosSemGrupo, function (i, plano) {
                                   semGrupoBody += `
                        <label class="flex items-center p-4">
                            <input type="radio" name="planos-radio" value="${plano.id}" class="w-4 h-4 text-purple-600 border-gray-300">
                            <span class="ml-2 text-gray-700">${plano.nome}</span>
                        </label>`;
                               });

                               semGrupoBody += `</div>`;

                               planosContainer.append(`${semGrupoHeader}${semGrupoBody}`);
                           }

                           // Inicializa interatividade dos Accordions
                           initAccordion();

                           // Adiciona opção Ambulatorial se disponível para essa operadora+cidade
                           if (response.tem_ambulatorial && response.plano_ambulatorial_id) {
                               planosContainer.append(`
                                   <label class="flex items-center p-2 rounded-lg w-full" style="border:2px solid white;">
                                       <input type="radio" name="planos-radio"
                                              value="${response.plano_ambulatorial_id}"
                                              class="w-4 h-4 text-purple-600 border-gray-300"
                                              data-ambulatorial="1">
                                       <span class="ml-2 text-white text-sm">Ambulatorial</span>
                                   </label>`);
                           }

                           planosContainer.removeClass("hidden");
                       },
                       error: function () {
                           alert("Erro ao buscar os planos. Tente novamente.");
                       }
                   });

                   return false;
               }








               function handleOperadoraChangejshfkjshdfkjshfjkshfjk(e) {
                   e.preventDefault();

                   let valor = $(this).val(); // Obtém o ID da administradora
                   let cidade = $("#cidade").val(); // Obtém o ID da tabela de origem

                   if ($("#resultado").is(":visible")) {
                       $("input[name='planos-radio']").prop("checked", false);
                       $("#resultado").hide().empty();
                   }

                   $.ajax({
                       url: '{{route('buscar_planos')}}', // URL para a requisição
                       type: 'POST',
                       data: {
                           administradora_id: valor,
                           tabela_origens_id: cidade
                       },
                       headers: {
                           "X-Requested-With": "XMLHttpRequest", // Requisição AJAX
                           "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") // CSRF Token
                       },
                       success: function (response) {
                           // Limpa os dados existentes no contêiner de planos
                           let planosContainer = $("#planos");
                           planosContainer.empty();

                           // Recupera os grupos e seus planos
                           let grupos = response.planos_por_grupo;

                           // Percorre cada grupo para criar os Accordions
                           $.each(grupos, function (grupoNome, planos) {
                               // Criar o título do accordion
                               let accordionHeader = `
                    <button class="accordion-header flex justify-between items-center w-full p-4 text-lg font-bold bg-white text-gray-800 hover:bg-gray-100 transition focus:outline-none">
                        <span>${grupoNome}</span>
                        <svg class="accordion-icon w-6 h-6 transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>`;

                               // Criar o corpo do accordion com os planos
                               let accordionBody = `<div class="accordion-body hidden bg-gray-50 divide-y divide-gray-200">`;
                               $.each(planos, function (i, plano) {
                                   accordionBody += `
                        <label class="flex items-center p-4">
                            <input type="radio" name="planos-radio" value="${plano.id}" class="w-4 h-4 text-purple-600 border-gray-300">
                            <span class="ml-2 text-gray-700">${plano.nome}</span>
                        </label>`;
                               });
                               accordionBody += `</div>`;

                               // Montar o bloco completo do accordion (header + body)
                               planosContainer.append(`
                    <div class="border border-gray-300 rounded-lg shadow-sm mb-3">
                        ${accordionHeader}
                        ${accordionBody}
                    </div>`);
                           });

                           // Torna os Accordions interativos
                           initAccordion();
                           planosContainer.removeClass("hidden");
                       },
                       error: function () {
                           alert("Erro ao buscar os planos. Tente novamente.");
                       }
                   });

                   return false;
               }

// Função para inicializar o comportamento dos Accordions









               function handleOperadoraChangeSemAccordion(e) {
                   e.preventDefault();
                   let valor = $(this).val();
                   let cidade = $("#cidade").val();




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
                           // Atualiza a lista de planos com os dados recebidos
                           $('#planos').removeClass('hidden').find('div[data-plano]').each(function() {
                               let planoId = $(this).data('plano');
                               if (response.planos.includes(planoId)) {
                                   $(this).show();  // Mostra o plano
                               } else {
                                   $(this).hide();  // Esconde o plano
                               }
                           });
                       },
                       error: function() {
                           alert('Erro ao buscar os planos. Tente novamente.');
                       }
                   });
                   return false;
               }




               $("body").on('change',"input[name='operadoras']",handleOperadoraChange);

                if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
                    $("body").on('touchstart', "input[name='operadoras']", function(e) {
                        // Força o foco e alteração imediata
                        $(this).prop('checked', true).trigger('change');
                        handleOperadoraChange.call(this, e);
                    });
                }



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



                   // Se ambas as condições forem verdadeiras, remova a classe 'hidden'
                   if (hasValue && cidadeSelected) {

                       if (!$('#operadoras').is(':visible')) {

                           $.ajax({
                               url: "{{route('filtrar.administradora')}}",
                               method: "POST",
                               data: {
                                   cidade: $('#cidade').val()
                               },
                               success: function(res) {
                                   let operadorasContainer = $('#operadoras');
                                   operadorasContainer.empty(); // Limpa o conteúdo existente

                                   let titulo = `
                    <button class="py-1.5 w-full px-1 me-2 mb-2 text-sm font-medium text-white bg-white rounded-lg border border-gray-200 bg-gray-500 bg-opacity-10">
                        Operadoras
                    </button>`;
                                   operadorasContainer.append(titulo);

                                   res.forEach(op => {
                                       let operadoraDiv = `
                        <div class="bg-white w-full container_image_operadora flex flex-wrap justify-between py-1 px-1 me-2 mb-1 text-sm font-medium text-white focus:outline-none bg-white rounded-lg border border-gray-200 focus:z-10 bg-gray-500 bg-opacity-10 dark:hover:text-gray-900">
                            <label class="flex justify-between items-center w-full cursor-pointer">
                                <div class="flex w-[50%] items-center">
                                    <input type="radio"
                                           name="operadoras"
                                           id="operadoras_${op.id}"
                                           value="${op.id}"
                                           class="w-4 text-purple-600 bg-gray-100 border-gray-300 focus:ring-purple-500 dark:focus:ring-purple-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <span class="ml-1 text-sm">${op.nome}</span>
                                </div>
                                <div class="flex w-[50%] justify-end">
                                    <img src="${op.logo}" alt="${op.nome}" width="100" height="40" loading="eager" decoding="async" class="image_operadora rounded-md" style="width:100px;height:40px;object-fit:contain;border-radius:5px;padding:2px;background-color: white;">
                                </div>
                            </label>
                        </div>`;
                                       operadorasContainer.append(operadoraDiv);
                                   });

                                   operadorasContainer.removeClass('hidden');
                               }
                           });

                           // Torna visível após carregamento
                           $('#operadoras').removeClass('hidden');
                       }

                       $('#operadoras').removeClass('hidden');
                   } else {
                       $('#operadoras').addClass('hidden');
                   }

                   if($("#planos").is(":visible") && $("#operadoras").is(":visible") && $("#resultado").is(":visible")) {
                       atualizarResultado();
                   }
               }

               $('input[type="text"]').on('input', checkFields);

               // Trocar de cidade zera as etapas seguintes, igual à troca de UF.
               // Só que apenas quando é troca mesmo: na primeira escolha não há nada
               // percorrido para desfazer, e resetar apagaria as vidas que a pessoa
               // já tivesse digitado antes de escolher a cidade.
               $('#cidade').on('change', function () {
                   const cidadeAtual = $(this).val() || '';
                   const trocou = cidadeAnterior !== '' && cidadeAnterior !== cidadeAtual;

                   cidadeAnterior = cidadeAtual;

                   if (trocou) {
                       resetarEtapas();
                   }

                   checkFields();
               });
               /*****************verificar se cidade e minus estão preenchidos para aparecer administradoras*******/

               /***********Incrementar valores aos input*****************************/
               $('.faixa-etaria-buttons').each(function() {
                   const $container = $(this);
                   const $input = $container.find('.faixa-etaria-input');
                   const $plusBtn = $container.find('.bg-green-400');
                   const $minusBtn = $container.find('.bg-red-400');

                   // Função para atualizar o estado dos botões
                   const updateButtonsState = () => {
                       const currentValue = parseInt($input.val()) || 0;
                       $minusBtn.prop('disabled', currentValue <= 0);
                   };

                   // Evento de incremento
                   $plusBtn.on('click', function(e) {
                       e.preventDefault();
                       const currentTotal = getTotal();
                       if (currentTotal < 7) {
                           $input.val((i, val) => {
                               const newVal = parseInt(val) + 1;
                               return isNaN(newVal) ? 1 : newVal;
                           }).trigger('input');
                           updateButtonsState();
                       } else {
                           toastr.error('O total máximo de 7 pessoas foi atingido!', 'Limite Atingido', {
                               timeOut: 3000,
                               progressBar: true,
                               closeButton: true
                           });
                       }
                   });

                   // Evento de decremento
                   $minusBtn.on('click', function(e) {
                       e.preventDefault();
                       $input.val((i, val) => {
                           const newVal = parseInt(val) - 1;
                           return newVal < 0 ? 0 : newVal;
                       }).trigger('input');
                       updateButtonsState();
                   });

                   // Validação direta no input
                   $input.on('input', function() {
                       let value = parseInt($(this).val()) || 0;
                       if (value < 0) value = 0;

                       // Verifica o total global
                       const currentTotal = getTotal();
                       if (currentTotal > 7) {
                           value -= (currentTotal - 7);
                           toastr.error(`Limitado a 7 pessoas.`, 'Limite Atingido', {
                               timeOut: 3000,
                               progressBar: true,
                               closeButton: true
                           });
                       }

                       $(this).val(value);
                       updateButtonsState();
                   });

                   updateButtonsState(); // Estado inicial
               });

               // Função para calcular o total
               function getTotal() {
                   return $('.faixa-etaria-input').get().reduce((total, input) => {
                       return total + (parseInt(input.value) || 0);
                   }, 0);
               }






               // let counterInput = $("input[type='text']");
               // let incrementButton = $("button:contains('+')");
               // let decrementButton = $("button:contains('-')");
               // incrementButton.click(function() {
               //     let inputField = $(this).siblings("input[type='text']");
               //     let currentValue = parseInt(inputField.val()) || 0;
               //     console.log(getTotal());
               //     if (getTotal() <= 7) {
               //         inputField.val(currentValue + 1);
               //         inputField.trigger('input'); // Dispara o evento 'input' no campo de texto
               //     }
               // });
               //
               // // Adiciona evento de clique para decremento
               // decrementButton.click(function() {
               //     let inputField = $(this).siblings("input[type='text']");
               //     let currentValue = parseInt(inputField.val()) || 0;
               //     if (currentValue > 0) {
               //         inputField.val(currentValue - 1);
               //         inputField.trigger('input'); // Dispara o evento 'input' no campo de texto
               //     }
               // });
               //
               //
               // function getTotal() {
               //     let total = 1;
               //     $("input[type='text']").each(function() {
               //         total += parseInt($(this).val()) || 0;
               //     });
               //     console.log("Total ",total);
               //     return total;
               // }
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
                       status_carencia = $("input[name='status_carencia']:checked").val();
                       status_carencia = status_carencia === 'true'
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
                           url: "{{ route('orcamento.montarOrcamento') }}",
                           method: "POST",
                           headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                           data: {
                               "tabela_origem": cidade,
                               "plano": plano,
                               "operadora": operadora,
                               "faixas": faixas,
                               "status_carencia":status_carencia,
                               "_token": "{{ csrf_token() }}",
                               "ambulatorial" : ambulatorial
                           },
                           success: function(res) {
                               $("#resultado").removeClass('hidden').slideDown('slow').html(res);
                               return false;
                           }
                       });
                   },0.1);
                   return false;
               }

               $("body").on('click',"input[name='planos-radio']",function(){
                   let ambulatorial = $(this).data('ambulatorial') ? 1 : 0;
                   atualizarResultado(ambulatorial);
               });

                $("#fecharModal").on("click", function () {
                    $("#modalPlano").addClass("hidden");
                });

               $("#modalPlano").on("click", function(event) {
                   // Verifica se clicou diretamente no overlay, e não no conteúdo interno
                   if (event.target === this) {
                       $(this).addClass("hidden");
                   }
               });



               $("#fecharModalAmbulatorial").on("click",function(){
                    $("#modalPlanoAmbulatorial").addClass("hidden");
                });

               $("#modalPlanoAmbulatorial").on("click", function(event) {
                   // Verifica se clicou diretamente no overlay, e não no conteúdo interno
                   if (event.target === this) {
                       $(this).addClass("hidden");
                   }
               });

               // Trava para não disparar duas gerações ao mesmo tempo: cada clique no Gerar
               // roda um ghostscript no servidor, e clicar várias vezes só piora a fila.
               let gerandoImagem = false;

               function avisarErroImagem(xhr) {
                   let mensagem = 'Não foi possível gerar a imagem. Tente novamente.';

                   if (xhr && xhr.status === 419) {
                       mensagem = 'Sua sessão expirou. Atualize a página e tente de novo.';
                   } else if (xhr && xhr.statusText === 'timeout') {
                       mensagem = 'A geração demorou demais e foi cancelada. Tente novamente.';
                   }

                   if (window.toastr) {
                       toastr.error(mensagem);
                   } else {
                       alert(mensagem);
                   }
               }

               $("body").on('click','#gerarImagemAmbulatorial',function(e){
                   e.preventDefault();

                   if (gerandoImagem) {
                       return false;
                   }

                   let botao = $(this);
                   let load = $(".ajax_load");
                   let linkUrl = $(this).attr("href");
                   let cidade = "";
                   let plano = "";
                   let operadora = "";
                   let faixas = [];
                   let odonto = "";
                   let status_carencia = $("input[name='status_carencia_ambulatorial']").is(':checked');
                   let status_desconto = $("input[name='status_desconto_ambulatorial']").is(':checked');
                   let tipo_documento = $("input[name='tipo_cotacao_ambulatorial']:checked").val();
                   let comCoparticipacaoAmb = $("#comCoparticipacaoAmb").is(":checked");
                   let semCoparticipacaoAmb = $("#semCoparticipacaoAmb").is(":checked");
                   cidade = $("#cidade").val();
                   plano = $("input[name='planos-radio']:checked").val();
                   operadora = $("input[name='operadoras']:checked").val();
                   // Exibe o valor booleano no console
                   odonto = $("#odonto_resultado").val();
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
                           "ambulatorial": 1,
                           "tipo_documento": tipo_documento,
                           "comcoparticipacao": comCoparticipacaoAmb,
                           "semcoparticipacao": semCoparticipacaoAmb
                           //"cliente" : cliente,
                           //"_token": "{{ csrf_token() }}"
                       },
                       xhrFields: {
                           responseType: 'blob'
                       },
                       timeout: 120000,
                       beforeSend: function () {
                           gerandoImagem = true;
                           botao.prop('disabled', true).addClass('opacity-60');
                           load.fadeIn(100).css("display", "flex");
                       },
                       error: function (xhr) {
                           avisarErroImagem(xhr);
                       },
                       // Sempre roda, dando certo ou errado: é o que garante que o spinner
                       // não fique girando para sempre quando a requisição falha.
                       complete: function () {
                           gerandoImagem = false;
                           botao.prop('disabled', false).removeClass('opacity-60');
                           load.fadeOut(100).css("display", "none");
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


                $("body").on('click','#gerarImagem',function(e){
                    e.preventDefault();

                    if (gerandoImagem) {
                        return false;
                    }

                    let botao = $(this);
                    let comCoparticipacaoMarcado = $("#comCoparticipacao").is(":checked");
                    let semCoparticipacaoMarcado = $("#semCoparticipacao").is(":checked");
                    let apenasValores = $("#apenasValores").is(":checked");
                    let mostrarApartamento = $("#apartamento").is(":checked");
                    let mostrarEnfermaria = $("#enfermaria").is(":checked");
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


                    let tipo_documento = $("input[name='tipo_cotacao']:checked").val();
                    odonto = $("#odonto_resultado").val();

                    // Exibe o valor booleano no console

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
                            "comcoparticipacao": comCoparticipacaoMarcado,
                            "semcoparticipacao": semCoparticipacaoMarcado,
                            "tabela_origem": cidade,
                            "plano": plano,
                            "operadora": operadora,
                            "faixas": faixas,
                            "odonto" : odonto,
                            "status_carencia" : status_carencia,
                            "status_desconto" : status_desconto,
                            "ambulatorial": 0,
                            "apenasvalores" : apenasValores,
                            "tipo_documento" : tipo_documento,
                            "mostrar_apartamento": mostrarApartamento,
                            "mostrar_enfermaria": mostrarEnfermaria
                        },
                        xhrFields: {
                            responseType: 'blob'
                        },
                        timeout: 120000,
                        beforeSend: function () {
                            gerandoImagem = true;
                            botao.prop('disabled', true).addClass('opacity-60');
                            load.fadeIn(100).css("display", "flex");
                        },
                        error: function (xhr) {
                            avisarErroImagem(xhr);
                        },
                        // Sempre roda, dando certo ou errado: é o que garante que o spinner
                        // não fique girando para sempre quando a requisição falha.
                        complete: function () {
                            gerandoImagem = false;
                            botao.prop('disabled', false).removeClass('opacity-60');
                            load.fadeOut(100).css("display", "none");
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







                $("body").on('click',".downloadLink",function(){
                    $("#modalPlano").removeClass("hidden"); // Exibe a modal
                    let odonto = $(this).attr('data-odonto');
                    $("#odonto_resultado").val(odonto);
                });

               $("body").on('click',".downloadLinkAmbulatorial",function(e){
                   $("#modalPlanoAmbulatorial").removeClass("hidden"); // Exibe a modal
                   let odonto = $(this).attr('data-odonto');
                   $("#odonto_resultado").val(odonto);
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
