<?php

/**
 * Opções do Gerar Documento do /hapvida-super-simples (08/09/2026):
 * Completa mostra o bloco "Preços Unitários", Resumida esconde;
 * Apartamento/Enfermaria filtram as colunas APART/ENFER nos blocos de
 * preços e no rodapé de totais — nos 4 modeloempresarial.
 */

function renderModeloEmpresarial(int $layout, array $flags = []): string
{
    $dadosTabela = [
        [
            'faixa_etaria'      => 'Faixa 1',
            'quantidade'        => 1,
            'valor_apartamento' => '111,11',
            'valor_enfermaria'  => '222,22',
            'total_apartamento' => '333,33',
            'total_enfermaria'  => '444,44',
        ],
    ];

    return view("cotacao.modeloempresarial{$layout}", array_merge([
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
        ],
        'quantidade_copar'      => 1,
        'status_excecao'        => false,
        'linha_01'              => '',
        'linha_02'              => '',
        'copart_com'            => 1,
        'copart_sem'            => 1,
        'apenas_valores'        => 0,
        'rotulo_com_copart'     => null,
        'rotulo_copart_parcial' => null,
        'cidade'                => 'Goiânia',
        'label'                 => 'Super Simples c/ Copart c/ Odonto',
        'dadosTabela'           => $dadosTabela,
        'nome'                  => 'Richard',
        'celular'               => '(62) 9 9358-1475',
        'image'                 => '',
    ], $flags))->render();
}

test('sem flags os 4 layouts mostram tudo (comportamento antigo preservado)', function () {
    foreach ([1, 2, 3, 4] as $layout) {
        $html = renderModeloEmpresarial($layout);
        expect($html)->toContain('Preços Unitários');
        expect($html)->toContain('Preços Totais');
        expect($html)->toContain('111,11'); // unitário apartamento
        expect($html)->toContain('222,22'); // unitário enfermaria
        expect($html)->toContain('333,33'); // total apartamento
        expect($html)->toContain('444,44'); // total enfermaria
    }
});

test('Resumida esconde o bloco Precos Unitarios nos 4 layouts', function () {
    foreach ([1, 2, 3, 4] as $layout) {
        $html = renderModeloEmpresarial($layout, ['mostrar_unitarios' => 0]);
        expect($html)->not->toContain('Preços Unitários');
        expect($html)->not->toContain('111,11');
        expect($html)->not->toContain('222,22');
        expect($html)->toContain('Preços Totais');
        expect($html)->toContain('333,33');
        expect($html)->toContain('444,44');
    }
});

test('desmarcar Apartamento esconde a coluna APART nos 4 layouts', function () {
    foreach ([1, 2, 3, 4] as $layout) {
        $html = renderModeloEmpresarial($layout, ['mostrar_apartamento' => 0]);
        expect($html)->not->toContain('APART');
        expect($html)->not->toContain('111,11');
        expect($html)->not->toContain('333,33');
        expect($html)->toContain('ENFER');
        expect($html)->toContain('222,22');
        expect($html)->toContain('444,44');
    }
});

test('desmarcar Enfermaria esconde a coluna ENFER nos 4 layouts', function () {
    foreach ([1, 2, 3, 4] as $layout) {
        $html = renderModeloEmpresarial($layout, ['mostrar_enfermaria' => 0]);
        expect($html)->not->toContain('ENFER');
        expect($html)->not->toContain('222,22');
        expect($html)->not->toContain('444,44');
        expect($html)->toContain('APART');
        expect($html)->toContain('111,11');
        expect($html)->toContain('333,33');
    }
});

// 08/09/2026: tabelinhas de copart centralizadas também no SS (mesma flag
// copart_centralizar da tabela completa: margin-left 490px no .copart-bloco).
test('as tabelinhas de copart saem centralizadas nos 4 layouts', function () {
    foreach ([1, 2, 3, 4] as $layout) {
        $html = renderModeloEmpresarial($layout);
        expect($html)->toContain('margin-left:490px');
    }
});

// 08/09/2026: o cabeçalho fixo "IDADE" virou o nome do plano (fonte encolhe
// para nomes longos caberem em uma linha, com padding compensando a altura).
test('cabecalho IDADE vira o nome do plano nos 4 layouts', function () {
    foreach ([1, 2, 3, 4] as $layout) {
        $html = renderModeloEmpresarial($layout, ['plano_titulo' => 'Super Simples - Pleno']);
        expect($html)->toContain('SUPER SIMPLES - PLENO');
        expect($html)->not->toContain('>IDADE<'); // "IDADE" solto casaria com QUANTIDADE
        // sem a variável, o comportamento antigo continua
        $htmlPadrao = renderModeloEmpresarial($layout);
        expect($htmlPadrao)->toContain('>IDADE<');
    }
});

// 10/09/2026: variante "Super Simples - Ambulatorial" no select — documento em
// coluna única com rótulo AMBUL. (slot da enfermaria) e cards com coluna única.
test('documento ambulatorial sai em coluna unica com rotulo AMBUL nos 4 layouts', function () {
    foreach ([1, 2, 3, 4] as $layout) {
        $html = renderModeloEmpresarial($layout, [
            'mostrar_apartamento' => 0,
            'mostrar_enfermaria'  => 1,
            'rotulo_enfer'        => 'AMBUL.',
            'plano_titulo'        => 'Super Simples - Ambulatorial',
        ]);
        expect($html)->toContain('AMBUL.');
        expect($html)->not->toContain('>ENFER<');
        expect($html)->not->toContain('APART');
        expect($html)->toContain('SUPER SIMPLES - AMBULATORIAL');
        expect($html)->toContain('222,22'); // valor no slot da enfermaria
        expect($html)->not->toContain('111,11');
    }
});

test('cards do cenario ambulatorial mostram coluna unica Ambul', function () {
    $row = [
        'faixa_etaria'      => 'Faixa 1',
        'quantidade'        => 1,
        'valor_apartamento' => 0,
        'valor_enfermaria'  => 142.54,
        'total_apartamento' => 0,
        'total_enfermaria'  => 142.54,
    ];
    $html = view('hapvida-super-simples.cards', ['resultados' => [[
        'label' => 'Com Copart - Com Odonto',
        'rows' => [$row],
        'copart' => 1,
        'odonto' => 1,
        'ambulatorial' => 1,
        'total_apartamento' => 0,
        'total_enfermaria' => 142.54,
    ]]])->render();

    expect($html)->toContain('Ambul.');
    expect($html)->not->toContain('Apart.');
    expect($html)->toContain('142,54');

    // cenário normal continua com Apart./Enfer.
    $htmlNormal = view('hapvida-super-simples.cards', ['resultados' => [[
        'label' => 'Com Copart - Com Odonto',
        'rows' => [array_merge($row, ['valor_apartamento' => 200.10, 'total_apartamento' => 200.10])],
        'copart' => 1,
        'odonto' => 1,
        'ambulatorial' => 0,
        'total_apartamento' => 200.10,
        'total_enfermaria' => 142.54,
    ]]])->render();
    expect($htmlNormal)->toContain('Apart.');
    expect($htmlNormal)->toContain('Enfer.');
});

test('Resumida com uma acomodacao so tambem funciona', function () {
    foreach ([1, 2, 3, 4] as $layout) {
        $html = renderModeloEmpresarial($layout, ['mostrar_unitarios' => 0, 'mostrar_enfermaria' => 0]);
        expect($html)->not->toContain('Preços Unitários');
        expect($html)->not->toContain('ENFER');
        expect($html)->toContain('APART');
        expect($html)->toContain('333,33');
    }
});
