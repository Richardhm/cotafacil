<x-app-layout>
    @section('css')
    <style>
        .card-stat { background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; padding: 1.2rem 1.5rem; }
        .card-stat .label { font-size: 0.8rem; font-weight: 600; color: rgba(255,255,255,0.7); margin-bottom: 4px; }
        .card-stat .value { font-size: 2rem; font-weight: 700; color:#fff; }
        .badge-tipo { font-size: 0.72rem; padding: 2px 8px; border-radius: 20px; font-weight: 600; }
        .badge-cartao  { background:#3b82f6; color:#fff; }
        .badge-pix     { background:#10b981; color:#fff; }
        .badge-pixauto { background:#8b5cf6; color:#fff; }
        .badge-trial   { background:#f59e0b; color:#fff; }
        .badge-status-ativo    { background:#10b981; color:#fff; }
        .badge-status-trial    { background:#f59e0b; color:#fff; }
        .badge-status-inativo  { background:#ef4444; color:#fff; }
        .badge-status-pendente { background:#d97706; color:#fff; }
        table thead th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: rgba(255,255,255,0.85); font-weight: 700; }
        table tbody td { color: #fff; }

        .btn-expand { cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
        .btn-expand .seta { transition: transform .2s ease; display:inline-block; }
        .btn-expand.aberto .seta { transform: rotate(90deg); }
        .linha-usuarios { display:none; }
        .linha-usuarios.aberta { display:table-row; }
        .usuarios-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(220px,1fr)); gap:8px; padding:10px 0; }
        .usuario-card { background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:8px; padding:8px 12px; }
        .usuario-card .u-nome { font-weight:600; color:#fff; font-size:0.82rem; }
        .usuario-card .u-tel  { color:rgba(255,255,255,0.7); font-size:0.78rem; margin-top:2px; }
        .usuario-card .u-email { color:rgba(255,255,255,0.55); font-size:0.74rem; }
        .crown { color:#fbbf24; font-size:0.7rem; margin-left:4px; }
        .zap-link { color:#25D366; display:inline-flex; align-items:center; margin-left:4px; }
        .zap-link:hover { color:#4ce884; }
        .zap-link svg { width:16px; height:16px; }
    </style>
    @endsection

    @php
        // Link do WhatsApp a partir do telefone gravado (formatos variados):
        // só dígitos, DDI 55 quando faltar; telefone curto demais fica sem link.
        $linkZap = function ($tel) {
            $digitos = preg_replace('/\D+/', '', (string) $tel);
            if (strlen($digitos) < 10) {
                return null;
            }
            if (! str_starts_with($digitos, '55')) {
                $digitos = '55' . $digitos;
            }
            return 'https://wa.me/' . $digitos;
        };
        $iconeZap = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>';
    @endphp

    <div class="max-w-7xl mx-auto px-4 py-6">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Vendas</h1>
                <p class="text-sm text-white opacity-70">Cadastros a partir de {{ $inicio->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="card-stat">
                <div class="label">Novos cadastros</div>
                <div class="value">{{ $totalNovas }}</div>
            </div>
            <div class="card-stat">
                <div class="label">Novos usuários</div>
                <div class="value" style="color:#3b82f6;">{{ $totalUsuarios }}</div>
            </div>
            <div class="card-stat">
                <div class="label">Ativas</div>
                <div class="value" style="color:#10b981;">{{ $totalAtivas }}</div>
            </div>
            <div class="card-stat">
                <div class="label">Em teste (trial)</div>
                <div class="value" style="color:#f59e0b;">{{ $totalTrial }}</div>
            </div>
        </div>

        <div class="bg-[#1e293b] border border-[#f97316] rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#f97316]">
                    <thead>
                    <tr>
                        <th class="px-4 py-3 text-left">Cadastro</th>
                        <th class="px-4 py-3 text-left">Cliente</th>
                        <th class="px-4 py-3 text-left">Contato</th>
                        <th class="px-4 py-3 text-center">Usuários</th>
                        <th class="px-4 py-3 text-center">Tipo</th>
                        <th class="px-4 py-3 text-center">Próx. cobrança / Fim do teste</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f97316]">
                    @forelse($contas as $i => $conta)
                        <tr>
                            <td class="px-4 py-3 text-sm whitespace-nowrap">{{ \Carbon\Carbon::parse($conta['created_at'])->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="btn-expand" data-alvo="usuarios-{{ $i }}">
                                    <span class="seta">▶</span>
                                    <span class="font-semibold">{{ $conta['admin_nome'] }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div>{{ $conta['admin_email'] }}</div>
                                <div class="opacity-70 flex items-center">
                                    {{ $conta['admin_phone'] ?? '—' }}
                                    @if($zap = $linkZap($conta['admin_phone'] ?? ''))
                                        <a href="{{ $zap }}" target="_blank" title="Chamar no WhatsApp" class="zap-link">{!! $iconeZap !!}</a>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-center">{{ $conta['total_users'] }}</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $tipoClasse = match ($conta['tipo']) {
                                        'cartao'         => 'badge-cartao',
                                        'PIX'            => 'badge-pix',
                                        'PIX_AUTOMATICO' => 'badge-pixauto',
                                        default          => 'badge-trial',
                                    };
                                @endphp
                                <span class="badge-tipo {{ $tipoClasse }}">{{ $conta['tipo'] ?? 'trial' }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-center whitespace-nowrap">
                                @if($conta['status'] === 'trial' && $conta['trial_ends_at'])
                                    {{ \Carbon\Carbon::parse($conta['trial_ends_at'])->format('d/m/Y') }}
                                @elseif($conta['next_charge'])
                                    {{ \Carbon\Carbon::parse($conta['next_charge'])->format('d/m/Y') }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        <tr class="linha-usuarios" id="usuarios-{{ $i }}">
                            <td colspan="6" class="px-6">
                                <div class="usuarios-grid">
                                    @foreach($conta['usuarios'] as $u)
                                        <div class="usuario-card">
                                            <div class="u-nome">{{ $u['nome'] }}@if($u['admin'])<span class="crown">★ admin</span>@endif</div>
                                            <div class="u-tel flex items-center">
                                                {{ $u['telefone'] }}
                                                @if($zap = $linkZap($u['telefone']))
                                                    <a href="{{ $zap }}" target="_blank" title="Chamar no WhatsApp" class="zap-link">{!! $iconeZap !!}</a>
                                                @endif
                                            </div>
                                            <div class="u-email">{{ $u['email'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm opacity-70">
                                Nenhum cadastro desde {{ $inicio->format('d/m/Y') }} ainda.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        document.querySelectorAll('.btn-expand').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var alvo = document.getElementById(btn.dataset.alvo);
                if (!alvo) return;
                btn.classList.toggle('aberto');
                alvo.classList.toggle('aberta');
            });
        });
    </script>
</x-app-layout>
