<?php

namespace App\Http\Controllers;

use App\Models\Assinatura;
use App\Models\EmailAssinatura;
use App\Models\PendingTeamUser;
use App\Models\PixPendente;
use App\Models\TipoPlano;
use App\Models\User;
use Carbon\Carbon;
use Efi\EfiPay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeamUserController extends Controller
{
    private const PRECO_POR_USUARIO = 29.90;

    private EfiPay $efi;

    public function __construct()
    {
        $mode    = config('gerencianet.mode');
        $options = [
            'client_id'     => config("gerencianet.{$mode}.client_id"),
            'client_secret' => config("gerencianet.{$mode}.client_secret"),
            'certificate'   => base_path('certs/' . config("gerencianet.{$mode}.certificate_name")),
            'sandbox'       => config('gerencianet.is_sandbox'),
            'debug'         => false,
        ];
        $this->efi = new EfiPay($options);
    }

    // Salva usuário na fila (sem pagamento ainda)
    public function salvar(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
        ], [
            'email.unique' => 'Este e-mail já está cadastrado no sistema.',
        ]);

        // Verifica se já existe um pendente com este e-mail para este admin
        if (PendingTeamUser::where('admin_user_id', Auth::id())
            ->where('email', $request->email)
            ->where('status', 'pending')
            ->exists()) {
            return response()->json(['success' => false, 'error' => 'Este e-mail já está na fila de ativação.']);
        }

        $assinatura = Assinatura::where('user_id', Auth::id())->firstOrFail();

        // Conta free: ativa direto sem pagamento
        if ($assinatura->free) {
            $novoUser = User::create([
                'name'              => $request->name,
                'email'             => $request->email,
                'phone'             => $request->phone,
                'password'          => bcrypt('12345678'),
                'email_verified_at' => now(),
                'primeiro_acesso'   => 1,
                'status'            => 1,
            ]);

            EmailAssinatura::create([
                'assinatura_id'    => $assinatura->id,
                'email'            => $request->email,
                'user_id'          => $novoUser->id,
                'is_administrador' => false,
            ]);

            return response()->json([
                'success'    => true,
                'activated'  => true,
                'id'         => null,
                'name'       => $novoUser->name,
                'email'      => $novoUser->email,
                'phone'      => $novoUser->phone,
                'valor_fmt'  => 'R$ 0,00',
                'is_trial'   => false,
                'quantidade' => 1,
            ]);
        }

        $isTrial = $assinatura->status === 'trial';

        // Ativação do admin só é cobrada UMA vez (primeiro da fila)
        $jaTemAtivacao = PendingTeamUser::where('admin_user_id', Auth::id())
            ->where('inclui_ativacao_admin', true)
            ->where('status', 'pending')
            ->exists();

        $incluiAtivacao = $isTrial && !$jaTemAtivacao;
        $quantidade     = $incluiAtivacao ? 2 : 1;
        $valorTotal     = self::PRECO_POR_USUARIO * $quantidade;

        $pending = PendingTeamUser::create([
            'assinatura_id'         => $assinatura->id,
            'admin_user_id'         => Auth::id(),
            'name'                  => $request->name,
            'email'                 => $request->email,
            'phone'                 => $request->phone,
            'valor'                 => $valorTotal,
            'inclui_ativacao_admin' => $incluiAtivacao,
            'payment_method'        => 'pending',
            'status'                => 'pending',
            'expires_at'            => now()->addDays(30),
        ]);

        return response()->json([
            'success'   => true,
            'activated' => false,
            'id'        => $pending->id,
            'name'      => $pending->name,
            'email'     => $pending->email,
            'phone'     => $pending->phone,
            'valor_fmt' => 'R$ ' . number_format($valorTotal, 2, ',', '.'),
            'is_trial'  => $isTrial,
            'quantidade'=> $quantidade,
        ]);
    }

    // Remove um usuário da fila de inativos
    public function remover(Request $request, int $id)
    {
        $pending = PendingTeamUser::where('id', $id)
            ->where('admin_user_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $pending->delete();

        return response()->json(['success' => true]);
    }

    // Gera PIX para ativar TODOS os usuários da fila de uma vez
    public function gerarPixTodos(Request $request)
    {
        $request->validate(['cpf' => 'required|string|min:11']);

        $pendentes = PendingTeamUser::where('admin_user_id', Auth::id())
            ->where('status', 'pending')
            ->get();

        if ($pendentes->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'Nenhum usuário na fila.']);
        }

        $valorTotal = $pendentes->sum('valor');
        $cpf        = preg_replace('/[^0-9]/', '', $request->cpf);

        PendingTeamUser::whereIn('id', $pendentes->pluck('id'))
            ->update(['payment_method' => 'pix']);

        try {
            $body = [
                'calendario'         => ['expiracao' => 1800],
                'devedor'            => ['cpf' => $cpf, 'nome' => Auth::user()->name],
                'valor'              => ['original' => number_format($valorTotal, 2, '.', '')],
                'chave'              => config('gerencianet.default_key_pix', '130ceaee-b233-481e-8feb-6029a5429b75'),
                'solicitacaoPagador' => 'Ativação de equipe - CotaFácil',
            ];

            $responsePix    = $this->efi->pixCreateImmediateCharge([], $body);
            $txid           = $responsePix['txid'];
            $responseQrcode = $this->efi->pixGenerateQRCode(['id' => $responsePix['loc']['id']]);
            $qrcode         = preg_replace('/^https?:\/\//', '', $responseQrcode['qrcode']);

            // Grava txid e define expires_at num único UPDATE (evita reset automático do MySQL)
            PendingTeamUser::whereIn('id', $pendentes->pluck('id'))
                ->update(['txid' => $txid, 'expires_at' => now()->addMinutes(30)]);

            PixPendente::create([
                'txid'    => $txid,
                'status'  => 'pending',
                'tipo'    => 'team_user',
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success'    => true,
                'imagem'     => $responseQrcode['imagemQrcode'],
                'copiacola'  => ' ' . $qrcode,
                'txid'       => $txid,
                'valor_fmt'  => 'R$ ' . number_format($valorTotal, 2, ',', '.'),
                'quantidade' => $pendentes->count(),
            ]);
        } catch (\Exception $e) {
            PendingTeamUser::whereIn('id', $pendentes->pluck('id'))
                ->update(['payment_method' => 'pending', 'expires_at' => now()->addDays(30)]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // Cartão para ativar TODOS os usuários da fila de uma vez
    public function gerarCartaoTodos(Request $request)
    {
        $request->validate(['paymentToken' => 'required|string']);

        $pendentes = PendingTeamUser::where('admin_user_id', Auth::id())
            ->where('status', 'pending')
            ->get();

        if ($pendentes->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'Nenhum usuário na fila.']);
        }

        $valorTotal = $pendentes->sum('valor');

        try {
            $body = [
                'items' => [[
                    'name'   => 'Ativação de equipe - CotaFácil',
                    'amount' => 1,
                    'value'  => intval($valorTotal * 100),
                ]],
                'payment' => [
                    'credit_card' => [
                        'payment_token'   => $request->paymentToken,
                        'billing_address' => [
                            'street'       => 'Av. Não Informado',
                            'number'       => 'S/N',
                            'neighborhood' => 'Centro',
                            'zipcode'      => '74000000',
                            'city'         => 'Goiânia',
                            'state'        => 'GO',
                        ],
                        'customer' => [
                            'name'         => Auth::user()->name,
                            'cpf'          => preg_replace('/[^0-9]/', '', Auth::user()->cpf ?? '01375583174'),
                            'phone_number' => preg_replace('/[^0-9]/', '', Auth::user()->phone ?? '62999999999'),
                            'email'        => Auth::user()->email,
                            'birth'        => '1990-01-01',
                        ],
                    ],
                ],
                'metadata' => ['notification_url' => config('gerencianet.notification_url')],
            ];

            $response = $this->efi->createOneStepCharge([], $body);

            $status = $response['data']['status'] ?? '';

            if (!in_array($status, ['paid', 'approved'])) {
                return response()->json(['success' => false, 'error' => 'Pagamento recusado pela operadora.']);
            }

            DB::transaction(function () use ($pendentes) {
                foreach ($pendentes as $p) {
                    $p->update(['payment_method' => 'cartao']);
                    $this->ativarUsuario($p);
                }
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // Gera cobrança PIX para ativar usuário da fila
    public function gerarPix(Request $request)
    {
        $request->validate([
            'pending_id' => 'required|integer',
            'cpf'        => 'required|string|min:11',
        ]);

        $pending = PendingTeamUser::where('id', $request->pending_id)
            ->where('admin_user_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $pending->update(['payment_method' => 'pix']);

        $cpf = preg_replace('/[^0-9]/', '', $request->cpf);

        try {
            $body = [
                'calendario'         => ['expiracao' => 1800],
                'devedor'            => ['cpf' => $cpf, 'nome' => Auth::user()->name],
                'valor'              => ['original' => number_format($pending->valor, 2, '.', '')],
                'chave'              => config('gerencianet.default_key_pix', '130ceaee-b233-481e-8feb-6029a5429b75'),
                'solicitacaoPagador' => 'Ativação de usuário - Equipe CotaFácil',
            ];

            $responsePix    = $this->efi->pixCreateImmediateCharge([], $body);
            $txid           = $responsePix['txid'];
            $responseQrcode = $this->efi->pixGenerateQRCode(['id' => $responsePix['loc']['id']]);
            $qrcode         = preg_replace('/^https?:\/\//', '', $responseQrcode['qrcode']);

            // Define txid e expires_at num único UPDATE (evita reset automático do MySQL)
            $pending->update(['txid' => $txid, 'expires_at' => now()->addMinutes(30)]);

            PixPendente::create([
                'txid'    => $txid,
                'status'  => 'pending',
                'tipo'    => 'team_user',
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success'   => true,
                'imagem'    => $responseQrcode['imagemQrcode'],
                'copiacola' => ' ' . $qrcode,
                'txid'      => $txid,
                'valor_fmt' => 'R$ ' . number_format($pending->valor, 2, ',', '.'),
                'is_trial'  => $pending->inclui_ativacao_admin,
            ]);
        } catch (\Exception $e) {
            $pending->update(['payment_method' => 'pending', 'expires_at' => now()->addDays(30)]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // Polling: verifica se o PIX foi pago e ativa o usuário
    // Com ?info=1 retorna dados de trial sem necessitar de txid
    public function verificar(Request $request)
    {
        if ($request->boolean('info')) {
            $assinatura = Assinatura::where('user_id', Auth::id())->first();
            $isTrial    = $assinatura && $assinatura->status === 'trial';
            $quantidade = $isTrial ? 2 : 1;
            $valorTotal = self::PRECO_POR_USUARIO * $quantidade;
            return response()->json([
                'is_trial'   => $isTrial,
                'quantidade' => $quantidade,
                'valor_fmt'  => 'R$ ' . number_format($valorTotal, 2, ',', '.'),
            ]);
        }

        $txid     = $request->input('txid');
        $pendings = PendingTeamUser::where('txid', $txid)
            ->where('admin_user_id', Auth::id())
            ->get();

        if ($pendings->isEmpty()) {
            return response()->json(['pago' => false]);
        }

        // Todos já pagos
        if ($pendings->every(fn($p) => $p->status === 'paid')) {
            return response()->json(['pago' => true]);
        }

        $first = $pendings->first();
        if ($first->status === 'expired' || $first->expires_at->isPast()) {
            PendingTeamUser::whereIn('id', $pendings->pluck('id'))->update(['status' => 'expired']);
            return response()->json(['pago' => false, 'expirado' => true]);
        }

        $pixPendente = PixPendente::where('txid', $txid)->first();

        if (!$pixPendente || $pixPendente->status !== 'approved') {
            return response()->json(['pago' => false]);
        }

        DB::transaction(function () use ($pendings) {
            foreach ($pendings as $p) {
                $this->ativarUsuario($p);
            }
        });

        return response()->json(['pago' => true]);
    }

    // Cartão: processa imediatamente (síncrono)
    public function gerarCartao(Request $request)
    {
        $request->validate([
            'pending_id'   => 'required|integer',
            'paymentToken' => 'required|string',
        ]);

        $pending = PendingTeamUser::where('id', $request->pending_id)
            ->where('admin_user_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $valorTotal = $pending->valor;

        try {
            $body = [
                'items' => [[
                    'name'   => 'Ativação de usuário - Equipe CotaFácil',
                    'amount' => 1,
                    'value'  => intval($valorTotal * 100),
                ]],
                'payment' => [
                    'credit_card' => [
                        'payment_token'   => $request->paymentToken,
                        'billing_address' => [
                            'street'       => 'Av. Não Informado',
                            'number'       => 'S/N',
                            'neighborhood' => 'Centro',
                            'zipcode'      => '74000000',
                            'city'         => 'Goiânia',
                            'state'        => 'GO',
                        ],
                        'customer' => [
                            'name'         => Auth::user()->name,
                            'cpf'          => preg_replace('/[^0-9]/', '', Auth::user()->cpf ?? '01375583174'),
                            'phone_number' => preg_replace('/[^0-9]/', '', Auth::user()->phone ?? '62999999999'),
                            'email'        => Auth::user()->email,
                            'birth'        => '1990-01-01',
                        ],
                    ],
                ],
                'metadata' => ['notification_url' => config('gerencianet.notification_url')],
            ];

            $response = $this->efi->createOneStepCharge([], $body);

            $status = $response['data']['status'] ?? '';
            if (!in_array($status, ['paid', 'approved'])) {
                return response()->json(['success' => false, 'error' => 'Pagamento recusado pela operadora.']);
            }

            $pending->update(['payment_method' => 'cartao']);
            DB::transaction(fn() => $this->ativarUsuario($pending));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function ativarUsuario(PendingTeamUser $pending): void
    {
        if ($pending->fresh()->status === 'paid') {
            return;
        }

        $assinatura = Assinatura::findOrFail($pending->assinatura_id);

        $novoUser = User::create([
            'name'              => $pending->name,
            'email'             => $pending->email,
            'phone'             => $pending->phone,
            'password'          => bcrypt('12345678'),
            'email_verified_at' => now(),
            'primeiro_acesso'   => 1,
            'status'            => 1,
        ]);

        EmailAssinatura::create([
            'assinatura_id'    => $assinatura->id,
            'email'            => $pending->email,
            'user_id'          => $novoUser->id,
            'is_administrador' => false,
        ]);

        $assinatura->emails_extra += 1;
        $precoExtra               = $assinatura->tipo_plano_id
            ? (TipoPlano::find($assinatura->tipo_plano_id)?->valor_por_email ?? self::PRECO_POR_USUARIO)
            : self::PRECO_POR_USUARIO;
        $assinatura->preco_total  += $precoExtra;

        if ($pending->inclui_ativacao_admin) {
            $assinatura->status        = 'ativo';
            $assinatura->trial_ends_at = null;
            $assinatura->next_charge   = Carbon::now()->addMonth();
        }

        $assinatura->save();
        $pending->update(['status' => 'paid']);
    }
}
