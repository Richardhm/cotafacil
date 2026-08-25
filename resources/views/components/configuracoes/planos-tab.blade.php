<div>
    <div>
        <!-- Mensagens -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Formulário -->
            <div class="md:order-1">
                <div class="bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold mb-4 text-center text-white">Novo Plano</h2>
                    <form action="{{ route('planos.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-white mb-2">Nome do Plano</label>
                            <input type="text" name="nome" required
                                   class="w-full px-3 py-2 border rounded-lg @error('nome') border-red-500 @enderror">
                            @error('nome')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-white mb-2">Apelido na cotação <span class="text-xs opacity-70">(1ª coluna; vazio = NOSSO PLANO)</span></label>
                            <input type="text" name="apelido" maxlength="40" placeholder="Ex.: INDIVIDUAL"
                                   class="w-full px-3 py-2 border rounded-lg">
                        </div>

                        <div class="mb-4 flex items-center">
                            <input type="checkbox" name="empresarial" id="empresarial"
                                   class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label class="text-white" for="empresarial">Plano Empresarial</label>
                        </div>

                        <button type="submit"
                                class="bg-blue-500 text-white px-4 py-2 w-full rounded hover:bg-blue-600">
                            Cadastrar
                        </button>
                    </form>
                </div>
            </div>

            <!-- Lista -->
            <div class="md:order-2">
                <div class="bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold mb-4 text-white">Planos Cadastrados</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                            <tr class="bg-[rgba(254,254,254,0.18)] rounded">
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Nome</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Apelido na cotação</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Ações</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                            @foreach($planos as $plano)
                                <tr>
                                    <td class="px-6 py-4 text-white">{{ $plano->nome }}</td>
                                    <td class="px-6 py-4">
                                        <form action="{{ route('planos.apelido', $plano->id) }}" method="POST" class="flex gap-1">
                                            @csrf
                                            <input type="text" name="apelido" maxlength="40" value="{{ $plano->apelido }}"
                                                   placeholder="NOSSO PLANO"
                                                   class="w-36 px-2 py-1 text-sm border rounded">
                                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white text-xs px-2 rounded">Salvar</button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($plano->empresarial)
                                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                            Empresarial
                                        </span>
                                        @else
                                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                            Individual
                                        </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <form action="{{ route('planos.destroy', $plano->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-600 hover:text-red-900 border-red-600 border-2 p-2 rounded bg-yellow-50"
                                                    onclick="return confirm('Deseja excluir este plano?')">
                                                Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
