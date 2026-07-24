@if(empty($resultados))
    <div class="text-white text-center py-4 text-sm">
        Nenhum dado encontrado para a cidade e faixas selecionadas.
    </div>
@else
@php
$colFaixa   = '18%';
$colQtd     = '12%';
$colApartU  = '17%';
$colEnferU  = '17%';
$colApartT  = '18%';
$colEnferT  = '18%';

$sepStyle   = 'border-left:2px solid rgba(255,255,255,0.5);';
$headerBase = 'color:white;font-weight:700;font-size:.85rem;text-align:center;padding:4px 3px;';
$cellBase   = 'color:white;font-size:.88rem;text-align:center;padding:4px 3px;';
@endphp

<style>
    @media (max-width: 480px) {
        .carousel-slide { padding: 0 22px !important; }
    }
</style>

<div class="relative" style="overflow:hidden;">

    {{-- Seta esquerda --}}
    <button type="button" class="carousel-prev"
            style="position:absolute;left:4px;top:50%;transform:translateY(-50%);z-index:10;background:rgba(255,255,255,0.25);border:2px solid rgba(255,255,255,0.6);color:white;font-size:1.4rem;width:30px;height:30px;border-radius:50%;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center;">
        &#8249;
    </button>

    {{-- Seta direita --}}
    <button type="button" class="carousel-next"
            style="position:absolute;right:4px;top:50%;transform:translateY(-50%);z-index:10;background:rgba(255,255,255,0.25);border:2px solid rgba(255,255,255,0.6);color:white;font-size:1.4rem;width:30px;height:30px;border-radius:50%;cursor:pointer;line-height:1;display:flex;align-items:center;justify-content:center;">
        &#8250;
    </button>

    {{-- Track --}}
    <div class="carousel-track" style="display:flex;transition:transform 0.35s ease;will-change:transform;">

        @foreach($resultados as $i => $cenario)
        <div class="carousel-slide" data-label="{{ $cenario['label'] }}" style="min-width:100%;box-sizing:border-box;padding:0 42px;">
        <div style="border:2px solid white;border-radius:1.5rem;background:rgba(254,254,254,0.18);backdrop-filter:blur(15px);box-shadow:0 20px 25px -5px rgba(0,0,0,.1);padding:8px 10px;">

            {{-- Título --}}
            <div style="border:1px solid white;border-radius:.75rem;color:white;text-align:center;font-weight:700;font-size:.95rem;background:rgba(254,254,254,0.18);backdrop-filter:blur(15px);text-transform:uppercase;padding:5px 8px;margin-bottom:10px;">
                {{ $cenario['label'] }}
            </div>

            @php
                $totalApart = 0;
                $totalEnfer = 0;
                $totalVidas = 0;
            @endphp

            {{-- Tabela de dados: rola na horizontal se a tela for estreita demais para as 6 colunas --}}
            <div style="width:100%;overflow-x:auto;">
            <div style="width:100%;min-width:300px;">

                {{-- Cabeçalho grupo (linha 1) --}}
                <div style="display:flex;align-items:stretch;">
                    <div style="flex-basis:{{ $colFaixa }};{{ $headerBase }}"></div>
                    <div style="flex-basis:{{ $colQtd }};{{ $headerBase }}"></div>
                    <div style="flex-basis:calc({{ $colApartU }} + {{ $colEnferU }});{{ $headerBase }}border-left:2px solid rgba(255,255,255,0.5);background:rgba(255,255,255,0.08);border-radius:6px 6px 0 0;">
                        Preços Unitários
                    </div>
                    <div style="flex-basis:calc({{ $colApartT }} + {{ $colEnferT }});{{ $headerBase }}{{ $sepStyle }}background:rgba(255,255,255,0.15);border-radius:6px 6px 0 0;">
                        Total
                    </div>
                </div>

                {{-- Cabeçalho sub-colunas (linha 2) --}}
                <div style="display:flex;align-items:stretch;border-bottom:1px solid rgba(255,255,255,0.4);padding-bottom:3px;margin-bottom:3px;">
                    <div style="flex-basis:{{ $colFaixa }};{{ $headerBase }}">Faixa</div>
                    <div style="flex-basis:{{ $colQtd }};{{ $headerBase }}">Qtd.</div>
                    <div style="flex-basis:{{ $colApartU }};{{ $headerBase }}border-left:2px solid rgba(255,255,255,0.5);background:rgba(255,255,255,0.06);">Apart.</div>
                    <div style="flex-basis:{{ $colEnferU }};{{ $headerBase }}background:rgba(255,255,255,0.06);">Enfer.</div>
                    <div style="flex-basis:{{ $colApartT }};{{ $headerBase }}{{ $sepStyle }}background:rgba(255,255,255,0.12);">Apart.</div>
                    <div style="flex-basis:{{ $colEnferT }};{{ $headerBase }}background:rgba(255,255,255,0.12);">Enfer.</div>
                </div>

                {{-- Linhas de dados --}}
                @foreach($cenario['rows'] as $row)
                @php
                    $faixa = match($row['faixa_etaria']) {
                        'Faixa 1'  => '0-18',
                        'Faixa 2'  => '19-23',
                        'Faixa 3'  => '24-28',
                        'Faixa 4'  => '29-33',
                        'Faixa 5'  => '34-38',
                        'Faixa 6'  => '39-43',
                        'Faixa 7'  => '44-48',
                        'Faixa 8'  => '49-53',
                        'Faixa 9'  => '54-58',
                        'Faixa 10' => '59+',
                        default    => $row['faixa_etaria'],
                    };
                    $totalApart += $row['total_apartamento'];
                    $totalEnfer += $row['total_enfermaria'];
                    $totalVidas += (int)$row['quantidade'];
                @endphp
                <div style="display:flex;align-items:center;margin-bottom:2px;">
                    <div style="flex-basis:{{ $colFaixa }};{{ $cellBase }}font-weight:700;">{{ $faixa }}</div>
                    <div style="flex-basis:{{ $colQtd }};{{ $cellBase }}font-weight:700;">{{ $row['quantidade'] }}</div>
                    <div style="flex-basis:{{ $colApartU }};{{ $cellBase }}border-left:2px solid rgba(255,255,255,0.5);background:rgba(255,255,255,0.06);">
                        {{ number_format($row['valor_apartamento'], 2, ',', '.') }}
                    </div>
                    <div style="flex-basis:{{ $colEnferU }};{{ $cellBase }}background:rgba(255,255,255,0.06);">
                        {{ number_format($row['valor_enfermaria'], 2, ',', '.') }}
                    </div>
                    <div style="flex-basis:{{ $colApartT }};{{ $cellBase }}{{ $sepStyle }}background:rgba(255,255,255,0.12);">
                        {{ number_format($row['total_apartamento'], 2, ',', '.') }}
                    </div>
                    <div style="flex-basis:{{ $colEnferT }};{{ $cellBase }}background:rgba(255,255,255,0.12);">
                        {{ number_format($row['total_enfermaria'], 2, ',', '.') }}
                    </div>
                </div>
                @if(!$loop->last)
                <div style="height:1px;background:rgba(255,255,255,0.2);margin:2px 0;"></div>
                @endif
                @endforeach

                {{-- Linha de totais gerais --}}
                <div style="display:flex;align-items:center;border-top:2px solid white;margin-top:4px;padding-top:4px;">
                    <div style="flex-basis:{{ $colFaixa }};color:white;font-size:.85rem;font-weight:700;text-align:center;padding:6px 3px;">TOTAL</div>
                    <div style="flex-basis:{{ $colQtd }};color:white;font-size:1.15rem;font-weight:800;text-align:center;padding:6px 3px;background:rgba(180,130,0,0.45);border-radius:6px;border:1px solid rgba(251,191,36,0.5);">{{ $totalVidas }}</div>
                    <div style="flex-basis:{{ $colApartU }};{{ $cellBase }}border-left:2px solid rgba(255,255,255,0.5);background:rgba(255,255,255,0.06);"></div>
                    <div style="flex-basis:{{ $colEnferU }};{{ $cellBase }}background:rgba(255,255,255,0.06);"></div>
                    <div style="flex-basis:{{ $colApartT }};{{ $sepStyle }}background:rgba(0,150,70,0.5);border:1px solid rgba(0,230,118,0.5);border-radius:6px;color:white;font-size:1.05rem;font-weight:800;text-align:center;padding:6px 3px;">
                        R$ {{ number_format($totalApart, 2, ',', '.') }}
                    </div>
                    <div style="flex-basis:{{ $colEnferT }};background:rgba(180,0,30,0.5);border:1px solid rgba(255,23,68,0.5);border-radius:6px;color:white;font-size:1.05rem;font-weight:800;text-align:center;padding:6px 3px;">
                        R$ {{ number_format($totalEnfer, 2, ',', '.') }}
                    </div>
                </div>

            </div>
            </div>{{-- /tabela --}}

            {{-- Botão Gerar Imagem --}}
            <button type="button"
                    data-coparticipacao="{{ $cenario['copart'] }}"
                    data-odonto="{{ $cenario['odonto'] }}"
                    data-label="{{ $cenario['label'] }}"
                    style="display:block;width:100%;margin-top:10px;padding:7px 8px;background:#f87171;color:white;border:none;border-radius:.5rem;font-size:.9rem;cursor:pointer;font-weight:600;"
                    class="btn-gerar-cenario">
                Gerar Imagem
            </button>

        </div>
        </div>
        @endforeach

    </div>{{-- /carousel-track --}}

    {{-- Dots + contador --}}
    <div style="display:flex;justify-content:center;gap:8px;margin-top:10px;">
        @foreach($resultados as $i => $cenario)
        <button type="button" class="carousel-dot" data-index="{{ $i }}"
                style="width:10px;height:10px;border-radius:50%;border:none;cursor:pointer;padding:0;transition:background .2s;background:{{ $i === 0 ? 'rgba(255,255,255,1)' : 'rgba(255,255,255,0.3)' }};"></button>
        @endforeach
    </div>
    <div style="text-align:center;color:rgba(255,255,255,0.7);font-size:.82rem;margin-top:4px;margin-bottom:4px;">
        <span class="carousel-counter">1 / {{ count($resultados) }}</span>
    </div>

</div>{{-- /relative --}}
@endif
