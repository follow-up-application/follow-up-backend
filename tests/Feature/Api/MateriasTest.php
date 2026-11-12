<?php

use App\Infrastructure\User\Persistence\MateriaModel;
use Illuminate\Support\Str;

test('el administrador crea una materia', function () {
    autenticarComo(usuarioFixture('administrador'));
    $docente = crearDocente();

    $response = $this->postJson('/api/v1/materias', [
        'nombre' => 'Matemática',
        'descripcion' => 'Álgebra y análisis',
        'curso_division_turno_id' => $docente['cdt']->id,
        'docente_id' => $docente['docente']->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('nombre', 'Matemática')
        ->assertJsonPath('descripcion', 'Álgebra y análisis')
        ->assertJsonPath('curso_division_turno_id', $docente['cdt']->id)
        ->assertJsonPath('docente_id', $docente['docente']->id);
});

test('crear una materia duplicada en el mismo curso devuelve 409', function () {
    autenticarComo(usuarioFixture('administrador'));
    $docente = crearDocente();

    $payload = [
        'nombre' => 'Matemática',
        'descripcion' => 'Álgebra y análisis',
        'curso_division_turno_id' => $docente['cdt']->id,
        'docente_id' => $docente['docente']->id,
    ];

    $this->postJson('/api/v1/materias', $payload)->assertCreated();
    $this->postJson('/api/v1/materias', $payload)->assertStatus(409);
});

test('se puede crear una materia con el mismo nombre en otro curso', function () {
    autenticarComo(usuarioFixture('administrador'));
    $primero = crearDocente();
    $segundo = crearDocente();

    $this->postJson('/api/v1/materias', [
        'nombre' => 'Matemática',
        'descripcion' => 'Primer curso',
        'curso_division_turno_id' => $primero['cdt']->id,
        'docente_id' => $primero['docente']->id,
    ])->assertCreated();

    $this->postJson('/api/v1/materias', [
        'nombre' => 'Matemática',
        'descripcion' => 'Segundo curso',
        'curso_division_turno_id' => $segundo['cdt']->id,
        'docente_id' => $segundo['docente']->id,
    ])->assertCreated();
});

test('crear una materia valida docente, curso y campos', function () {
    autenticarComo(usuarioFixture('administrador'));
    $docente = crearDocente();

    $this->postJson('/api/v1/materias', [
        'nombre' => '',
        'descripcion' => '',
        'curso_division_turno_id' => 999999,
        'docente_id' => 'no-es-uuid',
    ])->assertStatus(422)->assertJsonValidationErrors([
        'nombre',
        'descripcion',
        'curso_division_turno_id',
        'docente_id',
    ]);

    $this->postJson('/api/v1/materias', [
        'nombre' => 'Materia',
        'descripcion' => 'Descripción',
        'curso_division_turno_id' => $docente['cdt']->id,
        'docente_id' => Str::uuid(),
    ])->assertStatus(422)->assertJsonValidationErrors('docente_id');
});

test('el administrador lista y obtiene materias', function () {
    autenticarComo(usuarioFixture('administrador'));
    $data = crearMateria();

    $this->getJson('/api/v1/materias')
        ->assertOk()
        ->assertJsonFragment(['id' => $data['materia']->id]);

    $this->getJson('/api/v1/materias/'.$data['materia']->id)
        ->assertOk()
        ->assertJsonPath('id', $data['materia']->id)
        ->assertJsonPath('nombre', $data['materia']->nombre);
});

test('obtener una materia inexistente devuelve 404', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->getJson('/api/v1/materias/999999')->assertNotFound();
});

test('el administrador actualiza una materia', function () {
    autenticarComo(usuarioFixture('administrador'));
    $data = crearMateria();

    $this->putJson('/api/v1/materias/'.$data['materia']->id, [
        'nombre' => 'Historia',
        'descripcion' => 'Historia argentina',
    ])->assertOk()
        ->assertJsonPath('nombre', 'Historia')
        ->assertJsonPath('descripcion', 'Historia argentina');

    expect(MateriaModel::find($data['materia']->id)->nombre)->toBe('Historia');
});

test('actualizar una materia sin datos devuelve 422', function () {
    autenticarComo(usuarioFixture('administrador'));
    $data = crearMateria();

    $this->putJson('/api/v1/materias/'.$data['materia']->id, [])
        ->assertStatus(422);
});

test('el administrador inhabilita una materia', function () {
    autenticarComo(usuarioFixture('administrador'));
    $data = crearMateria();

    $this->patchJson('/api/v1/materias/'.$data['materia']->id.'/inhabilitar')
        ->assertStatus(204);

    expect(MateriaModel::withTrashed()->find($data['materia']->id)->trashed())->toBeTrue();
});

test('inhabilitar una materia inexistente devuelve 404', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->patchJson('/api/v1/materias/999999/inhabilitar')->assertNotFound();
});

test('se consultan las materias por docente y por curso', function () {
    $data = crearMateria();

    autenticarComo(usuarioFixture('administrador'));

    $this->getJson('/api/v1/docentes/'.$data['docente']->id.'/materias')
        ->assertOk()
        ->assertJsonFragment(['id' => $data['materia']->id]);

    $this->getJson('/api/v1/cursoDivisionTurno/'.$data['cdt']->id.'/materias')
        ->assertOk()
        ->assertJsonFragment(['id' => $data['materia']->id]);
});

test('un alumno no puede gestionar materias', function () {
    $alumno = crearAlumno();
    autenticarComo($alumno['user']);

    $this->getJson('/api/v1/materias')->assertForbidden();
    $this->postJson('/api/v1/materias', [])->assertForbidden();
});
