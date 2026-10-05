<?php

namespace App\Infrastructure\User\Persistence\Eloquent;

use App\Domain\Shared\ValueObjects\FechaFormateada;
use App\Domain\User\Entities\Alumno;
use App\Domain\User\Repositories\AlumnoRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\UserName;
use App\Infrastructure\User\Persistence\AlumnoModel;
use App\Infrastructure\User\Persistence\UserModel;

class EloquentAlumnoRepository implements AlumnoRepositoryInterface
{
    public function save(Alumno $alumno): Alumno
    {
        $userModel = UserModel::updateOrCreate(
            ['id' => $alumno->id()->value()],
            [
                'first_name' => $alumno->firstName(),
                'last_name' => $alumno->lastName(),
                'username' => $alumno->username(),
                'password' => $alumno->password(),
                'dni' => $alumno->dni(),
                'role' => $alumno->role()->value,
                'must_change_password' => $alumno->mustChangePassword(),
            ]
        );

        $alumnoModel = AlumnoModel::where('id', $userModel->id)->first();

        if ($alumnoModel) {
            $alumnoModel->update([
                'fecha_nacimiento' => $alumno->fechaNacimiento()->toDatabase(),
                'curso_division_turno_id' => $alumno->cursoDivisionTurno()->id(),
            ]);
        } else {
            $alumnoModel = AlumnoModel::create([
                'id' => $userModel->id,
                'fecha_nacimiento' => $alumno->fechaNacimiento()->toDatabase(),
                'curso_division_turno_id' => $alumno->cursoDivisionTurno()->id(),
            ]);
        }

        return $this->toDomain($userModel, $alumnoModel);
    }

    public function findById(UserId $id): ?Alumno
    {
        $userModel = UserModel::find($id->value());
        if (! $userModel) {
            return null;
        }

        $alumnoModel = AlumnoModel::find($userModel->id);

        return $alumnoModel ? $this->toDomain($userModel, $alumnoModel) : null;
    }

    public function findByUsername(string $username): ?Alumno
    {
        $userModel = UserModel::where('username', $username)->first();
        if (! $userModel) {
            return null;
        }

        $alumnoModel = AlumnoModel::find($userModel->id);

        return $alumnoModel ? $this->toDomain($userModel, $alumnoModel) : null;
    }

    public function all(): array
    {
        return AlumnoModel::with('user')
            ->get()
            ->map(fn (AlumnoModel $model) => $this->toDomain($model->user, $model))
            ->toArray();
    }

    public function destroy(UserId $id): void
    {
        // cascadeOnDelete en la migración se encarga de borrar alumnos también
        UserModel::destroy($id->value());
    }

    public function totalAlumnos(): int
    {
        return AlumnoModel::count();
    }

    private function toDomain(UserModel $userModel, AlumnoModel $alumnoModel): Alumno
    {
        $cdtModel = $alumnoModel->cursoDivisionTurno; // relación belongsTo, asumida en AlumnoModel

        if ($cdtModel === null) {
            throw new \RuntimeException(
                "Alumno {$userModel->id} no tiene curso_division_turno asociado (integridad referencial rota)."
            );
        }

        return new Alumno(
            new UserId($userModel->id),
            $userModel->first_name,
            $userModel->last_name,
            (int) $userModel->dni,
            $userModel->password,
            UserName::fromString($userModel->username),
            FechaFormateada::fromDatabase(
                $alumnoModel->fecha_nacimiento instanceof \DateTimeInterface
                    ? $alumnoModel->fecha_nacimiento->format('Y-m-d')
                    : $alumnoModel->fecha_nacimiento
            ),
            $cdtModel->toDomain(),
        );
    }
}
