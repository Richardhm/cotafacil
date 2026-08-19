<?php

namespace Database\Seeders;

use App\Models\Humana\HumanaFaixaEtaria;
use Illuminate\Database\Seeder;

/**
 * As 10 faixas idênticas em TODAS as 38 tabelas dos PDFs da Humana (ago/2026).
 * IDs fixos 1..10 — humana_precos referencia por id, e o importador (fase 3)
 * conta com essa numeração.
 */
class HumanaFaixaEtariasSeeder extends Seeder
{
    public function run(): void
    {
        $faixas = [
            1  => ['00 a 18',     0,  18],
            2  => ['19 a 23',    19,  23],
            3  => ['24 a 28',    24,  28],
            4  => ['29 a 33',    29,  33],
            5  => ['34 a 38',    34,  38],
            6  => ['39 a 43',    39,  43],
            7  => ['44 a 48',    44,  48],
            8  => ['49 a 53',    49,  53],
            9  => ['54 a 58',    54,  58],
            10 => ['59 ou mais', 59, null],
        ];

        foreach ($faixas as $id => [$nome, $min, $max]) {
            HumanaFaixaEtaria::updateOrCreate(
                ['id' => $id],
                ['nome' => $nome, 'idade_min' => $min, 'idade_max' => $max]
            );
        }
    }
}
