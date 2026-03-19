<?php

namespace App\Http\Controllers;

use App\Models\Assinatura;
use App\Models\EmailAssinatura;
use App\Models\PixPagamento;
use App\Models\SubscriptionCharge;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceiroController extends Controller
{
    public function index()
    {
        // --- Totais gerais ---
        $totalAtivas   = Assinatura::where('status', 'ativo')->count();
        $totalTrial    = Assinatura::where('status', 'trial')->count();
        $totalCartao   = Assinatura::where('tipo', 'cartao')->where('status', 'ativo')->count();
        $totalPix      = Assinatura::where('tipo', 'PIX')->where('status', 'ativo')->count();
        $totalPixAuto  = Assinatura::where('tipo', 'PIX_AUTOMATICO')->where('status', 'ativo')->count();

        // --- Receita mensal estimada (assinaturas ativas) ---
        $receitaMensal = Assinatura::where('status', 'ativo')->sum('preco_total');

        // --- Novas assinaturas este mês ---
        $novasEsteMes = Assinatura::where('status', 'ativo')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // --- Dias até fim do mês ---
        $diasAteFimMes = now()->diffInDays(now()->endOfMonth());

        // --- Assinaturas PIX vencendo em 7 dias ---
        $vencendoEm7Dias = Assinatura::whereIn('tipo', ['PIX', 'PIX_AUTOMATICO'])
            ->where('status', 'ativo')
            ->whereBetween('next_charge', [now(), now()->addDays(7)])
            ->count();

        // --- Lista de contas com admin e quantidade de usuários ---
        $contas = Assinatura::with(['emails.user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($assinatura) {
                $adminEmail = $assinatura->emails
                    ->firstWhere('is_administrador', true);

                $usuarios = $assinatura->emails->map(fn($e) => [
                    'nome'     => $e->user?->name  ?? $e->email,
                    'telefone' => $e->user?->phone  ?? '—',
                    'email'    => $e->user?->email  ?? $e->email,
                    'admin'    => (bool) $e->is_administrador,
                ]);

                // IDs de todos os usuários vinculados a esta assinatura
                $userIds = $assinatura->emails->pluck('user_id')->filter()->values()->toArray();

                // Estado ativo baseado no usuário administrador
                $adminAtivo = (bool) ($adminEmail?->user?->status ?? true);

                return [
                    'id'              => $assinatura->id,
                    'subscription_id' => $assinatura->subscription_id,
                    'admin_nome'      => $adminEmail?->user?->name  ?? '—',
                    'admin_email'     => $adminEmail?->user?->email ?? '—',
                    'total_users'     => $assinatura->emails->count(),
                    'tipo'            => $assinatura->tipo,
                    'status'          => $assinatura->status,
                    'preco_total'     => $assinatura->preco_total,
                    'next_charge'     => $assinatura->next_charge,
                    'trial_ends_at'   => $assinatura->trial_ends_at,
                    'created_at'      => $assinatura->created_at,
                    'usuarios'        => $usuarios,
                    'user_ids'        => $userIds,
                    'users_ativo'     => $adminAtivo,
                ];
            });

        // --- Evolução mensal (últimos 12 meses) baseado em created_at de assinaturas ativas ---
        $evolucao = Assinatura::where('status', 'ativo')
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as mes"),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(preco_total) as receita')
            )
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // Preenche meses sem dados com zero
        $meses = collect();
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $found = $evolucao->firstWhere('mes', $key);
            $meses->push([
                'mes'     => now()->subMonths($i)->format('M/y'),
                'total'   => $found ? (int) $found->total : 0,
                'receita' => $found ? (float) $found->receita : 0.0,
            ]);
        }

        // --- Histórico de pagamentos PIX (indexado por assinatura_id para o modal) ---
        $historicoPix = PixPagamento::with('user')
            ->orderBy('pago_em', 'desc')
            ->get()
            ->map(fn($p) => [
                'assinatura_id' => $p->assinatura_id,
                'txid'          => $p->txid,
                'valor'         => $p->valor,
                'tipo'          => $p->tipo,
                'pago_em'       => $p->pago_em?->format('d/m/Y H:i'),
            ])
            ->groupBy('assinatura_id');

        // --- Histórico de cobranças de cartão (indexado por subscription_id EfiPay) ---
        $historicoCartao = SubscriptionCharge::orderBy('payment_date', 'desc')
            ->get()
            ->map(fn($c) => [
                'subscription_id' => $c->subscription_id,
                'charge_id'       => $c->charge_id,
                'valor'           => $c->value,
                'status'          => $c->status,
                'pago_em'         => $c->payment_date
                                        ? Carbon::parse($c->payment_date)->format('d/m/Y H:i')
                                        : null,
                'evento_em'       => Carbon::parse($c->event_date)->format('d/m/Y H:i'),
            ])
            ->groupBy('subscription_id');

        return view('financeiro.index', compact(
            'totalAtivas',
            'totalTrial',
            'totalCartao',
            'totalPix',
            'totalPixAuto',
            'receitaMensal',
            'novasEsteMes',
            'diasAteFimMes',
            'vencendoEm7Dias',
            'contas',
            'meses',
            'historicoPix',
            'historicoCartao'
        ));
    }

    public function toggleStatus(Request $request)
    {
        $request->validate([
            'assinatura_id' => 'required|integer|exists:assinaturas,id',
        ]);

        // Busca todos os user_ids vinculados à assinatura
        $userIds = EmailAssinatura::where('assinatura_id', $request->assinatura_id)
            ->pluck('user_id')
            ->filter()
            ->values();

        if ($userIds->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Nenhum usuário encontrado.'], 404);
        }

        // Determina novo status baseado no estado atual do primeiro usuário
        $atual  = User::whereIn('id', $userIds)->value('status');
        $novo   = $atual ? 0 : 1;

        User::whereIn('id', $userIds)->update(['status' => $novo]);

        return response()->json([
            'success' => true,
            'ativo'   => (bool) $novo,
        ]);
    }
}
