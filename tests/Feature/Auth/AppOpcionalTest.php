<?php

use App\Models\User;
use App\Models\UserDevice;

/**
 * App opcional (01/09/2026): o navegador do celular deixa de ser bloqueado e
 * passa a ocupar a MESMA vaga mobile_app do aplicativo. Cobre os 4 cenários
 * do plano (Desktop\Importante\plano-app-opcional.txt).
 */

const UA_MOBILE  = 'Mozilla/5.0 (Linux; Android 13; moto g14) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36';
const UA_DESKTOP = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36';

function loginComo(User $user, string $ua, array $extra = [])
{
    return test()->withHeaders(['User-Agent' => $ua])->post('/login', array_merge([
        'email'    => $user->email,
        'password' => 'password',
    ], $extra));
}

test('celular ve a tela de login com o banner do app (sem redirect)', function () {
    $response = $this->withHeaders(['User-Agent' => UA_MOBILE])->get('/login');

    $response->assertStatus(200);
    $response->assertSee('Prefere usar nosso');
    $response->assertSee('app.bmsys.com.br/install', false);
    $response->assertDontSee('exclusivo pelo app'); // conteúdo da antiga mobile-redirect
});

test('desktop ve a tela de login sem o banner do app', function () {
    $response = $this->withHeaders(['User-Agent' => UA_DESKTOP])->get('/login');

    $response->assertStatus(200);
    $response->assertDontSee('app.bmsys.com.br/install', false);
});

test('celular novo registra como mobile_app e nao toca na vaga de desktop', function () {
    $user = User::factory()->create(['layout_id' => null]);

    UserDevice::create([
        'user_id'              => $user->id,
        'device_uuid'          => 'aaaaaaaa-1111-2222-3333-444444444444',
        'device_fingerprint'   => 'fp-desktop',
        'hardware_fingerprint' => 'hw-desktop',
        'device_name'          => 'Chrome 138 em Windows 10/11',
        'device_type'          => 'desktop',
        'is_active'            => true,
        'is_blocked'           => false,
        'registered_at'        => now(),
    ]);

    $response = loginComo($user, UA_MOBILE, ['device_uuid' => 'bbbbbbbb-1111-2222-3333-444444444444']);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $mobile = UserDevice::where('user_id', $user->id)->where('device_type', 'mobile_app')->first();
    expect($mobile)->not->toBeNull();
    expect($mobile->is_active)->toBeTruthy();

    // A vaga de desktop continua intacta
    $desktop = UserDevice::where('user_id', $user->id)->where('device_type', 'desktop')->first();
    expect($desktop->device_uuid)->toBe('aaaaaaaa-1111-2222-3333-444444444444');
    expect((bool) $desktop->is_active)->toBeTrue();
    expect(UserDevice::where('user_id', $user->id)->count())->toBe(2);
});

test('celular que ja tem o app reaproveita a vaga trocando o UUID e preserva o device_name', function () {
    $user = User::factory()->create(['layout_id' => null]);

    // Linha criada pelo aplicativo (a API grava o modelo em device_name)
    UserDevice::create([
        'user_id'              => $user->id,
        'device_uuid'          => 'cccccccc-1111-2222-3333-444444444444',
        'device_fingerprint'   => '',
        'hardware_fingerprint' => '1080x2400',
        'device_name'          => 'moto g14',
        'device_type'          => 'mobile_app',
        'is_active'            => true,
        'is_blocked'           => false,
        'registered_at'        => now(),
    ]);

    $response = loginComo($user, UA_MOBILE, ['device_uuid' => 'dddddddd-1111-2222-3333-444444444444']);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    // Não criou segunda linha: reaproveitou a vaga única trocando o UUID
    expect(UserDevice::where('user_id', $user->id)->count())->toBe(1);
    $device = UserDevice::where('user_id', $user->id)->first();
    expect($device->device_uuid)->toBe('dddddddd-1111-2222-3333-444444444444');
    // O nome que a API usa como chave de reencontro não pode ser sobrescrito
    expect($device->device_name)->toBe('moto g14');
});

