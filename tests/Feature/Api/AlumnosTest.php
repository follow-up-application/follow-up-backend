<?php

use App\Infrastructure\User\Persistence\AlumnoModel;
use App\Infrastructure\User\Persistence\TutorAlumnoModel;
use App\Infrastructure\User\Persistence\UserModel;
use Illuminate\Support\Str;

test('el administrador crea un alumno con un tutor nuevo', function () {
    autenticarComo(usuarioFixture('administrador'));
    $cdt = crearCDT();

    $payload = [
        'first_name' => 'Lucía',
        'last_name' => 'Alumna',
        'dni' => 40111222,
        'fecha_nacimiento' => '2005-03-15',
        'curso_division_turno_id' => $cdt->id,
        'relationship' => 'padre',
        'tutor_first_name' => 'Pedro',
        'tutor_last_name' => 'Tutor',
        'tutor_dni' => 50111222,
        'tutor_telephone' => '3515556666',
    ];

    $response = $this->postJson('/api/v1/alumnos', $payload);

    $response->assertCreated()
        ->assertJsonPath('first_name', 'Lucía')
        ->assertJsonPath('last_name', 'Alumna')
        ->assertJsonPath('fecha_nacimiento', '15/03/2005');

    $tutorUser = UserModel::where('dni', $payload['tutor_dni'])
        ->where('role', 'tutor')
        ->first();

    expect($tutorUser)->not->toBeNull();
    expect(TutorAlumnoModel::where('alumno_id', $response->json('id'))
        ->where('tutor_id', $tutorUser->id)
        ->exists())->toBeTrue();
});

test('el administrador crea un alumno con un tutor existente por tutor_id', function () {
    autenticarComo(usuarioFixture('administrador'));
    $tutor = crearTutor();
    $cdt = crearCDT();

    $response = $this->postJson('/api/v1/alumnos', [
        'first_name' => 'Mateo',
        'last_name' => 'Alumno',
        'dni' => 40222333,
        'fecha_nacimiento' => '2006-06-20',
        'curso_division_turno_id' => $cdt->id,
        'relationship' => 'madre',
        'tutor_id' => $tutor['tutor']->id,
    ]);

    $response->assertCreated();

    $alumnoId = $response->json('id');

    expect(TutorAlumnoModel::where('alumno_id', $alumnoId)
        ->where('tutor_id', $tutor['tutor']->id)
        ->exists())->toBeTrue();
});

test('el administrador crea un alumno con un tutor existente por dni', function () {
    autenticarComo(usuarioFixture('administrador'));
    $tutor = crearTutor();
    $cdt = crearCDT();

    $response = $this->postJson('/api/v1/alumnos', [
        'first_name' => 'Sofía',
        'last_name' => 'Alumna',
        'dni' => 40333444,
        'fecha_nacimiento' => '2007-09-12',
        'curso_division_turno_id' => $cdt->id,
        'relationship' => 'otra',
        'otra_relacion' => 'Abuela',
        'tutor_dni' => $tutor['user']->dni,
    ]);

    $response->assertCreated();

    expect(TutorAlumnoModel::where('alumno_id', $response->json('id'))
        ->where('tutor_id', $tutor['tutor']->id)
        ->exists())->toBeTrue();
});

test('crear un alumno exige todos los datos del tutor cuando no existe', function () {
    autenticarComo(usuarioFixture('administrador'));
    $cdt = crearCDT();

    $this->postJson('/api/v1/alumnos', [
        'first_name' => 'Sin',
        'last_name' => 'Tutor',
        'dni' => 40444555,
        'fecha_nacimiento' => '2005-01-01',
        'curso_division_turno_id' => $cdt->id,
        'relationship' => 'padre',
    ])->assertStatus(422)->assertJsonValidationErrors([
        'tutor_first_name',
        'tutor_last_name',
        'tutor_dni',
        'tutor_telephone',
    ]);
});

test('crear un alumno valida fecha, dni, curso y relacion', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->postJson('/api/v1/alumnos', [
        'first_name' => 'Inválido',
        'last_name' => 'Alumno',
        'dni' => 1,
        'fecha_nacimiento' => '31/02/2020',
        'curso_division_turno_id' => 999999,
        'relationship' => 'PADRE',
        'tutor_first_name' => 'Tutor',
        'tutor_last_name' => 'Nuevo',
        'tutor_dni' => 50555666,
        'tutor_telephone' => '3515556666',
    ])->assertStatus(422)->assertJsonValidationErrors([
        'dni',
        'fecha_nacimiento',
        'curso_division_turno_id',
        'relationship',
    ]);
});

