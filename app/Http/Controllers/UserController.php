<?php

namespace App\Http\Controllers;

use App\Models\Assinatura;
use App\Models\Cupom;
use App\Models\EmailAssinatura;
use App\Models\TipoPlano;
use App\Models\User;
use Efi\EfiPay;
use Efi\Exception\EfiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
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
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'sandbox' => true,
            'debug' => false
        ];

        $this->efi = new EfiPay($options);

    }



    public function index()
    {

        $user = Auth::user();
        if($user->primeiro_acesso == 0) {
            $user->primeiro_acesso = 1;
            $user->save();
        }
        $assinatura_id = Assinatura::where("user_id",auth()->user()->id)->first()->id;
        $users = User::whereIn(
            'id',
            EmailAssinatura::where('assinatura_id', $assinatura_id)

                ->pluck('user_id')
        )->get();

        $assinatura = Assinatura::where("user_id",auth()->user()->id)->first();

        if($assinatura->status == "trial") {
            $plan = TipoPlano::find($assinatura->tipo_plano_id);
            $valor = $assinatura->preco_total;
            $limite_gratuito = 1; // 5 por padrão
            $extra = number_format($plan->valor_por_email,2,",",".");
        } elseif($assinatura->cupom_id and empty($assinatura->tipo_plano_id)) {
            $cupom = Cupom::find($assinatura->cupom_id);
            $valor = $assinatura->preco_total;
            $limite_gratuito = $assinatura->emails_permitidos;
            $extra = 29.90 - $cupom->desconto_extra;
        } elseif (empty($assinatura->cupom_id) && in_array($assinatura->tipo_plano_id, [1, 2])) {
            $plan = TipoPlano::find($assinatura->tipo_plano_id);
            $valor = $assinatura->preco_total;
            $limite_gratuito = $assinatura->emails_permitidos; // 5 por padrão
            $extra = number_format($plan->valor_por_email,2,",",".");
        }



        return view('users.manage', compact('users','assinatura','valor','extra'));
    }

    private function atualizarAssinaturaEFi($assinatura)
    {

        $params = ["id" => $assinatura->subscription_id];

        $body = [
            "items" => [
                [
                    "name" => "Plano Multiusuário",
                    "value" => intval($assinatura->preco_total * 100)
                ]
            ]
        ];

        // Atualiza a assinatura na EFI
        $this->efi->updateSubscription($params, $body);

    }

    public function storeUser(Request $request)
    {



        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'email.unique' => 'Este e-mail já está cadastrado no sistema.',
            'phone.unique' => 'Este telefone já está cadastrado no sistema.',
        ]);

        $emails_cobrados = 0;
        DB::beginTransaction();




        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('users', 'public');
            }

            // Passo 1: Criar o usuário
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => bcrypt('12345678'), // Senha padrão criptografada
                'imagem' => $imagePath
            ]);



            $user->email_verified_at = now(); // Define o usuário como verificado
            $user->primeiro_acesso = 1;
            $user->save();



            // Passo 2: Obter a Assinatura do Administrador
            $assinatura = Assinatura::where('user_id', auth()->id())->firstOrFail();





            if($assinatura->user_id != 117 && $assinatura->user_id != 190) {

                if (!$assinatura) {
                    throw new \Exception("Assinatura não encontrada.");
                }

                $cupom_status = false;
                if($assinatura->cupom_id and empty($assinatura->tipo_plano_id)) {
                    $cupom = Cupom::find($assinatura->cupom_id);
                    $preco_base = $assinatura->preco_base;
                    $limite_gratuito = $assinatura->emails_permitidos;
                    $preco_extra_por_email = $assinatura->preco_base;
                    $cupom_status = true;
                } elseif (empty($assinatura->cupom_id) && in_array($assinatura->tipo_plano_id, [1, 2])) {




                    $plan = TipoPlano::find($assinatura->tipo_plano_id);





                    $preco_base = $plan->valor_base;
                    $limite_gratuito = $assinatura->emails_permitidos;
                    $preco_extra_por_email = $plan->valor_por_email;
                }
                $assinatura->emails_extra += 1;


                //            // Verifica se já passou do limite gratuito
                if ($assinatura->emails_extra > $limite_gratuito) {
                    $emails_cobrados = $assinatura->emails_extra - $limite_gratuito;
                    if($cupom_status) {
                        $valor_total_sem_desconto = $preco_base + ($emails_cobrados * $preco_extra_por_email);
                        $assinatura->preco_total = max($valor_total_sem_desconto - $cupom->desconto_plano, 0); // nunca menor que 0
                    } else {
                        $assinatura->preco_total = $preco_base + ($emails_cobrados * $preco_extra_por_email);
                    }
                } else {
                    $assinatura->preco_total = $preco_base; // Se ainda está no limite gratuito, mantém o preço base
                }
//

                $assinatura->save();

//                if($assinatura->status != "trial" && $assinatura->tipo != "PIX") {
//                    $this->atualizarAssinaturaEFi($assinatura);
//                }


            }




