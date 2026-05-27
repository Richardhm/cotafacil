<?php

use App\Http\Controllers\CallbackController;
use App\Http\Controllers\ConfiguracoesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GerenciadorController;
use App\Http\Controllers\LayoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AssinaturaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TabelaController;
use App\Models\EmailAssinatura;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Jobs\SendSuggestionEmail;
use App\Http\Controllers\BemvindoController;
use App\Http\Controllers\PixWebhookController;
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\TeamUserController;


Route::get('/dev/atualizar-assinatura-efi/{id}/{valor_centavos}', function ($id, $valor_centavos) {
    $mode    = config('gerencianet.mode');
    $options = [
        'client_id'     => config("gerencianet.{$mode}.client_id"),
        'client_secret' => config("gerencianet.{$mode}.client_secret"),
        'certificate'   => base_path('certs/' . config("gerencianet.{$mode}.certificate_name")),
        'sandbox'       => config('gerencianet.is_sandbox'),
        'debug'         => false,
    ];

    $efi      = new \Efi\EfiPay($options);
    $resposta = $efi->updateSubscription(
        ['id' => (int) $id],
        ['items' => [['name' => 'Plano Multiusuário', 'value' => (int) $valor_centavos]]]
    );

    return response()->json($resposta, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
});

Route::get('/dev/assinatura-efi/{id}', function ($id) {
    $mode    = config('gerencianet.mode');
    $options = [
        'client_id'     => config("gerencianet.{$mode}.client_id"),
        'client_secret' => config("gerencianet.{$mode}.client_secret"),
        'certificate'   => base_path('certs/' . config("gerencianet.{$mode}.certificate_name")),
        'sandbox'       => config('gerencianet.is_sandbox'),
        'debug'         => false,
    ];

    $efi      = new \Efi\EfiPay($options);
    $resposta = $efi->detailSubscription(['id' => (int) $id]);

    return response()->json($resposta, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
});

Route::get('/teste-tz', function () {
    return [
        'php_time' => date('Y-m-d H:i:s'),
        'php_timezone' => date_default_timezone_get(),
        'laravel_time' => now()->format('Y-m-d H:i:s'),
        'laravel_timezone' => now()->timezoneName,
        'app_config' => config('app.timezone'),
        'env_set' => env('APP_TIMEZONE')
    ];
});


Route::post('/send-suggestion', function (Illuminate\Http\Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'subject' => 'required|string|max:255',
        'message' => 'required|string|max:5000',
    ]);

    // Dados do e-mail
    $data = $request->only('name', 'email', 'subject', 'message');
    $data['message'] = e($data['message']);  // Escapa os caracteres HTML


    // Despachando o Job para a fila
    SendSuggestionEmail::dispatch($data);

    return response()->json(['success' => true, 'message' => 'Mensagem enviada com sucesso!']);
})->name('send.suggestion');

Route::get('/', function () {
    return view('welcome');
});


Route::post('/callback', [CallbackController::class,'index']);
Route::post('/pix/webhook', [CallbackController::class, 'pixWebhook'])->name('pix.webhook');
Route::post('/pix/webhook-rec', [CallbackController::class, 'pixWebhookRec'])->name('pix.webhook.rec');

Route::post('/assinatura/pix-automatico', [AssinaturaController::class, 'pixAutomatico'])->name('assinatura.pix.automatico');
Route::post('/pix-automatico/verificar-pagamento', [AssinaturaController::class, 'verificarPagamentoPixAutomatico'])->name('verificar.pagamento.pix.automatico');

// Dashboard financeiro (apenas desenvolvedores)
Route::middleware(['auth', 'apenasDesenvolvedores'])->group(function () {
    Route::get('/financeiro', [FinanceiroController::class, 'index'])->name('financeiro.index');
    Route::post('/financeiro/toggle-status', [FinanceiroController::class, 'toggleStatus'])->name('financeiro.toggle.status');
    Route::post('/financeiro/toggle-free',   [FinanceiroController::class, 'toggleFree'])->name('financeiro.toggle.free');
});

