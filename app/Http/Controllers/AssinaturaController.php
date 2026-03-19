<?php

namespace App\Http\Controllers;

use App\Jobs\SendVerificationEmail;
use App\Models\Assinatura;
use App\Models\Cupom;
use App\Models\EmailAssinatura;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\WelcomeNotification;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Efi\Exception\EfiException;
use Efi\EfiPay;
use PHPUnit\Exception;

class AssinaturaController extends Controller
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
            'certificate'   => $certificate_path,
            'sandbox'       => config('gerencianet.is_sandbox'),
            'debug'         => config('gerencianet.debug', false),
        ];


        $this->efi = new EfiPay($options);

    }

    public function createIndividual()
    {
        return view('assinaturas.individual.create');
    }

    public function createEmpresarial()
    {
        return view('assinaturas.empresarial.create');
    }

    public function createPromocional()
    {
        return view('assinaturas.promocional.create');
    }

    public function edit()
    {
        $assinatura = \auth()->user()->assinaturas()->first();
        $nome = \auth()->user()->name;
        $cpf = \auth()->user()->cpf;



        return view('assinaturas.trial.create',[
            'preco_total' => $assinatura->preco_total,
            'nome' => $nome,
            'cpf' => $cpf
        ]);
    }




    public function storeIndividual(Request $request)
    {
        $isTrial = $request->has('trial');
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|confirmed|min:8',
        ];

        if(!$isTrial) {
            $validationRules['paymentToken'] = 'required|string';
        }

        $request->validate($validationRules);

        if($isTrial) {
            return $this->handleTrialRegistration($request, null);
        } else {
            $imagePath = null;
            if ($request->hasFile('imagem')) {
                $imagePath = $request->file('imagem')->store('users', 'public');
            }
            return $this->handlePaidRegistration($request, $imagePath, $redirect = false);
        }
    }

    private function handleTrialRegistration($request, $imagePath)
    {
        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => $request->password,
            'phone'             => $request->phone,
            'email_verified_at' => now(),
        ]);

        $assinatura = Assinatura::create([
            'user_id' => $user->id,
            'tipo_plano_id' => 1,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(7),
            'emails_permitidos' => 1,
            'emails_extra' => 1,
            'preco_base' => 29.90,
            'tipo' => 'cartao',
            'preco_total' => 29.90,
        ]);

        EmailAssinatura::create([
            'assinatura_id' => $assinatura->id,
            'email' => $user->email,
            'user_id' => $user->id,
            'is_administrador' => true, // Marca este e-mail como administrador
        ]);

        $this->administradoraPlanos($assinatura->id);

        //SendVerificationEmail::dispatch($user);
        Auth::login($user);

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard')
        ]);
    }

    private function handlePaidRegistration($request, $imagePath)
    {
        try {
            // Parâmetros do plano
            $params = [
                "id" => config('gerencianet.plan_id')
            ];

            // Itens da assinatura
            $items = [
                [
                    "name" => "Plano Individual",
                    "amount" => 1,
                    "value" => 2990 // R$129,90 em centavos
                ]
            ];
            // Dados do cliente
            $customer = [
                "name" => $request->name,
                "cpf" => "01375583174",
                "phone_number" => preg_replace('/[^0-9]/', '', $request->phone),
                "email" => $request->email,
                "birth" => "1986-10-24"
            ];

            // Endereço (também necessário)
            $billingAddress = [
                "street" => "Avenida José Leandro da Cruz",
                "number" => "S/N",
                "neighborhood" => "Jardim Luz",
                "zipcode" => "74915130",
                "city" => "Aparecida de Goiânia",
                "state" => "GO",
            ];



            $body = [
                "items" => $items,
                "payment" => [
                    "credit_card" => [
                        "billing_address" => $billingAddress,
                        "payment_token" => $request->paymentToken,
                        "customer" => $customer
                    ]
                ],
                "metadata" => [
                    "notification_url" => config('gerencianet.notification_url')
                ]
            ];

            $response = $this->efi->createOneStepSubscription($params, $body);

            if (!isset($response['data']['subscription_id'])) {

                return response()->json([
                    'success' => false,
                    'data' => $response,
                    'redirect' => ""
                ]);
            }

            // 1. Criar o usuário no banco de dados
            $user = User::create([
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => $request->password,
                'phone'      => $request->phone,
                'cpf'        => preg_replace('/[^0-9]/', '', $request->cpf ?? ''),
                'birth_date' => $request->birth_date ?: null,
                'imagem'     => $imagePath,
            ]);

            // 2. Vincular o ID da assinatura ao usuário
            $assinatura = Assinatura::create([
                'user_id' => $user->id,
                'tipo_plano_id' => 1,
                'preco_base' => 29.90,
                'emails_permitidos' => 1,
                'emails_extra' => 1,
                'preco_total' => 29.90,
                'status' => 'ativo',
                'tipo' => 'cartao',
                'subscription_id' => $response['data']['subscription_id'],
                'next_charge' => now()->addMonth(),
            ]);

            EmailAssinatura::create([
                'assinatura_id' => $assinatura->id,
                'email' => $user->email,
                'user_id' => $user->id,
                'is_administrador' => true, // Marca este e-mail como administrador
            ]);

            $this->administradoraPlanos($assinatura->id);

            //SendVerificationEmail::dispatch($user);
            Auth::login($user);

            return response()->json([
                'success' => true,
                'data' => $response,
                'redirect' => route('dashboard')
            ]);

        } catch (EfiException $e) {
            return response()->json([
                'error' => true,
                'message' => $e->errorDescription,
                'code' => $e->code
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }

    }

    private function administradoraPlanos($assinatura_id)
    {
        // Array de associações, cada um é um registro que será inserido
        $dados = [
            [3, 1, 1],
            [3, 1, 2],
            [3, 1, 8],
            [3, 1, 9],
            [3, 2, 1],
            [3, 2, 2],
            [3, 2, 8],
            [3, 2, 9],
            [3, 3, 1],
            [3, 3, 2],
            [3, 3, 8],
            [3, 3, 9],
            [1, 4, 1],
            [1, 4, 2],
            [1, 4, 8],
            [1, 4, 9],
            [10, 4, 9],
            [11, 4, 9],
            [8, 2, 9],
            [14, 2, 9],
            [1, 4, 11],
            [1, 4, 12],
            [1, 4, 13],
            [16, 4, 14],
            [17, 4, 14],
            [18, 4, 14],
            [19, 4, 14],
            [20, 4, 14],
            [21, 4, 14],
            [22, 4, 14],
            [1, 4, 15],
            [5, 4, 15],
            [1, 4, 16],
            [5, 4, 16],
            [1, 4, 17],
            [1, 4, 18],
            [1, 4, 19],
            [1, 4, 21],
            [1, 4, 22],
            [1, 4, 23],
            [1, 4, 26],
            [1, 4, 27],
            [1, 4, 25],
            [1, 4, 29],
            [1, 4, 30],
            [1, 4, 32],
            [1, 4, 31],
            [1, 4, 33],
            [1, 4, 34],
            [1, 4, 36],
        ];

        $associacoes = array_map(function($item) {
            return [
                'plano_id' => $item[0],
                'administradora_id' => $item[1],
                'tabela_origens_id' => $item[2],
            ];
        }, $dados);


        foreach ($associacoes as $associacao) {
            // Cria o registro no banco vinculando a assinatura
            \App\Models\AdministradoraPlano::create([
                'plano_id'          => $associacao['plano_id'],
                'administradora_id' => $associacao['administradora_id'],
                'tabela_origens_id' => $associacao['tabela_origens_id'],
                'assinatura_id'     => $assinatura_id,
            ]);
        }
    }

    public function pixTrial(Request $request)
    {

        $valor = number_format((float) $request->precoPlano, 2, '.', '');

        $cpf = preg_replace('/[^0-9]/', '', $request->cpf);

        // Se o usuário ainda não tem CPF salvo, persiste agora
        if (auth()->check() && !auth()->user()->cpf && $cpf) {
            auth()->user()->update(['cpf' => $cpf]);
        }
        $body = [
            "calendario" => [
                "expiracao" => 3600 // Charge lifetime, specified in seconds from creation date
            ],
            "devedor" => [
                "cpf" => $cpf,
                "nome" => $request->nome
            ],
            "valor" => [
                "original" => (string) $valor
            ],
            "chave" => "130ceaee-b233-481e-8feb-6029a5429b75", // Pix key registered in the authenticated Efí account
            "solicitacaoPagador" => "Informe o número do identificador de pedido",

        ];

        $responsePix = $this->efi->pixCreateImmediateCharge([], $body);

        if($responsePix['txid']) {
            $params = [
                "id" => $responsePix['loc']['id']
            ];
            $responseQrcode = $this->efi->pixGenerateQRCode($params);

            $qrcode = $responseQrcode['qrcode'];

            // Remover prefixo 'http://' ou 'https://'
            $qrcode = preg_replace('/^https?:\/\//', '', $qrcode);

            // Adicionar espaço no final do código
            $qrcode = ' ' . $qrcode;

            // Salva txid na tabela pix_pendentes para o webhook atualizar
            \App\Models\PixPendente::create([
                'txid'    => $responsePix['txid'],
                'status'  => 'pending',
                'tipo'    => auth()->check() ? 'renewal' : 'new_user',
                'user_id' => auth()->check() ? auth()->id() : null,
            ]);

            return [
                "imagem" => $responseQrcode['imagemQrcode'],
                "copiacola" => $qrcode,
                "txid" => $responsePix['txid']
            ];
        }
        return "Erro";
    }

    /**
     * PIX Automático — Jornada 2
     * 1. Cria cobrança imediata (QR de ativação, valor simbólico)
     * 2. Cria location para a recorrência
     * 3. Cria recorrência vinculando location + txid
     * 4. Retorna QR code para o cliente autorizar no app do banco
     */
    public function pixAutomatico(Request $request)
    {
        $cpf      = preg_replace('/[^0-9]/', '', $request->cpf);
        $valor    = number_format((float) ($request->precoPlano ?? 29.90), 2, '.', '');
        $contrato = config('gerencianet.contrato_pix');
        $chavePix = config('gerencianet.default_key_pix');

        try {
            // Passo 1 — Cobrança imediata de ativação (jornada)
            $bodyCharge = [
                'calendario' => ['expiracao' => 3600],
                'devedor'    => ['cpf' => $cpf, 'nome' => $request->nome],
                'valor'      => ['original' => $valor],
                'chave'      => $chavePix,
                'solicitacaoPagador' => 'Autorização PIX Automático',
            ];
            $responsePix = $this->efi->pixCreateImmediateCharge([], $bodyCharge);
            $txid        = $responsePix['txid'];
            $locId       = $responsePix['loc']['id'];

            // Passo 2 — Gera QR Code da cobrança de ativação
            $responseQrcode = $this->efi->pixGenerateQRCode(['id' => $locId]);
            $qrcode         = $responseQrcode['qrcode'];

            // Passo 3 — Cria location para recorrência
            $responseLoc = $this->efi->pixCreateLocationRecurrenceAutomatic();
            $idLoc       = $responseLoc['id'];

            // Passo 4 — Cria recorrência vinculando location + txid de ativação
            $bodyRec = [
                'vinculo' => [
                    'contrato' => $contrato,
                    'devedor'  => ['cpf' => $cpf, 'nome' => $request->nome],
                    'objeto'   => 'Assinatura',
                ],
                'calendario' => [
                    'dataInicial'   => now()->format('Y-m-d'),
                    'periodicidade' => 'MENSAL',
                ],
                'valor' => [
                    'valorRec' => $valor,
                ],
                'politicaRetentativa' => 'PERMITE_3R_7D',
                'loc'      => $idLoc,
                'ativacao' => [
                    'dadosJornada' => ['txid' => $txid],
                ],
            ];
            $responseRec = $this->efi->pixCreateRecurrenceAutomatic([], $bodyRec);
            $idRec       = $responseRec['idRec'];

            // Salva na tabela pix_pendentes para o webhook atualizar
            \App\Models\PixPendente::create([
                'txid'    => $txid,
                'id_rec'  => $idRec,
                'status'  => 'pending',
                'tipo'    => auth()->check() ? 'renewal' : 'new_user',
                'user_id' => auth()->check() ? auth()->id() : null,
            ]);

            return response()->json([
                'imagem'    => $responseQrcode['imagemQrcode'],
                'copiacola' => $qrcode,
                'txid'      => $txid,
                'id_rec'    => $idRec,
            ]);

        } catch (\Exception $e) {
            \Log::error('PIX Automático erro: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Verifica se o PIX Automático foi autorizado (polling do frontend)
     * Para novos usuários: cria conta quando status = approved
     */
    public function verificarPagamentoPixAutomatico(Request $request)
    {
        $txid = $request->id;

        try {
            $resposta  = $this->efi->pixDetailCharge(['txid' => $txid]);
            $statusEfi = $resposta['status'] ?? null;
        } catch (\Exception $e) {
            return response()->json(['success' => false]);
        }

        if ($statusEfi !== 'CONCLUIDA') {
            return response()->json(['success' => false]);
        }

        $pixPendente = \App\Models\PixPendente::where('txid', $txid)->first();

        // Renovação de assinatura existente
        if (auth()->check()) {
            // Proteção contra polling duplo
            if (\App\Models\PixPagamento::where('txid', $txid)->exists()) {
                return response()->json(['success' => true, 'redirect' => route('dashboard')]);
            }

            $user       = auth()->user();
            $assinatura = $user->assinaturas()->first();

            $assinatura->next_charge   = Carbon::now()->addMonth();
            $assinatura->tipo          = 'PIX_AUTOMATICO';
            $assinatura->status        = 'ativo';
            $assinatura->contrato      = $pixPendente->id_rec ?? null;
            $assinatura->trial_ends_at = null;
            $assinatura->save();

            \App\Models\PixPagamento::create([
                'user_id'       => $user->id,
                'assinatura_id' => $assinatura->id,
                'txid'          => $txid,
                'valor'         => $assinatura->preco_total,
                'tipo'          => 'PIX_AUTOMATICO',
                'pago_em'       => Carbon::now(),
            ]);

            if ($pixPendente && $pixPendente->status !== 'approved') {
                $pixPendente->update(['status' => 'approved']);
            }

            return response()->json([
                'success'  => true,
                'redirect' => route('dashboard'),
            ]);
        }

        // Novo usuário
        $imagePath = null;
        if ($request->hasFile('imagem')) {
            $imagePath = $request->file('imagem')->store('users', 'public');
        }

        $user = User::create([
            'name'       => $request->nome,
            'email'      => $request->email,
            'password'   => $request->password,
            'phone'      => $request->phone,
            'cpf'        => preg_replace('/[^0-9]/', '', $request->cpf ?? ''),
            'imagem'     => $imagePath,
        ]);

        $assinatura = Assinatura::create([
            'user_id'           => $user->id,
            'tipo_plano_id'     => 1,
            'preco_base'        => 29.90,
            'emails_permitidos' => 1,
            'emails_extra'      => 1,
            'preco_total'       => 29.90,
            'status'            => 'ativo',
            'tipo'              => 'PIX_AUTOMATICO',
            'contrato'          => $pixPendente->id_rec,
            'next_charge'       => Carbon::now()->addMonth(),
        ]);

        EmailAssinatura::create([
            'assinatura_id'  => $assinatura->id,
            'email'          => $user->email,
            'user_id'        => $user->id,
            'is_administrador' => true,
        ]);

        $this->administradoraPlanos($assinatura->id);
        Auth::login($user);

        return response()->json([
            'success'  => true,
            'redirect' => route('dashboard'),
        ]);
    }







    public function pix(Request $request)
    {
        $cpf = preg_replace('/[^0-9]/', '', $request->cpf);
        $body = [
            "calendario" => [
                "expiracao" => 3600 // Charge lifetime, specified in seconds from creation date
            ],
            "devedor" => [
                "cpf" => $cpf,
                "nome" => $request->nome
            ],
            "valor" => [
                "original" => "0.05"
            ],
            "chave" => "130ceaee-b233-481e-8feb-6029a5429b75", // Pix key registered in the authenticated Efí account
            "solicitacaoPagador" => "Informe o número do identificador de pedido",

        ];

        $responsePix = $this->efi->pixCreateImmediateCharge([], $body);

        if($responsePix['txid']) {
            $params = [
                "id" => $responsePix['loc']['id']
            ];
            $responseQrcode = $this->efi->pixGenerateQRCode($params);

            $response = $this->efi->pixCreateLocationRecurrenceAutomatic();

            $id_loc = $response['id'];
            $txid = $responsePix['txid'];



            $body_pix = [
                "vinculo" => [
                    "contrato" => "63100864",
                    "devedor" => [
                        "cpf" => $cpf,
                        // "cnpj" => "11122233344444",
                        "nome" => $request->nome
                    ],
                    "objeto" => "Assinatura"
                ],
                "calendario" => [
                    "dataInicial" => date("Y-m-d"),
                    "periodicidade" => "MENSAL" // SEMANAL/MENSAL/TRIMESTRAL/SEMESTRAL/ANUAL
                ],
                "valor" => [
                    "valorRec" => "0.05",
                    // "valorMinimoRecebedor" => "30.00"
                ],
                "politicaRetentativa" => "NAO_PERMITE", // PERMITE_3R_7D
                "loc" => $id_loc,
                "ativacao" => [
                    "dadosJornada" => [
                        "txid" => $txid
                    ]
                ]
            ];

            return $body_pix;


//            return [
//              "imagem" => $responseQrcode['imagemQrcode'],
//              "copiacola" => $responseQrcode['qrcode'],
//              "txid" => $responsePix['txid']
//            ];
        }
        return "Erro";
    }

    public function historicoPagamentosPix(Request $request)
    {
        $cpf = \auth()->user()->first()->cpf;
        $params = [
            "inicio" => "2025-01-01T00:00:00Z",
            "fim" => "2025-12-30T23:59:59Z",
            "status" => "CONCLUIDA",
            "cpf" => $cpf,
        ];

        $response = $this->efi->pixListCharges($params);
        $dados = $response['cobs'];

        return view('assinaturas.historicopix', compact('dados'));

    }


    public function verificarPagamentoPIX(Request $request)
    {
        $txid = $request->id;

        try {
            $resposta = $this->efi->pixDetailCharge(['txid' => $txid]);
            $statusEfi = $resposta['status'] ?? null;
        } catch (\Exception $e) {
            return response()->json(['success' => false]);
        }

        if ($statusEfi !== 'CONCLUIDA') {
            return response()->json(['success' => false]);
        }

        // Se já foi processado antes (polling duplo), só redireciona
        if (\App\Models\PixPagamento::where('txid', $txid)->exists()) {
            return response()->json([
                'success'  => true,
                'redirect' => route('dashboard'),
            ]);
        }

        $pixPendente = \App\Models\PixPendente::where('txid', $txid)->first();
        if ($pixPendente && $pixPendente->status !== 'approved') {
            $pixPendente->update(['status' => 'approved']);
        }

        $user       = auth()->user();
        $assinatura = $user->assinaturas()->first();

        $assinatura->next_charge   = Carbon::now()->addMonth();
        $assinatura->preco_total   = $request->precoFinal;
        $assinatura->tipo          = 'PIX';
        $assinatura->status        = 'ativo';
        $assinatura->trial_ends_at = null;
        $assinatura->save();

        \App\Models\PixPagamento::create([
            'user_id'       => $user->id,
            'assinatura_id' => $assinatura->id,
            'txid'          => $txid,
            'valor'         => $request->precoFinal,
            'tipo'          => 'PIX',
            'pago_em'       => Carbon::now(),
        ]);

        session()->flash('success', 'Sua Assinatura foi renovada até a data ' . $assinatura->next_charge->format('d/m/Y'));

        return response()->json([
            'success'  => true,
            'redirect' => route('dashboard'),
        ]);
    }





    public function verificarPagamento(Request $request)
    {
        $txid = $request->id;

        try {
            $resposta = $this->efi->pixDetailCharge(['txid' => $txid]);
            $statusEfi = $resposta['status'] ?? null;
        } catch (\Exception $e) {
            return response()->json(['success' => false]);
        }

        if ($statusEfi !== 'CONCLUIDA') {
            return response()->json(['success' => false]);
        }

        $pixPendente = \App\Models\PixPendente::where('txid', $txid)->first();
        if ($pixPendente && $pixPendente->status !== 'approved') {
            $pixPendente->update(['status' => 'approved']);
        }

        $imagePath = null;
        if ($request->hasFile('imagem')) {
            $imagePath = $request->file('imagem')->store('users', 'public');
        }

        $user = User::create([
            'name'       => $request->nome,
            'email'      => $request->email,
            'password'   => $request->password,
            'phone'      => $request->phone,
            'cpf'        => preg_replace('/[^0-9]/', '', $request->cpf ?? ''),
            'birth_date' => $request->data_nascimento ?: null,
            'imagem'     => $imagePath,
        ]);

        $assinatura = Assinatura::create([
            'user_id'           => $user->id,
            'tipo_plano_id'     => 1,
            'preco_base'        => 29.90,
            'emails_permitidos' => 1,
            'emails_extra'      => 1,
            'preco_total'       => 29.90,
            'status'            => 'ativo',
            'subscription_id'   => null,
            'tipo'              => 'PIX',
            'next_charge'       => Carbon::now()->addMonth(),
        ]);

        EmailAssinatura::create([
            'assinatura_id'  => $assinatura->id,
            'email'          => $user->email,
            'user_id'        => $user->id,
            'is_administrador' => true,
        ]);

        $this->administradoraPlanos($assinatura->id);

        // Salva histórico de pagamento
        \App\Models\PixPagamento::create([
            'user_id'       => $user->id,
            'assinatura_id' => $assinatura->id,
            'txid'          => $request->id,
            'valor'         => 29.90,
            'tipo'          => 'PIX',
            'pago_em'       => Carbon::now(),
        ]);

        Auth::login($user);

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard')
        ]);


    }



    /*
     * Esse metodo da rota /assinatura/alterar quando acaba a assinatura trial do usuario cai nessa rota
     * A 2 caminhos aqui para a assinatura:
     * 1º se o usuario tiver um codigo promocional
     * 2º cadastro sem codigo promocional valor cheio
     * */
    public function storeTrial(Request $request)
    {

        //return $request->all();
        if($request->cupom_promocional) {

            return $this->storePromocionalTrialToPaid($request);
        } else {

            try {
                $user = User::find(auth()->user()->id);
                $params = ["id" => config('gerencianet.plan_id')];
                $items = [
                    [
                        "name" => "Plano Individual",
                        "amount" => 1,
                        "value" => (int) round($request->preco_base * 100)
                    ]
                ];
                // Dados do cliente
                $customer = [
                    "name" => $user->name,
                    "cpf" => "01375583174",
                    "phone_number" => preg_replace('/[^0-9]/', '', $user->phone),
                    "email" => $user->email,
                    "birth" => '1986-10-24'
                ];

                // Endereço (também necessário)
                $billingAddress = [
                    "street" => "Avenida José Leandro da Cruz",
                    "number" => "S/N",
                    "neighborhood" => "Jardim Luz", // Adicionar campo no formulário
                    "zipcode" => "74915130", // Adicionar campo no formulário
                    "city" => "Aparecida de Goiânia", // Adicionar campo no formulário
                    "state" => "GO", // Adicionar campo no formulário
                ];



                $body = [
                    "items" => $items,
                    "payment" => [
                        "credit_card" => [
                            "billing_address" => $billingAddress,
                            "payment_token" => $request->paymentToken,
                            "customer" => $customer
                        ]
                    ],
                    "metadata" => [
                        "notification_url" => config('gerencianet.notification_url')
                    ]
                ];

                $response = $this->efi->createOneStepSubscription($params, $body);

                $assinatura = Assinatura::where("user_id",$user->id)->first();
                $assinatura->preco_base = 29.90;
                $assinatura->preco_total = $request->preco_base;
                $assinatura->status = 'ativo';
                $assinatura->subscription_id = $response['data']['subscription_id'];
                $assinatura->next_charge = now()->addMonth();
                $assinatura->trial_ends_at = null;
                $assinatura->tipo = "CARTAO";
                $assinatura->save();

                session()->flash('success', 'Assinatura Atualizada com sucesso');
                return response()->json([
                    'success' => true,
                    'redirect' => route('dashboard')
                ]);


            } catch (EfiException $e) {
                return response()->json([
                    'error' => true,
                    'message' => $e->errorDescription,
                    'code' => $e->code
                ], 400);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => true,
                    'message' => $e->getMessage()
                ], 500);
            }
        }




    }


    public function storePromocionalTrialToPaid(Request $request)
    {
        $cupom = null;
        $precoBase = (int) round($request->preco_base * 100);
        $precoExtraPorEmail = 2990; // 29.90 reais em centavos

        if ($request->filled('cupom_promocional')) {
            $cupom = Cupom::where('codigo', $request->codigo_cupom)
                ->where('ativo', 1)
                ->where('validade', '>=', now()->format('Y-m-d H:i:s'))
                ->whereRaw('usos < usos_maximos')
                ->first();




            if (!$cupom) {
                return response()->json([
                    'error' => true,
                    'message' => 'Cupom inválido ou expirado'
                ], 400);
            }

            // Verificar validade
            if (now()->format('Y-m-d H:i:s') > $cupom->validade) {
                return response()->json([
                    'error' => true,
                    'message' => 'Cupom expirado'
                ], 400);
            }

            // Verificar usos máximos
            if ($cupom->usos_maximos && $cupom->usos >= $cupom->usos_maximos) {
                return response()->json([
                    'error' => true,
                    'message' => 'Cupom já utilizado o máximo de vezes'
                ], 400);
            }

            // Aplicar descontos
            //$precoBase -= $cupom->desconto_plano * 100;



            ///$precoExtraPorEmail -= $cupom->desconto_extra * 100;

            // Garantir valores mínimos
            //$precoBase = max($precoBase, 0);
            $precoExtraPorEmail = max($precoExtraPorEmail, 0);



        }

        $imagePath = null;
        if ($request->hasFile('imagem')) {
            $imagePath = $request->file('imagem')->store('users', 'public');
        }

        try {
            $params = [
                "id" => 13290
            ];

            $items = [
                [
                    "name" => "Plano Start",
                    "amount" => 1,
                    "value" => $precoBase
                ]
            ];

            $customer = [
                "name" => auth()->user()->name,
                "cpf" => auth()->user()->cpf,
                "phone_number" => preg_replace('/[^0-9]/', '', auth()->user()->phone),
                "email" => auth()->user()->email,
                "birth" => auth()->user()->birth_date instanceof \Carbon\Carbon ? auth()->user()->birth_date->format('Y-m-d') : auth()->user()->birth_date
            ];

            // Endereço (também necessário)
            $billingAddress = [
                "street" => $request->street, // Adicionar campo no formulário
                "number" => !empty($request->number) ? $request->number : "S/N", // Adicionar campo no formulário
                "neighborhood" => $request->neighborhood, // Adicionar campo no formulário
                "zipcode" => str_replace('-', '', $request->zipcode), // Adicionar campo no formulário
                "city" => $request->city, // Adicionar campo no formulário
                "state" => $request->state, // Adicionar campo no formulário
            ];

            $body = [
                "items" => $items,
                "payment" => [
                    "credit_card" => [
                        "billing_address" => $billingAddress,
                        "payment_token" => $request->paymentToken,
                        "customer" => $customer
                    ]
                ],
                "metadata" => [
                    "notification_url" => config('gerencianet.notification_url')
                ]
            ];


            $response = $this->efi->createOneStepSubscription($params, $body);

            if (!isset($response['data']['subscription_id'])) {
                return response()->json([
                    'success' => false,
                    'data' => $response,
                    'redirect' => ""
                ]);
            }

            $assinatura = Assinatura::where("user_id",auth()->user()->id)->first();
            $assinatura->preco_base = 29.90;
            //$assinatura->emails_permitidos = 1;
            //$assinatura->emails_extra = 1;
            $assinatura->tipo_plano_id = null;
            $assinatura->cupom_id = $cupom->id;
            $assinatura->preco_total = $request->preco_base;
            $assinatura->status = 'ativo';
            $assinatura->subscription_id = $response['data']['subscription_id'];
            $assinatura->trial_ends_at = null;
            $assinatura->save();


            session()->flash('success', 'Assinatura Atualizada com sucesso');
            return response()->json([
                'success' => true,
                'redirect' => route('dashboard')

            ]);



        }catch(Exception $e) {

        }




    }



    public function storePromocional(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'cpf' => 'required|string',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|confirmed|min:8|confirmed',
            'paymentToken' => 'required|string',
            'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cupom_promocional' => 'required|string|max:10'
        ]);

        $cupom = null;
        $precoBase = 29.90; // 250 reais em centavos
        $precoExtraPorEmail = 29.90; // 50 reais em centavos

        if ($request->filled('cupom_promocional')) {
            $cupom = Cupom::where('codigo', $request->cupom_promocional)
                ->where('ativo', true)
                ->first();




            if (!$cupom) {
                return response()->json([
                    'error' => true,
                    'message' => 'Cupom inválido ou expirado'
                ], 400);
            }

            // Verificar validade
            if (now()->gt($cupom->validade)) {
                return response()->json([
                    'error' => true,
                    'message' => 'Cupom expirado'
                ], 400);
            }

            // Verificar usos máximos
            if ($cupom->usos_maximos && $cupom->usos >= $cupom->usos_maximos) {
                return response()->json([
                    'error' => true,
                    'message' => 'Cupom já utilizado o máximo de vezes'
                ], 400);
            }

            // Aplicar descontos
            $precoBase -= $cupom->desconto_plano * 100;
            $precoExtraPorEmail -= $cupom->desconto_extra * 100;

            // Garantir valores mínimos
            $precoBase = max($precoBase, 0);
            $precoExtraPorEmail = max($precoExtraPorEmail, 0);



        }

        $imagePath = null;
        if ($request->hasFile('imagem')) {
            $imagePath = $request->file('imagem')->store('users', 'public');
        }

        try {

            $params = [
                "id" => 13290
            ];

            $items = [
                [
                    "name" => "Plano Multiusuário",
                    "amount" => 1,
                    "value" => $precoBase
                ]
            ];



            $customer = [
                "name" => $request->name,
                "cpf" => preg_replace('/[^0-9]/', '', $request->cpf),
                "phone_number" => preg_replace('/[^0-9]/', '', $request->phone),
                "email" => $request->email,
                "birth" => $request->birth_date // Você precisa adicionar este campo no formulário!
            ];



            // Endereço (também necessário)
            $billingAddress = [
                "street" => $request->street, // Adicionar campo no formulário
                "number" => !empty($request->number) ? $request->number : "S/N", // Adicionar campo no formulário
                "neighborhood" => $request->neighborhood, // Adicionar campo no formulário
                "zipcode" => str_replace('-', '', $request->zipcode), // Adicionar campo no formulário
                "city" => $request->city, // Adicionar campo no formulário
                "state" => $request->state, // Adicionar campo no formulário
            ];

            $body = [
                "items" => $items,
                "payment" => [
                    "credit_card" => [
                        "billing_address" => $billingAddress,
                        "payment_token" => $request->paymentToken,
                        "customer" => $customer
                    ]
                ],
                "metadata" => [
                    "notification_url" => config('gerencianet.notification_url')
                ]
            ];


            $response = $this->efi->createOneStepSubscription($params, $body);

            if (!isset($response['data']['subscription_id'])) {

                return response()->json([
                    'success' => false,
                    'data' => $response,
                    'redirect' => ""
                ]);
            }



            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'phone' => $request->phone,
                'imagem' => $imagePath,
            ]);



            // 2. Vincular o ID da assinatura ao usuário
            $assinatura = Assinatura::create([
                'user_id' => $user->id,
                'tipo_plano_id' => null, // ID do plano Individual
                'preco_base' => 29.90,
                'emails_permitidos' => 1,
                'emails_extra' => 1,
                'preco_total' => 29.90, // Preço base sem e-mails extras
                'status' => 'ativo',
                'tipo' => 'cartao',
                'subscription_id' => $response['data']['subscription_id']
            ]);

            EmailAssinatura::create([
                'assinatura_id' => $assinatura->id,
                'email' => $user->email,
                'user_id' => $user->id,
                'is_administrador' => true, // Marca este e-mail como administrador
            ]);

            if ($cupom) {
                $cupom->increment('usos');
                $assinatura->update(['cupom_id' => $cupom->id]);
            }

            // Dispara o email
            //SendVerificationEmail::dispatch($user);
            Auth::login($user);

            return response()->json([
                'success' => true,
                'data' => $response,
                'redirect' => route('dashboard')
            ]);



        } catch (EfiException $e) {
            return response()->json([
                'error' => true,
                'message' => $e->errorDescription,
                'code' => $e->code
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function storeEmpresarial(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'cpf' => 'required|string',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|confirmed|min:8|confirmed',
            'paymentToken' => 'required|string',
            'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'

        ]);

        $imagePath = null;
        if ($request->hasFile('imagem')) {
            $imagePath = $request->file('imagem')->store('users', 'public');
        }

        try {
            // Parâmetros do plano
            $params = [
                "id" => 13290 // ID do plano multiusuario
            ];

            // Itens da assinatura
            $items = [
                [
                    "name" => "Plano Multiusuário",
                    "amount" => 1,
                    "value" => 2990 // R$250,00 em centavos
                ]
            ];



            // Dados do cliente
            $customer = [
                "name" => $request->name,
                "cpf" => preg_replace('/[^0-9]/', '', $request->cpf),
                "phone_number" => preg_replace('/[^0-9]/', '', $request->phone),
                "email" => $request->email,
                "birth" => $request->birth_date // Você precisa adicionar este campo no formulário!
            ];

            // Endereço (também necessário)
            $billingAddress = [
                "street" => $request->street, // Adicionar campo no formulário
                "number" => !empty($request->number) ? $request->number : "S/N", // Adicionar campo no formulário
                "neighborhood" => $request->neighborhood, // Adicionar campo no formulário
                "zipcode" => str_replace('-', '', $request->zipcode), // Adicionar campo no formulário
                "city" => $request->city, // Adicionar campo no formulário
                "state" => $request->state, // Adicionar campo no formulário
            ];



            $body = [
                "items" => $items,
                "payment" => [
                    "credit_card" => [
                        "billing_address" => $billingAddress,
                        "payment_token" => $request->paymentToken,
                        "customer" => $customer
                    ]
                ],
                "metadata" => [
                    "notification_url" => config('gerencianet.notification_url')
                ]
            ];

            $response = $this->efi->createOneStepSubscription($params, $body);

            // Aqui você deve:
            // 1. Criar o usuário no seu banco de dados
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'phone' => $request->phone,
                'imagem' => $imagePath,
            ]);

            // 2. Vincular o ID da assinatura ao usuário
            $assinatura = Assinatura::create([
                'user_id' => $user->id,
                'tipo_plano_id' => 2, // ID do plano Individual
                'preco_base' => 29.90,
                'emails_permitidos' => 1,
                'emails_extra' => 1,
                'preco_total' => 29.90, // Preço base sem e-mails extras
                'status' => 'ativo',
                'tipo' => 'cartao',
                'subscription_id' => $response['data']['subscription_id']
            ]);

            EmailAssinatura::create([
                'assinatura_id' => $assinatura->id,
                'email' => $user->email,
                'user_id' => $user->id,
                'is_administrador' => true, // Marca este e-mail como administrador
            ]);


            // Dispara o email
            //SendVerificationEmail::dispatch($user);
            Auth::login($user);


            return response()->json([
                'success' => true,
                'data' => $response,
                'redirect' => route('bemvindo', ['user' => $user->id])
            ]);

        } catch (EfiException $e) {
            return response()->json([
                'error' => true,
                'message' => $e->errorDescription,
                'code' => $e->code
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function historicoPagamentos()
    {
        try {
            $assinatura = Assinatura::where('user_id', auth()->id())->firstOrFail();
            $params = ['id' => $assinatura->subscription_id];

            // Buscar detalhes da assinatura
            $response = $this->efi->detailSubscription($params);

            $dados = [
                'proxima_fatura' => [
                    'data' => Carbon::parse($response['data']['next_execution'])->format('d/m/Y'),
                    'valor' => number_format($response['data']['value'] / 100, 2, ',', '.')
                ],
                'historico' => []
            ];

            foreach ($response['data']['history'] as $evento) {
                // Buscar detalhes completos da cobrança
                $chargeResponse = $this->efi->detailCharge(['id' => $evento['charge_id']]);

                // Formatar dados conforme nova estrutura
                $dados['historico'][] = [
                    'charge_id' => $chargeResponse['data']['charge_id'],
                    'status' => $chargeResponse['data']['status'],
                    'valor_total' => number_format($chargeResponse['data']['total'] / 100, 2, ',', '.'),
                    'data_criacao' => Carbon::parse($chargeResponse['data']['created_at'])->format('d/m/Y H:i'),
                    'metodo_pagamento' => $chargeResponse['data']['payment']['method'] ?? 'N/A',
                    'ultima_mensagem' => end($chargeResponse['data']['history'])['message'] ?? 'Sem informações',
                    'cliente' => [
                        'nome' => $chargeResponse['data']['customer']['name'],
                        'email' => $chargeResponse['data']['customer']['email']
                    ],
                    'items' => array_map(function($item) {
                        return [
                            'nome' => $item['name'],
                            'valor_unitario' => number_format($item['value'] / 100, 2, ',', '.'),
                            'quantidade' => $item['amount']
                        ];
                    }, $chargeResponse['data']['items'])
                ];
            }

            return view('assinaturas.historico', compact('dados'));

        } catch (\Exception $e) {
            \Log::error("Erro ao buscar histórico: " . $e->getMessage());
            return redirect()->back()->withErrors('Erro ao carregar histórico de pagamentos');
        }
    }

}