//
//

//

//
//            // Passo 3: Cadastrar o novo e-mail na Tabela `emails_assinatura`




            EmailAssinatura::create([
                'assinatura_id' => (int) $assinatura->id,
                'email' => $validated['email'],
                'user_id' => (int) $user->id,
                'is_administrador' => false,
            ]);

            DB::commit(); // Confirma as transações

            // Recuperar todos os usuários da assinatura
            $users = User::whereIn(
                'id',
                EmailAssinatura::where('assinatura_id', $assinatura->id)
                    ->pluck('user_id')
            )
                //->where('status', 1)
                ->orderBy("id","desc")
                ->get();

            // Retornar o HTML da tabela de usuários
            $html = view('partials.user-table', compact('users'))->render();
            return response()->json(['success' => true, 'html' => $html,'emails_extra' => $assinatura->emails_extra,'preco_total' => number_format($assinatura->preco_total,2,",","."),'emails_cobrados' => $emails_cobrados]);
        } catch (\Exception $e) {
            DB::rollBack(); // Reverte em caso de erro
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }

    }




    public function storeUserold(Request $request)
    {
        // Validação dos dados
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $emails_cobrados = 0;
        DB::beginTransaction(); // Inicia uma transação

        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('users', 'public');
            }

            // Passo 1: Criar o usuário
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => bcrypt('12345678'), // Senha padrão criptografada
                'imagem' => $imagePath
            ]);

            $user->email_verified_at = now(); // Define o usuário como verificado
            $user->primeiro_acesso = 1;
            $user->save();

            // Passo 2: Obter a Assinatura do Administrador
            $assinatura = Assinatura::where('user_id', auth()->id())->firstOrFail();


            if (!$assinatura) {
                throw new \Exception("Assinatura não encontrada.");
            }

            $cupom_status = false;
            if($assinatura->cupom_id and empty($assinatura->tipo_plano_id)) {
                $cupom = Cupom::find($assinatura->cupom_id);
                $preco_base = $assinatura->preco_base;
                $limite_gratuito = $assinatura->emails_permitidos;
                $preco_extra_por_email = $assinatura->preco_base;
                $cupom_status = true;
            } elseif (empty($assinatura->cupom_id) && in_array($assinatura->tipo_plano_id, [1, 2])) {
                $plan = TipoPlano::find($assinatura->tipo_plano_id);
                $preco_base = $plan->valor_base;
                $limite_gratuito = $assinatura->emails_permitidos;
                $preco_extra_por_email = $plan->valor_por_email;
            }
//
//
//            // Definir valores do plano
//
//
//            // Aumenta emails_extra
            $assinatura->emails_extra += 1;
//
//            // Verifica se já passou do limite gratuito
            if ($assinatura->emails_extra > $limite_gratuito) {
                $emails_cobrados = $assinatura->emails_extra - $limite_gratuito;
                if($cupom_status) {
                    $valor_total_sem_desconto = $preco_base + ($emails_cobrados * $preco_extra_por_email);
                    $assinatura->preco_total = max($valor_total_sem_desconto - $cupom->desconto_plano, 0); // nunca menor que 0
                } else {
                    $assinatura->preco_total = $preco_base + ($emails_cobrados * $preco_extra_por_email);
                }
            } else {
                $assinatura->preco_total = $preco_base; // Se ainda está no limite gratuito, mantém o preço base
            }
//

            $assinatura->save();
//
//
            if($assinatura->status != "trial" && $assinatura->tipo != "PIX") {
                $this->atualizarAssinaturaEFi($assinatura);
            }
//

