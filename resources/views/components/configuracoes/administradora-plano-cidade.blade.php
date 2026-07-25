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
                    @forelse($assinaturasVinculadas as $resumo)
                        @php
                            $assinaturaId = $resumo->assinatura_id;
                            $usuario = $resumo->assinatura?->user;
                        @endphp
                        <tbody>
                        <tr>
                            <td class="px-2 py-1 font-bold text-white">
                                @if($usuario)
                                    {{ $usuario->name }}
                                    <span class="block font-normal text-[11px] opacity-80">{{ $usuario->email }}</span>
                                @else
                                    Assinatura #{{ $assinaturaId }}
                                @endif
                            </td>
                            <td class="px-2 py-1">
                                <button type="button"
                                        id="btn-vinculos-{{ $assinaturaId }}"
                                        onclick="abrirModalVinculos('{{ $assinaturaId }}', @js($usuario?->name ?? 'Assinatura #' . $assinaturaId))"
                                        class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 whitespace-nowrap">
                                    Ver Vínculos ({{ $resumo->total_vinculos }})
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    @empty
                        <tbody>
                        <tr>
                            <td colspan="2" class="px-2 py-3 text-white text-center opacity-80">
                                Nenhuma associação cadastrada.
                            </td>
                        </tr>
                        </tbody>
                    @endforelse
                </table>

            </div>
        </div>
    </div>

    <!-- Modal de vínculos (cidade -> administradora/plano) -->
    <div id="modal-vinculos"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4"
         onclick="if (event.target === this) fecharModalVinculos()">
        <div class="bg-gray-900 border border-white/10 rounded-lg shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col">

            <div class="flex items-start justify-between gap-4 px-4 py-3 border-b border-white/10">
                <div>
                    <h3 id="modal-vinculos-titulo" class="text-white font-semibold"></h3>
                    <p id="modal-vinculos-subtitulo" class="text-white text-xs opacity-70"></p>
                </div>
                <button type="button" onclick="fecharModalVinculos()"
                        class="text-white text-2xl leading-none opacity-70 hover:opacity-100">&times;</button>
            </div>

            <div class="px-4 py-2 border-b border-white/10">
                <input type="text" id="modal-vinculos-busca" placeholder="Filtrar cidade..."
                       oninput="renderVinculos()"
                       class="w-full bg-black/30 border border-white/10 rounded px-2 py-1 text-sm text-white placeholder-white/40">
            </div>

            <div id="modal-vinculos-corpo" class="overflow-y-auto flex-1"></div>
        </div>
    </div>
</div>
