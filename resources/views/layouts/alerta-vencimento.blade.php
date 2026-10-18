@php
    /** Barra de aviso de vencimento do PIX (18/10/2026): aparece só para o
     *  ADMINISTRADOR da conta quando a assinatura PIX/PIX Automático vence em
     *  até Assinatura::ALERTA_PIX_DIAS dias, para ele regularizar antes do
     *  bloqueio travar a equipe toda. CSS puro no <style> (classe nova do
     *  Tailwind não existe no build de produção). */
    $assinaturaAlerta = auth()->user()?->assinaturas;
    $mostrarAlertaPix = $assinaturaAlerta
        && auth()->id() === $assinaturaAlerta->user_id
        && $assinaturaAlerta->pixAVencer();
@endphp

@if($mostrarAlertaPix)
    @php
        $diasRestantes = (int) now()->startOfDay()->diffInDays($assinaturaAlerta->next_charge->copy()->startOfDay());
    @endphp
    <style>
        .alerta-pix-barra {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px 16px;
            padding: 10px 16px;
            background: linear-gradient(90deg, #b45309, #d97706);
            border-bottom: 2px solid #f59e0b;
            color: #fffbeb;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
        }
        .alerta-pix-barra .alerta-pix-botao {
            display: inline-block;
            padding: 6px 16px;
            background: #fffbeb;
            color: #92400e;
            font-weight: 700;
            border-radius: 6px;
            text-decoration: none;
            white-space: nowrap;
            transition: transform .15s ease;
        }
        .alerta-pix-barra .alerta-pix-botao:hover {
            transform: scale(1.05);
        }
        @media (max-width: 768px) {
            .alerta-pix-barra { font-size: 13px; padding: 8px 10px; }
        }
    </style>
    <div class="alerta-pix-barra">
        <span>
            ⚠️ Sua assinatura vence
            @if($diasRestantes === 0)
                <strong>HOJE</strong> ({{ $assinaturaAlerta->next_charge->format('d/m/Y') }}).
            @else
                dia <strong>{{ $assinaturaAlerta->next_charge->format('d/m/Y') }}</strong>
                ({{ $diasRestantes }} {{ $diasRestantes === 1 ? 'dia' : 'dias' }}).
            @endif
            Regularize agora e evite o bloqueio do sistema para você e sua equipe.
        </span>
        <a href="{{ route('assinatura.edit') }}" class="alerta-pix-botao">Regularizar agora</a>
    </div>
@endif
