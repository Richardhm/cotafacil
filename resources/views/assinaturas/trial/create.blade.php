<x-guest-layout>
    <!-- Session Status -->

    <div id="loading-cidades" style="display:none; position: absolute; left:0; right:0; margin:auto; top:0; bottom:0; z-index:9999; background:rgba(0,0,0,0.2); width:100%; height:100%; text-align:center;">
        <div style="position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);">
            <div class="jumping-dots-loader">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-center">
            {{ session('error') }}
        </div>
    @endif

    <input type="hidden" id="preco_final">

    <input type="hidden" id="txid">

    <x-auth-session-status class="mb-1" :status="session('status')" />
    <section class="md:w-[70%] rounded-lg mx-auto">
        <img src="{{ asset('logo_bm_1.png') }}" class="mx-auto my-1 w-32 md:w-32" alt="">

        <input type="hidden" id="nome" value="{{$nome}}">

        {{-- CPF: hidden se já cadastrado; campo aparece abaixo dos botões ao escolher PIX --}}
        @if($cpf)
            <input type="hidden" id="cpf" value="{{$cpf}}">
        @endif

            <div class="w-full">
                {{-- CUPOM PROMOCIONAL --}}
                <div class="mb-2">
                    <label class="text-white">

                        <input type="checkbox" id="cupom_promocional" name="usar_cupom">
                        Possui cupom promocional?
                    </label>
                </div>

                <div id="cardCupom" class="mb-2" style="display:none;">
                    <label for="codigo_cupom" class="block mb-1 font-medium text-white text-sm">Código do Cupom</label>
                    <div class="flex gap-2">
                        <input type="text" id="codigo_cupom" name="codigo_cupom" class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg" autocomplete="off">
                        <button type="button" id="aplicarCupom" class="bg-blue-600 text-white px-4 py-1 rounded">Aplicar</button>
                    </div>
                    <div id="cupomMensagem" class="text-sm text-red-600"></div>
                </div>

                {{-- RESUMO DA COMPRA --}}
                <div id="resumoCompra" class="bg-gray-100 rounded-lg p-4 mb-4 text-black">
                    <h3 class="text-lg font-semibold mb-2">Resumo da compra</h3>
                    <ul>
                        <li>Plano: <span id="precoPlanoOriginal"></span> <span id="descontoPlano"></span></li>
                        <li><b>Total:</b> <span id="totalFinal"></span></li>
                    </ul>
                </div>

                <fieldset class="border border-gray-300 px-1 py-1 rounded-lg flex gap-4" id="opcoes_pagamento">
                    <legend class="text-lg font-semibold text-white">Forma de Pagamento</legend>
                    <button type="button" id="pixButton" class="flex items-center justify-center w-full bg-green-500 text-white px-1 py-2 rounded-xl text-sm shadow-lg" class="mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="#FFFFFF" width="28px" height="28px" viewBox="0 0 16 16"><path d="M11.917 11.71a2.046 2.046 0 0 1-1.454-.602l-2.1-2.1a.4.4 0 0 0-.551 0l-2.108 2.108a2.044 2.044 0 0 1-1.454.602h-.414l2.66 2.66c.83.83 2.177.83 3.007 0l2.667-2.668h-.253zM4.25 4.282c.55 0 1.066.214 1.454.602l2.108 2.108a.39.39 0 0 0 .552 0l2.1-2.1a2.044 2.044 0 0 1 1.453-.602h.253L9.503 1.623a2.127 2.127 0 0 0-3.007 0l-2.66 2.66h.414z"/><path d="m14.377 6.496-1.612-1.612a.307.307 0 0 1-.114.023h-.733c-.379 0-.75.154-1.017.422l-2.1 2.1a1.005 1.005 0 0 1-1.425 0L5.268 5.32a1.448 1.448 0 0 0-1.018-.422h-.9a.306.306 0 0 1-.109-.021L1.623 6.496c-.83.83-.83 2.177 0 3.008l1.618 1.618a.305.305 0 0 1 .108-.022h.901c.38 0 .75-.153 1.018-.421L7.375 8.57a1.034 1.034 0 0 1 1.426 0l2.1 2.1c.267.268.638.421 1.017.421h.733c.04 0 .079.01.114.024l1.612-1.612c.83-.83.83-2.178 0-3.008z"/></svg>
                        PIX
                    </button>
                    <button type="button" id="pixAutomaticoButton" style="display:none;" class="flex items-center justify-center w-full bg-purple-600 text-white px-1 py-2 rounded-xl text-sm shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="#FFFFFF" width="28px" height="28px" viewBox="0 0 16 16"><path d="M11.917 11.71a2.046 2.046 0 0 1-1.454-.602l-2.1-2.1a.4.4 0 0 0-.551 0l-2.108 2.108a2.044 2.044 0 0 1-1.454.602h-.414l2.66 2.66c.83.83 2.177.83 3.007 0l2.667-2.668h-.253zM4.25 4.282c.55 0 1.066.214 1.454.602l2.108 2.108a.39.39 0 0 0 .552 0l2.1-2.1a2.044 2.044 0 0 1 1.453-.602h.253L9.503 1.623a2.127 2.127 0 0 0-3.007 0l-2.66 2.66h.414z"/><path d="m14.377 6.496-1.612-1.612a.307.307 0 0 1-.114.023h-.733c-.379 0-.75.154-1.017.422l-2.1 2.1a1.005 1.005 0 0 1-1.425 0L5.268 5.32a1.448 1.448 0 0 0-1.018-.422h-.9a.306.306 0 0 1-.109-.021L1.623 6.496c-.83.83-.83 2.177 0 3.008l1.618 1.618a.305.305 0 0 1 .108-.022h.901c.38 0 .75-.153 1.018-.421L7.375 8.57a1.034 1.034 0 0 1 1.426 0l2.1 2.1c.267.268.638.421 1.017.421h.733c.04 0 .079.01.114.024l1.612-1.612c.83-.83.83-2.178 0-3.008z"/></svg>
                        PIX Automático
                    </button>
                    <button type="button" id="creditCardButton" class="flex items-center justify-center w-full bg-blue-500 text-white px-1 py-2 rounded-xl text-sm shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="28px" height="28px" class="mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>
                        Cartão de Crédito
                    </button>
                </fieldset>

                {{-- Seção PIX normal: CPF + botão Gerar QR Code --}}
                <div id="pix_acao" class="mt-3" style="display:none;">
                    @if(!$cpf)
                    <div class="mb-2">
                        <label for="cpf" class="block mb-1 font-medium text-white text-sm">CPF <small>(obrigatório para PIX)</small></label>
                        <input type="text" id="cpf" class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg" placeholder="XXX.XXX.XXX-XX" />
                        <span id="cpf_erro" class="text-red-400 text-xs" style="display:none;">CPF inválido. Verifique e tente novamente.</span>
                    </div>
                    @endif
                    <button type="button" id="gerarQrcodeButton" class="flex items-center justify-center gap-2 w-full bg-green-600 hover:bg-green-700 text-white px-3 py-2.5 rounded-xl text-sm font-semibold shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="20px" height="20px"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75V16.5ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75V13.5ZM13.5 19.5h.75v.75h-.75V19.5ZM19.5 13.5h.75v.75h-.75V13.5ZM19.5 19.5h.75v.75h-.75V19.5ZM16.5 16.5h.75v.75h-.75V16.5Z" /></svg>
                        Gerar QR Code PIX
                    </button>
                </div>

                {{-- Seção PIX Automático: CPF + botão Gerar QR Code --}}
                <div id="pix_automatico_acao" class="mt-3" style="display:none;">
                    @if(!$cpf)
                    <div class="mb-2">
                        <label for="cpf_automatico" class="block mb-1 font-medium text-white text-sm">CPF <small>(obrigatório para PIX Automático)</small></label>
                        <input type="text" id="cpf_automatico" class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg" placeholder="XXX.XXX.XXX-XX" />
                        <span id="cpf_automatico_erro" class="text-red-400 text-xs" style="display:none;">CPF inválido. Verifique e tente novamente.</span>
                    </div>
                    @endif
                    <button type="button" id="gerarQrcodeAutomaticoButton" class="flex items-center justify-center gap-2 w-full bg-purple-600 hover:bg-purple-700 text-white px-3 py-2.5 rounded-xl text-sm font-semibold shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="#FFFFFF" width="20px" height="20px" viewBox="0 0 16 16"><path d="M11.917 11.71a2.046 2.046 0 0 1-1.454-.602l-2.1-2.1a.4.4 0 0 0-.551 0l-2.108 2.108a2.044 2.044 0 0 1-1.454.602h-.414l2.66 2.66c.83.83 2.177.83 3.007 0l2.667-2.668h-.253zM4.25 4.282c.55 0 1.066.214 1.454.602l2.108 2.108a.39.39 0 0 0 .552 0l2.1-2.1a2.044 2.044 0 0 1 1.453-.602h.253L9.503 1.623a2.127 2.127 0 0 0-3.007 0l-2.66 2.66h.414z"/><path d="m14.377 6.496-1.612-1.612a.307.307 0 0 1-.114.023h-.733c-.379 0-.75.154-1.017.422l-2.1 2.1a1.005 1.005 0 0 1-1.425 0L5.268 5.32a1.448 1.448 0 0 0-1.018-.422h-.9a.306.306 0 0 1-.109-.021L1.623 6.496c-.83.83-.83 2.177 0 3.008l1.618 1.618a.305.305 0 0 1 .108-.022h.901c.38 0 .75-.153 1.018-.421L7.375 8.57a1.034 1.034 0 0 1 1.426 0l2.1 2.1c.267.268.638.421 1.017.421h.733c.04 0 .079.01.114.024l1.612-1.612c.83-.83.83-2.178 0-3.008z"/></svg>
                        Gerar QR Code PIX Automático
                    </button>
                </div>

                {{-- PIX --}}
                <div id="pix_montar" class="mt-2" style="display: none;">
                    <h2 class="text-white text-center">Pague com o <strong>QR Code</strong> abaixo:</h2>
                    <img id="qrcode_img" class="mx-auto" />

                    <div class="flex gap-2 mt-3">
                        <input type="text" id="copiacola_input" class="border rounded p-2 flex-1 text-xs text-gray-700 bg-gray-50" readonly placeholder="Código PIX (copia e cola)" />
                        <button type="button" id="copyButton" class="bg-gray-700 hover:bg-gray-800 text-white text-xs px-3 py-1 rounded whitespace-nowrap">Copiar</button>
                    </div>


                    <div class="flex flex-col gap-1 p-2 border rounded-lg bg-gray-50 mt-3">
                        <h3 class="text-sm text-center font-semibold">Resumo da Compra</h3>

                        <div class="flex justify-between text-sm">
                            <span>Plano:</span>
                            <span>Mensal</span>
                        </div>


                        <div class="flex justify-between text-sm">
                            <span>Método de Pagamento:</span>
                            <span>PIX</span>
                        </div>


                        <div class="flex justify-between font-bold text-blue-700 border-t text-sm">
                            <span>Total:</span>
                            <span id="resumo_total"></span>
                        </div>

                        <div class="flex items-center justify-center mt-4">
                            <span>Aguardando pagamento:</span>
                            <div class="loading-dots ml-2">
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>

                            <div id="resultado_pix text-black" style="display: none;">
                                <span class="text-green-900">PAGO</span>
                            </div>
                        </div>



                    </div>
                </div>
                {{-- FIM PIX --}}

                {{-- DADOS DO CARTÃO --}}
                <div id="container_cartao" style="display: none;">
                    <form method="POST" name="cadastrar_individual" class="p-1 flex flex-wrap gap-4">
                        @csrf

                    <fieldset id="cardDados" class="border border-gray-300 p-1 rounded-lg mb-4 w-full">
                        <legend class="text-lg font-semibold text-white">Dados Cartão</legend>
                        <div class="mb-2">
                            <label for="numero_cartao" class="block mb-1 font-medium text-white text-sm">Número do Cartão</label>
                            <input type="text" name="numero_cartao" required id="numero_cartao" placeholder="XXXX XXXX XXXX XXXX" class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 focus:border-transparent focus:ring-0 focus:outline-none rounded-lg">
                        </div>
                        <div class="mb-2">
                            <label for="nome_titular" class="block mb-1 font-medium text-white text-sm">Nome do Titular</label>
                            <input type="text" name="nome_titular" required id="nome_titular" placeholder="Nome do Titular do Cartão" class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 focus:border-transparent focus:ring-0 focus:outline-none rounded-lg">
                        </div>
{{--                        <div class="mb-2">--}}
{{--                            <label for="data_nascimento" class="block mb-1 font-medium text-white text-sm">Data Nascimento</label>--}}
{{--                            <input type="date" name="data_nascimento" required id="data_nascimento" placeholder="data_nascimento" class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 focus:border-transparent focus:ring-0 focus:outline-none rounded-lg">--}}
{{--                        </div>--}}
                        <div class="flex justify-between gap-2">
                            <div class="w-1/3">
                                <label for="mes" class="block mb-1 font-medium text-white text-sm">Mês</label>
                                <select name="mes" id="mes" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5">
                                    <option value="">MM</option>
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ sprintf('%02d', $i) }}">{{ sprintf('%02d', $i) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="w-1/3">
                                <label for="ano" class="block mb-1 font-medium text-white text-sm">Ano</label>
                                <select name="ano" id="ano" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5">
                                    <option value="">ANO</option>
                                    @for ($i = now()->year; $i <= now()->year + 12; $i++)
                                        <option value="{{ substr($i, -2) }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="w-1/3">
                                <label for="cvv" class="block mb-1 font-medium text-white text-sm">CVV</label>
                                <input type="text" name="cvv" required id="cvv" placeholder="XXX" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5">
                            </div>
                        </div>
                    </fieldset>