//
//            // Passo 3: Cadastrar o novo e-mail na Tabela `emails_assinatura`
            EmailAssinatura::create([
                'assinatura_id' => (int) $assinatura->id,
                'email' => $validated['email'],
                'user_id' => (int) $user->id,
                'is_administrador' => false,
            ]);

            DB::commit(); // Confirma as transações

            // Recuperar todos os usuários da assinatura
            $users = User::whereIn(
                'id',
                EmailAssinatura::where('assinatura_id', $assinatura->id)
                    ->pluck('user_id')
            )
                //->where('status', 1)
                ->orderBy("id","desc")
                ->get();

            // Retornar o HTML da tabela de usuários
            $html = view('partials.user-table', compact('users'))->render();
            return response()->json(['success' => true, 'html' => $html,'emails_extra' => $assinatura->emails_extra,'preco_total' => number_format($assinatura->preco_total,2,",","."),'emails_cobrados' => $emails_cobrados]);
        } catch (\Exception $e) {
            DB::rollBack(); // Reverte em caso de erro
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }

    }


    public function alterar(Request $request)
    {
        $user_id = (int) $request->userId;
        $status  = $request->status == 'false' ? 0 : 1;

        // Administrador não pode se auto-desativar
        if ($user_id === (int) auth()->id()) {
            return response()->json(['success' => false, 'error' => 'Você não pode desativar sua própria conta.'], 403);
        }

        DB::beginTransaction();

        try {
            $user = User::findOrFail($user_id);
            $id   = (int) auth()->id();

            // Buscar a assinatura do administrador
            $assinatura = Assinatura::where('user_id', $id)->firstOrFail();

            // Verificar se o usuário pertence à assinatura do admin
            $pertence = EmailAssinatura::where('assinatura_id', $assinatura->id)
                ->where('user_id', $user_id)
                ->exists();

            if (!$pertence) {
                return response()->json(['success' => false, 'error' => 'Usuário não pertence à sua assinatura.'], 403);
            }


            $cupom_status = false;
            if($assinatura->cupom_id and empty($assinatura->tipo_plano_id)) {
                $cupom = Cupom::find($assinatura->cupom_id);
                $preco_base = $assinatura->preco_base;
                $limite_gratuito = $assinatura->emails_permitidos;
                $preco_extra_por_email = $assinatura->preco_base;
                $cupom_status = true;
            } elseif (empty($assinatura->cupom_id) && in_array($assinatura->tipo_plano_id, [1,2])) {
                $tipo_plano_id = (int) $assinatura->tipo_plano_id;
                $plan = TipoPlano::find($tipo_plano_id);
                $preco_base = $plan->valor_base;
                $limite_gratuito = $assinatura->emails_permitidos; // 5 por padrão
                $preco_extra_por_email = $plan->valor_por_email;
            }







            // Definir valores do plano


            // Verificar se está ativando ou desativando
            if ($status == 1) {
                // Se estiver ativando e o usuário estava desativado, incrementar emails_extra
                if ($user->status == 0) {
                    $assinatura->emails_extra += 1;
                }
            } else {
                // Se estiver desativando e emails_extra for maior que zero, decrementa
                if ($user->status == 1 && $assinatura->emails_extra > 0) {
                    $assinatura->emails_extra -= 1;
                }
            }
            //dd($assinatura->emails_extra);
            // Recalcular o preço total da assinatura
            if ($assinatura->emails_extra > $limite_gratuito) {
                $emails_cobrados = $assinatura->emails_extra - $limite_gratuito;

                if($cupom_status) {
                    $valor_total_sem_desconto = $preco_base + ($emails_cobrados * $preco_extra_por_email);
                    $assinatura->preco_total = max($valor_total_sem_desconto - $cupom->desconto_plano, 29.90); // nunca menor que 0
                } else {
                    $valor_total_sem_desconto = $preco_base + ($emails_cobrados * $preco_extra_por_email);
                    $assinatura->preco_total = max($valor_total_sem_desconto, 29.90); // nunca menor que 0
                }
            } else {

                $emails_cobrados = 0;
                $assinatura->preco_total = max($preco_base, 29.90);
            }

            // Salvar a assinatura
            $assinatura->save();




            // Atualizar o status do usuário
            $user->status = $status;
            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'emails_extra' => $assinatura->emails_extra,
                'preco_total' => number_format($assinatura->preco_total, 2, ",", "."),
                'emails_cobrados' => $emails_cobrados
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Reverte em caso de erro
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }






    }


    public function storeUserOld2222(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::beginTransaction(); // Inicia uma transação

        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('users', 'public');
            }




            // Passo 1: Cadastrar o usuário
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => '12345678', // Senha padrão
                'imagem' => $imagePath

            ]);

            $user->email_verified_at = now(); // Define o usuário como verificado automaticamente
            $user->save();

            // Passo 2: Lógica de Assinaturas
            $assinatura = Assinatura::where('user_id', auth()->id())->firstOrFail();
            if($assinatura) {
                $assinatura->emails_extra += 1;
                $assinatura->save();
            }
            //$emailCount = $assinatura->emailsAssinatura()->count();
            if ($assinatura->emails_extra > 5) {
                //$emailsExtra = $emailCount + 1;
                $assinatura->update([
                    'preco_total' => $assinatura->preco_total + 25

                ]);
            }

            // Passo 3: Atualizar a tabela `emails_assinatura`
            EmailAssinatura::create([
                'assinatura_id' => $assinatura->id,
                'email' => $validated['email'],
                'user_id' => $user->id,
                'is_administrador' => false,

            ]);


            DB::commit(); // Confirma as transações

            // Recuperar todos os usuários da assinatura do usuário logado
            $assinatura_id = Assinatura::where("user_id",auth()->user()->id)->first()->id;

            $users = User::whereIn(
                'id',
                EmailAssinatura::where('assinatura_id', $assinatura_id)
                    ->where('is_administrador', 0)
                    ->pluck('user_id')
            )->get();

            // Retornar o HTML da tabela de usuários
            $html = view('partials.user-table', compact('users'))->render();

            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            DB::rollBack(); // Reverte em caso de erro
            return $e->getMessage();
            //return response()->json(['success' => false], 500);
        }
    }

    public function getUser(Request $request)
    {

        // Busca o usuário pelo ID
        $user = User::find($request->id);
        $user->imagem = $user->imagem ? asset('storage/' . $user->imagem) : null;
        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }
        // Retorna os dados do usuário como JSON
        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        $users = Auth::user()->subscription->users;

        return response()->json([
            'success' => true,
            'html' => view('partials.user-table', ['users' => $users])->render(),
        ]);
    }

    public function deletar(Request $request)
    {
        $emailAssinatura = EmailAssinatura::where('user_id', $request->id);
        if ($emailAssinatura->count() == 1) {
            $emailAssinatura->delete();
        }

        $user = User::find($request->id);
        if ($user) {
            // Deleta a imagem se existir
            if ($user->imagem) {
                Storage::disk('public')->delete($user->imagem);
            }
            $user->delete();
        }

        $assinatura_id = Assinatura::where("user_id", auth()->user()->id)->first()->id;
        $users = User::whereIn(
            'id',
            EmailAssinatura::where('assinatura_id', $assinatura_id)
                ->where('is_administrador', 0)
                ->pluck('user_id')
        )->get();

        return [
            "success" => true,
            'html' => view('partials.user-table', ['users' => $users])->render(),
        ];
    }

    public function update(Request $request)
    {

        $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|max:255|unique:users,email,' . $request->id_edit,
            'phone'  => 'nullable|string|max:20|unique:users,phone,' . $request->id_edit,
            'imagem' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'email.unique' => 'Este e-mail já está cadastrado no sistema.',
            'phone.unique' => 'Este telefone já está cadastrado no sistema.',
        ]);

        $user = User::find($request->id_edit);
        $imagemAntiga = $user->imagem;

        // Atualiza os campos do usuário
        $emailAntigo = $user->email;

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        if ($request->hasFile('imagem')) {
            if (!empty($user->imagem) && Storage::disk('public')->exists($imagemAntiga)) {
                Storage::disk('public')->delete($imagemAntiga);
            }
            $imagePath = $request->file('imagem')->store('users', 'public');
            $user->imagem = $imagePath;
        }

        $user->save();

        // Atualiza o email na tabela emails_assinatura se mudou
        if ($emailAntigo !== $request->email) {
            EmailAssinatura::where('user_id', $user->id)
                ->update(['email' => $request->email]);
        }

        $assinatura_id = Assinatura::where("user_id",auth()->user()->id)->first()->id;

        $users = User::whereIn(
            'id',
            EmailAssinatura::where('assinatura_id', $assinatura_id)
                ->where('is_administrador', 0)
                ->pluck('user_id')
        )->get();

        // Retornar o HTML da tabela de usuários
        $html = view('partials.user-table', compact('users'))->render();
        return response()->json([
            'success' => true,
            'message' => 'Dados atualizados com sucesso!',
            'user' => $user,
            'html' => $html
        ]);
    }



}
