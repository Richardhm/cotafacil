<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PixPendente extends Model
{
    protected $fillable = ['txid', 'status', 'tipo', 'user_id', 'id_rec', 'valor', 'assinatura_id'];

    protected $casts = ['valor' => 'decimal:2'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assinatura()
    {
        return $this->belongsTo(Assinatura::class);
    }
}
