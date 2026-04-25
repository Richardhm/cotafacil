<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingTeamUser extends Model
{
    protected $fillable = [
        'assinatura_id',
        'admin_user_id',
        'name',
        'email',
        'phone',
        'valor',
        'inclui_ativacao_admin',
        'txid',
        'payment_method',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'inclui_ativacao_admin' => 'boolean',
        'expires_at'            => 'datetime',
        'valor'                 => 'float',
    ];
}
