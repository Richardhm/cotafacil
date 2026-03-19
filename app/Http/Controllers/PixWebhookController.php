<?php

namespace App\Http\Controllers;

use Efi\EfiPay;
use Illuminate\Http\Request;

class PixWebhookController extends Controller
{
    private $efi;

    public function __construct()
    {
        $mode = config('gerencianet.mode');
        $certificate = config("gerencianet.{$mode}.certificate_name");
        $certificate_path = base_path("certs/{$certificate}");

        $options = [
            'client_id'   => config("gerencianet.{$mode}.client_id"),
            'client_secret' => config("gerencianet.{$mode}.client_secret"),
            'certificate' => $certificate_path,
            'sandbox'     => $mode === 'sandbox',
            'debug'       => false,
        ];

        $this->efi = new EfiPay($options);
    }

    /**
     * Registra a URL do webhook PIX na Efi Bank.
     * Acessar apenas uma vez (ou quando a URL mudar).
     */
    public function registrar()
    {
        $chavePix = config('gerencianet.default_key_pix');
        $webhookUrl = env('WEBHOOK_PIX_URL', route('pix.webhook'));

        try {
            $params = ['chave' => $chavePix];
            $body   = ['webhookUrl' => $webhookUrl];

            $response = $this->efi->pixConfigWebhook($params, $body);

            return response()->json([
                'success' => true,
                'message' => 'Webhook registrado com sucesso.',
                'webhook_url' => $webhookUrl,
                'resposta_efi' => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Registra o webhook de PIX Automático (recorrência) na Efi.
     */
    public function registrarRec()
    {
        try {
            $body     = ['webhookUrl' => route('pix.webhook.rec')];
            $response = $this->efi->pixConfigWebhookRecurrenceAutomatic([], $body);

            return response()->json([
                'success'      => true,
                'message'      => 'Webhook PIX Automático registrado.',
                'webhook_url'  => route('pix.webhook.rec'),
                'resposta_efi' => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Consulta o webhook registrado atualmente na Efi.
     */
    public function consultar()
    {
        $chavePix = config('gerencianet.default_key_pix');

        try {
            $params   = ['chave' => $chavePix];
            $response = $this->efi->pixDetailWebhook($params);

            return response()->json([
                'success'  => true,
                'resposta' => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
