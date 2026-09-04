<?php

namespace App\Http\Controllers;

use App\Models\Assinatura;
use Carbon\Carbon;

/**
 * Página /vendas — acompanhamento dos cadastros novos para a equipe de vendas.
 * Mostra apenas assinaturas criadas a partir de INICIO_VENDAS (início da
 * vendedora). Somente leitura — as ações continuam no /financeiro.
 */
class VendasController extends Controller
{
    // Data em que a vendedora começou: só cadastros a partir daqui aparecem
    public const INICIO_VENDAS = '2026-08-20';

    public function index()
    {
        $inicio = Carbon::parse(self::INICIO_VENDAS)->startOfDay();

        $contas = Assinatura::with(['emails.user'])
            ->where('created_at', '>=', $inicio)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($assinatura) {
                $adminEmail = $assinatura->emails->firstWhere('is_administrador', true);

                return [
                    'admin_nome'    => $adminEmail?->user?->name ?? '—',
                    'admin_email'   => $adminEmail?->user?->email ?? '—',
                    'admin_phone'   => $adminEmail?->user?->phone ?? null,
                    'total_users'   => $assinatura->emails->count(),
                    'tipo'          => $assinatura->tipo,
                    'status'        => $assinatura->status,
                    'preco_total'   => $assinatura->preco_total,
                    'created_at'    => $assinatura->created_at,
                    'next_charge'   => $assinatura->next_charge,
                    'trial_ends_at' => $assinatura->trial_ends_at,
                    'usuarios'      => $assinatura->emails->map(fn ($e) => [
                        'nome'     => $e->user?->name ?? $e->email,
                        'email'    => $e->user?->email ?? $e->email,
                        'telefone' => $e->user?->phone ?? '—',
                        'admin'    => (bool) $e->is_administrador,
                    ])->values(),
                ];
            });

        return view('vendas.index', [
            'inicio'      => $inicio,
            'contas'      => $contas,
            'totalNovas'  => $contas->count(),
            'totalUsuarios' => $contas->sum('total_users'), // usuários DAS contas novas (bate com a coluna da tabela)
            'totalAtivas' => $contas->where('status', 'ativo')->count(),
            'totalTrial'  => $contas->where('status', 'trial')->count(),
            'receita'     => $contas->where('status', 'ativo')->sum('preco_total'),
        ]);
    }
}
