<?php

namespace App\Models\Humana;

use Illuminate\Database\Eloquent\Model;

class HumanaTabela extends Model
{
    protected $table = 'humana_tabelas';

    protected $fillable = [
        'humana_plano_id', 'acomodacao', 'coparticipacao',
        'registro_ans', 'vigencia_inicio', 'vigencia_fim',
    ];

    protected $casts = [
        'vigencia_inicio' => 'date',
        'vigencia_fim'    => 'date',
    ];

    public function plano()
    {
        return $this->belongsTo(HumanaPlano::class, 'humana_plano_id');
    }

    public function precos()
    {
        return $this->hasMany(HumanaPreco::class, 'humana_tabela_id');
    }
}
