<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plano extends Model
{
    protected $fillable = ['nome', 'apelido', 'empresarial'];

    public function dependentes()
    {
        return $this->hasMany(Tabela::class); // Ajuste para relacionamentos reais
    }
}
