<div>
    <div>
        <!-- Mensagens -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Formulário -->
            <div class="bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4 text-white">Novo Desconto</h2>
                <form id="formDesconto" method="POST">
                    @csrf
                    <div class="grid grid-cols-3 gap-3">
                        <!-- Planos -->
                        <div>
                            <h3 class="text-white mb-2 font-medium">Planos</h3>
                            <div class="space-y-2 max-h-60 overflow-y-auto">
                                @foreach($planos as $plano)
                                    <label class="flex items-center space-x-2 text-white">
                                        <input type="checkbox" name="planos[]" value="{{ $plano->id }}"
                                               class="rounded border-gray-300">
                                        <span>{{ $plano->nome }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Cidades -->
                        <div>
                            <h3 class="text-white mb-2 font-medium">Cidades</h3>
                            <div class="space-y-2 max-h-60 overflow-y-auto">
                                @foreach($cidades as $cidade)
                                    <label class="flex items-center space-x-2 text-white">
                                        <input type="checkbox" name="cidades[]" value="{{ $cidade->id }}"
                                               class="rounded border-gray-300">
                                        <span>{{ $cidade->nome }} - {{ $cidade->uf }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Administradoras -->
                        <div>
                            <h3 class="text-white mb-2 font-medium">Administradoras</h3>
                            <div class="space-y-2 max-h-60 overflow-y-auto">
                                @foreach($administradoras as $adm)
                                    <label class="flex items-center space-x-2 text-white">
                                        <input type="checkbox" name="administradora[]" value="{{ $adm->id }}"
                                               class="rounded border-gray-300">
                                        <span>{{ $adm->nome }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Valor -->
                    <div class="mt-4">
                        <label class="block text-white mb-2">Valor do Desconto (opcional)</label>
                        <input type="number" step="0.01" name="valor"
                               class="w-full px-3 py-2 border rounded-lg bg-white/90">
                    </div>

                    <button type="submit"
                            class="mt-4 bg-blue-500 text-white px-4 py-2 w-full rounded hover:bg-blue-600">
                        Salvar Descontos
                    </button>
                </form>
            </div>

            <!-- Lista -->
            <div class="bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4 text-white">Descontos Cadastrados</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                        <tr class="bg-[rgba(254,254,254,0.18)] rounded">
                            <th class="px-6 py-3 text-left text-white">Plano</th>
                            <th class="px-6 py-3 text-left text-white">Cidade</th>
                            <th class="px-6 py-3 text-left text-white">Desconto</th>
                            <th class="px-6 py-3 text-left text-white">Texto na Cotação</th>
                            <th class="px-6 py-3 text-left text-white">Ações</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @foreach($descontos as $desconto)
                            <tr>
                                <td class="px-6 py-4 text-white">{{ $desconto->plano->nome }}</td>
                                <td class="px-6 py-4 text-white">{{ $desconto->cidade->nome }}</td>
                                <td class="px-6 py-4 text-white">
                                    {{ $desconto->valor ? number_format($desconto->valor, 0, ',', '.') . '%' : 'N/A' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <input type="text"
                                               class="texto-desconto-input border border-gray-300 rounded px-2 py-1 text-sm text-gray-900 bg-white/90 w-44"
                                               value="{{ $desconto->texto_desconto ?? 'Des. ' . (int)$desconto->valor . '% 3/meses' }}"
                                               data-id="{{ $desconto->id }}"
                                               data-url="{{ route('descontos.update.texto', $desconto->id) }}">
                                        <button class="salvar-texto-desconto text-green-400 hover:text-green-200 text-sm font-medium" data-id="{{ $desconto->id }}">Salvar</button>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <form action="{{ route('descontos.destroy', $desconto->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-500 hover:text-red-700"
                                                onclick="return confirm('Excluir este desconto?')">
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

<script>
document.querySelectorAll('.salvar-texto-desconto').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        var input = document.querySelector('.texto-desconto-input[data-id="' + id + '"]');
        var url = input.dataset.url;
        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ texto_desconto: input.value }),
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                toastr.success('Texto atualizado!');
            } else {
                toastr.error('Erro ao salvar.');
            }
        });
    });
});
</script>
