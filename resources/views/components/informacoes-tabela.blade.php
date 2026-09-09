<div class="mt-2 rounded p-1 bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border w-full lg:w-[22%]" id="container_informacoes">
    <button class="py-1.5 w-full px-1 me-2 mb-2 text-sm font-medium text-white bg-white rounded-lg border border-gray-200 bg-gray-500 bg-opacity-10">
        Tabela de Origem
    </button>

    <form>
        <div class="w-full flex">
            <div class="ml-1 w-[35%]">
                <label for="estado" class="text-white text-sm">UF</label>
                <select id="estado" class="py-2 text-black w-full dark:border-white text-xs px-1 me-2 mb-2 font-medium rounded-lg dark:bg-transparent dark:text-white">
                    <option value="" class="text-xs text-black">Escolher UF</option>
                    @foreach($estados as $uf)
                        @if($uf != null)
                            <option value="{{$uf->uf}}" {{ ($ufpreferencia ?? '') == $uf->uf ? 'selected' : '' }} class="text-black">{{$uf->uf}} - {{$uf->descricao}}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="ml-1 w-[62%]">
                <label for="cidade" class="text-white text-sm">Cidade</label>
                <select id="cidade" class="py-2 text-black w-full dark:border-white text-xs px-1 me-2 mb-2 font-medium rounded-lg dark:bg-transparent dark:text-white">
                    <option value="" class="text-xs text-black">Escolher Cidade</option>
                </select>
            </div>
        </div>
    </form>



</div>
