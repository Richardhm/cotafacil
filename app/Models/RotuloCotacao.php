<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Rótulo personalizado da cotação Hapvida (ver migration rotulos_cotacao).
 * Resolução: usuário > assinatura > padrão.
 */
class RotuloCotacao extends Model
{
    protected $table = 'rotulos_cotacao';

    protected $fillable = ['user_id', 'assinatura_id', 'plano_id', 'chave', 'texto'];

    public const CHAVES = [
        'nome_plano'     => 'Nome do plano (cabeçalho da cotação)',
        'com_copart'     => 'Título "COM COPARTICIPAÇÃO"',
        'copart_parcial' => 'Título "COM COPART PARCIAL *" / "SEM COPARTICIPAÇÃO *"',
    ];

    public function user()       { return $this->belongsTo(User::class); }
    public function assinatura() { return $this->belongsTo(Assinatura::class); }
    public function plano()      { return $this->belongsTo(Plano::class); }

    /**
     * Texto do rótulo para o usuário logado: linha do usuário > linha da
     * assinatura dele > $padrao. Para 'nome_plano' a busca é por plano;
     * para os títulos de copay, plano_id é sempre null.
     */
    public static function resolver(User $user, string $chave, ?int $planoId, ?string $padrao): ?string
    {
        $porPlano = $chave === 'nome_plano';

        $buscar = function (string $coluna, int $id) use ($chave, $porPlano, $planoId) {
            $q = static::where($coluna, $id)->where('chave', $chave);
            $porPlano ? $q->where('plano_id', $planoId) : $q->whereNull('plano_id');
            return $q->value('texto');
        };

        if ($texto = $buscar('user_id', $user->id)) {
            return $texto;
        }

        $assinaturaId = EmailAssinatura::where('email', $user->email)->value('assinatura_id');
        if ($assinaturaId && ($texto = $buscar('assinatura_id', $assinaturaId))) {
            return $texto;
        }

        return $padrao;
    }
}
