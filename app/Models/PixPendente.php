<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PixPendente extends Model
{
    protected $fillable = ['txid', 'status', 'tipo', 'user_id', 'id_rec'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
