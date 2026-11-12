<?php

use App\Infrastructure\User\Persistence\TutorModel;
use App\Infrastructure\User\Persistence\UserModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

test('el administrador crea un tutor directamente', function () {
    autenticarComo(usuarioFixture('administrador'));

    $response = $this->postJson('/api/v1/tutores', [
        'first_name' => 'Teresa',
        'last_name' => 'Tutora',
        'dni' => 60111222,
        'telephone' => '3516667777',
        'email' => 'teresa.tutora@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertCreated()
        ->assertJsonPath('first_name', 'Teresa')
        ->assertJsonPath('email', 'teresa.tutora@example.com');

    $user = UserModel::find($response->json('id'));

    expect($user->role)->toBe('tutor');
    expect($user->username)->toBe('teresa.tutora@example.com');
    expect(Hash::check('Password123!', $user->password))->toBeTrue();
});

test('crear un tutor valida email, dni y password', function () {
    autenticarComo(usuarioFixture('administrador'));
    $existente = crearTutor();
    crearTutor(['user' => ['dni' => 60222333]]);

    $this->postJson('/api/v1/tutores', [
        'first_name' => 'Inválido',
        'last_name' => 'Tutor',
        'dni' => 60222333,
        'telephone' => '3516667777',
        'email' => $existente['user']->username,
        'password' => '123',
    ])->assertStatus(422)->assertJsonValidationErrors(['dni', 'email', 'password']);
});

test('el administrador lista tutores', function () {
    autenticarComo(usuarioFixture('administrador'));
    $tutor = crearTutor();

    $this->getJson('/api/v1/tutores')
        ->assertOk()
        ->assertJsonFragment(['id' => $tutor['tutor']->id]);
});

test('el administrador obtiene un tutor por id y por dni', function () {
    autenticarComo(usuarioFixture('administrador'));
    $tutor = crearTutor();

    $this->getJson('/api/v1/tutores/'.$tutor['tutor']->id)
        ->assertOk()
        ->assertJsonPath('id', $tutor['tutor']->id)
        ->assertJsonPath('dni', $tutor['user']->dni);

    $this->getJson('/api/v1/tutores/dni/'.$tutor['user']->dni)
        ->assertOk()
        ->assertJsonPath('id', $tutor['tutor']->id);
});

test('obtener un tutor inexistente devuelve 404', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->getJson('/api/v1/tutores/'.Str::uuid())
        ->assertNotFound();

    $this->getJson('/api/v1/tutores/dni/12345678')
        ->assertNotFound();
});

test('el administrador actualiza un tutor sin perder la contrasena', function () {
    autenticarComo(usuarioFixture('administrador'));
    $tutor = crearTutor();

    $this->putJson('/api/v1/tutores/'.$tutor['tutor']->id, [
        'first_name' => 'Nombre',
        'last_name' => 'Actualizado',
        'telephone' => '3519998888',
        'email' => 'tutor.actualizado@example.com',
    ])->assertOk()
        ->assertJsonPath('first_name', 'Nombre')
        ->assertJsonPath('telephone', '3519998888')
        ->assertJsonPath('email', 'tutor.actualizado@example.com');

    expect(Hash::check('Password123!', $tutor['user']->fresh()->password))->toBeTrue();
});

test('actualizar un tutor sin datos devuelve 422', function () {
    autenticarComo(usuarioFixture('administrador'));
    $tutor = crearTutor();

    $this->putJson('/api/v1/tutores/'.$tutor['tutor']->id, [])
        ->assertStatus(422);
});

test('el tutor puede ver sus datos y sus alumnos', function () {
    $tutor = crearTutor();
    $alumno = crearAlumno();
    crearVinculoTutorAlumno($alumno['alumno'], $tutor['tutor']);

    autenticarComo($tutor['user']);

    $this->getJson('/api/v1/tutores/'.$tutor['tutor']->id)
        ->assertOk()
        ->assertJsonPath('id', $tutor['tutor']->id);

    $this->getJson('/api/v1/tutores/'.$tutor['tutor']->id.'/alumnos')
        ->assertOk()
        ->assertJsonFragment(['id' => $alumno['alumno']->id]);
});

test('el administrador inhabilita un tutor', function () {
    autenticarComo(usuarioFixture('administrador'));
    $tutor = crearTutor();

    $this->patchJson('/api/v1/tutores/'.$tutor['tutor']->id.'/inhabilitar')
        ->assertStatus(204);

    expect(TutorModel::withTrashed()->find($tutor['tutor']->id)->trashed())->toBeTrue();
    expect(UserModel::withTrashed()->find($tutor['user']->id)->trashed())->toBeTrue();
});

test('inhabilitar un tutor inexistente devuelve 404', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->patchJson('/api/v1/tutores/'.Str::uuid().'/inhabilitar')
        ->assertNotFound();
});

