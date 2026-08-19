<?php

namespace App\Models\Humana;

use Illuminate\Database\Eloquent\Model;

class HumanaFaixaEtaria extends Model
{
    protected $table = 'humana_faixa_etarias';

    protected $fillable = ['nome', 'idade_min', 'idade_max'];

    public function precos()
    {
        return $this->hasMany(HumanaPreco::class, 'humana_faixa_etaria_id');
    }
}