// Gerenciamento do webhook PIX (apenas desenvolvedores)
Route::middleware(['auth', 'apenasDesenvolvedores'])->group(function () {
    Route::get('/pix/webhook/registrar', [PixWebhookController::class, 'registrar'])->name('pix.webhook.registrar');
    Route::get('/pix/webhook/registrar-rec', [PixWebhookController::class, 'registrarRec'])->name('pix.webhook.registrar.rec');
    Route::get('/pix/webhook/consultar', [PixWebhookController::class, 'consultar'])->name('pix.webhook.consultar');
});
Route::get('/bem-vindo/{user}', [BemvindoController::class, 'index'])->name('bemvindo');

Route::post('/buscar_planos',[DashboardController::class,'buscar_planos'])->middleware(['auth', 'verified'])->name('buscar_planos');
Route::post('/dashboard/orcamento',[DashboardController::class,'orcamento'])->middleware(['auth', 'verified'])->name('orcamento.montarOrcamento');
Route::post("/pdf",[DashboardController::class,'criarPDF'])->middleware(['auth', 'verified'])->name('gerar.imagem');

Route::post("/tabela/pdf",[DashboardController::class,'tabelaCompleta'])->middleware(['auth', 'verified'])->name('tabela.gerar');
Route::post("/tabela/ambulatorial/pdf",[DashboardController::class,'tabelaCompletaAmbulatorial'])->middleware(['auth', 'verified'])->name('tabela.gerar-ambulatorial');

Route::get('/assinaturas/plano', [AssinaturaController::class, 'createIndividual'])->name('assinaturas.individual.create');
Route::post('/assinaturas/individual', [AssinaturaController::class, 'storeIndividual'])->name('assinaturas.individual.store');
Route::post('/assinatura/pix',[AssinaturaController::class, 'pix'])->name('assinatura.pix');
Route::post('/assinatura/trial/pix',[AssinaturaController::class, 'pixTrial'])->name('assinatura.pix.trial');

Route::post('/verificar-pagamento', [AssinaturaController::class, 'verificarPagamento'])->name('verificar.pagamento');
Route::post('/pix/verificar-pagamento', [AssinaturaController::class, 'verificarPagamentoPIX'])->name('verificar.pagamento.pix');

Route::get('/assinatura/historico', [AssinaturaController::class, 'historicoPagamentos'])->middleware(['auth', 'verified','check'])->name('assinatura.historico');
Route::get('/assinatura/pix/historico', [AssinaturaController::class, 'historicoPagamentosPix'])->middleware(['auth', 'verified','check'])->name('assinatura.historico.pix');

Route::get('/assinaturas/empresarial', [AssinaturaController::class, 'createEmpresarial'])->name('assinaturas.empresarial.create');
Route::post('/assinaturas/empresarial', [AssinaturaController::class, 'storeEmpresarial'])->name('assinaturas.empresarial.store');

Route::get('/assinaturas/promocional', [AssinaturaController::class, 'createPromocional'])->name('assinaturas.promocional.create');
Route::post('/assinaturas/promocional', [AssinaturaController::class, 'storePromocional'])->name('assinaturas.promocional.store');





//Route::get('/csrf-token', function () {
//    return response()->json(['token' => csrf_token()]);
//})->name('csrf-token');


