<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    public $timestamps = false;

    protected $fillable = ['ip_address', 'blocked_at'];

    protected $casts = ['blocked_at' => 'datetime'];
}