test('segundo celular com a vaga bloqueada cai no limite de celulares', function () {
    $user = User::factory()->create(['layout_id' => null]);

    UserDevice::create([
        'user_id'              => $user->id,
        'device_uuid'          => 'eeeeeeee-1111-2222-3333-444444444444',
        'device_fingerprint'   => '',
        'hardware_fingerprint' => '1080x2400',
        'device_name'          => 'moto g14',
        'device_type'          => 'mobile_app',
        'is_active'            => true,
        'is_blocked'           => true, // bloqueado pelo admin: a rede de segurança NÃO pode reaproveitar
        'registered_at'        => now(),
    ]);

    $response = loginComo($user, UA_MOBILE, ['device_uuid' => 'ffffffff-1111-2222-3333-444444444444']);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toContain('Limite de celulares');
});

test('desktop continua barrado no limite de computadores como hoje', function () {
    $user = User::factory()->create(['layout_id' => null]);

    UserDevice::create([
        'user_id'              => $user->id,
        'device_uuid'          => 'aaaaaaaa-5555-6666-7777-888888888888',
        'device_fingerprint'   => 'fp-antigo',
        'hardware_fingerprint' => 'hw-antigo',
        'device_name'          => 'Chrome 130 em Windows 10/11',
        'device_type'          => 'desktop',
        'is_active'            => true,
        'is_blocked'           => false,
        'registered_at'        => now(),
    ]);

    $response = loginComo($user, UA_DESKTOP, ['device_uuid' => 'bbbbbbbb-5555-6666-7777-888888888888']);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toContain('Limite de computadores');
});

test('com 1 vaga, o segundo celular derruba a sessao web do primeiro (e o desktop fica de pe)', function () {
    $user = User::factory()->create(['layout_id' => null]);

    // Computador loga primeiro — nao pode ser afetado pela dança dos celulares
    loginComo($user, UA_DESKTOP, ['device_uuid' => '99999999-dddd-eeee-ffff-000000000009']);
    $this->assertAuthenticated();
    $this->flushSession();
    $this->app['auth']->forgetGuards();

    // Celular 1 loga (resolucao propria para diferenciar o hardware do celular 2)
    loginComo($user, UA_MOBILE, [
        'device_uuid'       => '11111111-aaaa-bbbb-cccc-000000000001',
        'screen_resolution' => '1080x2400',
    ]);
    $this->assertAuthenticated();
    expect(\App\Models\LoginSession::where('user_id', $user->id)->whereNull('logged_out_at')->count())->toBe(2);

    // Celular 2: outro aparelho, sessao nova (sem passar pelo /logout).
    // flushSession nao basta: o guard fica em memoria entre requests do teste
    // e o middleware guest devolveria o POST /login sem executar o store().
    $this->flushSession();
    $this->app['auth']->forgetGuards();
    loginComo($user, UA_MOBILE, [
        'device_uuid'       => '22222222-aaaa-bbbb-cccc-000000000002',
        'screen_resolution' => '720x1600',
    ]);
    $this->assertAuthenticated();

    // A vaga foi roubada (mesma linha mobile, UUID novo; desktop + mobile = 2 linhas)
    expect(UserDevice::where('user_id', $user->id)->count())->toBe(2);
    expect(UserDevice::where('user_id', $user->id)->where('device_type', 'mobile_app')->count())->toBe(1);
    expect(UserDevice::where('user_id', $user->id)->where('device_type', 'mobile_app')->first()->device_uuid)
        ->toBe('22222222-aaaa-bbbb-cccc-000000000002');

    // ... e a sessao web do celular 1 foi derrubada na hora
    $sessaoAntiga = \App\Models\LoginSession::where('user_id', $user->id)
        ->where('device_uuid', '11111111-aaaa-bbbb-cccc-000000000001')
        ->first();
    expect($sessaoAntiga->logged_out_at)->not->toBeNull();
    expect((bool) $sessaoAntiga->was_displaced)->toBeTrue();

    // Ficam abertas exatamente: a do computador e a do celular 2
    expect(\App\Models\LoginSession::where('user_id', $user->id)->whereNull('logged_out_at')->count())->toBe(2);
    $sessaoDesktop = \App\Models\LoginSession::where('user_id', $user->id)
        ->where('device_uuid', '99999999-dddd-eeee-ffff-000000000009')
        ->first();
    expect($sessaoDesktop->logged_out_at)->toBeNull();
});

test('modo pagamento continua sem registrar dispositivo', function () {
    $user = User::factory()->create(['layout_id' => null]);

    $response = loginComo($user, UA_MOBILE, ['pagamento' => '1']);

    $this->assertAuthenticated();
    $response->assertRedirect(route('assinatura.edit'));
    expect(UserDevice::where('user_id', $user->id)->count())->toBe(0);
    expect(session('modo_pagamento'))->toBeTrue();
});
