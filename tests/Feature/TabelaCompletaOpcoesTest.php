<?php

/**
 * Opções do Gerar Imagem da /tabela_completa (03/09/2026): o modal manda
 * com/sem coparticipação e apartamento/enfermaria, e os modelotabela1-4
 * (que tinham as flags HARDCODED em 1) passam a respeitá-las — mesma lógica
 * do /dashboard. A tabelinha "somente em Terapias" (partials/copart) já era
 * gateada por $sem_coparticipacao.
 */

function dadoTabelaCompleta(string $faixa, int $acomodacao, float $valor, int $copar): object
{
    return (object) [
        'faixaEtaria'    => (object) ['nome' => $faixa],
        'acomodacao_id'  => $acomodacao,
        'valor'          => $valor,
        'odonto'         => 1,
        'coparticipacao' => $copar,
        'quantidade'     => 1,
    ];
}

function renderModeloTabela(int $layout, array $flags = []): string
{
    $dados = collect([
        dadoTabelaCompleta('00 a 18 anos', 1, 100.11, 1),
        dadoTabelaCompleta('00 a 18 anos', 2, 90.22, 1),
        dadoTabelaCompleta('00 a 18 anos', 1, 130.33, 0),
        dadoTabelaCompleta('00 a 18 anos', 2, 115.44, 0),
    ]);

    return view("cotacao.modelotabela{$layout}", array_merge([
        'dados'                 => $dados,
        'image'                 => '',
        'nome'                  => 'Richard',
        'celular'               => '(85) 9 9999-9999',
        'cidade_nome'           => 'Porto Alegre',
        'frase'                 => 'Individual/Com Odonto',
        'pdf'                   => (object) [
            'linha01' => 'Coparticipação somente em Terapias',
            'linha02' => '',
            'linha03' => '',
            'consultas_eletivas_total' => 'R$ 20,00',
            'consultas_de_urgencia_total' => 'R$ 55,00',
            'exames_simples_total' => 'R$ 15,00',
            'exames_complexos_total' => 'R$ 55,00',
            'terapias_especiais_total' => 'R$ 79,00',
            'demais_terapias_total' => 'R$ 42,00',
            'internacoes_total' => 'Isento',
            'cirurgia_total' => 'Isento',
            'consultas_eletivas_parcial' => '-',
            'consultas_de_urgencia_parcial' => '-',
            'exames_simples_parcial' => '-',
            'exames_complexos_parcial' => '-',
            'terapias_especiais_parcial' => 'R$ 79,00',
            'demais_terapias_parcial' => 'R$ 42,00',
            'internacoes_parcial' => 'Isento',
            'cirurgia_parcial' => 'Isento',
        ],
        'quantidade_copar'      => 1,
        'status_excecao'        => false,
        'linha_01'              => '',
        'linha_02'              => '',
        'apelido_plano'         => null,
        'rotulo_com_copart'     => null,
        'rotulo_copart_parcial' => null,
        'apenas_valores'        => 0,
    ], $flags))->render();
}

test('sem flags os 4 layouts mostram tudo (comportamento antigo preservado)', function () {
    foreach ([1, 2, 3, 4] as $layout) {
        $html = renderModeloTabela($layout);
        expect($html)->toContain('COM COPARTICIPAÇÃO');
        expect($html)->toContain('ENFER');
        expect($html)->toContain('APART');
        expect($html)->toContain('100,11');
        expect($html)->toContain('130,33');
    }
});

test('desmarcar Sem Coparticipacao esconde o bloco parcial e a tabelinha somente em Terapias', function () {
    foreach ([1, 2, 3, 4] as $layout) {
        $html = renderModeloTabela($layout, ['sem_coparticipacao' => 0]);
        expect($html)->toContain('COM COPARTICIPAÇÃO');
        // bloco parcial some (rotulo varia por layout: COM COPART PARCIAL * / SEM COPARTICIPAÇÃO *)
        expect($html)->not->toContain('PARCIAL *');
        expect($html)->not->toContain('SEM COPARTICIPAÇÃO *');
        expect($html)->not->toContain('130,33'); // valor do lado sem copar
        // tabelinha "somente em Terapias" some junto
        expect($html)->not->toContain('somente em Terapias');
    }
});

test('desmarcar Com Coparticipacao esconde o bloco com copar', function () {
    foreach ([1, 2, 3, 4] as $layout) {
        $html = renderModeloTabela($layout, ['com_coparticipacao' => 0]);
        expect($html)->not->toContain('100,11'); // valor do lado com copar
        expect($html)->toContain('130,33');
    }
});

test('desmarcar Apartamento esconde a coluna APART nos 4 layouts', function () {
    foreach ([1, 2, 3, 4] as $layout) {
        $html = renderModeloTabela($layout, ['mostrar_apartamento' => 0]);
        expect($html)->not->toContain('APART');
        expect($html)->toContain('ENFER');
        expect($html)->not->toContain('100,11'); // apartamento com copar
        expect($html)->toContain('90,22');       // enfermaria com copar continua
    }
});

// 08/09/2026: tabelinhas de copart centralizadas na tabela completa (mesma
// posição do ambulatorial: margin-left de 490px no wrapper .copart-bloco).
test('as tabelinhas de copart saem centralizadas nos 4 layouts', function () {
    foreach ([1, 2, 3, 4] as $layout) {
        $html = renderModeloTabela($layout);
        expect($html)->toContain('margin-left:490px');
    }
});

test('desmarcar Enfermaria esconde a coluna ENFER nos 4 layouts', function () {
    foreach ([1, 2, 3, 4] as $layout) {
        $html = renderModeloTabela($layout, ['mostrar_enfermaria' => 0]);
        expect($html)->not->toContain('ENFER');
        expect($html)->toContain('APART');
        expect($html)->not->toContain('90,22');
        expect($html)->toContain('100,11');
    }
});
