<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Assinatura modelo
    |--------------------------------------------------------------------------
    |
    | Toda assinatura nova nasce com os mesmos vínculos administradora/plano/cidade
    | desta assinatura. Hoje é a 22 (Richard Figueiredo Lopes, user 44), que serve
    | de referência do que um cliente novo deve enxergar.
    |
    | Para mudar o modelo basta apontar para outra assinatura — os vínculos são
    | copiados na hora do cadastro, então editar os vínculos da assinatura modelo
    | pelo painel já muda o que os próximos clientes recebem.
    |
    */

    'assinatura_modelo' => env('VINCULOS_ASSINATURA_MODELO', 22),

    /*
    |--------------------------------------------------------------------------
    | Planos que não entram na cópia
    |--------------------------------------------------------------------------
    |
    | IDs de planos que a assinatura modelo tem mas que cliente novo não recebe.
    |
    */

    'planos_excluidos' => [
        15,  // SindiLojas
        140, // Sindilojas (duplicado do 15, com outra caixa)
        // O SindiLojas continua ATIVO para quem já o usa (assinaturas 22, 26,
        // 59 e 65) — inclusive na assinatura modelo, para o Richard poder cotar.
        // O que não pode é cliente NOVO herdar: o plano é de sindicato e não
        // faz parte do pacote do SaaS.
        //
        // O 140 estava faltando aqui, e é por isso que os cadastros 122, 123,
        // 124 e 125 (07 a 14/08/2026) nasceram com SindiLojas por engano.
    ],

];
