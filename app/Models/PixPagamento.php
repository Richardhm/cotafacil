<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PixPagamento extends Model
{
    protected $fillable = ['user_id', 'assinatura_id', 'txid', 'valor', 'tipo', 'pago_em'];

    protected $casts = ['pago_em' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assinatura()
    {
        return $this->belongsTo(Assinatura::class);
    }
}
