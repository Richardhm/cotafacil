<?php

namespace App\Models\Humana;

use Illuminate\Database\Eloquent\Model;

class HumanaPromocao extends Model
{
    protected $table = 'humana_promocoes';

    protected $fillable = ['contratacao', 'coparticipacao', 'texto', 'pct_desconto', 'rotulo', 'ativo', 'ordem'];

    protected $casts = ['ativo' => 'boolean', 'pct_desconto' => 'integer'];

    /**
     * Promoções ativas que valem para a contratação (a copay é resolvida por
     * quem exibe). pct_desconto/rotulo alimentam a linha "com desconto".
     */
    public static function daContratacao(string $contratacao)
    {
        return static::where('ativo', true)
            ->where(function ($q) use ($contratacao) {
                $q->whereNull('contratacao')->orWhere('contratacao', $contratacao);
            })
            ->orderBy('ordem')
            ->get(['coparticipacao', 'texto', 'pct_desconto', 'rotulo']);
    }
}
