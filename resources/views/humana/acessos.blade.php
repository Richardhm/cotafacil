{{-- Gestão de acesso ao módulo Humana — só desenvolvedor. --}}
<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h1 class="text-2xl font-bold mb-1">Acessos ao módulo Humana</h1>
                <p class="text-gray-600 mb-4 text-sm">
                    A liberação é por assinatura: vale para o titular e toda a equipe dela.
                    Desenvolvedores sempre têm acesso.
                </p>

                @if (session('status'))
                    <div class="mb-4 p-3 rounded bg-green-100 text-green-800 text-sm">{{ session('status') }}</div>
                @endif

                <input type="text" id="filtro-assinatura" placeholder="Filtrar por nome ou e-mail..."
                       class="mb-4 w-full border-gray-300 rounded-md shadow-sm text-sm">

                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="py-2 pr-2">#</th>
                            <th class="py-2 pr-2">Titular</th>
                            <th class="py-2 pr-2">E-mail</th>
                            <th class="py-2 pr-2">Status</th>
                            <th class="py-2">Humana</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assinaturas as $assinatura)
                            <tr class="border-b linha-assinatura"
                                data-busca="{{ mb_strtolower(($assinatura->user->name ?? '') . ' ' . ($assinatura->user->email ?? '')) }}">
                                <td class="py-2 pr-2">{{ $assinatura->id }}</td>
                                <td class="py-2 pr-2">{{ $assinatura->user->name ?? '—' }}</td>
                                <td class="py-2 pr-2">{{ $assinatura->user->email ?? '—' }}</td>
                                <td class="py-2 pr-2">{{ $assinatura->status }}</td>
                                <td class="py-2">
                                    <form method="POST" action="{{ route('humana-acessos.toggle') }}">
                                        @csrf
                                        <input type="hidden" name="assinatura_id" value="{{ $assinatura->id }}">
                                        @if (isset($liberadas[$assinatura->id]))
                                            <button type="submit"
                                                    class="px-3 py-1 rounded bg-green-600 text-white text-xs font-bold">
                                                LIBERADA — clique p/ bloquear
                                            </button>
                                        @else
                                            <button type="submit"
                                                    class="px-3 py-1 rounded bg-gray-300 text-gray-700 text-xs">
                                                bloqueada — clique p/ liberar
                                            </button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @section('scripts')
        <script>
            document.getElementById('filtro-assinatura').addEventListener('input', function () {
                const termo = this.value.toLowerCase();
                document.querySelectorAll('.linha-assinatura').forEach(function (linha) {
                    linha.style.display = linha.dataset.busca.includes(termo) ? '' : 'none';
                });
            });
        </script>
    @endsection
</x-app-layout>