test('no se puede crear un alumno con un dni ya registrado', function () {
    autenticarComo(usuarioFixture('administrador'));
    $existente = crearAlumno();

    $this->postJson('/api/v1/alumnos', [
        'first_name' => 'Duplicado',
        'last_name' => 'Alumno',
        'dni' => $existente['user']->dni,
        'fecha_nacimiento' => '2005-01-01',
        'curso_division_turno_id' => crearCDT()->id,
        'relationship' => 'padre',
        'tutor_first_name' => 'Tutor',
        'tutor_last_name' => 'Nuevo',
        'tutor_dni' => 50666777,
        'tutor_telephone' => '3515556666',
    ])->assertStatus(422)->assertJsonValidationErrors('dni');
});

test('el administrador lista alumnos', function () {
    $admin = usuarioFixture('administrador');
    $alumno = crearAlumno();

    autenticarComo($admin);

    $this->getJson('/api/v1/alumnos')
        ->assertOk()
        ->assertJsonFragment(['id' => $alumno['alumno']->id]);
});

test('el administrador obtiene un alumno por id', function () {
    autenticarComo(usuarioFixture('administrador'));
    $alumno = crearAlumno();

    $this->getJson('/api/v1/alumnos/'.$alumno['alumno']->id)
        ->assertOk()
        ->assertJsonPath('id', $alumno['alumno']->id)
        ->assertJsonPath('first_name', $alumno['user']->first_name)
        ->assertJsonPath('fecha_nacimiento', '15/03/2005');
});

test('el alumno puede ver su propio registro', function () {
    $alumno = crearAlumno();

    autenticarComo($alumno['user']);

    $this->getJson('/api/v1/alumnos/'.$alumno['alumno']->id)
        ->assertOk()
        ->assertJsonPath('id', $alumno['alumno']->id);
});

test('obtener un alumno inexistente devuelve 404', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->getJson('/api/v1/alumnos/'.Str::uuid())
        ->assertNotFound();
});

test('el administrador actualiza un alumno', function () {
    autenticarComo(usuarioFixture('administrador'));
    $alumno = crearAlumno();
    $nuevoCdt = crearCDT();

    $this->putJson('/api/v1/alumnos/'.$alumno['alumno']->id, [
        'first_name' => 'Nombre',
        'last_name' => 'Nuevo',
        'fecha_nacimiento' => '2010-01-02',
        'curso_division_turno_id' => $nuevoCdt->id,
    ])->assertOk()
        ->assertJsonPath('first_name', 'Nombre')
        ->assertJsonPath('last_name', 'Nuevo')
        ->assertJsonPath('fecha_nacimiento', '02/01/2010');

    expect(AlumnoModel::find($alumno['alumno']->id)->curso_division_turno_id)
        ->toBe($nuevoCdt->id);
});

test('actualizar un alumno sin datos devuelve 422', function () {
    autenticarComo(usuarioFixture('administrador'));
    $alumno = crearAlumno();

    $this->putJson('/api/v1/alumnos/'.$alumno['alumno']->id, [])
        ->assertStatus(422);
});

test('actualizar un alumno valida el curso informado', function () {
    autenticarComo(usuarioFixture('administrador'));
    $alumno = crearAlumno();

    $this->putJson('/api/v1/alumnos/'.$alumno['alumno']->id, [
        'curso_division_turno_id' => 999999,
    ])->assertStatus(422)->assertJsonValidationErrors('curso_division_turno_id');
});

test('el administrador inhabilita un alumno', function () {
    autenticarComo(usuarioFixture('administrador'));
    $alumno = crearAlumno();

    $this->patchJson('/api/v1/alumnos/'.$alumno['alumno']->id.'/inhabilitar')
        ->assertStatus(204);

    expect(AlumnoModel::withTrashed()->find($alumno['alumno']->id)->trashed())->toBeTrue();
});

test('inhabilitar un alumno inexistente devuelve 404', function () {
    autenticarComo(usuarioFixture('administrador'));

    $this->patchJson('/api/v1/alumnos/'.Str::uuid().'/inhabilitar')
        ->assertNotFound();
});

test('el docente puede listar y crear alumnos', function () {
    $docente = crearDocente();
    autenticarComo($docente['user']);

    $this->getJson('/api/v1/alumnos')->assertOk();

    $this->postJson('/api/v1/alumnos', [
        'first_name' => 'Alumno',
        'last_name' => 'Docente',
        'dni' => 40777888,
        'fecha_nacimiento' => '2005-01-01',
        'curso_division_turno_id' => crearCDT()->id,
        'relationship' => 'padre',
        'tutor_first_name' => 'Tutor',
        'tutor_last_name' => 'Nuevo',
        'tutor_dni' => 50777888,
        'tutor_telephone' => '3515556666',
    ])->assertCreated();
});

test('el tutor no puede crear ni inhabilitar alumnos', function () {
    $tutor = crearTutor();
    autenticarComo($tutor['user']);

    $this->postJson('/api/v1/alumnos', [])->assertForbidden();

    $alumno = crearAlumno();
    $this->patchJson('/api/v1/alumnos/'.$alumno['alumno']->id.'/inhabilitar')
        ->assertForbidden();
});
