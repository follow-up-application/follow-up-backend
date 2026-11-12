<?php

use App\Infrastructure\User\Persistence\DocenteModel;
use Illuminate\Support\Str;

test('el administrador crea un docente con fecha yyyy-mm-dd', function () {
    autenticarComo(usuarioFixture('administrador'));
    $cdt = crearCDT();

    $response = $this->postJson('/api/v1/docentes', [
        'first_name' => 'Marta',
        'last_name' => 'Docente',
        'dni' => 41111222,
        'telephone' => '3514445555',
        'fecha_ingreso' => '2020-03-01',
        'curso_division_turno_id' => $cdt->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('first_name', 'Marta')
        ->assertJsonPath('last_name', 'Docente')
        ->assertJsonPath('fecha_ingreso', '01/03/2020');

    expect(DocenteModel::find($response->json('id')))->not->toBeNull();
});

test('el administrador crea un docente con fecha dd/mm/yyyy', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->postJson('/api/v1/docentes', [
        'first_name' => 'Marta',
        'last_name' => 'Docente',
        'dni' => 41222333,
        'telephone' => '3514445555',
        'fecha_ingreso' => '01/03/2020',
        'curso_division_turno_id' => crearCDT()->id,
    ])->assertCreated()->assertJsonPath('fecha_ingreso', '01/03/2020');
});

test('crear un docente valida fecha, dni, telefono y curso', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->postJson('/api/v1/docentes', [
        'first_name' => 'Inválido',
        'last_name' => 'Docente',
        'dni' => 1,
        'telephone' => '',
        'fecha_ingreso' => '31/02/2020',
        'curso_division_turno_id' => 999999,
    ])->assertStatus(422)->assertJsonValidationErrors([
        'dni',
        'telephone',
        'fecha_ingreso',
        'curso_division_turno_id',
    ]);
});

test('no se puede crear un docente con un dni ya registrado', function () {
    autenticarComo(usuarioFixture('administrador'));
    $existente = crearDocente();

    $this->postJson('/api/v1/docentes', [
        'first_name' => 'Duplicado',
        'last_name' => 'Docente',
        'dni' => $existente['user']->dni,
        'telephone' => '3514445555',
        'fecha_ingreso' => '2020-03-01',
        'curso_division_turno_id' => crearCDT()->id,
    ])->assertStatus(422)->assertJsonValidationErrors('dni');
});

test('el administrador lista docentes', function () {
    autenticarComo(usuarioFixture('administrador'));
    $docente = crearDocente();

    $this->getJson('/api/v1/docentes')
        ->assertOk()
        ->assertJsonFragment(['id' => $docente['docente']->id]);
});

test('el administrador obtiene un docente por id', function () {
    autenticarComo(usuarioFixture('administrador'));
    $docente = crearDocente();

    $this->getJson('/api/v1/docentes/'.$docente['docente']->id)
        ->assertOk()
        ->assertJsonPath('id', $docente['docente']->id)
        ->assertJsonPath('first_name', $docente['user']->first_name)
        ->assertJsonPath('fecha_ingreso', '01/03/2020');
});

test('obtener un docente inexistente devuelve 404', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->getJson('/api/v1/docentes/'.Str::uuid())
        ->assertNotFound();
});

test('el administrador actualiza un docente', function () {
    autenticarComo(usuarioFixture('administrador'));
    $docente = crearDocente();

    $this->putJson('/api/v1/docentes/'.$docente['docente']->id, [
        'first_name' => 'Nombre',
        'last_name' => 'Actualizado',
        'telephone' => '3510001111',
        'fecha_ingreso' => '2015-08-20',
    ])->assertOk()
        ->assertJsonPath('first_name', 'Nombre')
        ->assertJsonPath('telephone', '3510001111')
        ->assertJsonPath('fecha_ingreso', '20/08/2015');
});

test('actualizar un docente sin datos devuelve 422', function () {
    autenticarComo(usuarioFixture('administrador'));
    $docente = crearDocente();

    $this->putJson('/api/v1/docentes/'.$docente['docente']->id, [])
        ->assertStatus(422);
});

test('actualizar un docente valida fecha y curso', function () {
    autenticarComo(usuarioFixture('administrador'));
    $docente = crearDocente();

    $this->putJson('/api/v1/docentes/'.$docente['docente']->id, [
        'fecha_ingreso' => '2020-02-31',
        'curso_division_turno_id' => 999999,
    ])->assertStatus(422)->assertJsonValidationErrors([
        'fecha_ingreso',
        'curso_division_turno_id',
    ]);
});

test('el administrador inhabilita un docente', function () {
    autenticarComo(usuarioFixture('administrador'));
    $docente = crearDocente();

    $this->patchJson('/api/v1/docentes/'.$docente['docente']->id.'/inhabilitar')
        ->assertStatus(204);

    expect(DocenteModel::withTrashed()->find($docente['docente']->id)->trashed())->toBeTrue();
});

test('inhabilitar un docente inexistente devuelve 404', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->patchJson('/api/v1/docentes/'.Str::uuid().'/inhabilitar')
        ->assertNotFound();
});

test('el administrador y el docente pueden consultar las materias del docente', function () {
    $docente = crearDocente();
    $materia = crearMateria($docente['cdt'], $docente['docente']);

    autenticarComo(usuarioFixture('administrador'));

    $this->getJson('/api/v1/docentes/'.$docente['docente']->id.'/materias')
        ->assertOk()
        ->assertJsonFragment(['id' => $materia['materia']->id]);

    autenticarComo($docente['user']);

    $this->getJson('/api/v1/docentes/'.$docente['docente']->id.'/materias')
        ->assertOk();
});

test('un alumno no puede listar ni inhabilitar docentes', function () {
    $alumno = crearAlumno();
    autenticarComo($alumno['user']);

    $this->getJson('/api/v1/docentes')->assertForbidden();
    $this->patchJson('/api/v1/docentes/'.Str::uuid().'/inhabilitar')
        ->assertForbidden();
});
