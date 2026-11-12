<?php

use App\Infrastructure\User\Persistence\UserModel;
use Illuminate\Support\Facades\Hash;

test('un usuario puede iniciar sesion con username y password', function () {
    $user = usuarioFixture('administrador', [
        'username' => 'admin.login',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'username' => 'admin.login',
        'password' => 'Password123!',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'rol', 'first_name', 'last_name', 'username'])
        ->assertJsonPath('rol', 'administrador')
        ->assertJsonPath('username', 'admin.login');

    expect($response->json('token'))->not->toBeEmpty();
    expect($user->tokens()->count())->toBe(1);
});

test('login rechaza credenciales invalidas', function () {
    usuarioFixture('alumno', ['username' => 'alumno.login']);

    $this->postJson('/api/v1/login', [
        'username' => 'alumno.login',
        'password' => 'incorrecta',
    ])->assertStatus(422)->assertJsonValidationErrors('username');
});

test('el perfil autenticado se obtiene con el formato esperado', function () {
    $user = usuarioFixture('docente', ['first_name' => 'Ana', 'last_name' => 'Docente']);

    $this->withToken(tokenPara($user))
        ->getJson('/api/v1/users/me')
        ->assertOk()
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('firstName', 'Ana')
        ->assertJsonPath('lastName', 'Docente')
        ->assertJsonPath('role', 'docente');
});

test('logout elimina el token utilizado', function () {
    $user = usuarioFixture('alumno');
    $token = tokenPara($user);

    $this->withToken($token)
        ->postJson('/api/v1/logout')
        ->assertOk();

    expect($user->tokens()->count())->toBe(0);

    $this->app['auth']->forgetGuards();

    $this->withToken($token)
        ->getJson('/api/v1/users/me')
        ->assertUnauthorized();
});

test('un usuario autenticado puede cambiar su contrasena', function () {
    $user = usuarioFixture('alumno', ['username' => 'alumno.password']);

    $this->withToken(tokenPara($user))
        ->putJson('/api/v1/password', ['password' => 'NuevaPassword123!'])
        ->assertOk();

    expect(Hash::check('NuevaPassword123!', $user->fresh()->password))->toBeTrue();

    $this->postJson('/api/v1/login', [
        'username' => 'alumno.password',
        'password' => 'NuevaPassword123!',
    ])->assertOk();

    $this->postJson('/api/v1/login', [
        'username' => 'alumno.password',
        'password' => 'Password123!',
    ])->assertStatus(422);
});

test('cambiar contrasena valida la confirmacion y el minimo', function () {
    $user = usuarioFixture('alumno');

    $this->withToken(tokenPara($user))
        ->putJson('/api/v1/password', [
            'password' => 'corta',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('password');

    $this->withToken(tokenPara($user))
        ->putJson('/api/v1/password', [
            'password' => 'Password123!',
            'password_confirmation' => 'OtraPassword123!',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('password_confirmation');
});

test('los endpoints protegidos rechazan peticiones sin token', function () {
    $this->getJson('/api/v1/users/me')->assertUnauthorized();
    $this->getJson('/api/v1/alumnos')->assertUnauthorized();
    $this->getJson('/api/v1/users')->assertUnauthorized();
});

test('un token invalido no autentica', function () {
    $this->withToken('token-invalido')
        ->getJson('/api/v1/users/me')
        ->assertUnauthorized();
});

test('el modelo de autenticacion configurado existe', function () {
    expect(config('auth.providers.users.model'))->toBe(UserModel::class);
});
