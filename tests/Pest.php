<?php

use App\Domain\User\ValueObjects\TurnoEscolar;
use App\Infrastructure\User\Persistence\AlumnoModel;
use App\Infrastructure\User\Persistence\CursoDivisionTurnoModel;
use App\Infrastructure\User\Persistence\DocenteModel;
use App\Infrastructure\User\Persistence\InscripcionModel;
use App\Infrastructure\User\Persistence\MateriaModel;
use App\Infrastructure\User\Persistence\TutorAlumnoModel;
use App\Infrastructure\User\Persistence\TutorModel;
use App\Infrastructure\User\Persistence\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

function usuarioFixture(string $role = 'alumno', array $overrides = []): UserModel
{
    $attributes = array_merge([
        'id' => (string) Str::uuid(),
        'first_name' => 'Usuario',
        'last_name' => ucfirst($role),
        'dni' => fake()->unique()->numberBetween(10_000_000, 99_999_999),
        'username' => $role.'_'.Str::lower(Str::random(10)),
        'password' => Hash::make('Password123!'),
        'role' => $role,
        'must_change_password' => false,
    ], $overrides);

    return UserModel::create($attributes);
}

function autenticarComo(UserModel $user): UserModel
{
    Sanctum::actingAs($user);

    return $user;
}

function tokenPara(UserModel $user): string
{
    return $user->createToken('test-token')->plainTextToken;
}

function crearCDT(array $overrides = []): CursoDivisionTurnoModel
{
    static $sequence = 0;

    $sequence++;

    return CursoDivisionTurnoModel::create(array_merge([
        'curso' => (($sequence - 1) % 7) + 1,
        'division' => chr(65 + intdiv($sequence - 1, 7) % 26),
        'turno' => $sequence % 2 === 0 ? TurnoEscolar::TARDE->value : TurnoEscolar::MANANA->value,
        'capacidad_maxima' => 30,
    ], $overrides));
}

function crearDocente(?CursoDivisionTurnoModel $cdt = null, array $overrides = []): array
{
    $cdt ??= crearCDT();

    $user = usuarioFixture('docente', $overrides['user'] ?? []);

    $docente = DocenteModel::create(array_merge([
        'id' => $user->id,
        'telephone' => '3512345678',
        'fecha_ingreso' => '2020-03-01',
        'curso_division_turno_id' => $cdt->id,
    ], $overrides['docente'] ?? []));

    return [
        'user' => $user,
        'docente' => $docente,
        'cdt' => $cdt,
    ];
}

function crearTutor(array $overrides = []): array
{
    $user = usuarioFixture('tutor', $overrides['user'] ?? []);

    $tutor = TutorModel::create(array_merge([
        'id' => $user->id,
        'telephone' => '3511112222',
        'email' => 'tutor_'.Str::lower(Str::random(10)).'@example.com',
    ], $overrides['tutor'] ?? []));

    return [
        'user' => $user,
        'tutor' => $tutor,
    ];
}

function crearAlumno(?CursoDivisionTurnoModel $cdt = null, array $overrides = []): array
{
    $cdt ??= crearCDT();

    $user = usuarioFixture('alumno', $overrides['user'] ?? []);

    $alumno = AlumnoModel::create(array_merge([
        'id' => $user->id,
        'fecha_nacimiento' => '2005-03-15',
        'curso_division_turno_id' => $cdt->id,
    ], $overrides['alumno'] ?? []));

    return [
        'user' => $user,
        'alumno' => $alumno,
        'cdt' => $cdt,
    ];
}

function crearMateria(?CursoDivisionTurnoModel $cdt = null, ?DocenteModel $docente = null, array $overrides = []): array
{
    if ($docente === null) {
        $docenteData = crearDocente($cdt);
        $docente = $docenteData['docente'];
        $cdt = $docenteData['cdt'];
    }

    $cdt ??= CursoDivisionTurnoModel::find($docente->curso_division_turno_id);

    $materia = MateriaModel::create(array_merge([
        'nombre' => 'Materia '.Str::lower(Str::random(8)),
        'descripcion' => 'Descripción de la materia',
        'curso_division_turno_id' => $cdt->id,
        'docente_id' => $docente->id,
    ], $overrides));

    return [
        'materia' => $materia,
        'docente' => $docente,
        'cdt' => $cdt,
    ];
}

function crearInscripcion(AlumnoModel $alumno, MateriaModel $materia, array $overrides = []): InscripcionModel
{
    return InscripcionModel::create(array_merge([
        'alumno_id' => $alumno->id,
        'materia_id' => $materia->id,
        'fecha_inscripcion' => '2026-03-01',
        'estado' => 'pendiente',
    ], $overrides));
}

function crearVinculoTutorAlumno(AlumnoModel $alumno, TutorModel $tutor, array $overrides = []): TutorAlumnoModel
{
    return TutorAlumnoModel::create(array_merge([
        'id' => (string) Str::uuid(),
        'alumno_id' => $alumno->id,
        'tutor_id' => $tutor->id,
        'relationship' => 'padre',
        'otra_relacion' => null,
        'codigo_institucional' => 'ALU-'.strtoupper(Str::random(6)).'-'.strtoupper(Str::random(6)),
    ], $overrides));
}