{{--                        <fieldset id="cardFields" class="border border-gray-300 p-1 rounded-lg w-full">--}}
{{--                            <legend class="text-lg font-semibold text-white">Endereço</legend>--}}

{{--                            <div class="flex flex-col md:flex-row gap-4">--}}
{{--                                <div class="w-full md:w-1/3">--}}
{{--                                    <label for="zipcode" class="block mb-1 font-medium text-white text-sm">CEP</label>--}}
{{--                                    <input type="text" name="zipcode" id="zipcode" placeholder="XXXXX-XXX"--}}
{{--                                           class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg" required>--}}
{{--                                </div>--}}

{{--                                <div class="w-full md:w-1/2">--}}
{{--                                    <label for="street" class="block mb-1 font-medium text-white text-sm">Rua</label>--}}
{{--                                    <input type="text" name="street" id="street" placeholder="Rua"--}}
{{--                                           class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg" required>--}}
{{--                                </div>--}}
{{--                                <div class="w-full md:w-1/4">--}}
{{--                                    <label for="number" class="block mb-1 font-medium text-white text-sm">Nº <small>(Opcional)</small></label>--}}
{{--                                    <input type="text" name="number" id="number" placeholder="Nº"--}}
{{--                                           class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg">--}}
{{--                                </div>--}}
{{--                            </div>--}}

{{--                            <div class="flex flex-col md:flex-row gap-4">--}}
{{--                                <div class="w-full md:w-1/3">--}}
{{--                                    <label for="city" class="block mb-1 font-medium text-white text-sm">Cidade</label>--}}
{{--                                    <input type="text" name="city" id="city" placeholder="Cidade"--}}
{{--                                           class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg" required>--}}
{{--                                </div>--}}
{{--                                <div class="w-full md:w-1/2">--}}
{{--                                    <label for="neighborhood" class="block mb-1 font-medium text-white text-sm">Bairro</label>--}}
{{--                                    <input type="text" name="neighborhood" id="neighborhood" placeholder="Bairro"--}}
{{--                                           class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg" required>--}}
{{--                                </div>--}}
{{--                                <div class="w-full md:w-1/4">--}}
{{--                                    <label for="state" class="block mb-1 font-medium text-white text-sm">Estado</label>--}}
{{--                                    <input type="text" name="state" id="state" placeholder="UF"--}}
{{--                                           class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg" required>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </fieldset>--}}


                    <input type="hidden" name="bandeira" id="bandeira">

                        <div class="w-full">
                            <button type="submit" id="buttonSubmit" class="hidden text-white bg-gradient-to-r from-cyan-500 to-cyan-600 hover:bg-gradient-to-br dark:focus:ring-cyan-800 font-medium px-5 py-2 text-center me-2 mb-1 w-full rounded-lg">Salvar</button>
                        </div>

                    </form>



                </div>
                {{-- FIM DADOS DO CARTÃO --}}



                {{-- BOTÃO SUBMIT --}}

            </div>

    </section>



    @section('scripts')
        <script>
            var precoPlano = {{$preco_total}};

            function validarCPF(cpf) {
                cpf = cpf.replace(/[^\d]/g, '');
                if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;
                let sum = 0;
                for (let i = 0; i < 9; i++) sum += parseInt(cpf[i]) * (10 - i);
                let rev = 11 - (sum % 11); if (rev >= 10) rev = 0;
                if (rev !== parseInt(cpf[9])) return false;
                sum = 0;
                for (let i = 0; i < 10; i++) sum += parseInt(cpf[i]) * (11 - i);
                rev = 11 - (sum % 11); if (rev >= 10) rev = 0;
                return rev === parseInt(cpf[10]);
            }

            $(function() {

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                let precoPorUsuario = 29.90;
                let descontoPlano = 0.0;
                let descontoUsuario = 0.0;
                let totalUsuarios = 1; // Atualize se tiver multiusuário


                $("#copyButton").on('click', function(e) {
                    e.preventDefault();
                    let copyText = $("#copiacola_input");
                    copyText.select();
                    navigator.clipboard.writeText(copyText.val()).then(function(){
                        toastr.success("Sucesso","Codigo copiado com sucesso");
                    }).catch(function(error){
                        console.error("Erro ao copiar texto: ", error);
                    });
                    return false;
                });



                function validarCPF(cpf) {
                    cpf = cpf.replace(/[^\d]/g, '');
                    if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;
                    let sum = 0;
                    for (let i = 0; i < 9; i++) sum += parseInt(cpf[i]) * (10 - i);
                    let rev = 11 - (sum % 11); if (rev >= 10) rev = 0;
                    if (rev !== parseInt(cpf[9])) return false;
                    sum = 0;
                    for (let i = 0; i < 10; i++) sum += parseInt(cpf[i]) * (11 - i);
                    rev = 11 - (sum % 11); if (rev >= 10) rev = 0;
                    return rev === parseInt(cpf[10]);
                }

                $("#pixButton").on('click', function () {
                    if ($("#container_cartao").is(":visible")) $("#container_cartao").hide();
                    if ($("#pix_montar").is(":visible")) $("#pix_montar").hide();
                    if ($("#buttonSubmit").is(":visible")) $("#buttonSubmit").addClass('hidden');
                    $("#pix_acao").fadeIn(200);
                });

                $("#gerarQrcodeButton").on('click', function () {
                    let cpf  = $("#cpf").val();
                    let nome = $("#nome").val();

                    // Valida CPF apenas se o campo for visível (usuário sem CPF salvo)
                    if ($("#cpf").is(":visible") || $("#cpf").attr("type") !== "hidden") {
                        if (!validarCPF(cpf)) {
                            $("#cpf_erro").show();
                            $("#cpf").addClass("border-red-500");
                            return;
                        }
                        $("#cpf_erro").hide();
                        $("#cpf").removeClass("border-red-500");
                    }

                    $('#loading-cidades').fadeIn(150);

                    $.ajax({
                        url: "{{route('assinatura.pix.trial')}}",
                        method: "POST",
                        data: { cpf, nome, precoPlano },
                        success: function (res) {
                            if (res && res.txid) {
                                $("#qrcode_img").attr("src", res.imagem);
                                $("#copiacola_input").val(res.copiacola.trim());
                                $("#txid").val(res.txid);
                                $("#pix_acao").hide();
                                $("#pix_montar").fadeIn();
                            } else {
                                toastr.error("Erro ao gerar o QR Code PIX. Tente novamente.", "Erro");
                            }
                        },
                        error: function () {
                            toastr.error("Erro ao gerar o QR Code PIX. Tente novamente.", "Erro");
                        },
                        complete: function () {
                            $('#loading-cidades').fadeOut(150);
                        }
                    });
                });


                $("#creditCardButton").on('click',function(){
                    if ($("#pix_montar").is(":visible")) $("#pix_montar").fadeOut();
                    if ($("#pix_acao").is(":visible")) $("#pix_acao").hide();
                    $("#container_cartao").fadeIn();

                    if ($("#buttonSubmit").hasClass("hidden")) {
                        $("#buttonSubmit").removeClass("hidden");
                    }
                });







                function atualizarResumo() {
                    let valorPlano = precoPlano - descontoPlano;
                    let valorUsuarios = (precoPorUsuario - descontoUsuario) * (totalUsuarios - 1);
                    valorUsuarios = Math.max(0, valorUsuarios);

                    let total = valorPlano + valorUsuarios;
                    $("#precoPlanoOriginal").text(`${precoPlano.toLocaleString('pt-BR', {style: 'currency',currency: 'BRL'})}`);
                    $("#preco_final").val(valorPlano.toFixed(2));
                    $("#descontoPlano").html(descontoPlano > 0 ? `<span class="text-red-400">(- R$ ${descontoPlano.toFixed(2)})</span>` : "");
                    $("#precoUsuarioOriginal").text(`R$ ${precoPorUsuario.toFixed(2)}`);
                    $("#totalFinal").html(`<span class="text-green-600">R$ ${total.toFixed(2)}</span>`);
                    $("#resumo_total").html(`<span class="text-green-600">R$ ${total.toFixed(2)}</span>`);
                }

                atualizarResumo();

                // Mostrar/esconder campo cupom
                $('#cupom_promocional').on('change', function() {
                    $('#cardCupom').toggle(this.checked);
                });

                // Aplicação do cupom
                $('#aplicarCupom').on('click', function() {
                    let codigoCupom = $('#codigo_cupom').val();
                    if (!codigoCupom) {
                        $('#cupomMensagem').text('Informe o código do cupom');
                        return;
                    }

                    $.post("{{ route('cupom.validar') }}", { codigo_cupom: codigoCupom, _token: '{{ csrf_token() }}' }, function(data) {
                        console.log(data);
                        if (data.success) {
                            descontoPlano = parseFloat(data.desconto_plano || 0);
                            descontoUsuario = parseFloat(data.desconto_extra || 0);
                            atualizarResumo();
                            $('#cupomMensagem').removeClass('text-red-600').addClass('text-green-600 bg-white w-full p-2 text-center mt-2').text(data.mensagem);
                        } else {
                            descontoPlano = 0;
                            descontoUsuario = 0;
                            atualizarResumo();
                            $('#cupomMensagem').removeClass('text-green-600').addClass('text-red-600').text(data.mensagem);
                        }
                    });
                });
            });

            $gn.ready(function(checkout){

                $("form[name='cadastrar_individual']").on('submit',function(e){
                    e.preventDefault();

                    let load = $(".ajax_load");
                    load.fadeIn(100).css("display", "flex");


                    let numero_cartao = $("#numero_cartao").val();

                    let cupom_promocional = $('#cupom_promocional').is(':checked') == true ? 1 : 0;


                    let bandeira_validar = getBandeira(numero_cartao.replace(/\s/g, "").substring(0,6));
                    if (!bandeira_validar) {
                        toastr.error("O número do cartão informado não corresponde a nenhuma bandeira válida.", "Erro");
                        return false;
                    }
                    if (!validarCartaoCredito(numero_cartao)) {
                        toastr.error("O número do cartão de crédito é inválido. Verifique e tente novamente.", "Erro");
                        return false;
                    }

                    let mes = $("#mes").val();
                    let ano = $("#ano").val();
                    let cvv = $("#cvv").val();
                    let bandeira = $("#bandeira").val();
                    let precoPlano = $("#preco_final").val();

                    let paymentToken = "";
                    let mascaraCartao = "";

                    checkout.getPaymentToken(
                        {
                            brand: bandeira,
                            number: numero_cartao,
                            cvv: cvv,
                            expiration_month: mes,
                            expiration_year: ano
                        },
                        function(error,response) {

                            if(error) {
                                load.fadeOut(100).css("display", "none");
                            } else {
                                paymentToken = response.data.payment_token;
                                mascaraCartao = response.data.card_mask;

                                let csrf = $('meta[name="csrf-token"]').attr('content');
                                $("input[name='_token']").val(csrf);

                                let formData = new FormData($("form[name='cadastrar_individual']")[0]);
                                formData.append("paymentToken", paymentToken);
                                formData.append("mascaraCartao", mascaraCartao);
                                formData.append("cupom_promocional",cupom_promocional);
                                formData.append("preco_base",precoPlano);

                                $.ajax({
                                    url:"{{ route('assinaturas.trial.store') }}",
                                    method:"POST",
                                    data: formData,
                                    contentType: false,
                                    processData: false,
                                    headers: {
                                        'X-CSRF-TOKEN': csrf
                                    },
                                    beforeSend: function () {
                                        //load.fadeIn(100).css("display", "flex");
                                    },
                                    success:function(res) {


                                        load.fadeOut(100).css("display", "none");
                                        if (res.success && res.redirect) {
                                            window.location.href = res.redirect;
                                        }

                                    },
                                    error: function (xhr) {
                                        load.fadeOut(100).css("display", "none");
                                        if (xhr.status === 422) {
                                            load.fadeOut(100).css("display", "none");
                                            let errors = xhr.responseJSON.errors;
                                            $.each(errors, function (key, value) {
                                                toastr.error(value[0], "Erro");
                                            });
                                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                            toastr.error(xhr.responseJSON.message, "Erro");
                                        } else {
                                            toastr.error("Ocorreu um erro inesperado. Tente novamente.", "Erro");
                                        }
                                    }
                                });

                            }
                        }
                    );













                    // let nome_titular = $("#nome_titular").val();
                    // let mes = $("#mes").val();
                    // let ano = $("#ano").val();
                    // let cvv = $("#cvv").val();
                    // let bandeira = $("#bandeira").val();
                    //
                    //
                    // let nome = $("#name").val();
                    // let email_usuario = $("#email").val();
                    // let cpf = $("#cpf").val();
                    // let telefone = $("#phone").val();
                    // let password = $("#password").val();
                    // let password_confirmation = $("#password_confirmation").val();
                    // let birth_date = $("#birth_date").val();
                    // let zipcode = $("#zipcode").val();
                    // let street = $("#street").val();
                    // let number = $("#number").val();
                    // let city = $("#city").val();
                    // let neighborhood = $("#neighborhood").val();
                    // let state = $("#state").val();




                    return false;
                });







                $('#cardCupom').hide();
                // Coloca um listener no checkbox
                $('#cupom_promocional').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#cardCupom').show();
                    } else {
                        $('#cardCupom').hide();
                    }
                });
                $("#zipcode").change(function(){
                    let cep = $(this).val().replace("-","");
                    const url = `https://viacep.com.br/ws/${cep}/json`;
                    const options = {method: "GET",mode: "cors",
                        headers: {'content-type': 'application/json;charset=utf-8'}
                    }
                    fetch(url,options).then(response => response.json()).then(
                        data => {
                            console.log(data);
                            $("#street").val(data.logradouro);
                            $("#neighborhood").val(data.bairro);

                            $("#state").val(data.uf);
                            $("#city").val(data.localidade);
                        }
                    )
                    if($(this).val() != "") {
                        $(".errorcep").html('');
                    }
                });


                function verificarPagamento() {
                    let transactionId = $("#txid").val(); // substitua pelo ID real da transação

                    if(transactionId) {
                        let precoPlano = $("#preco_final").val();
                        let cpf = $("#cpf").val();
                        let formData = new FormData(); // Cria um novo FormData
                        formData.append("id", transactionId);
                        formData.append("precoFinal",precoPlano);
                        formData.append("cpf", cpf);
                        $.ajax({
                            url: `{{route('verificar.pagamento.pix')}}`,
                            method: 'POST',
                            data: formData,
                            processData: false, // Necessário para o envio de FormData
                            contentType: false, // Necessário para o envio de FormData
                            success: function(res) {
                                console.log(res);
                                if (res.success) { // Verifique conforme o status que a API retorna
                                    clearInterval(intervalo);
                                    window.location.href = res.redirect;
                                    // Atualize a interface ou redirecione o usuário
                                }
                            },
                            error: function() {
                                console.error("Erro ao verificar o pagamento");
                            }
                        });
                    }
                }



                let intervalo = setInterval(verificarPagamento, 3000);

                // PIX AUTOMÁTICO — renovação de assinatura existente
                let intervaloAutomatico = null;

                $("#pixAutomaticoButton").on('click', function () {
                    if ($("#container_cartao").is(":visible")) $("#container_cartao").hide();
                    if ($("#pix_montar").is(":visible")) $("#pix_montar").hide();
                    if ($("#pix_acao").is(":visible")) $("#pix_acao").hide();
                    if ($("#buttonSubmit").is(":visible")) $("#buttonSubmit").addClass('hidden');
                    $("#pix_automatico_acao").fadeIn(200);
                });

                $("#gerarQrcodeAutomaticoButton").on('click', function () {
                    let cpf  = @if($cpf) "{{ $cpf }}" @else $("#cpf_automatico").val() @endif;
                    let nome = $("#nome").val();

                    @if(!$cpf)
                    if (!validarCPF(cpf)) {
                        $("#cpf_automatico_erro").show();
                        $("#cpf_automatico").addClass("border-red-500");
                        return;
                    }
                    $("#cpf_automatico_erro").hide();
                    $("#cpf_automatico").removeClass("border-red-500");
                    @endif

                    $('#loading-cidades').fadeIn(150);
                    $.ajax({
                        url: "{{ route('assinatura.pix.automatico') }}",
                        method: "POST",
                        data: { cpf, nome, precoPlano },
                        success: function (res) {
                            if (res.txid) {
                                $("#qrcode_img").attr("src", res.imagem);
                                $("#copiacola_input").val(res.copiacola);
                                $("#txid").val(res.txid);
                                $("#pix_automatico_acao").hide();
                                $("#pix_montar").fadeIn();
                                intervaloAutomatico = setInterval(verificarPagamentoAutomatico, 5000);
                            } else {
                                toastr.error(res.error || "Erro ao gerar PIX Automático.", "Erro");
                            }
                        },
                        error: function (xhr) {
                            let msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : "Erro ao gerar PIX Automático.";
                            toastr.error(msg, "Erro");
                        },
                        complete: function () { $('#loading-cidades').fadeOut(150); }
                    });
                });

                function verificarPagamentoAutomatico() {
                    let txid = $("#txid").val();
                    if (!txid) return;

                    let formData = new FormData();
                    formData.append("id", txid);
                    formData.append("precoFinal", precoPlano);
                    formData.append("cpf", $("#cpf").val());

                    $.ajax({
                        url: "{{ route('verificar.pagamento.pix.automatico') }}",
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (res) {
                            if (res.success) {
                                clearInterval(intervaloAutomatico);
                                window.location.href = res.redirect;
                            }
                        }
                    });
                }





                function getBandeira(numero) {
                    let bins = {
                        visa: [/^4[0-9]{5}/],
                        mastercard: [/^5[1-5][0-9]{4}/, /^2[2-7][0-9]{4}/],
                        amex: [/^3[47][0-9]{3}/],
                        elo: [/^(636368|438935|504175|451416|509048|509067|509049|509069|509050|509074|509068|509040|509045|509051|509046|509066|509047|509042|509052|509043|509064|509040|36297[8-9]|5067[0-6][0-9]{2}|50677[0-8])/],
                        hipercard: [/^(606282|637095|637568|637599|637609|637612)/]
                    };

                    for (let bandeira in bins) {
                        for (let regex of bins[bandeira]) {
                            if (regex.test(numero)) {
                                return bandeira;
                            }
                        }
                    }
                    return null;
                }



                const zipCode = document.getElementById('zipcode');
                const cvvInput = document.getElementById('cvv');
                const cardNumberInput = document.getElementById('numero_cartao');


                //const zip = new Inputmask('99999-999');
                const cvv = new Inputmask('999');
                const cardMask = new Inputmask('9999 9999 9999 9999');


                //zip.mask(zipCode);
                cvv.mask(cvvInput);
                cardMask.mask(cardNumberInput);

                $("#numero_cartao").on("input", function() {
                    let numero = $(this).val().replace(/\D/g, ""); // Remove tudo que não for número
                    numero = numero.replace(/(.{4})/g, "$1 "); // Adiciona espaços a cada 4 dígitos
                    $("#cartao-numero").text(numero || "•••• •••• •••• ••••");
                    let bandeira = getBandeira(numero.replace(/\s/g, "").substring(0,6));
                    if(bandeira) {
                        $("#bandeira").val(bandeira);
                    }
                });

                // Atualiza o nome do titular
                $("#nome_titular").on("input", function() {
                    let nome = $(this).val().toUpperCase();
                    $("#cartao-nome").text(nome || "SEU NOME");
                });

                // Atualiza validade do cartão
                $("#mes, #ano").on("change", function() {
                    let mes = $("#mes").val() || "MM";
                    let ano = $("#ano").val() || "AA";
                    $("#cartao-validade").text(`${mes}/${ano}`);
                });

                // Atualiza CVV e vira o cartão
                $("#cvv").on("focus", function() {
                    $("#cartao").addClass("rotate-y-180");
                    $("#cartao-frente").addClass("opacity-0");
                    $("#cartao-verso").removeClass("opacity-0");
                });

                // Volta a mostrar a frente do cartão quando sai do CVV
                $("#cvv").on("blur", function() {
                    $("#cartao").removeClass("rotate-y-180");
                    $("#cartao-frente").removeClass("opacity-0");
                    $("#cartao-verso").addClass("opacity-0");
                });

                $("#cvv").on("input", function() {
                    let cvv = $(this).val().replace(/\D/g, "").substr(0, 4);
                    $("#cartao-cvv").text(cvv || "•••");
                });

                function validarCartaoCredito(numeroCartao) {
                    let numero = numeroCartao.replace(/\D/g, ""); // Remove tudo que não for número
                    let soma = 0;
                    let alternar = false;

                    for (let i = numero.length - 1; i >= 0; i--) {
                        let n = parseInt(numero.charAt(i), 10);

                        if (alternar) {
                            n *= 2;
                            if (n > 9) {
                                n -= 9;
                            }
                        }

                        soma += n;
                        alternar = !alternar;
                    }

                    return (soma % 10) === 0; // Se o resultado for divisível por 10, o cartão é válido
                }






            });
        </script>

    @endsection


</x-guest-layout>
