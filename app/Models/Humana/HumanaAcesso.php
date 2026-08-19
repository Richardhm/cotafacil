<?php

namespace App\Models\Humana;

use Illuminate\Database\Eloquent\Model;

class HumanaAcesso extends Model
{
    protected $table = 'humana_acessos';

    protected $fillable = ['user_id', 'ativo'];

    protected $casts = ['ativo' => 'boolean'];

    public static function usuarioTemAcesso(?int $userId): bool
    {
        if (!$userId) {
            return false;
        }

        return static::where('user_id', $userId)->where('ativo', true)->exists();
    }
}