test('se valida un codigo institucional de pre-registro', function () {
    $tutor = crearTutor();
    $alumno = crearAlumno();
    crearVinculoTutorAlumno($alumno['alumno'], $tutor['tutor'], [
        'codigo_institucional' => 'COD-VALIDAR-123',
    ]);

    $this->getJson('/api/v1/tutores/validarCodigo/COD-VALIDAR-123')
        ->assertOk()
        ->assertJsonPath('id', $alumno['alumno']->id);

    $this->getJson('/api/v1/tutores/validarCodigo/COD-INEXISTENTE')
        ->assertNotFound();
});

test('el tutor completa su registro con el codigo institucional', function () {
    $tutor = crearTutor(['tutor' => ['email' => null]]);
    $alumno = crearAlumno();
    $vinculo = crearVinculoTutorAlumno($alumno['alumno'], $tutor['tutor'], [
        'codigo_institucional' => 'COD-REGISTRO-123',
    ]);

    $this->postJson('/api/v1/tutores/register', [
        'codigo_institucional' => $vinculo->codigo_institucional,
        'email' => 'tutor.registrado@example.com',
        'password' => 'Password123!',
    ])->assertCreated()->assertJsonPath('email', 'tutor.registrado@example.com');

    $user = $tutor['user']->fresh();

    expect($user->username)->toBe('tutor.registrado@example.com');
    expect(Hash::check('Password123!', $user->password))->toBeTrue();

    $this->postJson('/api/v1/login', [
        'username' => 'tutor.registrado@example.com',
        'password' => 'Password123!',
    ])->assertOk();
});

test('no se puede registrar dos veces al mismo tutor', function () {
    $tutor = crearTutor(['tutor' => ['email' => null]]);
    $alumno = crearAlumno();
    $vinculo = crearVinculoTutorAlumno($alumno['alumno'], $tutor['tutor'], [
        'codigo_institucional' => 'COD-REGISTRO-456',
    ]);

    $payload = [
        'codigo_institucional' => $vinculo->codigo_institucional,
        'email' => 'tutor.registrado@example.com',
        'password' => 'Password123!',
    ];

    $this->postJson('/api/v1/tutores/register', $payload)->assertCreated();
    $this->postJson('/api/v1/tutores/register', $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

test('registrar un tutor con codigo inexistente devuelve 404', function () {
    $this->postJson('/api/v1/tutores/register', [
        'codigo_institucional' => 'COD-INEXISTENTE',
        'email' => 'tutor.registrado@example.com',
        'password' => 'Password123!',
    ])->assertNotFound();
});

test('un alumno no puede crear ni modificar tutores', function () {
    $alumno = crearAlumno();
    autenticarComo($alumno['user']);

    $this->postJson('/api/v1/tutores', [])->assertForbidden();
    $this->putJson('/api/v1/tutores/'.Str::uuid(), [])->assertForbidden();
    $this->getJson('/api/v1/tutores')->assertForbidden();
});
