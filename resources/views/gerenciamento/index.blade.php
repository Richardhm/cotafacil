<x-app-layout>
    <!-- Botão redondo -->
    <div
        class="flex p-2 mt-2 justify-between flex-wrap items-center w-full bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] rounded md:w-[99%]">
        <div class="flex items-center flex-wrap gap-4">
            @if(!auth()->user()->estaEmTrial())
                <p class="text-sm text-white dark:text-gray-400 text-center">
                    Valor por usuário <strong class="text-blue-200 font-bold">R$ 29,90</strong>
                </p>
            @endif

            @if(auth()->user()->estaEmTrial())
                <p class="text-sm text-white dark:text-gray-400 text-center">
                    Valor por usuário <strong class="text-blue-200 font-bold">R$ 29,90</strong>
                </p>
            @endif
        </div>

        <div class="flex items-center text-sm gap-2 flex-wrap">
            <p>
                <span class="text-white">📈 Valor Total:</span>
                <strong class="text-red-500 preco_total">R$ {{ number_format($valor, 2, ',', '.') }}</strong>
            </p>
            <a class="p-1 bg-orange-500 text-white rounded" href="{{route('assinatura.edit')}}">Pagar</a>
        </div>
    </div>

    <!--Modal Editar-->
    <x-modals.edit-user-modal />
    <!--Fim Modal Editar-->

    <!-- Modal Cadastrar -->
    <x-modals.cadastrar-user-modal />
    <!-- Fim Modal Cadastrar -->

    <div class="w-[99%] mx-auto flex flex-wrap justify-between gap-4 mt-3">

        <!-- Coluna da esquerda com título + grid -->
        <div class="w-full md:w-[32%]">
            <h3 class="text-white font-semibold text-base sm:text-lg md:text-xl lg:text-2xl mb-1">
                Gerenciador de Layout e UF de Referência
            </h3>

            <div
                class="flex w-full justify-around bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border-white border mt-1 mb-1 rounded p-1 items-center gap-2">
                <label for="regiao" class="block text-sm font-medium text-white w-full md:w-[50%]">
                    Região (UF) de Preferência
                </label>
                <select name="regiao" id="regiao"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-700 bg-white w-[40%]">
                    <option value="" disabled selected>UF</option>
                    @foreach($cidades as $uf => $grupo)
                        <option value="{{ $uf }}" {{ auth()->user()->uf_preferencia === $uf ? 'selected' : '' }}>{{ $uf }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Grid de Layouts -->
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($layouts as $layout)
                    <label
                        class="layout relative group flex flex-col items-center border rounded p-2 bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px]">
                        <!-- Input Radio -->
                        <input type="radio" id="layout_{{ $layout->id }}" name="layout_id" value="{{ $layout->id }}"
                               {{ $layout->id == $user->layout_id ? 'checked' : '' }}
                               class="w-6 h-6 cursor-pointer rounded-full border-4 border-white transition-transform hover:scale-110 focus:ring-2 focus:ring-blue-500" />

                        <!-- Imagem -->
                        <div class="w-full h-[150px] flex justify-center">
                            <img src="{{ $folder ? asset($folder.'/'.$layout->imagem) : asset($layout->imagem) }}" alt="{{ $layout->nome }}"
                                 class="w-full max-w-[90%] rounded-lg" />
                        </div>

                        <!-- Nome do Layout -->
                        <p class="mt-1 text-center text-sm font-semibold text-white">
                            {{ $layout->nome }}
                        </p>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Tabela de usuários à direita -->
        <div class="w-full md:w-[65%]">
            <div class="flex items-center mb-2">
                <h3 class="text-white font-semibold text-base">Usuários Adicionais</h3>
                <button id="toggle-modal"
                        class="w-8 ml-3 h-8 flex items-center justify-center rounded-full bg-green-500 border-white border-4 text-white text-2xl font-bold shadow-lg transition hover:scale-110">
                    +
                </button>
            </div>

            <div id="user-table"
                 class="max-h-[550px] h-[550px] overflow-y-auto rounded-lg scrollbar-thin scrollbar-thumb-yellow-500 scrollbar-track-white/10">
                @include('partials.user-table', ['users' => $users])
            </div>
        </div>

    </div>

    @section('css')
        <style>
            #user-table::-webkit-scrollbar {
                width: 8px;
            }

            #user-table::-webkit-scrollbar-track {
                background: rgba(255, 255, 255, 0.1);
            }

            #user-table::-webkit-scrollbar-thumb {
                background-color: #facc15;
                border-radius: 8px;
            }

            #user-table {
                scrollbar-color: #facc15 rgba(255, 255, 255, 0.1);
                scrollbar-width: thin;
            }
        </style>
    @endsection

    @section('scripts')
        <script>
            const routes = {
                storeUser: "{{ route('storeUser') }}",
                assinaturaEdit: "{{ route('assinatura.edit') }}",
                gerenciamentoRegiao: "{{ route('gerenciamento.regiao') }}",
                layoutsSelect: "{{ route('layouts.select') }}",
                usersGet: "{{ route('users.get') }}",
                usersAlterar: "{{ route('users.alterar') }}",
                usersUpdate: "{{ route('users.update') }}",
                deletarUser: "{{ route('deletar.user') }}",
            };
            const csrfToken = "{{ csrf_token() }}";
        </script>
        <script src="{{asset('js/gerencimento.js')}}"></script>
    @endsection
</x-app-layout>
