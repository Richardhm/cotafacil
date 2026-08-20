<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cotação Humana</title>
    <style>
        @page { margin: 0; }
        @font-face { font-family: 'Roboto'; src: url('{{ public_path("fonts/Roboto-Regular.ttf") }}') format('truetype'); font-weight: normal; font-style: normal; }
        @font-face { font-family: 'Roboto'; src: url('{{ public_path("fonts/Roboto-Bold.ttf") }}') format('truetype'); font-weight: bold; font-style: normal; }
        /* Fundo SEMPRE explícito: o gs gera PNG com alpha (pngalpha) e fundo
           transparente vira preto nos visualizadores — texto escuro some. */
        html, body { margin: 0; padding: 0; font-family: 'Roboto', sans-serif; color: #1f2937; background-color: #ffffff; }

        /* Palheta da marca Humana (amostrada da logo): azul-marinho + laranja */
        .navy   { color: #0f0f6d; }

        .topo { padding: 26px 40px 18px 40px; }
        .topo table { width: 100%; border-collapse: collapse; }
        .topo .logo-humana img { height: 78px; }
        .topo .logo-corretora { text-align: right; }
        .topo .logo-corretora img { height: 82px; }

        .faixa-titulo { background-color: #0f0f6d; color: #fff; padding: 20px 40px 18px 40px; border-top: 6px solid #f5a623; }
        .faixa-titulo .plano { font-size: 34px; font-weight: bold; }
        .faixa-titulo .contratacao { font-size: 18px; color: #f5a623; font-weight: bold; margin-top: 2px; }
        .faixa-titulo .detalhes { font-size: 20px; margin-top: 12px; line-height: 1.7; color: #e5e7f5; }

        .conteudo { padding: 26px 40px; }
        table.valores { width: 100%; border-collapse: collapse; font-size: 20px; }
        table.valores th { background-color: #0f0f6d; color: #fff; padding: 14px 10px; text-align: right; font-size: 18px; }
        table.valores th.esq { text-align: left; }
        table.valores th.centro, table.valores td.centro { text-align: center; }
        table.valores td { padding: 12px 10px; border-bottom: 1px solid #c7c9e2; text-align: right; }
        table.valores td.esq { text-align: left; font-size: 19px; }
        table.valores .unit { color: #6b7280; font-size: 14px; }
        table.valores td strong { color: #0f0f6d; font-size: 22px; }
        table.valores tr.total td { border-top: 3px solid #f5a623; border-bottom: none; font-weight: bold; font-size: 26px; padding-top: 16px; color: #0f0f6d; }

        .legenda { font-size: 14px; color: #4b5563; margin-top: 14px; line-height: 1.6; }

        .copay { margin-top: 26px; }
        .copay h3 { font-size: 19px; margin: 0 0 8px 0; color: #0f0f6d; border-left: 6px solid #f5a623; padding-left: 10px; }
        table.grade { width: 100%; border-collapse: collapse; font-size: 16px; }
        table.grade td { padding: 7px 10px; border-bottom: 1px solid #e5e7eb; }
        table.grade td:last-child { text-align: right; font-weight: bold; color: #0f0f6d; }

        .rodape { position: fixed; bottom: 0; left: 0; right: 0; background-color: #0f0f6d; padding: 18px 40px; font-size: 14px; color: #c7c9e2; border-top: 5px solid #f5a623; }
        .rodape .corretor { font-size: 19px; font-weight: bold; color: #ffffff; }
    </style>
</head>
<body>

{{-- Topo branco com as duas marcas: Humana (operadora) e a corretora --}}
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
    <div class="contratacao">Teresina (PI) &mdash; {{ $plano->contratacao === 'pf' ? 'Pessoa Física' : 'PME · Coletivo Empresarial' }}</div>
    <div class="detalhes">
        Segmentação: {{ $plano->segmentacao }}<br>
        @if ($tabela->acomodacao !== 'nenhuma')
            Acomodação: <b style="color:#fff">{{ $acomodacaoLabel }}</b> &nbsp;•&nbsp;
        @endif
        Coparticipação: <b style="color:#fff">{{ $copayLabel }}</b><br>
        Abrangência: {{ $plano->abrangencia }}
        @if ($tabela->registro_ans) &nbsp;•&nbsp; ANS nº {{ $tabela->registro_ans }} @endif
        @if ($tabela->vigencia_inicio) &nbsp;•&nbsp; Vigência {{ $tabela->vigencia_inicio->format('d/m/Y') }} a {{ $tabela->vigencia_fim->format('d/m/Y') }} @endif
    </div>
</div>

<div class="conteudo">
    <table class="valores">
        <thead>
            <tr>
                <th class="esq">Faixa etária</th>
                <th class="centro">Vidas</th>
                @foreach ($produtos as $titulo)
                    <th>{{ $titulo }}</th>
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
                </tr>
            @endforeach
            <tr class="total">
                <td class="esq">TOTAL</td>
                <td class="centro">{{ $totalVidas }}</td>
                @foreach ($produtos as $chave => $titulo)
                    <td>R$ {{ number_format($totais[$chave], 2, ',', '.') }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>

    @if ($temCombos)
        <div class="legenda">
            <b class="navy">Valor Saúde</b> = plano sem odonto &nbsp;•&nbsp;
            <b class="navy">Combo Essencial</b> = saúde + odonto de urgência/emergência e prevenção &nbsp;•&nbsp;
            <b class="navy">Combo Odonto Pleno</b> = saúde + odonto completo.
        </div>
    @endif

    @if ($gradeCopay)
        <div class="copay">
            <h3>Taxas de coparticipação ({{ $copayLabel }})</h3>
            <table class="grade">
                @foreach ($gradeCopay as [$procedimento, $valor])
                    <tr>
                        <td>{{ $procedimento }}</td>
                        <td>{{ $valor }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif
</div>

<div class="rodape">
    <span class="corretor">{{ $corretor['nome'] }}</span>
    @if ($corretor['celular']) &nbsp;•&nbsp; {{ $corretor['celular'] }} @endif
    <br>
    Cotação gerada em {{ $geradoEm }}. Valores da tabela oficial Humana vigente, sujeitos a confirmação da operadora na contratação.
</div>

</body>
</html>
