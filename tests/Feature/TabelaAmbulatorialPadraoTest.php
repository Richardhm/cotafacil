<?php

/**
 * Tabela Completa Ambulatorial (03/09/2026): passou a usar as MESMAS views do
 * ambulatorial do /dashboard (cotacao-ambulatorial{1-4}) — as antigas
 * modelotabelaambulatorial1-4 eram 4 cópias do layout 1 (laranja), sem cidade,
 * sem copart e sem carências. Este teste renderiza os 4 layouts com o conjunto
 * de variáveis que o tabelaCompletaAmbulatorial() envia.
 */

function renderAmbulatorialTabelaPdf(): object
{
    return (object) [
        'linha01' => 'Coparticipação somente em Terapias',
        'linha02' => 'Terapias Especiais - R$ 78,87 | Demais Terapias - R$ 24,27',
        'linha03' => 'Adesão de R$ 35,00 por contrato',
        'consultas_eletivas_total' => 'R$ 25,42',
        'consultas_de_urgencia_total' => 'R$ 43,63',
        'exames_simples_total' => 'R$ 45,79',
        'exames_complexos_total' => 'R$ 114,48',
        'terapias_especiais_total' => 'R$ 78,87',
        'demais_terapias_total' => 'R$ 24,27',
        'internacoes_total' => 'Isento',
        'cirurgia_total' => 'Isento',
        'consultas_eletivas_parcial' => '-',
        'consultas_de_urgencia_parcial' => '-',
        'exames_simples_parcial' => '-',
        'exames_complexos_parcial' => '-',
        'terapias_especiais_parcial' => 'R$ 78,87',
        'demais_terapias_parcial' => 'R$ 24,27',
        'internacoes_parcial' => 'Isento',
        'cirurgia_parcial' => 'Isento',
    ];
}

function renderAmbulatorialTabela(int $layout, array $flags = []): string
{
    $dados = collect(array_map(function ($faixa) {
        return (object) [
            'faixaEtaria'    => (object) ['nome' => $faixa],
            'acomodacao_id'  => 3,
            'valor'          => 142.54,
            'odonto'         => 1,
            'coparticipacao' => 1,
            'quantidade'     => 1,
        ];
    }, ['00 a 18', '19 a 23', '24 a 28']));

    return view("cotacao.cotacao-ambulatorial{$layout}", array_merge([
        'apelido_plano'         => null,
        'rotulo_com_copart'     => null,
        'rotulo_copart_parcial' => null,
        'com_coparticipacao'    => 1,
        'sem_coparticipacao'    => 1,
        'image'                 => '',
        'dados'                 => $dados,
        'codigo'                => null,
        'folder'                => '',
        'pdf'                   => renderAmbulatorialTabelaPdf(),
        'plano_nome'            => 'Individual',
        'linha_01'              => 'Terapias Especiais - R$ 78,87',
        'linha_02'              => 'Demais Terapias - R$ 24,27',
        'nome'                  => 'Richard',
        'desconto'              => 0,
        'valor_desconto'        => 0,
        'texto_desconto'        => '',
        'cidade'                => 'Goiânia',
        'plano'                 => 'Individual',
        'odonto_frase'          => ' c/ Odonto',
        'administradora'        => 'Hapvida',
        'frase'                 => 'Ambulatorial  c/ Odonto',
        'carencia'              => 1,
        'status_desconto'       => 0,
        'odonto'                => 1,
        'celular'               => '(62) 9 9358-1475',
        'linhas'                => 10,
        'corretora'             => null,
    ], $flags))->render();
}

test('os 4 layouts do ambulatorial renderizam com cidade, faixas e valores', function () {
    foreach ([1, 2, 3, 4] as $layout) {
        $html = renderAmbulatorialTabela($layout);
        expect($html)->toContain('Goiânia');
        expect($html)->toContain('00 a 18');
        expect($html)->toContain('142,54');
    }
});

// Tabela completa (08/09/2026): sem CARÊNCIAS DE SAÚDE e sem a linha "** adesão"
// — o controller manda carencia=0 e linha03 null; as views devem respeitar.
test('com carencia 0 e linha03 nula some o bloco de carencias e a frase **', function () {
    foreach ([1, 2, 3, 4] as $layout) {
        $pdfSemAdesao = renderAmbulatorialTabelaPdf();
        $pdfSemAdesao->linha03 = null;
        $html = renderAmbulatorialTabela($layout, ['carencia' => 0, 'pdf' => $pdfSemAdesao]);
        expect($html)->not->toContain('CARÊNCIAS DE SAÚDE');
        expect($html)->not->toContain('** ');
        expect($html)->toContain('Goiânia');
        expect($html)->toContain('142,54');
        expect($html)->toContain('somente em Terapias'); // tabelinhas de copart continuam
    }
});
