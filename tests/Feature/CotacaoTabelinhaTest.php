<?php

/**
 * Tabelinha da dashboard (cotacao/cotacao2.blade.php).
 * Bug corrigido em 01/09/2026: cidades sem alguma combinação de coparticipação
 * (ex.: Maringá sem copar parcial) estouravam "Undefined array key 1_sem_copar" —
 * a inicialização criava chaves 'apartamento_com_copar' mas a leitura usava
 * '{acomodacao_id}_com_copar'. Agora as chaves nascem certas e o grupo de
 * colunas sem nenhum dado é escondido inteiro.
 */

function dadoTabelinha(string $faixa, int $acomodacao, float $valor, int $odonto, int $copar): object
{
    return (object) [
        'faixaEtaria'    => (object) ['nome' => $faixa],
        'acomodacao_id'  => $acomodacao,
        'valor'          => $valor,
        'odonto'         => $odonto,
        'coparticipacao' => $copar,
        'quantidade'     => 1,
    ];
}

function renderTabelinha($dados): string
{
    return view('cotacao.cotacao2', [
        'dados'           => collect($dados),
        'imagem_plano'    => 'operadora.png',
        'plano_nome'      => 'Nosso Plano',
        'cidade_nome'     => 'Maringá',
        'status_odonto'   => true,
        'status'          => true,
        'status_desconto' => false,
    ])->render();
}

test('cidade sem copar parcial renderiza sem erro e esconde o grupo Sem Copar', function () {
    // Só coparticipacao=1 (caso Maringá): antes estourava Undefined array key "1_sem_copar"
    $html = renderTabelinha([
        dadoTabelinha('00 a 18 anos', 1, 100.50, 1, 1),
        dadoTabelinha('00 a 18 anos', 2, 90.25, 1, 1),
        dadoTabelinha('00 a 18 anos', 1, 80.00, 0, 1),
        dadoTabelinha('00 a 18 anos', 2, 70.00, 0, 1),
    ]);

    expect($html)->toContain('Com Copar');
    expect($html)->not->toContain('Sem Copar');
    expect($html)->toContain('100,50');
    expect($html)->toContain('90,25');
});

test('cidade so com copar parcial esconde o grupo Com Copar', function () {
    $html = renderTabelinha([
        dadoTabelinha('00 a 18 anos', 1, 120.00, 1, 0),
        dadoTabelinha('00 a 18 anos', 2, 110.00, 1, 0),
        dadoTabelinha('00 a 18 anos', 1, 95.00, 0, 0),
    ]);

    expect($html)->toContain('Sem Copar');
    expect($html)->not->toContain('Com Copar</td>'); // header do grupo ausente ("Sem Copar" contém "Copar")
    expect($html)->toContain('120,00');
});

test('cidade completa mostra os dois grupos como sempre', function () {
    $html = renderTabelinha([
        dadoTabelinha('00 a 18 anos', 1, 100.00, 1, 1),
        dadoTabelinha('00 a 18 anos', 2, 90.00, 1, 1),
        dadoTabelinha('00 a 18 anos', 1, 130.00, 1, 0),
        dadoTabelinha('00 a 18 anos', 2, 115.00, 1, 0),
        dadoTabelinha('00 a 18 anos', 1, 80.00, 0, 1),
        dadoTabelinha('00 a 18 anos', 2, 75.00, 0, 1),
        dadoTabelinha('00 a 18 anos', 1, 105.00, 0, 0),
        dadoTabelinha('00 a 18 anos', 2, 95.00, 0, 0),
    ]);

    expect($html)->toContain('Com Copar');
    expect($html)->toContain('Sem Copar');
    expect($html)->toContain('130,00');
    expect($html)->toContain('95,00');
});

test('faixa com valor faltando dentro de um grupo existente mostra 0,00 em vez de estourar', function () {
    // Grupo Sem Copar existe (faixa 19-23 tem), mas a faixa 00-18 não tem o valor
    $html = renderTabelinha([
        dadoTabelinha('00 a 18 anos', 1, 100.00, 1, 1),
        dadoTabelinha('19 a 23 anos', 1, 110.00, 1, 1),
        dadoTabelinha('19 a 23 anos', 1, 140.00, 1, 0),
    ]);

    expect($html)->toContain('Com Copar');
    expect($html)->toContain('Sem Copar');
    expect($html)->toContain('140,00');
    expect($html)->toContain('0,00');
});
