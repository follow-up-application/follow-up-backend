<?php

use App\Infrastructure\User\Persistence\UserModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

test('un administrador puede registrarse publicamente', function () {
    $response = $this->postJson('/api/v1/admins/register', [
        'first_name' => 'Nuevo',
        'last_name' => 'Admin',
        'username' => 'admin.nuevo',
        'password' => 'Password123!',
    ]);

    $response->assertCreated()
        ->assertJsonPath('username', 'admin.nuevo')
        ->assertJsonPath('role', 'administrador');

    $user = UserModel::find($response->json('id'));

    expect($user->role)->toBe('administrador');
    expect(Hash::check('Password123!', $user->password))->toBeTrue();

    $this->postJson('/api/v1/login', [
        'username' => 'admin.nuevo',
        'password' => 'Password123!',
    ])->assertOk();
});

test('el registro de administrador valida username y password', function () {
    usuarioFixture('administrador', ['username' => 'admin.existente']);

    $this->postJson('/api/v1/admins/register', [
        'first_name' => 'Otro',
        'last_name' => 'Admin',
        'username' => 'admin.existente',
        'password' => '123',
    ])->assertStatus(422)->assertJsonValidationErrors(['username', 'password']);
});

test('el administrador lista y obtiene administradores', function () {
    $admin = usuarioFixture('administrador');
    $otro = usuarioFixture('administrador');

    autenticarComo($admin);

    $this->getJson('/api/v1/admins')
        ->assertOk()
        ->assertJsonFragment(['id' => $admin->id])
        ->assertJsonFragment(['id' => $otro->id]);

    $this->getJson('/api/v1/admins/'.$otro->id)
        ->assertOk()
        ->assertJsonPath('id', $otro->id)
        ->assertJsonPath('role', 'administrador');
});

test('obtener un administrador inexistente devuelve 404', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->getJson('/api/v1/admins/'.Str::uuid())
        ->assertNotFound();
});

test('el administrador actualiza a otro administrador', function () {
    $admin = usuarioFixture('administrador');
    $otro = usuarioFixture('administrador', ['username' => 'admin.original']);

    autenticarComo($admin);

    $this->putJson('/api/v1/admins/'.$otro->id, [
        'first_name' => 'Nombre',
        'last_name' => 'Actualizado',
        'username' => 'admin.actualizado',
    ])->assertOk()
        ->assertJsonPath('first_name', 'Nombre')
        ->assertJsonPath('last_name', 'Actualizado')
        ->assertJsonPath('username', 'admin.actualizado');

    expect($otro->fresh()->username)->toBe('admin.actualizado');
});

test('actualizar un administrador sin datos devuelve 422', function () {
    $admin = usuarioFixture('administrador');
    autenticarComo($admin);

    $this->putJson('/api/v1/admins/'.$admin->id, [])
        ->assertStatus(422);
});

test('el administrador inhabilita a otro administrador', function () {
    $admin = usuarioFixture('administrador');
    $otro = usuarioFixture('administrador');

    autenticarComo($admin);

    $this->patchJson('/api/v1/admins/'.$otro->id.'/inhabilitar')
        ->assertStatus(204);

    expect(UserModel::withTrashed()->find($otro->id)->trashed())->toBeTrue();
});

test('inhabilitar un administrador inexistente devuelve 404', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->patchJson('/api/v1/admins/'.Str::uuid().'/inhabilitar')
        ->assertNotFound();
});

test('el administrador obtiene las estadisticas', function () {
    $admin = usuarioFixture('administrador');
    crearAlumno();
    crearAlumno();
    crearTutor();

    autenticarComo($admin);

    $this->getJson('/api/v1/admins/estadisticas')
        ->assertOk()
        ->assertJsonPath('total_alumnos', 2)
        ->assertJsonPath('total_tutores', 1);
});

test('un docente no puede acceder a la gestion de administradores', function () {
    $docente = crearDocente();
    autenticarComo($docente['user']);

    $this->getJson('/api/v1/admins')->assertForbidden();
    $this->getJson('/api/v1/admins/estadisticas')->assertForbidden();
    $this->postJson('/api/v1/admins/register', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['first_name', 'last_name', 'username', 'password']);
});
