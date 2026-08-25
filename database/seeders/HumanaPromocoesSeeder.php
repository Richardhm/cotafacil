<?php

namespace Database\Seeders;

use App\Models\Humana\HumanaPromocao;
use Illuminate\Database\Seeder;

/**
 * Promoções vigentes informadas pela usuária da Humana (25/08/2026).
 * IDs fixos para o updateOrCreate ser idempotente; quando a promoção mudar,
 * é só editar texto/pct/ativo na tabela (ou atualizar aqui e re-seedar).
 *
 * pct_desconto + rotulo geram a linha "com desconto" abaixo do TOTAL
 * (como a Hapvida faz). A promo de PF Completa são DOIS descontos, então
 * são duas linhas.
 */
class HumanaPromocoesSeeder extends Seeder
{
    public function run(): void
    {
        $promocoes = [
            // id, contratacao, copay, texto, pct, rotulo, ordem
            [1, 'pme', null,       '30% de desconto nas 6 primeiras mensalidades', 30,  'Des. 30% 6/meses', 1],
            [2, 'pf',  'completa', '1º boleto grátis',                              100, '1º boleto GRÁTIS', 1],
            [3, 'pf',  'completa', '50% de desconto no 2º boleto',                  50,  '2º boleto -50%',   2],
            [4, 'pf',  'basica',   '20% de desconto nas 4 primeiras mensalidades', 20,  'Des. 20% 4/meses', 3],
        ];

        foreach ($promocoes as [$id, $contratacao, $copay, $texto, $pct, $rotulo, $ordem]) {
            HumanaPromocao::updateOrCreate(
                ['id' => $id],
                [
                    'contratacao'    => $contratacao,
                    'coparticipacao' => $copay,
                    'texto'          => $texto,
                    'pct_desconto'   => $pct,
                    'rotulo'         => $rotulo,
                    'ordem'          => $ordem,
                    'ativo'          => true,
                ]
            );
        }
    }
}