Route::middleware(['auth'])->group(function () {
    Route::post('cupom/validar', [ConfiguracoesController::class, 'validar'])->name('cupom.validar');
    /********* Configurações **************/
    Route::middleware(['apenasDesenvolvedores'])->group(function () {
        Route::get("/configuracoes", [ConfiguracoesController::class, 'index'])->name('configuracoes.index');

        Route::post('/configuracoes/cidades/', [ConfiguracoesController::class, 'cidadeStore'])->name('cidades.store');
        Route::delete('/configuracoes/cidades/{id}', [ConfiguracoesController::class, 'cidadeDestroy'])->name('cidades.destroy');

        Route::post('/administradoras/store', [ConfiguracoesController::class, 'storeAdministradora'])->name('administradoras.store');
        Route::delete('/administradoras/{administradora}', [ConfiguracoesController::class, 'administradoraDestroy'])->name('administradoras.destroy');

        Route::post('/planos', [ConfiguracoesController::class, 'storePlanos'])->name('planos.store');
        Route::delete('/planos/{plano}', [ConfiguracoesController::class, 'planosDestroy'])->name('planos.destroy');

        Route::get('/assinaturas-cidades/{assinatura}/cidades', [ConfiguracoesController::class, 'getCidades'])->name('assinaturas.cidades');
        Route::post('/assinaturas-cidades/vincular', [ConfiguracoesController::class, 'vincular'])->name('assinaturas.vincular');
        Route::post('/assinaturas-cidades/desvincular', [ConfiguracoesController::class, 'desvincular'])->name('assinaturas.desvincular');


        Route::post('/store/pdf', [ConfiguracoesController::class, 'storePdf'])->name('pdf.store');
        Route::delete('/pdf/{pdf}', [ConfiguracoesController::class, 'destroyPdf'])->name('pdf.destroy');

        Route::get('/pdf/{pdf}/edit', [ConfiguracoesController::class, 'editPdf'])->name('pdf.edit');
        Route::put('/pdf/{pdf}', [ConfiguracoesController::class, 'updatePdf'])->name('pdf.update');

        Route::post('/verificar/tabela', [ConfiguracoesController::class, 'verificar'])->name('tabelas.verificar');
        Route::post('/tabelas/salvar', [ConfiguracoesController::class, 'salvarTabela'])->name('tabelas.salvar');
        Route::post('/mudar/valor/tabela', [ConfiguracoesController::class, 'mudarTabela'])->name('corretora.mudar.valor.tabela');

        Route::post('/desconto', [ConfiguracoesController::class, 'storeDesconto'])->name('descontos.store');
        Route::delete('/desconto/{desconto}', [ConfiguracoesController::class, 'destroyDesconto'])->name('descontos.destroy');

        Route::get('/plano/administradoras/cidades', [ConfiguracoesController::class, 'index'])->name('admin-planos.index');
        Route::post('/plano/administradoras/cidades', [ConfiguracoesController::class, 'store'])->name('admin-planos.store');
        Route::delete('/plano/administradoras/cidades/{id}', [ConfiguracoesController::class, 'destroy'])->name('admin-planos.destroy');

        Route::post('/cupons', [ConfiguracoesController::class, 'storeCupon'])->name('cupons.store');

        Route::post('/config/filtrar-planos-por-admin', [ConfiguracoesController::class, 'planosPorAdministradora'])->name('filtrar.planos.admin');
        Route::post('/config/filtrar-tabelas-por-admin-plano', [ConfiguracoesController::class, 'tabelasPorAdminPlano'])->name('filtrar.tabelas.adminplano');


        Route::post("/config/carencia",[ConfiguracoesController::class, 'storeCarencia'])->name('carencia.store');

        Route::get('/carencia/detalhe', [ConfiguracoesController::class, 'detalheCarencia'])->name('carencia.detalhe');
        Route::delete('/carencia/deleteGrupo', [ConfiguracoesController::class, 'deleteGrupoCarencia'])->name('carencia.deleteGrupo');

        Route::post('/pdf-excecao', [ConfiguracoesController::class, 'storePdfExcecao'])->name('pdf-excecao.store');
        Route::delete('/pdf-excecao/{id}', [ConfiguracoesController::class, 'destroyPdfExcecao'])->name('pdf-excecao.destroy');



    });
    /********* Fim Configurações **************/


    /************Gerenciar************************/
    Route::get("/gerenciamento", [GerenciadorController::class, 'index'])->name('gerenciamento.index')->middleware(['apenasAdministradores','check']);
    Route::post("/gerenciamento/regiao", [GerenciadorController::class, 'regiao'])->name('gerenciamento.regiao')->middleware(['check']);
    Route::get("/gerenciamento/logins-compartilhados", [GerenciadorController::class, 'loginsCompartilhados'])->name('gerenciamento.logins-compartilhados')->middleware(['apenasDesenvolvedores']);
    Route::post("/gerenciamento/desativar-login-compartilhado/{user}", [GerenciadorController::class, 'desativarLoginCompartilhado'])->name('gerenciamento.desativar-login-compartilhado')->middleware(['apenasDesenvolvedores']);
    Route::post("/gerenciamento/limpar-sessoes/{user}", [GerenciadorController::class, 'limparSessoes'])->name('gerenciamento.limpar-sessoes')->middleware(['apenasDesenvolvedores']);
    Route::post("/gerenciamento/ativar-usuario/{user}", [GerenciadorController::class, 'ativarUsuario'])->name('gerenciamento.ativar-usuario')->middleware(['apenasDesenvolvedores']);
    Route::post("/gerenciamento/bloquear-ip", [GerenciadorController::class, 'bloquearIp'])->name('gerenciamento.bloquear-ip')->middleware(['apenasDesenvolvedores']);
    Route::post("/gerenciamento/desbloquear-ip", [GerenciadorController::class, 'desbloquearIp'])->name('gerenciamento.desbloquear-ip')->middleware(['apenasDesenvolvedores']);
    Route::post("/gerenciamento/bloquear-usuario-ip", [GerenciadorController::class, 'bloquearUsuarioIp'])->name('gerenciamento.bloquear-usuario-ip')->middleware(['apenasDesenvolvedores']);
    Route::post("/gerenciamento/desbloquear-usuario-ip", [GerenciadorController::class, 'desbloquearUsuarioIp'])->name('gerenciamento.desbloquear-usuario-ip')->middleware(['apenasDesenvolvedores']);
    Route::post("/gerenciamento/remover-dispositivo/{device}", [GerenciadorController::class, 'removerDispositivo'])->name('gerenciamento.remover-dispositivo')->middleware(['apenasDesenvolvedores']);
    Route::post("/gerenciamento/bloquear-dispositivo/{device}", [GerenciadorController::class, 'bloquearDispositivo'])->name('gerenciamento.bloquear-dispositivo')->middleware(['apenasDesenvolvedores']);
    Route::post("/gerenciamento/desbloquear-dispositivo/{device}", [GerenciadorController::class, 'desbloquearDispositivo'])->name('gerenciamento.desbloquear-dispositivo')->middleware(['apenasDesenvolvedores']);
    Route::post("/gerenciamento/liberar-titular/{user}", [GerenciadorController::class, 'liberarTitular'])->name('gerenciamento.liberar-titular')->middleware(['apenasDesenvolvedores']);




    //Route::post('/profile/regiao', [ProfileController::class, ''])->name('profile.regiao');




    /************ Equipe (pré-pago) **************/
    Route::post('/gerenciamento/equipe/salvar',       [TeamUserController::class, 'salvar'])->name('equipe.salvar')->middleware(['apenasAdministradores']);
    Route::delete('/gerenciamento/equipe/remover/{id}',[TeamUserController::class, 'remover'])->name('equipe.remover')->middleware(['apenasAdministradores']);
    Route::post('/gerenciamento/equipe/pix',           [TeamUserController::class, 'gerarPix'])->name('equipe.pix')->middleware(['apenasAdministradores']);
    Route::post('/gerenciamento/equipe/pix-todos',     [TeamUserController::class, 'gerarPixTodos'])->name('equipe.pix-todos')->middleware(['apenasAdministradores']);
    Route::post('/gerenciamento/equipe/cartao-todos',  [TeamUserController::class, 'gerarCartaoTodos'])->name('equipe.cartao-todos')->middleware(['apenasAdministradores']);
    Route::get('/gerenciamento/equipe/verificar',      [TeamUserController::class, 'verificar'])->name('equipe.verificar')->middleware(['apenasAdministradores']);
    Route::post('/gerenciamento/equipe/cartao',        [TeamUserController::class, 'gerarCartao'])->name('equipe.cartao')->middleware(['apenasAdministradores']);
    /************ Fim Equipe *********************/

    /************Fim Gerenciar********************/







    Route::get('/users/manage', [UserController::class, 'index'])->name('users.manage')
        ->middleware(['apenasAdministradores','check']);
    Route::post('/users/manage', [UserController::class, 'storeUser'])->name('storeUser')->middleware('apenasAdministradores');

    Route::get("/assinatura/alterar", [AssinaturaController::class, 'edit'])->name('assinatura.edit')->middleware('apenasAdministradores');
    Route::post("/assinatura/trial/store", [AssinaturaController::class, 'storeTrial'])->name('assinaturas.trial.store')->middleware('apenasAdministradores');




    Route::get('/assinatura/expirada', function () {
        return view('assinaturas.expirada');
    })->name('assinatura.expirada')->middleware(['checkExpired']);

    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit')->middleware(['check']);
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/regiao', [ProfileController::class, 'regiao'])->name('profile.regiao');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/imagem/atualizar', [ProfileController::class, 'updateAvatar'])->name('profile.imagem.atualizar');
    Route::post('/profile/user/imagem/atualizar', [ProfileController::class, 'updateAvatarUser'])->name('profile.imagem.atualizar.user');

    Route::get('/dashboard', [DashboardController::class,"index"])
        ->middleware(['verified','check'])
        ->name('dashboard');

    Route::post('/cidades/origem', [DashboardController::class, 'getCidadesDeOrigem'])->name('cidades.origem');


    Route::post('/filtrar/administradora',[DashboardController::class,'filtrarAdministradora'])->name('filtrar.administradora');

    Route::get('/layout', [LayoutController::class, 'index'])->name('layouts.index')->middleware(['check']);
    Route::post('/layouts/select', [LayoutController::class, 'select'])->name('layouts.select');

    Route::get('/tabela_completa',[TabelaController::class,'index'])->name('tabela_completa.index')->middleware(['check']);
    Route::get('/tabela',[TabelaController::class,'tabela_preco'])->name('tabela.config');
    Route::post('/corretora/select/planos/administradoras',[TabelaController::class,'planosAdministradoraSelect'])->name('planos.administradora.select');
    //Route::post('/corretora/mudar/valor/tabela',[TabelaController::class,'mudarValorTabela'])->name('corretora.mudar.valor.tabela');

    Route::post('/users/editar/manager', [UserController::class, 'getUser'])->name('users.get');
    Route::post('/users/update', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/deletar', [UserController::class, 'deletar'])->name('deletar.user');
    Route::post('/users/alterar',[UserController::class,'alterar'])->name('users.alterar');


});
//Route::get('/configuracoes', function () {
//    $assinatura_id = \App\Models\Assinatura::where("user_id",auth()->user()->id)->first()->id;
//
//    $users = User::whereIn(
//        'id',
//        EmailAssinatura::where('assinatura_id', $assinatura_id)
//            ->where('is_administrador', 0)
//            ->pluck('user_id')
//    )->get();
//
//
//
//
//
//    //$users = \App\Models\User::all(); // Carrega os usuários para a tabela de gerenciamento
//
//    $user = auth()->user();
//    return view('configuracoes', compact('users','user'));
//})->middleware('auth')->name('configuracoes');
Route::post('/dashboard/tabela/orcamento',[TabelaController::class,'orcamento'])->middleware(['auth', 'verified'])->name('orcamento.tabela.montarOrcamento');

require __DIR__.'/auth.php';
