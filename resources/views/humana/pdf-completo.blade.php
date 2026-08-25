<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cotação Humana — Completa</title>
    <style>
        @page { margin: 0; }
        @font-face { font-family: 'Roboto'; src: url('{{ public_path("fonts/Roboto-Regular.ttf") }}') format('truetype'); font-weight: normal; font-style: normal; }
        @font-face { font-family: 'Roboto'; src: url('{{ public_path("fonts/Roboto-Bold.ttf") }}') format('truetype'); font-weight: bold; font-style: normal; }
        /* Fundo SEMPRE explícito: pngalpha transforma transparente em preto */
        html, body { margin: 0; padding: 0; font-family: 'Roboto', sans-serif; color: #1f2937; background-color: #ffffff; }
        .navy { color: #0f0f6d; }

        .topo { padding: 22px 40px 14px 40px; }
        .topo table { width: 100%; border-collapse: collapse; }
        .topo .logo-humana img { height: 70px; }
        .topo .logo-corretora { text-align: right; }
        .topo .logo-corretora img { height: 74px; }

        .faixa-titulo { background-color: #0f0f6d; color: #fff; padding: 16px 40px 14px 40px; border-top: 6px solid #f5a623; }
        .faixa-titulo .plano { font-size: 30px; font-weight: bold; }
        .faixa-titulo .contratacao { font-size: 17px; color: #f5a623; font-weight: bold; margin-top: 2px; }
        .faixa-titulo .detalhes { font-size: 17px; margin-top: 8px; line-height: 1.6; color: #e5e7f5; }
        .promocao { background-color: #f5a623; color: #0f0f6d; padding: 10px 40px; font-weight: bold; font-size: 17px; line-height: 1.5; }
        tr.desconto td { font-weight: bold; color: #0f0f6d; font-size: 16px; border-bottom: none; padding-top: 6px; }
        .selo { display: inline-block; background-color: #e11d48; color: #fff; padding: 4px 10px; border-radius: 14px; font-size: 12px; font-weight: bold; }


        .conteudo { padding: 22px 40px; }
        .bloco-produto { margin-bottom: 26px; }
        .bloco-produto h2 { font-size: 20px; margin: 0 0 8px 0; color: #0f0f6d; border-left: 6px solid #f5a623; padding-left: 10px; }

        table.valores { width: 100%; border-collapse: collapse; font-size: 16px; }
        table.valores th { background-color: #0f0f6d; color: #fff; padding: 9px 8px; text-align: right; font-size: 14px; }
        table.valores th.esq { text-align: left; }
        table.valores th.centro, table.valores td.centro { text-align: center; }
        table.valores th.grupo { text-align: center; font-size: 16px; border-bottom: 3px solid #f5a623; }
        table.valores th.grupo-basica { border-left: 4px solid #f5a623; }
        table.valores td.divisa { border-left: 4px solid #f5a623; }
        table.valores td { padding: 9px 8px; border-bottom: 1px solid #c7c9e2; text-align: right; }
        table.valores td.esq { text-align: left; font-size: 15px; }
        table.valores .unit { color: #6b7280; font-size: 11px; }
        table.valores td strong { color: #0f0f6d; font-size: 16px; }
        table.valores tr.total td { border-top: 3px solid #f5a623; border-bottom: none; font-weight: bold; font-size: 17px; padding-top: 10px; color: #0f0f6d; }

        .legenda { font-size: 13px; color: #4b5563; margin-top: 4px; line-height: 1.6; }

        .copay { margin-top: 18px; }
        .copay h3 { font-size: 16px; margin: 0 0 6px 0; color: #0f0f6d; border-left: 6px solid #f5a623; padding-left: 10px; }
        table.grades-wrap { width: 100%; border-collapse: collapse; }
        table.grades-wrap td.metade { vertical-align: top; width: 50%; padding-right: 18px; }
        table.grade { width: 100%; border-collapse: collapse; font-size: 13px; }
        table.grade td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; }
        table.grade td:last-child { text-align: right; font-weight: bold; color: #0f0f6d; }

        .rodape { position: fixed; bottom: 0; left: 0; right: 0; background-color: #0f0f6d; padding: 14px 40px; font-size: 13px; color: #c7c9e2; border-top: 5px solid #f5a623; }
        .rodape .corretor { font-size: 17px; font-weight: bold; color: #ffffff; }
    </style>
</head>
<body>

<div class="topo">
    <table>
        <tr>
            <td class="logo-humana"><img src="{{ public_path('images/humana/logo-humana.png') }}" alt="Humana Saúde"></td>
            <td class="logo-corretora"><img src="{{ public_path('images/humana/logo-teresina-corretora.png') }}" alt="Teresina Corretora de Planos de Saúde"></td>
        </tr>
    </table>
</div>

<div class="faixa-titulo">
    <div class="plano">{{ $plano->nome }}</div>
    <div class="contratacao">Teresina (PI) &mdash; {{ $plano->contratacao === 'pf' ? 'Pessoa Física' : 'PME · Coletivo Empresarial' }} &mdash; Cotação Completa</div>
    <div class="detalhes">
        Segmentação: {{ $plano->segmentacao }} &nbsp;•&nbsp; Abrangência: {{ $plano->abrangencia }}<br>
        @foreach ($ansPorAcomodacao as $acomLabel => $ans)
            ANS {{ $acomLabel }}: {{ $ans }} @if (!$loop->last) &nbsp;•&nbsp; @endif
        @endforeach
        @if ($tabela->vigencia_inicio) &nbsp;•&nbsp; Vigência {{ $tabela->vigencia_inicio->format('d/m/Y') }} a {{ $tabela->vigencia_fim->format('d/m/Y') }} @endif
    </div>
</div>

@if (!empty($promocoes))
<div class="promocao">
    @foreach ($promocoes as $promo)
        <div>PROMOÇÃO{{ $promo['copay'] ? ' (Coparticipação ' . ($promo['copay'] === 'basica' ? 'Básica' : 'Completa') . ')' : '' }}: {{ $promo['texto'] }}</div>
    @endforeach
</div>
@endif

<div class="conteudo">
    @foreach ($produtos as $chave => $titulo)
        <div class="bloco-produto">
            <h2>{{ $titulo }}</h2>
            <table class="valores">
                <thead>
                    <tr>
                        <th class="esq" rowspan="2">Faixa etária</th>
                        <th class="centro" rowspan="2">Vidas</th>
                        <th class="grupo" colspan="2">COPARTICIPAÇÃO COMPLETA</th>
                        <th class="grupo grupo-basica" colspan="2">COPARTICIPAÇÃO BÁSICA</th>
                    </tr>
                    <tr>
                        <th>{{ $labels['apartamento'] }}</th>
                        <th>{{ $labels['enfermaria'] }}</th>
                        <th class="grupo-basica">{{ $labels['apartamento'] }}</th>
                        <th>{{ $labels['enfermaria'] }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($linhas as $linha)
                        <tr>
                            <td class="esq">{{ $linha['faixa'] }}</td>
                            <td class="centro">{{ $linha['qtd'] }}</td>
                            @foreach (['completa/apartamento', 'completa/enfermaria', 'basica/apartamento', 'basica/enfermaria'] as $combo)
                                @php $valor = $precosPorCombo[$combo][$linha['faixa']][$chave]; @endphp
                                <td @if ($combo === 'basica/apartamento') class="divisa" @endif>
                                    <span class="unit">{{ $linha['qtd'] }} × R$ {{ number_format($valor, 2, ',', '.') }}</span><br>
                                    <strong>R$ {{ number_format($valor * $linha['qtd'], 2, ',', '.') }}</strong>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    <tr class="total">
                        <td class="esq">TOTAL</td>
                        <td class="centro">{{ $totalVidas }}</td>
                        @foreach (['completa/apartamento', 'completa/enfermaria', 'basica/apartamento', 'basica/enfermaria'] as $combo)
                            <td @if ($combo === 'basica/apartamento') class="divisa" @endif>
                                R$ {{ number_format($totaisPorCombo[$combo][$chave], 2, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>
                    {{-- Linhas "com desconto": só nas colunas da copay que a promo cobre --}}
                    @foreach ($promocoes as $promo)
                        @if ($promo['pct'] !== null)
                        <tr class="desconto">
                            <td class="esq"><span class="selo">{{ $promo['rotulo'] }}</span></td>
                            <td class="centro"></td>
                            @foreach (['completa/apartamento', 'completa/enfermaria', 'basica/apartamento', 'basica/enfermaria'] as $combo)
                                @php $copayCombo = explode('/', $combo)[0]; @endphp
                                <td @if ($combo === 'basica/apartamento') class="divisa" @endif>{{ in_array($promo['copay'], [null, $copayCombo], true) ? 'R$ ' . number_format($totaisPorCombo[$combo][$chave] * (1 - $promo['pct'] / 100), 2, ',', '.') : '—' }}</td>
                            @endforeach
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="legenda">
        <b class="navy">Valor Saúde</b> = plano sem odonto &nbsp;•&nbsp;
        <b class="navy">Combo Essencial</b> = saúde + odonto de urgência/emergência e prevenção &nbsp;•&nbsp;
        <b class="navy">Combo Odonto Pleno</b> = saúde + odonto completo.
    </div>

    <div class="copay">
        <table class="grades-wrap">
            <tr>
                <td class="metade">
                    <h3>Taxas — Coparticipação Completa</h3>
                    <table class="grade">
                        @foreach ($gradeCompleta as [$procedimento, $valor])
                            <tr><td>{{ $procedimento }}</td><td>{{ $valor }}</td></tr>
                        @endforeach
                    </table>
                </td>
                <td class="metade">
                    <h3>Taxas — Coparticipação Básica</h3>
                    <table class="grade">
                        @foreach ($gradeBasica as [$procedimento, $valor])
                            <tr><td>{{ $procedimento }}</td><td>{{ $valor }}</td></tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="rodape">
    <span class="corretor">{{ $corretor['nome'] }}</span>
    @if ($corretor['celular']) &nbsp;•&nbsp; {{ $corretor['celular'] }} @endif
    <br>
    Cotação gerada em {{ $geradoEm }}. Valores da tabela oficial Humana vigente, sujeitos a confirmação da operadora na contratação.
</div>

</body>
</html>
