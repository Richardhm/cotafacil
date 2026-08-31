{{-- Aba Rótulos: títulos personalizados da cotação (usuário > assinatura > padrão) --}}
<div>
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ $errors->first() }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Formulário --}}
        <div class="md:col-span-1">
            <div class="bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] p-6 rounded-lg shadow text-white">
                <h2 class="text-xl font-semibold mb-1 text-center">Novo rótulo</h2>
                <p class="text-xs opacity-75 mb-4 text-center">Usuário vence assinatura, que vence o padrão. Cadastrar de novo o mesmo alvo substitui.</p>

                <form action="{{ route('rotulos.store') }}" method="POST" id="form-rotulo">
                    @csrf
                    <div class="mb-3">
                        <label class="block mb-1 text-sm">Nível</label>
                        <div class="flex gap-4 text-sm">
                            <label><input type="radio" name="nivel" value="usuario" checked class="mr-1"> Usuário</label>
                            <label><input type="radio" name="nivel" value="assinatura" class="mr-1"> Assinatura (escritório)</label>
                        </div>
                    </div>

                    <div class="mb-3" id="campo-usuario">
                        <label class="block mb-1 text-sm">Usuário</label>
                        <select name="user_id" class="w-full px-2 py-2 rounded text-black text-sm">
                            <option value="">— escolher —</option>
                            @foreach($usuarios as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} — {{ $u->email }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 hidden" id="campo-assinatura">
                        <label class="block mb-1 text-sm">Assinatura</label>
                        <select name="assinatura_id" class="w-full px-2 py-2 rounded text-black text-sm">
                            <option value="">— escolher —</option>
                            @foreach($assinaturas as $a)
                                <option value="{{ $a->id }}">#{{ $a->id }} — {{ $a->user->name ?? '?' }} ({{ $a->user->email ?? '' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="block mb-1 text-sm">Qual título</label>
                        <select name="chave" id="select-chave" class="w-full px-2 py-2 rounded text-black text-sm">
                            @foreach($chaves as $valor => $rotulo)
                                <option value="{{ $valor }}">{{ $rotulo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3" id="campo-plano">
                        <label class="block mb-1 text-sm">Plano (só para o nome do plano)</label>
                        <select name="plano_id" class="w-full px-2 py-2 rounded text-black text-sm">
                            <option value="">— escolher —</option>
                            @foreach($planos as $p)
                                <option value="{{ $p->id }}">{{ $p->nome }} ({{ $p->id }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 text-sm">Texto (sai em MAIÚSCULAS)</label>
                        <input type="text" name="texto" maxlength="40" required placeholder="Ex.: ADESÃO / COPARTICIPAÇÃO TOTAL"
                               class="w-full px-3 py-2 rounded text-black text-sm">
                    </div>

                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 w-full rounded">Salvar</button>
                </form>
            </div>
        </div>

        {{-- Lista --}}
        <div class="md:col-span-2">
            <div class="bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4 text-white">Rótulos cadastrados ({{ $rotulos->count() }})</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-[rgba(254,254,254,0.18)] text-white text-xs uppercase">
                                <th class="px-3 py-2 text-left">Nível</th>
                                <th class="px-3 py-2 text-left">Quem</th>
                                <th class="px-3 py-2 text-left">Título</th>
                                <th class="px-3 py-2 text-left">Plano</th>
                                <th class="px-3 py-2 text-left">Texto</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-white">
                            @forelse($rotulos as $r)
                                <tr>
                                    <td class="px-3 py-2">
                                        @if($r->user_id)
                                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded">Usuário</span>
                                        @else
                                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-0.5 rounded">Assinatura</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        @if($r->user_id)
                                            {{ $r->user->name ?? '?' }}<br><span class="text-xs opacity-75">{{ $r->user->email ?? '' }}</span>
                                        @else
                                            #{{ $r->assinatura_id }} — {{ $r->assinatura->user->name ?? '?' }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-xs">{{ $chaves[$r->chave] ?? $r->chave }}</td>
                                    <td class="px-3 py-2">{{ $r->plano->nome ?? '—' }}</td>
                                    <td class="px-3 py-2">
                                        <form action="{{ route('rotulos.texto', $r->id) }}" method="POST" class="flex gap-1">
                                            @csrf
                                            <input type="text" name="texto" maxlength="40" value="{{ $r->texto }}"
                                                   class="w-44 px-2 py-1 text-sm border rounded text-black">
                                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white text-xs px-2 rounded">Salvar</button>
                                        </form>
                                    </td>
                                    <td class="px-3 py-2">
                                        <form action="{{ route('rotulos.destroy', $r->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" onclick="return confirm('Remover? Volta ao padrão.')"
                                                    class="text-red-300 hover:text-red-100 border border-red-300 text-xs px-2 py-1 rounded">Remover</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-4 text-center opacity-75">Nenhum rótulo personalizado — todo mundo usa o padrão.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('form-rotulo');
            const alternar = () => {
                const nivel = form.querySelector('input[name="nivel"]:checked').value;
                document.getElementById('campo-usuario').classList.toggle('hidden', nivel !== 'usuario');
                document.getElementById('campo-assinatura').classList.toggle('hidden', nivel !== 'assinatura');
                document.getElementById('campo-plano').classList.toggle('hidden', document.getElementById('select-chave').value !== 'nome_plano');
            };
            form.querySelectorAll('input[name="nivel"]').forEach(r => r.addEventListener('change', alternar));
            document.getElementById('select-chave').addEventListener('change', alternar);
            alternar();
        })();
    </script>
</div>
