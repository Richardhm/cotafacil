<?php

namespace App\Models\Humana;

use Illuminate\Database\Eloquent\Model;

class HumanaPreco extends Model
{
    protected $table = 'humana_precos';

    protected $fillable = [
        'humana_tabela_id', 'humana_faixa_etaria_id',
        'valor_saude', 'valor_combo_essencial', 'valor_combo_pleno',
    ];

    protected $casts = [
        'valor_saude'           => 'decimal:2',
        'valor_combo_essencial' => 'decimal:2',
        'valor_combo_pleno'     => 'decimal:2',
    ];

    public function tabela()
    {
        return $this->belongsTo(HumanaTabela::class, 'humana_tabela_id');
    }

    public function faixaEtaria()
    {
        return $this->belongsTo(HumanaFaixaEtaria::class, 'humana_faixa_etaria_id');
    }
}
