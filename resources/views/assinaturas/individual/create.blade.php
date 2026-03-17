<x-guest-layout>

    <div id="loading-cidades" style="display:none; position: absolute; left:0; right:0; margin:auto; top:0; bottom:0; z-index:9999; background:rgba(0,0,0,0.2); width:100%; height:100%; text-align:center;">
        <div style="position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);">
            <div class="jumping-dots-loader">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
    </div>




    <!-- Session Status -->
    <x-auth-session-status class="mb-1" :status="session('status')" />
    <section class="w-full rounded-lg">

        <img src="{{asset('cotafacil_logo.png')}}" class="mx-auto my-2 h-20 p-1" alt="">
        <form method="POST" name="cadastrar_individual" class="p-1 flex flex-wrap gap-4" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <!--Lado Esquerdo-->
            <div class="w-full md:w-[48%]">
                <fieldset class="border border-gray-300 p-1 rounded-lg">
                    <legend class="text-lg font-semibold text-white">Dados Pessoais</legend>
                    <div class="mb-2">
                        <label for="name" class="block mb-1 font-medium text-white text-sm">Nome</label>
                        <input type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg" placeholder="Seu Nome" required />
                    </div>
                    <div class="mb-2">
                        <label for="email" class="block mb-1 font-medium text-white text-sm">Email</label>
                        <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg" placeholder="Seu Email" required />
                    </div>
                    <div class="flex flex-col md:flex-row gap-4 mb-3">
                        <div class="w-full md:w-1/2">
                            <label for="birth_date" class="block mb-1 font-medium text-white text-sm">Data de Nascimento</label>
                            <input type="date" name="birth_date" id="birth_date" class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg" required>
                        </div>
                        <div class="w-full md:w-1/2">
                            <label for="cpf" class="block mb-1 font-medium text-white text-sm">CPF</label>
                            <input type="text" name="cpf" id="cpf" class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg" placeholder="XXX.XXX.XXX-XX" required />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="block mb-1 font-medium text-white text-sm">Telefone</label>
                        <input type="text" name="phone" id="phone" class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg" placeholder="(XX) X XXXX-XXXX" required />
                    </div>
                    <div class="mb-2">
                        <label for="imagem" class="block mb-1 font-medium text-white text-sm">Imagem</label>
                        <input type="file" name="imagem" id="imagem" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 p-1.5" />
                    </div>
                </fieldset>

                <fieldset class="border border-gray-300 px-1 py-3 rounded-lg">
                    <legend class="text-lg font-semibold text-white">Senha</legend>
                    <div class="mb-5">
                        <label for="password" class="block mb-1 font-medium text-white text-sm">Senha <small>(Minimo 8 caracteres)</small></label>
                        <div class="relative">
                            <input type="password" name="password" id="password" class="bg-gray-50 border text-gray-950 border-gray-300 text-sm block w-full p-1.5 rounded-lg" placeholder="Sua Senha" required />
                            <button type="button" id="togglePassword" class="absolute right-2 top-2 cursor-pointer" style="color:black;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6" id="showIcon">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 hidden" id="hideIcon">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>

                    </div>
                    <div class="mb-5">
                        <label for="password_confirmation" class="block mb-1 font-medium text-white text-sm">Confirmar Senha <small>(Minimo 8 caracteres)</small></label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="bg-gray-50 border text-gray-950 border-gray-300 text-sm block w-full p-1.5 rounded-lg" placeholder="Confirmar Senha" required />
                            <button type="button" id="togglePasswordConfirmation" class="absolute right-2 top-2 cursor-pointer" style="color:black;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6" id="showIconConfirmation">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 hidden" id="hideIconConfirmation">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>

                    </div>
                </fieldset>





            </div>
            <!--Fim Lado Esquerdo-->

            <!--Lado Direito-->

            <div class="w-full md:w-[49%]">
                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="trial" id="trialCheckbox" class="form-checkbox text-cyan-500">
                        <span class="ml-2 text-white">Experimente por 7 dias grátis</span>
                    </label>
                </div>


                <fieldset class="border border-gray-300 px-1 py-1 rounded-lg flex gap-4" id="opcoes_pagamento">
                    <legend class="text-lg font-semibold text-white">Forma de Pagamento</legend>
                    <button type="button" id="pixButton" class="flex items-center justify-center w-full bg-green-500 text-white px-1 py-2 rounded-xl text-sm shadow-lg" class="mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="#FFFFFF" width="28px" height="28px" viewBox="0 0 16 16"><path d="M11.917 11.71a2.046 2.046 0 0 1-1.454-.602l-2.1-2.1a.4.4 0 0 0-.551 0l-2.108 2.108a2.044 2.044 0 0 1-1.454.602h-.414l2.66 2.66c.83.83 2.177.83 3.007 0l2.667-2.668h-.253zM4.25 4.282c.55 0 1.066.214 1.454.602l2.108 2.108a.39.39 0 0 0 .552 0l2.1-2.1a2.044 2.044 0 0 1 1.453-.602h.253L9.503 1.623a2.127 2.127 0 0 0-3.007 0l-2.66 2.66h.414z"/><path d="m14.377 6.496-1.612-1.612a.307.307 0 0 1-.114.023h-.733c-.379 0-.75.154-1.017.422l-2.1 2.1a1.005 1.005 0 0 1-1.425 0L5.268 5.32a1.448 1.448 0 0 0-1.018-.422h-.9a.306.306 0 0 1-.109-.021L1.623 6.496c-.83.83-.83 2.177 0 3.008l1.618 1.618a.305.305 0 0 1 .108-.022h.901c.38 0 .75-.153 1.018-.421L7.375 8.57a1.034 1.034 0 0 1 1.426 0l2.1 2.1c.267.268.638.421 1.017.421h.733c.04 0 .079.01.114.024l1.612-1.612c.83-.83.83-2.178 0-3.008z"/></svg>
                        PIX
                    </button>
                    <button type="button" id="creditCardButton" class="flex items-center justify-center w-full bg-blue-500 text-white px-1 py-2 rounded-xl text-sm shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="28px" height="28px" class="mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>
                        Cartão de Crédito
                    </button>
                </fieldset>
                {{-- PIX --}}
                <div id="pix_montar" class="mt-2" style="display: none;">
                    <img id="qrcode_img" class="mx-auto"  />

                    <div class="flex items-center my-2">
                        <input type="text" id="copiacola_input" class="border rounded p-2 flex-1" readonly />
                        <button id="copyButton" class="bg-blue-500 text-white p-2 ml-1 rounded">Pix Copia e Cola</button>
                    </div>

                    <div class="flex flex-col gap-1 p-2 border rounded-lg bg-gray-50">
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
                            <span>R$ 29,90</span>
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

                    {{-- Cartão de Credito--}}

                    <div id="container_cartao" style="display: none;">
                        <fieldset id="cardFields" class="border border-gray-300 p-1 rounded-lg">
                            <legend class="text-lg font-semibold text-white">Endereço</legend>

                            <div class="flex flex-col md:flex-row gap-4">
                                <div class="w-full md:w-1/3">
                                    <label for="zipcode" class="block mb-1 font-medium text-white text-sm">CEP</label>
                                    <input type="text" name="zipcode" id="zipcode" placeholder="XXXXX-XXX"
                                           class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg" required>
                                </div>

                                <div class="w-full md:w-1/2">
                                    <label for="street" class="block mb-1 font-medium text-white text-sm">Rua</label>
                                    <input type="text" name="street" id="street" placeholder="Rua"
                                           class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg" required>
                                </div>
                                <div class="w-full md:w-1/4">
                                    <label for="number" class="block mb-1 font-medium text-white text-sm">Nº <small>(Opcional)</small></label>
                                    <input type="text" name="number" id="number" placeholder="Nº"
                                           class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg">
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row gap-4">
                                <div class="w-full md:w-1/3">
                                    <label for="city" class="block mb-1 font-medium text-white text-sm">Cidade</label>
                                    <input type="text" name="city" id="city" placeholder="Cidade"
                                           class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg" required>
                                </div>
                                <div class="w-full md:w-1/2">
                                    <label for="neighborhood" class="block mb-1 font-medium text-white text-sm">Bairro</label>
                                    <input type="text" name="neighborhood" id="neighborhood" placeholder="Bairro"
                                           class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg" required>
                                </div>
                                <div class="w-full md:w-1/4">
                                    <label for="state" class="block mb-1 font-medium text-white text-sm">Estado</label>
                                    <input type="text" name="state" id="state" placeholder="UF"
                                           class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 rounded-lg" required>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset id="cardDados" class="border border-gray-300 p-1 rounded-lg">
                            <legend class="text-lg font-semibold text-white">Dados Cartão</legend>
                            <div class="mb-2">
                                <label for="numero_cartao" class="block mb-1 font-medium text-white text-sm">Número do Cartão</label>
                                <input type="text" name="numero_cartao" required id="numero_cartao" placeholder="XXXX XXXX XXXX XXXX"
                                       class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 focus:border-transparent focus:ring-0 focus:outline-none rounded-lg">
                            </div>

                            <div class="mb-2">
                                <label for="nome_titular" class="block mb-1 font-medium text-white text-sm">Nome do Titular</label>
                                <input type="text" name="nome_titular" required id="nome_titular" placeholder="Nome do Titular do Cartão"
                                       class="bg-gray-50 border border-gray-300 text-gray-950 text-sm block w-full p-1.5 focus:border-transparent focus:ring-0 focus:outline-none rounded-lg">
                            </div>

                            <div class="flex justify-between gap-2">
                                <div class="w-1/3">
                                    <label for="mes" class="block mb-1 font-medium text-white text-sm">Mês</label>
                                    <select name="mes" id="mes" required
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5">
                                        <option value="">MM</option>
                                        <option value="01">01</option>
                                        <option value="02">02</option>
                                        <option value="03">03</option>
                                        <option value="04">04</option>
                                        <option value="05">05</option>
                                        <option value="06">06</option>
                                        <option value="07">07</option>
                                        <option value="08">08</option>
                                        <option value="09">09</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                    </select>
                                </div>

                                <div class="w-1/3">
                                    <label for="ano" class="block mb-1 font-medium text-white text-sm">Ano</label>
                                    <select name="ano" id="ano" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5">
                                        <option value="">ANO</option>
                                        <option value="25">2025</option>
                                        <option value="26">2026</option>
                                        <option value="27">2027</option>
                                        <option value="28">2028</option>
                                        <option value="29">2029</option>
                                        <option value="30">2030</option>
                                        <option value="31">2031</option>
                                        <option value="32">2032</option>
                                        <option value="33">2033</option>
                                        <option value="34">2034</option>
                                        <option value="35">2035</option>
                                        <option value="36">2036</option>
                                        <option value="37">2037</option>
                                        <option value="38">2038</option>
                                        <option value="39">2039</option>
                                        <option value="40">2040</option>
                                        <option value="41">2041</option>
                                        <option value="42">2042</option>
                                        <option value="43">2043</option>
                                        <option value="44">2044</option>
                                        <option value="45">2045</option>
                                        <option value="46">2046</option>
                                        <option value="47">2047</option>
                                        <option value="48">2048</option>
                                        <option value="49">2049</option>
                                        <option value="50">2050</option>
                                    </select>
                                </div>

                                <div class="w-1/3">
                                    <label for="cvv" class="block mb-1 font-medium text-white text-sm">CVV</label>
                                    <input type="text" name="cvv" required id="cvv" placeholder="XXX"
                                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5">
                                </div>

                            </div>
                        </fieldset>
                        <input type="hidden" name="bandeira" id="bandeira">
                    </div>
                    {{-- Fim Cartão de Credito--}}
            </div>
            <input type="hidden" id="txid">
            <!--Fim Lado Direito-->
            <div class="w-full mx-auto my-1">
                <button type="submit" id="buttonSubmit" class="hidden text-white bg-gradient-to-r from-cyan-400 via-cyan-500 to-cyan-600 hover:bg-gradient-to-br dark:focus:ring-cyan-800 font-medium px-5 py-2 text-center me-2 mb-1 w-full rounded-lg">Cadastrar</button>
            </div>
        </form>
    </section>

    @section('scripts')
        <script>

            const passwordInput = document.getElementById('password');
            const passwordInputConfirmation = document.getElementById('password_confirmation');
            const toggleButton = document.getElementById('togglePassword');
            const toggleButtonConfirmation = document.getElementById('togglePasswordConfirmation');
            const showIcon = document.getElementById('showIcon');
            const showIconConfirmation = document.getElementById('showIconConfirmation');
            const hideIcon = document.getElementById('hideIcon');
            const hideIconConfirmation = document.getElementById('hideIconConfirmation');

            toggleButton.addEventListener('click', () => {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    showIcon.style.display = 'none';
                    hideIcon.style.display = 'block';
                } else {
                    passwordInput.type = 'password';
                    showIcon.style.display = 'block';
                    hideIcon.style.display = 'none';
                }
            });

            toggleButtonConfirmation.addEventListener('click', () => {
                if (passwordInputConfirmation.type === 'password') {
                    passwordInputConfirmation.type = 'text';
                    showIconConfirmation.style.display = 'none';
                    hideIconConfirmation.style.display = 'block';
                } else {
                    passwordInputConfirmation.type = 'password';
                    showIconConfirmation.style.display = 'block';
                    hideIconConfirmation.style.display = 'none';
                }
            });





            $gn.ready(function(checkout){

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                function TestaCPF(strCPF) {
                    var Soma;
                    var Resto;
                    Soma = 0;

                    if (strCPF == "00000000000") return false;
                    for (i=1; i<=9; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (11 - i);
                    Resto = (Soma * 10) % 11;
                    if ((Resto == 10) || (Resto == 11))  Resto = 0;
                    if (Resto != parseInt(strCPF.substring(9, 10)) ) return false;
                    Soma = 0;
                    for (i = 1; i <= 10; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (12 - i);
                    Resto = (Soma * 10) % 11;
                    if ((Resto == 10) || (Resto == 11))  Resto = 0;
                    if (Resto != parseInt(strCPF.substring(10, 11) ) ) return false;
                    return true;
                }



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

                const phoneInput = document.getElementById('phone');
                const cpfInput = document.getElementById('cpf');
                const zipCode = document.getElementById('zipcode');
                const cvvInput = document.getElementById('cvv');
                const cardNumberInput = document.getElementById('numero_cartao');
                const im = new Inputmask('(99) 9 9999-9999');
                const cpf = new Inputmask('999.999.999-99');
                const zip = new Inputmask('99999-999');
                const cvv = new Inputmask('999');
                const cardMask = new Inputmask('9999 9999 9999 9999');
                im.mask(phoneInput);
                cpf.mask(cpfInput);
                zip.mask(zipCode);
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


                $("form[name='cadastrar_individual']").on('submit',function(e){
                    e.preventDefault();

                    let load = $(".ajax_load");
                    load.fadeIn(100).css("display", "flex");

                    const isTrial = $('#trialCheckbox').is(':checked');

                    if(!TestaCPF($("#cpf").val().replace(/[^0-9]/g,''))) {
                        load.fadeOut(100).css("display", "none");
                        toastr.error("O CPF informado é inválido. Verifique e tente novamente.", "Error",{
                            toastClass: "toast-custom-width"
                        });
                        return false;
                    }
                    if(!isTrial) {
                        let numero_cartao = $("#numero_cartao").val();
                        let bandeira_validar = getBandeira(numero_cartao.replace(/\s/g, "").substring(0,6));
                        if (!bandeira_validar) {
                            toastr.error("O número do cartão informado não corresponde a nenhuma bandeira válida.", "Erro");
                            return false;
                        }
                        if (!validarCartaoCredito(numero_cartao)) {
                            toastr.error("O número do cartão de crédito é inválido. Verifique e tente novamente.", "Erro");
                            return false;
                        }

                        let paymentToken = "";
                        let mascaraCartao = "";

                        let nome_titular = $("#nome_titular").val();
                        let mes = $("#mes").val();
                        let ano = $("#ano").val();
                        let cvv = $("#cvv").val();
                        let bandeira = $("#bandeira").val();


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

                                    let formData = new FormData($("form[name='cadastrar_individual']")[0]);
                                    formData.append("paymentToken", paymentToken);
                                    formData.append("mascaraCartao", mascaraCartao);

                                    $.ajax({
                                        url:"{{ route('assinaturas.individual.store') }}",
                                        method:"POST",
                                        data: formData,
                                        contentType: false,  // Importante para envio de arquivos
                                        processData: false,  // Impede que o jQuery converta os dados

                                        beforeSend: function () {
                                            load.fadeIn(100).css("display", "flex");

                                        },
                                        success:function(res) {

                                            //if(res.success == true) {
                                            load.fadeOut(100).css("display", "none");
                                            //}
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
                    } else {
                        let formData = new FormData($("form[name='cadastrar_individual']")[0]);
                        formData.append("trial", isTrial);
                        $.ajax({
                            url:"{{ route('assinaturas.individual.store') }}",
                            method:"POST",
                            data: formData,
                            contentType: false,  // Importante para envio de arquivos
                            processData: false,  // Impede que o jQuery converta os dados

                            beforeSend: function () {
                                load.fadeIn(100).css("display", "flex");

                            },
                            success:function(res) {
                                //if(res.success == true) {
                                load.fadeOut(100).css("display", "none");
                                //}
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

                document.getElementById('trialCheckbox').addEventListener('change', function() {
                    //const cardFields = document.getElementById('cardFields');
                    //const cardDados = document.getElementById('cardDados');
                    //const cardPreview = document.getElementById('cartao');
                    const opcoesPagamento = document.getElementById('opcoes_pagamento');


                    if(this.checked) {
                        //cardFields.style.display = 'none';
                        //cardPreview.style.display = 'none';
                        //cardDados.style.display = 'none';
                        opcoesPagamento.style.display = 'none';
                        // Remove required dos campos
                        document.querySelectorAll('#cardFields [required]').forEach(el => el.removeAttribute('required'));
                        document.querySelectorAll('#cardDados [required]').forEach(el => el.removeAttribute('required'));

                        window.scrollTo({
                            top: document.body.scrollHeight,
                            behavior: 'smooth'
                        });

                        if ($("#container_cartao").is(":visible")) {
                            $("#container_cartao").hide(); // ou .fadeOut()
                        }

                        if ($("#pix_montar").is(":visible")) {
                            $("#pix_montar").fadeOut(); // ou .hide()
                        }



                        $('#buttonSubmit').show();

                    } else {
                        //cardFields.style.display = 'block';
                        //cardPreview.style.display = 'block';
                        //cardDados.style.display = 'block';
                        opcoesPagamento.style.display = 'flex';
                        // Adiciona required novamente
                        document.querySelectorAll('#cardFields [required]').forEach(el => el.setAttribute('required', 'true'));
                        document.querySelectorAll('#cardDados [required]').forEach(el => el.setAttribute('required', 'true'));
                        $('#buttonSubmit').hide();
                    }
                });


                $("#pixButton").on('click',function(){
                    if ($("#container_cartao").is(":visible")) {
                        $("#container_cartao").hide(); // ou .fadeOut()
                    }

                    $("#buttonSubmit").addClass("hidden");

                    let cpf = $("#cpf").val();
                    let nome = $("#name").val();
                    let email = $("#email").val();
                    let data_nascimento = $("#birth_date").val();
                    let phone = $("#phone").val();
                    let password = $("#password").val();
                    let password_confirmation = $("#password_confirmation").val();

                    if(!cpf || !TestaCPF(cpf.replace(/[^0-9]/g,''))) {
                        //load.fadeOut(100).css("display", "none");
                        toastr.error("O CPF informado está vazio ou inválido. Verifique os dados e tente novamente.", "Error",{
                            toastClass: "toast-custom-width"
                        });
                        return false;
                    }

                    if(!nome) {
                        toastr.error("Por favor, preencha o campo Nome.", "Error",{
                            toastClass: "toast-custom-width"
                        });
                        return false;
                    }

                    if(!email) {
                        toastr.error("Por favor, preencha o campo Email.", "Error",{
                            toastClass: "toast-custom-width"
                        });
                        return false;
                    }

                    if(!data_nascimento) {
                        toastr.error("Por favor, preencha o campo data de nascimento.", "Error",{
                            toastClass: "toast-custom-width"
                        });
                        return false;
                    }

                    if(!phone) {
                        toastr.error("Por favor, preencha o campo Telefone.", "Error",{
                            toastClass: "toast-custom-width"
                        });
                        return false;
                    }


                    if (!password) {
                        toastr.error("Por favor, preencha o campo Senha.", "Error", {
                            toastClass: "toast-custom-width"
                        });
                        return false;
                    }

                    // Confirmação de Senha
                    if (password !== password_confirmation) {
                        toastr.error("As senhas não coincidem. Verifique e tente novamente.", "Error", {
                            toastClass: "toast-custom-width"
                        });
                        return false;
                    }





                    $('#loading-cidades').fadeIn(150);
                    $.ajax({
                        url:"{{route('assinatura.pix')}}",
                        method:"POST",
                        data: {
                            cpf,nome
                        },
                        success:function(res) {
                            console.log(res);
                            // if(res != "error") {
                            //     $("#qrcode_img").attr("src", res.imagem);
                            //     $("#copiacola_input").val(res.copiacola);
                            //
                            //     $("#txid").val(res.txid);
                            //
                            //     $("#pix_montar").fadeIn();
                            //
                            // } else {
                            //
                            // }
                        },
                        complete: function() {
                            // Esconde loader sempre ao finalizar
                            $('#loading-cidades').fadeOut(150);
                            $('#resultado_pix').fadeIn();
                        }
                   });
                });

                function verificarPagamento() {
                    let transactionId = $("#txid").val(); // substitua pelo ID real da transação
                    let nome = $("#name").val();
                    let email = $("#email").val();
                    let data_nascimento = $("#birth_date").val();
                    let cpf = $("#cpf").val();
                    let phone = $("#phone").val();
                    let password = $("#password").val();
                    let imagem = $("#imagem")[0].files[0];



                    if(transactionId) {
                        let formData = new FormData(); // Cria um novo FormData
                        formData.append("id", transactionId);
                        formData.append("nome", nome);
                        formData.append("email", email);
                        formData.append("data_nascimento", data_nascimento);
                        formData.append("cpf", cpf);
                        formData.append("phone", phone);
                        formData.append("imagem", imagem); // Anexa o arquivo
                        formData.append("password",password);


                        $.ajax({
                            url: `{{route('verificar.pagamento')}}`,
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
                // Inicia o polling a cada 10 segundos
                let intervalo = setInterval(verificarPagamento, 20000);

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

                $("body").on('click','#creditCardButton',function(){
                    if ($("#pix_montar").is(":visible")) {
                        $("#pix_montar").fadeOut(); // ou .hide()
                    }
                    $("#container_cartao").fadeIn();

                    if ($("#buttonSubmit").hasClass("hidden")) {
                        $("#buttonSubmit").removeClass("hidden");
                    }

                });


            });
        </script>

    @endsection


</x-guest-layout>
