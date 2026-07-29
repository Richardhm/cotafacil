<x-app-layout>
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative w-[80%] mx-auto text-center" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

        @if (session('error'))
            <div class="bg-red-600 border border-red-400 text-white px-4 py-3 rounded relative w-[80%] mx-auto text-center" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif


    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>
    <div class="w-[99%] mx-auto flex justify-between">


        <div class="md:w-[32%] mt-3">

            <!-- Título -->
            <h3 class="text-white font-semibold text-lg mb-1 text-lg">Gerenciador de Layout e UF de Referência</h3>

            <div class="flex w-full md:w-[94%] justify-around bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border-white border mt-1 mb-1 rounded p-1 items-center">
                <label  for="regiao" class="block text-sm font-medium text-white mb-1 w-[50%]">Região (UF) de Preferência</label>
                <select name="regiao" id="regiao"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm
           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
           text-gray-700 bg-white  w-[20%]" translate="no">
                    <option value="" disabled selected>UF</option>
                    @foreach($cidades as $uf => $grupo)
                        <option value="{{ $uf }}" {{ auth()->user()->uf_preferencia === $uf ? 'selected' : '' }}>{{ $uf }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Grid de Layouts -->
            <div class="grid grid-cols-2 gap-1">
                @foreach($layouts as $layout)
                    <label style="height: 250px;max-height: 250px;width:210px;max-width:210px;" class="layout relative group flex flex-col items-center border p-0.5 rounded bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px]">
                        <!-- Input Radio -->
                        <input
                            type="radio"
                            id="layout_{{ $layout->id }}"
                            name="layout_id"
                            value="{{ $layout->id }}"
                            {{ $layout->id == $user->layout_id ? 'checked' : '' }}
                            class="w-[70%] mb-1 cursor-pointer appearance-none border-4 border-white rounded-full transition-all duration-300 group-hover:scale-110 group-hover:z-10 group-hover:shadow-xl"
                        />

                        <!-- Imagem -->
                        <div class="w-full" style="height: 190px;">
                            <img
                                src="{{ $folder ? asset($folder.'/'.$layout->imagem) : asset($layout->imagem) }}"
                                alt="{{ $layout->nome }}"
                                class="w-[80%] mx-auto rounded-lg transition-all duration-300"
                                style="height:100%;"
                            />
                        </div>

                        <!-- Nome do Layout -->
                        <p class="mt-1 text-center text-sm font-semibold text-white drop-shadow-sm">
                            {{ $layout->nome }}
                        </p>
                    </label>
                @endforeach
            </div>

        </div>

        <div class="w-[60%] mt-3">
            <div class="flex justify-between flex-wrap">
                <!-- Título -->
                <h3 class="text-white font-semibold text-lg mb-1 w-full bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] rounded p-2">Gerenciador de Perfil</h3>
                <div class="w-[48%]">
                    <div class="w-full bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] rounded border border-white">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
                <div class="w-[48%]">
                    <div class="w-full bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] rounded border border-white">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>






            </div>
        </div>
    </div>


    @section('css')
        <style>
            input[type="radio"] {

                width: 20px;

                height: 20px;

                background-color: white;

                border-radius: 50%;

                border: 4px solid white;

                transition: all 0.3s ease-in-out;

            }



            input[type="radio"]:checked {

                border-color: #7B3AED;

                background-color: #7B3AED;

                box-shadow: 0 0 10px rgba(123, 58, 237, 0.6);

            }



            input[type="radio"]:hover {

                transform: scale(1.2);

            }

        </style>
    @endsection

    @section('scripts')
        <script>
            $(document).ready(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $("input[name='layout_id']").on('change',function(){

                    let valor = $(this).val();
                    let load = $(".ajax_load");
                    $.ajax({
                        url:"{{route('layouts.select')}}",
                        method:"POST",
                        data: {valor},
                        beforeSend: function () {
                            load.fadeIn(100).css("display", "flex");
                        },
                        success:function(res) {
                            load.fadeOut(100).css("display", "none");
                            if(res == "sucesso") {
                                toastr.success("Layout trocado com sucesso.", "Sucesso",{
                                    toastClass: "toast-custom-width"
                                });
                            } else {
                                toastr.error("Erro ao mudar de Layout. Verifique e tente novamente.", "Error",{
                                    toastClass: "toast-custom-width"
                                });
                            }
                        }
                    });

                });

                $("body").on('change','#regiao',function(){
                    let regiao = $(this).val();
                    $.ajax({
                        url:"{{route('gerenciamento.regiao')}}",
                        method:"POST",
                        data: {regiao},
                        success:function(res) {
                            if(res == true) {
                                toastr.success("Região alterada com sucesso", 'Sucesso');
                            } else {
                                toastr.error("Erro ao alterar região", 'Error');
                            }
                        }
                    });
                });



            });

            document.getElementById('user-avatar').addEventListener('click', function() {
                document.getElementById('avatar-input').click();
            });

            document.getElementById('avatar-input').addEventListener('change', function(event) {
                if (event.target.files.length > 0) {

                    const form = new FormData();
                    form.append('imagem', event.target.files[0]);

                    fetch('/profile/imagem/atualizar', {
                        method: 'POST',
                        body: form,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(async response => {
                        const data = await response.json().catch(() => ({}));

                        // O servidor pode recusar o upload (422 de validação, 500 do
                        // controller). Sem esta checagem a foto trocava na tela do mesmo
                        // jeito, porque a imagem exibida vem do arquivo local — e só o F5
                        // revelava que nada tinha sido salvo.
                        if (!response.ok || data.error) {
                            toastr.error(data.error || data.message || 'Erro ao atualizar a imagem.', 'Erro');
                            return;
                        }

                        document.getElementById('user-avatar').src =
                            URL.createObjectURL(event.target.files[0]);
                        toastr.success(data.message || 'Imagem atualizada com sucesso!', 'Sucesso');
                    }).catch(error => {
                        console.error('Erro:', error);
                        toastr.error('Não foi possível enviar a imagem.', 'Erro');
                    });



                }
            });






        </script>
    @endsection



</x-app-layout>
