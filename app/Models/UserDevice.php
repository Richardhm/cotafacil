<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'device_uuid',
        'device_fingerprint',
        'hardware_fingerprint',
        'device_name',
        'device_model',
        'device_type',
        'is_active',
        'is_blocked',
        'registered_at',
        'last_used_at',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'is_blocked'    => 'boolean',
        'registered_at' => 'datetime',
        'last_used_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAtivos($query)
    {
        return $query->where('is_active', true);
    }
}
