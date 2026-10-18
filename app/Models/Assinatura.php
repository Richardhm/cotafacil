<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assinatura extends Model
{
    /**
     * Dias de tolerância após o vencimento de uma assinatura de cartão antes de
     * bloquear o acesso — cobre as retentativas automáticas de cobrança da Efi.
     */
    public const CARENCIA_CARTAO_DIAS = 3;

    /**
     * Dias antes do vencimento do PIX em que o sistema avisa o administrador
     * (e-mail via pix:alerta-vencimento e barra no topo das telas).
     */
    public const ALERTA_PIX_DIAS = 3;

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
        'preco_fixo',
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

    /**
     * Tipo de pagamento normalizado: o banco tem 'cartao' e 'CARTAO' misturados
     * (o fluxo trial→cartão gravava maiúsculo), e comparação === em PHP é
     * case-sensitive — era isso que deixava o bloqueio de cartão sem efeito.
     */
    public function tipoPagamento(): string
    {
        return mb_strtoupper(trim((string) $this->tipo));
    }

    public function trialVencido(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at
            && now()->gt($this->trial_ends_at);
    }

    public function pixVencido(): bool
    {
        return in_array($this->tipoPagamento(), ['PIX', 'PIX_AUTOMATICO'])
            && $this->next_charge
            && now()->gt($this->next_charge);
    }

    /**
     * PIX perto de vencer (mas ainda em dia): janela em que o administrador vê a
     * barra de aviso para regularizar antes do bloqueio travar a equipe toda.
     */
    public function pixAVencer(int $dias = self::ALERTA_PIX_DIAS): bool
    {
        return in_array($this->tipoPagamento(), ['PIX', 'PIX_AUTOMATICO'])
            && $this->status === 'ativo'
            && $this->next_charge
            && $this->next_charge->isFuture()
            && $this->next_charge->lte(now()->addDays($dias));
    }

    /**
     * Cartão inadimplente: a Efi derrubou o status da assinatura (inativo/cancelado),
     * ou a mensalidade venceu e passou da carência sem confirmação de pagamento.
     */
    public function cartaoInadimplente(): bool
    {
        if ($this->tipoPagamento() !== 'CARTAO') {
            return false;
        }

        return ! in_array($this->status, ['ativo', 'trial'])
            || ($this->next_charge && now()->gt($this->next_charge->copy()->addDays(self::CARENCIA_CARTAO_DIAS)));
    }

    /**
     * Regra única de "assinatura vencida", usada pelos middlewares CheckSubscription
     * (bloqueio) e CheckSubscriptionExpired (acesso à página /assinatura/expirada).
     */
    public function estaVencida(): bool
    {
        return $this->trialVencido() || $this->pixVencido() || $this->cartaoInadimplente();
    }

    /**
     * Recalcula emails_extra e preco_total a partir da contagem REAL de usuários
     * ativos (users.status = 1): desativou vendedor, o valor cai R$ 29,90;
     * reativou, volta. Vale para PIX (o QR da renovação usa preco_total) e
     * cartão (o valor é espelhado na Efi pelo chamador / cartao:corrigir-valores).
     * Retorna quantos usuários são cobrados além do limite gratuito.
     */
    public function recalcularPrecoPorAtivos(): int
    {
        $ativos = EmailAssinatura::where('assinatura_id', $this->id)
            ->join('users', 'users.id', '=', 'emails_assinatura.user_id')
            ->where('users.status', 1)
            ->count();

        // Preço combinado na mão com o cliente: atualiza só a contagem de ativos.
        if ($this->preco_fixo) {
            $this->emails_extra = $ativos;
            $this->save();

            return max(0, $ativos - $this->emails_permitidos);
        }

        $cupom = null;
        if ($this->cupom_id && empty($this->tipo_plano_id)) {
            $cupom                 = Cupom::find($this->cupom_id);
            $preco_base            = $this->preco_base;
            $preco_extra_por_email = $this->preco_base;
        } elseif (empty($this->cupom_id) && in_array($this->tipo_plano_id, [1, 2])) {
            $plan                  = TipoPlano::find($this->tipo_plano_id);
            $preco_base            = $plan->valor_base;
            $preco_extra_por_email = $plan->valor_por_email;
        } else {
            $preco_base            = $this->preco_base;
            $preco_extra_por_email = $this->preco_base;
        }

        $emails_cobrados = max(0, $ativos - $this->emails_permitidos);

        $total = $preco_base + ($emails_cobrados * $preco_extra_por_email);
        if ($cupom) {
            $total -= $cupom->desconto_plano;
        }

        $this->emails_extra = $ativos;
        $this->preco_total  = max($total, 29.90);
        $this->save();

        return $emails_cobrados;
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
