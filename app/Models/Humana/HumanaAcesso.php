<?php

namespace App\Models\Humana;

use Illuminate\Database\Eloquent\Model;

class HumanaAcesso extends Model
{
    protected $table = 'humana_acessos';

    protected $fillable = ['assinatura_id', 'ativo'];

    protected $casts = ['ativo' => 'boolean'];

    public static function assinaturaTemAcesso(?int $assinaturaId): bool
    {
        if (!$assinaturaId) {
            return false;
        }

        return static::where('assinatura_id', $assinaturaId)->where('ativo', true)->exists();
    }
}
