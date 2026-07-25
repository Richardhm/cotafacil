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
        15, // SindiLojas
    ],

];
