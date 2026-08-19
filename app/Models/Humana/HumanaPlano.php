<?php

namespace App\Models\Humana;

use Illuminate\Database\Eloquent\Model;

class HumanaPlano extends Model
{
    protected $table = 'humana_planos';

    protected $fillable = [
        'nome', 'contratacao', 'linha', 'obstetricia',
        'segmentacao', 'abrangencia', 'ordem', 'ativo',
    ];

    protected $casts = [
        'obstetricia' => 'boolean',
        'ativo'       => 'boolean',
    ];

    public function tabelas()
    {
        return $this->hasMany(HumanaTabela::class, 'humana_plano_id');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true)->orderBy('ordem');
    }
}
