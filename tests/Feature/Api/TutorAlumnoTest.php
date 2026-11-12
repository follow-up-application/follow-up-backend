<?php

use App\Infrastructure\User\Persistence\TutorAlumnoModel;
use Illuminate\Support\Str;

test('el administrador vincula un tutor con un alumno', function () {
    autenticarComo(usuarioFixture('administrador'));
    $alumno = crearAlumno();
    $tutor = crearTutor();

    $response = $this->postJson('/api/v1/tutor-alumno', [
        'alumno_id' => $alumno['alumno']->id,
        'tutor_id' => $tutor['tutor']->id,
        'relationship' => 'madre',
    ]);

    $response->assertCreated()
        ->assertJsonPath('alumno_id', $alumno['alumno']->id)
        ->assertJsonPath('tutor_id', $tutor['tutor']->id)
        ->assertJsonPath('relationship', 'madre');

    expect($response->json('codigo_institucional'))->not->toBeEmpty();
    expect(TutorAlumnoModel::find($response->json('id')))->not->toBeNull();
});

test('el vinculo tutor alumno valida sus campos', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->postJson('/api/v1/tutor-alumno', [
        'alumno_id' => 'no-existe',
        'tutor_id' => 'no-existe',
        'relationship' => 'otro',
    ])->assertStatus(422)->assertJsonValidationErrors([
        'alumno_id',
        'tutor_id',
        'relationship',
    ]);
});

test('se consultan los tutores de un alumno', function () {
    $alumno = crearAlumno();
    $tutor = crearTutor();
    $vinculo = crearVinculoTutorAlumno($alumno['alumno'], $tutor['tutor']);

    autenticarComo(usuarioFixture('administrador'));

    $this->getJson('/api/v1/alumnos/'.$alumno['alumno']->id.'/tutores')
        ->assertOk()
        ->assertJsonFragment(['id' => $tutor['tutor']->id]);

    expect($vinculo->tutor_id)->toBe($tutor['tutor']->id);
});

test('consultar tutores de un alumno inexistente devuelve 404', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->getJson('/api/v1/alumnos/'.Str::uuid().'/tutores')
        ->assertNotFound();
});

test('el administrador inhabilita un vinculo tutor alumno', function () {
    autenticarComo(usuarioFixture('administrador'));
    $alumno = crearAlumno();
    $tutor = crearTutor();
    $vinculo = crearVinculoTutorAlumno($alumno['alumno'], $tutor['tutor']);

    $this->patchJson('/api/v1/tutor-alumno/'.$vinculo->id.'/inhabilitar')
        ->assertStatus(204);

    expect(TutorAlumnoModel::withTrashed()->find($vinculo->id)->trashed())->toBeTrue();
});

test('inhabilitar un vinculo inexistente devuelve 404', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->patchJson('/api/v1/tutor-alumno/'.Str::uuid().'/inhabilitar')
        ->assertNotFound();
});

test('un alumno no puede crear ni inhabilitar vinculos', function () {
    $alumno = crearAlumno();
    autenticarComo($alumno['user']);

    $this->postJson('/api/v1/tutor-alumno', [])->assertForbidden();
    $this->patchJson('/api/v1/tutor-alumno/'.Str::uuid().'/inhabilitar')
        ->assertForbidden();
});
