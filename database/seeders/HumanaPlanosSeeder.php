<?php

namespace Database\Seeders;

use App\Models\Humana\HumanaPlano;
use Illuminate\Database\Seeder;

/**
 * As 12 linhas comerciais dos PDFs PF e PME I de Teresina-PI (ago/2026).
 * IDs fixos: o importador da fase 3 e os seeds de humana_tabelas dependem deles.
 *
 * obstetricia: null = não se aplica (Ambulatorial PME, sem internação).
 * Nas linhas onde ela não é escolha (IDEAL, SUPERIOR, PME, REFERÊNCIA) o valor
 * é fixo em true — o front mostra badge informativo, não toggle.
 */
class HumanaPlanosSeeder extends Seeder
{
    public function run(): void
    {
        $planos = [
            // id, nome, contratacao, linha, obstetricia, segmentacao, abrangencia, ordem
            [1,  'VITAL — Sem Obstetrícia', 'pf',  'VITAL',            false, 'Ambulatorial + Hospitalar',               'Timon (MA) e Teresina (PI)', 1],
            [2,  'VITAL — Com Obstetrícia', 'pf',  'VITAL',            true,  'Ambulatorial + Hospitalar + Obstetrícia', 'Timon (MA) e Teresina (PI)', 2],
            [3,  'IDEAL',                   'pf',  'IDEAL',            true,  'Ambulatorial + Hospitalar + Obstetrícia', 'Timon (MA) e Teresina (PI)', 3],
            [4,  'SUPERIOR',                'pf',  'SUPERIOR',         true,  'Ambulatorial + Hospitalar + Obstetrícia', 'Timon (MA) e Teresina (PI)', 4],
            [5,  'REFERÊNCIA',              'pf',  'REFERENCIA',       true,  'Ambulatorial + Hospitalar + Obstetrícia', 'Grupo de Municípios (MA/PI)', 5],
            [6,  'AMBULATORIAL',            'pme', 'AMBULATORIAL',     null,  'Ambulatorial',                            'Timon (MA) e Teresina (PI)', 1],
            [7,  'VITAL',                   'pme', 'VITAL',            true,  'Ambulatorial + Hospitalar + Obstetrícia', 'Timon (MA) e Teresina (PI)', 2],
            [8,  'IDEAL',                   'pme', 'IDEAL',            true,  'Ambulatorial + Hospitalar + Obstetrícia', 'Timon (MA) e Teresina (PI)', 3],
            [9,  'SUPERIOR',                'pme', 'SUPERIOR',         true,  'Ambulatorial + Hospitalar + Obstetrícia', 'Timon (MA) e Teresina (PI)', 4],
            [10, 'SUPERIOR R2',             'pme', 'SUPERIOR R2',      true,  'Ambulatorial + Hospitalar + Obstetrícia', 'Timon (MA) e Teresina (PI)', 5],
            [11, 'PREMIUM NACIONAL',        'pme', 'PREMIUM NACIONAL', true,  'Ambulatorial + Hospitalar + Obstetrícia', 'Território Nacional',        6],
            [12, 'REFERÊNCIA',              'pme', 'REFERENCIA',       true,  'Ambulatorial + Hospitalar + Obstetrícia', 'Grupo de Municípios (MA/PI)', 7],
        ];

        foreach ($planos as [$id, $nome, $contratacao, $linha, $obs, $seg, $abr, $ordem]) {
            HumanaPlano::updateOrCreate(
                ['id' => $id],
                [
                    'nome'         => $nome,
                    'contratacao'  => $contratacao,
                    'linha'        => $linha,
                    'obstetricia'  => $obs,
                    'segmentacao'  => $seg,
                    'abrangencia'  => $abr,
                    'ordem'        => $ordem,
                    'ativo'        => true,
                ]
            );
        }
    }
}
