<?php

namespace App\Http\Controllers;

use App\Models\PendingTeamUser;
use App\Models\SubscriptionCharge;
use App\Models\TipoPlano;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Assinatura;
use App\Models\EmailAssinatura;
use App\Models\User;
use Efi\Exception\EfiException;
use Efi\EfiPay;


class CallbackController extends Controller
{
    private $efi;

    public function __construct()
    {
        $mode = config('gerencianet.mode');
        $certificate = config("gerencianet.{$mode}.certificate_name");

        $client_id = config("gerencianet.{$mode}.client_id");
        $client_secret = config("gerencianet.{$mode}.client_secret");
        $certificate_path = base_path("certs/{$certificate}");

        $options = [
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'sandbox'       => config('gerencianet.is_sandbox'),
            'debug'         => config('gerencianet.debug', false),
        ];

        $this->efi = new EfiPay($options);
    }

    public function index()
    {
        $token = request()->notification;
        $params = [
            "token" => $token
        ];
        try {
            \Log::channel('gerencianet')->info("Iniciando processamento da notificação", [
                'token' => $token,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            $response = $this->efi->getNotification($params);

            \Log::channel('gerencianet')->debug("Resposta completa da API", [
                'raw_response' => $response,
                'memory_usage' => memory_get_usage(true) / 1024 / 1024 . " MB"
            ]);


            header("HTTP/1.1 200");
            if ($response && isset($response['data'])) {

                $this->processNotifications($response['data']);

                \Log::channel('gerencianet')->info("Notificação processada com sucesso", [
                    'subscription_id' => $response['data'][0]['identifiers']['subscription_id'] ?? null,
                    'event_count' => count($response['data'])
                ]);

                return response()->json(['success' => true]);
                //return response()->json($response['data']);

//                return response()->json([
//                    'success' => true,
//                    'data' => $response
//                ], 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);


            }
            return response()->json(['error' => 'Resposta inválida'], 400);
        } catch (EfiException $e) {
            header("HTTP/1.1 400");
            print_r($e->code . "<br>");
            print_r($e->error . "<br>");
            print_r($e->errorDescription);
        } catch (Exception $e) {
            header("HTTP/1.1 403");
            print_r($e->getMessage());
        }
    }


    private function processNotifications(array $notifications)
    {
        foreach ($notifications as $event) {
            try {
                switch ($event['type']) {
                    case 'subscription':
                        $this->processSubscription($event);
                        break;

                    case 'subscription_charge':
                        $this->processCharge($event);
                        break;
                }
            } catch (\Exception $e) {
                \Log::error("Erro processando evento {$event['id']}: " . $e->getMessage());
            }
        }
    }

    private function processSubscription(array $subscriptionEvent)
    {
        $statusMap = [
            'active'    => 'ativo',
            'inactive'  => 'inativo',
            'suspended' => 'inativo',
            'canceled'  => 'cancelado',
        ];

        $statusEfi = $subscriptionEvent['status']['current'];
        $status    = $statusMap[$statusEfi] ?? $statusEfi;

        Assinatura::updateOrCreate(
            ['subscription_id' => $subscriptionEvent['identifiers']['subscription_id']],
            [
                'status'       => $status,
                'last_updated' => $subscriptionEvent['created_at'],
            ]
        );
    }

    /**
     * Webhook para PIX Automático — notificação de autorização da recorrência
     * Endpoint: POST /pix/webhook-rec
     */
    public function pixWebhookRec(Request $request)
    {
        $body = $request->getContent();
        \Log::channel('gerencianet')->info('PIX webhook-rec recebido', ['body' => $body]);

        $data = json_decode($body, true);

        // Notificação de autorização da recorrência
        if (isset($data['rec'][0]['idRec'])) {
            $idRec  = $data['rec'][0]['idRec'];
            $status = $data['rec'][0]['status'] ?? null;

            if ($status === 'AUTORIZADO') {
                \App\Models\PixPendente::where('id_rec', $idRec)
                    ->where('status', 'pending')
                    ->update(['status' => 'approved']);
            }
        }

        // Notificação de cobrança automática mensal
        if (isset($data['cobr'][0]['idRec'])) {
            $idRec  = $data['cobr'][0]['idRec'];
            $status = $data['cobr'][0]['status'] ?? null;

            if ($status === 'CONCLUIDA') {
                // Atualiza next_charge da assinatura vinculada
                \App\Models\Assinatura::where('contrato', $idRec)
                    ->update(['next_charge' => now()->addMonth()]);
            }
        }

        return response()->json(['success' => true], 200);
    }

    public function pixWebhook(Request $request)
    {
        $body = $request->getContent();
        \Log::channel('gerencianet')->info('PIX webhook recebido', ['body' => $body]);

        $data = json_decode($body, true);

        if (isset($data['pix'][0]['txid'])) {
            $txid = $data['pix'][0]['txid'];

            \App\Models\PixPendente::where('txid', $txid)
                ->where('status', 'pending')
                ->update(['status' => 'approved']);

            $pixPendente = \App\Models\PixPendente::where('txid', $txid)->first();

            if ($pixPendente && $pixPendente->tipo === 'team_user') {
                $pendings = PendingTeamUser::where('txid', $txid)
                    ->where('status', 'pending')
                    ->get();

                if ($pendings->isNotEmpty()) {
                    DB::transaction(function () use ($pendings) {
                        foreach ($pendings as $p) {
                            $this->ativarTeamUser($p);
                        }
                    });
                }
            }
        }

        return response()->json(['success' => true], 200);
    }

    private function ativarTeamUser(PendingTeamUser $pending): void
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

        $precoExtra              = $assinatura->tipo_plano_id
            ? (TipoPlano::find($assinatura->tipo_plano_id)?->valor_por_email ?? 29.90)
            : 29.90;
        $assinatura->emails_extra += 1;
        $assinatura->preco_total  += $precoExtra;

        if ($pending->inclui_ativacao_admin) {
            $assinatura->status        = 'ativo';
            $assinatura->trial_ends_at = null;
            $assinatura->next_charge   = Carbon::now()->addMonth();
        }

        $assinatura->save();
        $pending->update(['status' => 'paid']);
    }

    public function processCharge(array $chargeEvent)
    {
        // Converter valores para decimal
        try {
            $params = ['id' => $chargeEvent['identifiers']['subscription_id']];
            $chargeDetails = $this->efi->detailSubscription($params);

            $value = $chargeDetails['data']['value'] / 100; // Valor em decimal
        } catch (\Exception $e) {
            \Log::error("Erro ao buscar detalhes da cobrança: " . $e->getMessage());
            $value = 0;
        }

        // Gravar cobrança individual
        SubscriptionCharge::updateOrCreate(
            [
                'charge_id' => $chargeEvent['identifiers']['charge_id'],
                'subscription_id' => $chargeEvent['identifiers']['subscription_id']
            ],
            [
                'status' => $chargeEvent['status']['current'],
                'value' => $value,
                'payment_date' => isset($chargeEvent['received_by_bank_at'])
                    ? Carbon::parse($chargeEvent['received_by_bank_at'])
                    : null,
                'event_date' => Carbon::parse($chargeEvent['created_at']),
                'metadata' => json_encode($chargeEvent)
            ]
        );

        $chargeStatus = $chargeEvent['status']['current'];

        if ($chargeStatus === 'paid') {
            Assinatura::where('subscription_id', $chargeEvent['identifiers']['subscription_id'])
                ->update([
                    'status'       => 'ativo',
                    'last_payment' => Carbon::parse($chargeEvent['received_by_bank_at']),
                    'next_charge'  => Carbon::parse($chargeEvent['received_by_bank_at'])->addMonth(),
                ]);
        } elseif (in_array($chargeStatus, ['unpaid', 'overdue', 'canceled'])) {
            Assinatura::where('subscription_id', $chargeEvent['identifiers']['subscription_id'])
                ->update(['status' => 'inativo']);
        }
    }

}
