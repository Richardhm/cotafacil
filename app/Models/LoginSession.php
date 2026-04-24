<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginSession extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'city',
        'country',
        'timezone',
        'screen_resolution',
        'canvas_hash',
        'gpu_renderer',
        'cpu_cores',
        'device_memory',
        'user_agent',
        'device_fingerprint',
        'browser',
        'os',
        'is_mobile',
        'device_model',
        'logged_in_at',
        'last_seen_at',
        'logged_out_at',
        'was_displaced',
    ];

    protected $casts = [
        'is_mobile'      => 'boolean',
        'was_displaced'  => 'boolean',
        'logged_in_at'   => 'datetime',
        'last_seen_at'   => 'datetime',
        'logged_out_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAtivas($query)
    {
        return $query->whereNull('logged_out_at');
    }

    public function scopeRecentes($query, int $horas = 24)
    {
        return $query->where('logged_in_at', '>=', now()->subHours($horas));
    }
}
