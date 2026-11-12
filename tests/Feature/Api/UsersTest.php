<?php

use App\Infrastructure\User\Persistence\UserModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

test('un usuario base puede registrarse publicamente con rol por defecto', function () {
    $payload = [
        'first_name' => 'Juan',
        'last_name' => 'Pérez',
        'dni' => 12345678,
        'username' => 'juan.perez',
        'password' => 'Password123!',
    ];

    $response = $this->postJson('/api/v1/users', $payload);

    $response->assertCreated()
        ->assertJsonPath('firstName', 'Juan')
        ->assertJsonPath('lastName', 'Pérez')
        ->assertJsonPath('role', 'alumno');

    $user = UserModel::findOrFail($response->json('id'));

    expect($user->role)->toBe('alumno');
    expect(Hash::check('Password123!', $user->password))->toBeTrue();
});

test('un usuario base se puede registrar con un rol explicito', function () {
    $this->postJson('/api/v1/users', [
        'first_name' => 'Carlos',
        'last_name' => 'Docente',
        'dni' => 22334455,
        'username' => 'carlos.docente',
        'password' => 'Password123!',
        'role' => 'docente',
    ])->assertCreated()->assertJsonPath('role', 'docente');
});

test('el alta de usuario valida username, dni y password', function () {
    usuarioFixture('alumno', ['dni' => 33445566, 'username' => 'usuario.existente']);

    $this->postJson('/api/v1/users', [
        'first_name' => 'Otro',
        'last_name' => 'Usuario',
        'dni' => 33445566,
        'username' => 'usuario.existente',
        'password' => '123',
    ])->assertStatus(422)->assertJsonValidationErrors(['dni', 'username', 'password']);
});

test('el administrador puede listar usuarios', function () {
    $admin = usuarioFixture('administrador');
    $alumno = usuarioFixture('alumno');

    autenticarComo($admin);

    $this->getJson('/api/v1/users')
        ->assertOk()
        ->assertJsonFragment(['id' => $admin->id])
        ->assertJsonFragment(['id' => $alumno->id]);
});

test('el administrador puede obtener un usuario por id', function () {
    $admin = usuarioFixture('administrador');
    $alumno = usuarioFixture('alumno');

    autenticarComo($admin);

    $this->getJson('/api/v1/users/'.$alumno->id)
        ->assertOk()
        ->assertJsonPath('id', $alumno->id)
        ->assertJsonPath('firstName', $alumno->first_name)
        ->assertJsonPath('role', 'alumno');
});

test('obtener un usuario inexistente devuelve 404', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->getJson('/api/v1/users/'.Str::uuid())
        ->assertNotFound();
});

test('el administrador puede actualizar un usuario', function () {
    $admin = usuarioFixture('administrador');
    $alumno = usuarioFixture('alumno', ['username' => 'alumno.original']);

    autenticarComo($admin);

    $this->putJson('/api/v1/users/'.$alumno->id, [
        'first_name' => 'Nombre',
        'last_name' => 'Actualizado',
        'username' => 'alumno.actualizado',
    ])->assertOk()
        ->assertJsonPath('firstName', 'Nombre')
        ->assertJsonPath('lastName', 'Actualizado');

    expect($alumno->fresh()->username)->toBe('alumno.actualizado');
});

test('actualizar un usuario sin datos devuelve 422', function () {
    $admin = usuarioFixture('administrador');
    $alumno = usuarioFixture('alumno');

    autenticarComo($admin);

    $this->putJson('/api/v1/users/'.$alumno->id, [])
        ->assertStatus(422);
});

test('el administrador puede inhabilitar un usuario', function () {
    $admin = usuarioFixture('administrador');
    $alumno = usuarioFixture('alumno');

    autenticarComo($admin);

    $this->patchJson('/api/v1/users/'.$alumno->id.'/inhabilitar')
        ->assertStatus(204);

    expect(UserModel::withTrashed()->find($alumno->id)->trashed())->toBeTrue();
});

test('inhabilitar un usuario inexistente devuelve 404', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->patchJson('/api/v1/users/'.Str::uuid().'/inhabilitar')
        ->assertNotFound();
});

test('un usuario autenticado puede ver su perfil con cualquier rol', function () {
    foreach (['administrador', 'docente', 'tutor', 'alumno'] as $role) {
        $user = usuarioFixture($role);

        $this->actingAs($user)
            ->getJson('/api/v1/users/me')
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('role', $role);
    }
});

test('solo el administrador puede listar y actualizar usuarios', function () {
    $alumno = usuarioFixture('alumno');
    $docente = usuarioFixture('docente');

    $this->actingAs($alumno)->getJson('/api/v1/users')->assertForbidden();
    $this->actingAs($docente)->getJson('/api/v1/users')->assertForbidden();
    $this->actingAs($alumno)->getJson('/api/v1/users/'.$docente->id)->assertForbidden();
});
