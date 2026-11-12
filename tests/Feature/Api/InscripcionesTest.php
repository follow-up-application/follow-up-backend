<?php

use App\Infrastructure\User\Persistence\InscripcionModel;

test('el administrador crea una inscripcion', function () {
    autenticarComo(usuarioFixture('administrador'));
    $alumno = crearAlumno();
    $materia = crearMateria();

    $response = $this->postJson('/api/v1/inscripciones', [
        'alumno_id' => $alumno['alumno']->id,
        'materia_id' => $materia['materia']->id,
        'fecha_inscripcion' => '01/03/2026',
    ]);

    $response->assertCreated()
        ->assertJsonPath('alumno_id', $alumno['alumno']->id)
        ->assertJsonPath('materia_id', $materia['materia']->id)
        ->assertJsonPath('fecha_inscripcion', '2026-03-01')
        ->assertJsonPath('estado', 'pendiente');
});

test('la fecha de inscripcion es opcional y por defecto usa la fecha actual', function () {
    autenticarComo(usuarioFixture('administrador'));
    $alumno = crearAlumno();
    $materia = crearMateria();

    $response = $this->postJson('/api/v1/inscripciones', [
        'alumno_id' => $alumno['alumno']->id,
        'materia_id' => $materia['materia']->id,
    ]);

    $response->assertCreated()->assertJsonPath('estado', 'pendiente');
    expect($response->json('fecha_inscripcion'))->toBe(now()->format('Y-m-d'));
});

test('crear una inscripcion duplicada devuelve 409', function () {
    autenticarComo(usuarioFixture('administrador'));
    $alumno = crearAlumno();
    $materia = crearMateria();

    $payload = [
        'alumno_id' => $alumno['alumno']->id,
        'materia_id' => $materia['materia']->id,
    ];

    $this->postJson('/api/v1/inscripciones', $payload)->assertCreated();
    $this->postJson('/api/v1/inscripciones', $payload)->assertStatus(409);
});

test('crear una inscripcion valida alumno, materia y fecha', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->postJson('/api/v1/inscripciones', [
        'alumno_id' => 'no-es-uuid',
        'materia_id' => 999999,
        'fecha_inscripcion' => '31/02/2026',
    ])->assertStatus(422)->assertJsonValidationErrors([
        'alumno_id',
        'materia_id',
        'fecha_inscripcion',
    ]);
});

test('el administrador lista y obtiene inscripciones', function () {
    autenticarComo(usuarioFixture('administrador'));
    $alumno = crearAlumno();
    $materia = crearMateria();
    $inscripcion = crearInscripcion($alumno['alumno'], $materia['materia']);

    $this->getJson('/api/v1/inscripciones')
        ->assertOk()
        ->assertJsonFragment(['id' => $inscripcion->id]);

    $this->getJson('/api/v1/inscripciones/'.$inscripcion->id)
        ->assertOk()
        ->assertJsonPath('id', $inscripcion->id)
        ->assertJsonPath('estado', 'pendiente');
});

test('obtener una inscripcion inexistente devuelve 404', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->getJson('/api/v1/inscripciones/999999')->assertNotFound();
});

test('se listan las inscripciones de un alumno con filtro de estado', function () {
    autenticarComo(usuarioFixture('administrador'));
    $alumno = crearAlumno();
    $materia = crearMateria();
    $inscripcion = crearInscripcion($alumno['alumno'], $materia['materia'], [
        'estado' => 'aprobado',
    ]);

    $this->getJson('/api/v1/alumnos/'.$alumno['alumno']->id.'/inscripciones?estado=aprobado')
        ->assertOk()
        ->assertJsonFragment(['id' => $inscripcion->id]);

    $this->getJson('/api/v1/alumnos/'.$alumno['alumno']->id.'/inscripciones?estado=pendiente')
        ->assertOk()
        ->assertJsonMissing(['id' => $inscripcion->id]);
});

test('se listan las inscripciones de una materia con filtro de estado', function () {
    $alumno = crearAlumno();
    $materia = crearMateria();
    $inscripcion = crearInscripcion($alumno['alumno'], $materia['materia'], [
        'estado' => 'rechazado',
    ]);

    autenticarComo(usuarioFixture('administrador'));

    $this->getJson('/api/v1/materias/'.$materia['materia']->id.'/inscripciones?estado=rechazado')
        ->assertOk()
        ->assertJsonFragment(['id' => $inscripcion->id]);
});

test('el docente puede cambiar el estado de una inscripcion', function () {
    $docente = crearDocente();
    $alumno = crearAlumno();
    $materia = crearMateria($docente['cdt'], $docente['docente']);
    $inscripcion = crearInscripcion($alumno['alumno'], $materia['materia']);

    autenticarComo($docente['user']);

    $this->putJson('/api/v1/inscripciones/'.$inscripcion->id.'/estado', [
        'estado' => 'aprobado',
    ])->assertOk()
        ->assertJsonPath('estado', 'aprobado');

    expect(InscripcionModel::find($inscripcion->id)->estado->value)->toBe('aprobado');
});

test('cambiar el estado valida el valor permitido', function () {
    autenticarComo(usuarioFixture('administrador'));
    $alumno = crearAlumno();
    $materia = crearMateria();
    $inscripcion = crearInscripcion($alumno['alumno'], $materia['materia']);

    $this->putJson('/api/v1/inscripciones/'.$inscripcion->id.'/estado', [
        'estado' => 'otro',
    ])->assertStatus(422)->assertJsonValidationErrors('estado');
});

test('el alumno puede inscribirse en una materia', function () {
    $alumno = crearAlumno();
    $materia = crearMateria();

    autenticarComo($alumno['user']);

    $this->postJson('/api/v1/inscripciones', [
        'alumno_id' => $alumno['alumno']->id,
        'materia_id' => $materia['materia']->id,
    ])->assertCreated();
});

test('el tutor no puede acceder a las inscripciones', function () {
    $tutor = crearTutor();
    autenticarComo($tutor['user']);

    $this->getJson('/api/v1/inscripciones')->assertForbidden();
    $this->postJson('/api/v1/inscripciones', [])->assertForbidden();
});

test('el docente no puede crear inscripciones', function () {
    $docente = crearDocente();
    autenticarComo($docente['user']);

    $this->postJson('/api/v1/inscripciones', [])->assertForbidden();
});

test('no se permite eliminar inscripciones', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->deleteJson('/api/v1/inscripciones/1')->assertStatus(405);
});
