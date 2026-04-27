<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assinatura extends Model
{

    protected $casts = [
        'next_charge'            => 'datetime',
        'alerta_pix_enviado_at'  => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'cupom_id',
        'tipo_plano_id',
        'preco_base',
        'emails_permitidos',
        'emails_extra',
        'preco_total',
        'status',
        'subscription_id',
        'last_updated',
        'last_payment',
        'next_charge',
        'tipo',
        'trial_ends_at',
        'alerta_pix_enviado_at',
        'contrato',
        'free',
    ];

    public function emails()
    {
        return $this->hasMany(EmailAssinatura::class,'assinatura_id','id');
    }
//
    public function emailsAssinatura()
    {
        return $this->hasMany(EmailAssinatura::class,'assinatura_id');
    }

    public function tabelasOrigens()
    {
        return $this->belongsToMany(
            TabelaOrigens::class,
            'assinatura_cidade',
            'assinatura_id',
            'tabela_origem_id'
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cidades()
    {

        return $this->belongsToMany(TabelaOrigens::class, 'administradora_planos','assinatura_id','tabela_origens_id');
    }


    public function emailAssinatura()
    {
        return $this->hasOne(EmailAssinatura::class, 'assinatura_id');
    }

    public function emailsAssinaturaUser()
    {
        return $this->hasMany(EmailAssinatura::class)->with('user');
    }

    public function calcularPrecoTotal()
    {
        $totalUsers = $this->emails()->count();
        $emailsExtras = max(0, $totalUsers - $this->emails_permitidos);
        $this->emails_extra = $emailsExtras;
        $this->preco_total = $totalUsers * $this->preco_base;
        $this->save();
    }
}
