<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cotação Humana — Comparativo</title>
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
        tr.desconto td { font-weight: bold; color: #0f0f6d; font-size: 18px; border-bottom: none; padding-top: 6px; }
        .selo { display: inline-block; background-color: #e11d48; color: #fff; padding: 4px 10px; border-radius: 14px; font-size: 13px; font-weight: bold; }


        .conteudo { padding: 22px 40px; }
        table.valores { width: 100%; border-collapse: collapse; font-size: 17px; }
        table.valores th { background-color: #0f0f6d; color: #fff; padding: 10px 8px; text-align: right; font-size: 15px; }
        table.valores th.esq { text-align: left; }
        table.valores th.centro, table.valores td.centro { text-align: center; }
        table.valores th.grupo { text-align: center; font-size: 17px; border-bottom: 3px solid #f5a623; }
        table.valores th.grupo-basica { border-left: 4px solid #f5a623; }
        table.valores td.divisa { border-left: 4px solid #f5a623; }
        table.valores td { padding: 10px 8px; border-bottom: 1px solid #c7c9e2; text-align: right; }
        table.valores td.esq { text-align: left; font-size: 16px; }
        table.valores .unit { color: #6b7280; font-size: 12px; }
        table.valores td strong { color: #0f0f6d; font-size: 18px; }
        table.valores tr.total td { border-top: 3px solid #f5a623; border-bottom: none; font-weight: bold; font-size: 20px; padding-top: 12px; color: #0f0f6d; }

        .legenda { font-size: 13px; color: #4b5563; margin-top: 12px; line-height: 1.6; }

        .copay { margin-top: 20px; }
        .copay h3 { font-size: 16px; margin: 0 0 6px 0; color: #0f0f6d; border-left: 6px solid #f5a623; padding-left: 10px; }
        table.grades-wrap { width: 100%; border-collapse: collapse; }
        table.grades-wrap > tr > td, table.grades-wrap td.metade { vertical-align: top; width: 50%; padding-right: 18px; }
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
    <div class="contratacao">Teresina (PI) &mdash; {{ $plano->contratacao === 'pf' ? 'Pessoa Física' : 'PME · Coletivo Empresarial' }} &mdash; Comparativo de Coparticipação</div>
    <div class="detalhes">
        Segmentação: {{ $plano->segmentacao }}
        @if ($tabela->acomodacao !== 'nenhuma')
            &nbsp;•&nbsp; Acomodação: <b style="color:#fff">{{ $acomodacaoLabel }}</b>
        @endif
        <br>
        Abrangência: {{ $plano->abrangencia }}
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
    <table class="valores">
        <thead>
            <tr>
                <th class="esq" rowspan="2">Faixa etária</th>
                <th class="centro" rowspan="2">Vidas</th>
                <th class="grupo" colspan="{{ count($produtos) }}">COPARTICIPAÇÃO COMPLETA</th>
                <th class="grupo grupo-basica" colspan="{{ count($produtos) }}">COPARTICIPAÇÃO BÁSICA</th>
            </tr>
            <tr>
                @foreach ($produtos as $titulo)
                    <th>{{ $titulo }}</th>
                @endforeach
                @foreach ($produtos as $titulo)
                    <th @if ($loop->first) class="grupo-basica" @endif>{{ $titulo }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($linhas as $linha)
                <tr>
                    <td class="esq">{{ $linha['faixa'] }}</td>
                    <td class="centro">{{ $linha['qtd'] }}</td>
                    @foreach ($produtos as $chave => $titulo)
                        <td>
                            <span class="unit">{{ $linha['qtd'] }} × R$ {{ number_format($linha[$chave], 2, ',', '.') }}</span><br>
                            <strong>R$ {{ number_format($linha[$chave] * $linha['qtd'], 2, ',', '.') }}</strong>
                        </td>
                    @endforeach
                    @foreach ($produtos as $chave => $titulo)
                        @php $valorBasica = $precosBasica[$linha['faixa']][$chave]; @endphp
                        <td @if ($loop->first) class="divisa" @endif>
                            <span class="unit">{{ $linha['qtd'] }} × R$ {{ number_format($valorBasica, 2, ',', '.') }}</span><br>
                            <strong>R$ {{ number_format($valorBasica * $linha['qtd'], 2, ',', '.') }}</strong>
                        </td>
                    @endforeach
                </tr>
            @endforeach
            <tr class="total">
                <td class="esq">TOTAL</td>
                <td class="centro">{{ $totalVidas }}</td>
                @foreach ($produtos as $chave => $titulo)
                    <td>R$ {{ number_format($totaisCompleta[$chave], 2, ',', '.') }}</td>
                @endforeach
                @foreach ($produtos as $chave => $titulo)
                    <td @if ($loop->first) class="divisa" @endif>R$ {{ number_format($totaisBasica[$chave], 2, ',', '.') }}</td>
                @endforeach
            </tr>
            {{-- Linhas "com desconto": só nas colunas da copay que a promo cobre --}}
            @foreach ($promocoes as $promo)
                @if ($promo['pct'] !== null)
                <tr class="desconto">
                    <td class="esq"><span class="selo">{{ $promo['rotulo'] }}</span></td>
                    <td class="centro"></td>
                    @foreach ($produtos as $chave => $titulo)
                        <td>{{ in_array($promo['copay'], [null, 'completa'], true) ? 'R$ ' . number_format($totaisCompleta[$chave] * (1 - $promo['pct'] / 100), 2, ',', '.') : '—' }}</td>
                    @endforeach
                    @foreach ($produtos as $chave => $titulo)
                        <td @if ($loop->first) class="divisa" @endif>{{ in_array($promo['copay'], [null, 'basica'], true) ? 'R$ ' . number_format($totaisBasica[$chave] * (1 - $promo['pct'] / 100), 2, ',', '.') : '—' }}</td>
                    @endforeach
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>

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
