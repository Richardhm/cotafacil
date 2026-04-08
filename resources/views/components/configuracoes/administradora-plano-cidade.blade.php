<div>
    <div>
        <!-- Mensagens -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            <!-- Formulário -->
            <div class="col-span-3 bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4 text-white">Vincular Planos</h2>
                <form id="formPlanos" method="POST" action="{{ route('admin-planos.store') }}">
                    @csrf
                    <div class="grid grid-cols-4 gap-1">

                        <!-- assinaturas -->
                        <div>
                            <h3 class="text-white mb-2 font-medium">Assinaturas</h3>
                            <div class="space-y-2 max-h-60 overflow-y-auto">
                                @foreach($assinaturas as $ass)
                                    <label class="flex items-center space-x-1 text-white text-sm">
                                        <input type="radio" name="assinatura_id" value="{{ $ass->id }}"
                                               class="rounded border-gray-300">
                                        <span>{{ $ass->user->name }} ({{ $ass->user->email }})</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Administradoras -->
                        <div>
                            <h3 class="text-white mb-2 font-medium">Administradoras</h3>
                            <div class="space-y-2 max-h-60 overflow-y-auto">
                                @foreach($administradoras as $adm)
                                    <label class="flex items-center space-x-1 text-white text-sm">
                                        <input type="radio" name="administradora_id" value="{{ $adm->id }}"
                                               class="rounded border-gray-300">
                                        <span>{{ $adm->nome }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Planos -->
                        <div id="bloco-planos" style="display:none;">
                            <h3 class="text-white mb-2 font-medium">Planos</h3>
                            <div class="space-y-2 max-h-60 overflow-y-auto" id="lista-planos">
                                <!-- Populado via JS -->
                            </div>
                        </div>
                        <!-- Tabelas de Origem -->
                        <div id="bloco-tabelas" style="display:none;">
                            <h3 class="text-white mb-2 font-medium">Tabelas de Origem</h3>
                            <div class="space-y-2 max-h-60 overflow-y-auto" id="lista-tabelas">
                                <!-- Populado via JS -->
                            </div>
                        </div>

                    </div>

                    <button type="submit"
                            class="mt-4 bg-blue-500 text-white px-4 py-2 w-full rounded hover:bg-blue-600">
                        Salvar Associações
                    </button>
                </form>
            </div>

            <!-- Lista -->
            <div class="col-span-2 bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4 text-white">Associações Existentes</h2>

                <table class="min-w-full text-xs">
                    <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-2 py-1 text-left">Assinatura</th>
                        <th class="px-2 py-1 text-left">Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($vinculosAgrupados as $assinaturaId => $vinculos)
                        @php
                            $firstVinculo = $vinculos->first();
                        @endphp
                        <tr>
                            <td class="px-2 py-1 font-bold text-white">
                                
                            </td>
                            <td class="px-2 py-1">
                                <button onclick="toggleVinculos('{{ $assinaturaId }}')"
                                        class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600">
                                    Ver Vínculos
                                </button>
                            </td>
                        </tr>

                    <tbody id="vinculos-{{ $assinaturaId }}" style="display:none;">
                    @foreach($vinculos as $vinculo)
                        <tr class="bg-gray-700">
                            <td class="px-2 py-1 text-white">
                                {{ $vinculo->administradora->nome }} /
                                {{ $vinculo->plano->nome }} /
                                {{ $vinculo->tabelaOrigem->nome ?? 'N/A' }}
                            </td>
                            <td class="px-2 py-1">
                                <form action="{{ route('admin-planos.destroy', $vinculo->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-500 hover:text-red-700"
                                            onclick="return confirm('Excluir este vínculo?')">
                                        Excluir
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
