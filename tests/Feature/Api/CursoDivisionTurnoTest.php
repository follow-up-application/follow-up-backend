<?php

use App\Infrastructure\User\Persistence\CursoDivisionTurnoModel;

test('el administrador crea un curso division turno', function () {
    autenticarComo(usuarioFixture('administrador'));

    $response = $this->postJson('/api/v1/cursoDivisionTurno', [
        'curso' => 5,
        'division' => 'a',
        'turno' => 'Mañana',
        'capacidad_maxima' => 35,
    ]);

    $response->assertCreated()
        ->assertJsonPath('curso', 5)
        ->assertJsonPath('division', 'A')
        ->assertJsonPath('turno', 'Mañana')
        ->assertJsonPath('capacidad_maxima', 35);
});

test('crear un curso division turno duplicado devuelve 409', function () {
    autenticarComo(usuarioFixture('administrador'));

    $payload = [
        'curso' => 5,
        'division' => 'A',
        'turno' => 'Mañana',
        'capacidad_maxima' => 35,
    ];

    $this->postJson('/api/v1/cursoDivisionTurno', $payload)->assertCreated();
    $this->postJson('/api/v1/cursoDivisionTurno', $payload)->assertStatus(409);
});

test('crear un curso division turno valida sus campos', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->postJson('/api/v1/cursoDivisionTurno', [
        'curso' => 0,
        'division' => 'AB',
        'turno' => 'Noche',
        'capacidad_maxima' => 0,
    ])->assertStatus(422)->assertJsonValidationErrors([
        'curso',
        'division',
        'turno',
        'capacidad_maxima',
    ]);
});

test('el administrador lista cursos division turno', function () {
    autenticarComo(usuarioFixture('administrador'));
    $cdt = crearCDT();

    $this->getJson('/api/v1/cursoDivisionTurno')
        ->assertOk()
        ->assertJsonFragment(['id' => $cdt->id]);
});

test('el administrador obtiene un curso division turno por id', function () {
    autenticarComo(usuarioFixture('administrador'));
    $cdt = crearCDT(['capacidad_maxima' => 40]);

    $this->getJson('/api/v1/cursoDivisionTurno/'.$cdt->id)
        ->assertOk()
        ->assertJsonPath('id', $cdt->id)
        ->assertJsonPath('capacidad_maxima', 40);
});

test('obtener un curso division turno inexistente devuelve 404', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->getJson('/api/v1/cursoDivisionTurno/999999')->assertNotFound();
});

test('el administrador actualiza un curso division turno', function () {
    autenticarComo(usuarioFixture('administrador'));
    $cdt = crearCDT();

    $this->putJson('/api/v1/cursoDivisionTurno/'.$cdt->id, [
        'division' => 'b',
        'capacidad_maxima' => 45,
    ])->assertOk()
        ->assertJsonPath('division', 'B')
        ->assertJsonPath('capacidad_maxima', 45);

    expect(CursoDivisionTurnoModel::find($cdt->id)->capacidad_maxima)->toBe(45);
});

test('actualizar un curso division turno sin datos devuelve 422', function () {
    autenticarComo(usuarioFixture('administrador'));
    $cdt = crearCDT();

    $this->putJson('/api/v1/cursoDivisionTurno/'.$cdt->id, [])
        ->assertStatus(422);
});

test('actualizar a un curso division turno duplicado devuelve 409', function () {
    autenticarComo(usuarioFixture('administrador'));

    $primero = CursoDivisionTurnoModel::create([
        'curso' => 5,
        'division' => 'A',
        'turno' => 'Mañana',
        'capacidad_maxima' => 30,
    ]);
    CursoDivisionTurnoModel::create([
        'curso' => 5,
        'division' => 'B',
        'turno' => 'Mañana',
        'capacidad_maxima' => 30,
    ]);

    $this->putJson('/api/v1/cursoDivisionTurno/'.$primero->id, [
        'division' => 'B',
    ])->assertStatus(409);
});

test('el administrador inhabilita un curso division turno', function () {
    autenticarComo(usuarioFixture('administrador'));
    $cdt = crearCDT();

    $this->patchJson('/api/v1/cursoDivisionTurno/'.$cdt->id.'/inhabilitar')
        ->assertStatus(204);

    expect(CursoDivisionTurnoModel::withTrashed()->find($cdt->id)->trashed())->toBeTrue();
});

test('inhabilitar un curso division turno inexistente devuelve 404', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->patchJson('/api/v1/cursoDivisionTurno/999999/inhabilitar')
        ->assertNotFound();
});

test('el administrador y el docente consultan las materias del curso', function () {
    $data = crearMateria();

    autenticarComo(usuarioFixture('administrador'));

    $this->getJson('/api/v1/cursoDivisionTurno/'.$data['cdt']->id.'/materias')
        ->assertOk()
        ->assertJsonFragment(['id' => $data['materia']->id]);

    autenticarComo($data['docente']->user);

    $this->getJson('/api/v1/cursoDivisionTurno/'.$data['cdt']->id.'/materias')
        ->assertOk();
});

test('un alumno no puede crear ni listar cursos division turno', function () {
    $alumno = crearAlumno();
    autenticarComo($alumno['user']);

    $this->getJson('/api/v1/cursoDivisionTurno')->assertForbidden();
    $this->postJson('/api/v1/cursoDivisionTurno', [])->assertForbidden();
});
