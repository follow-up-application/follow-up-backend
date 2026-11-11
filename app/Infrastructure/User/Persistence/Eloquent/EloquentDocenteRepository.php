<?php

namespace App\Infrastructure\User\Persistence\Eloquent;

use App\Domain\Shared\ValueObjects\FechaFormateada;
use App\Domain\User\Entities\Docente;
use App\Domain\User\Repositories\DocenteRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\UserName;
use App\Infrastructure\User\Persistence\DocenteModel;
use App\Infrastructure\User\Persistence\UserModel;

class EloquentDocenteRepository implements DocenteRepositoryInterface
{
    public function save(Docente $docente): Docente
    {
        $userModel = UserModel::updateOrCreate(
            ['id' => $docente->id()->value()],
            [
                'first_name'           => $docente->firstName(),
                'last_name'            => $docente->lastName(),
                'dni'                  => $docente->dni(),
                'password'             => $docente->password(),
                'username'             => $docente->username(),
                'role'                 => $docente->role()->value,
                'must_change_password' => $docente->mustChangePassword(),
            ]
        );

        $docenteModel = DocenteModel::updateOrCreate(
            ['id' => $userModel->id],
            [
                'telephone' => $docente->telephone(),
                'fecha_ingreso' => $docente->fechaIngreso()->toDatabase(),
                'curso_division_turno_id' => $docente->cursoDivisionTurno()
            ]
        );

        return $this->toDomain($userModel, $docenteModel);
    }

    public function findById(UserId $id): ?Docente
    {
        $userModel = UserModel::find($id->value());

        if (!$userModel) return null;

        $docenteModel = DocenteModel::where('id', $userModel->id)->first();

        return $docenteModel ? $this->toDomain($userModel, $docenteModel) : null;
    }

public function findByUsername(string $username): ?Docente
    {
        // username vive en users, no en docentes.
        $userModel = UserModel::where('username', $username)->first();

        if (! $userModel) return null;

        $docenteModel = DocenteModel::where('id', $userModel->id)->first();

        return $docenteModel ? $this->toDomain($userModel, $docenteModel) : null;
    }

    public function all(): array
    {
        return DocenteModel::with('user')
            ->get()
            ->map(fn(DocenteModel $model) => $this->toDomain($model->user, $model))
            ->toArray();
    }

    public function inhabilitar(UserId $id): void
    {
        $docenteModel = DocenteModel::find($id->value());

        if ($docenteModel) {
            $docenteModel->delete();
        }

        $userModel = UserModel::find($id->value());

        if ($userModel) {
            $userModel->delete();
        }
    }

    private function toDomain(UserModel $userModel, DocenteModel $docenteModel): Docente
    {
        return new Docente(
            new UserId($userModel->id),
            $userModel->first_name,
            $userModel->last_name,
            (int) $userModel->dni,
            $userModel->username,
            $userModel->password,
            $docenteModel->telephone,
            FechaFormateada::fromDatabase(
                $docenteModel->fecha_ingreso instanceof \DateTimeInterface
                    ? $docenteModel->fecha_ingreso->format('Y-m-d')
                    : $docenteModel->fecha_ingreso
            ),
            $docenteModel->curso_division_turno_id ? $docenteModel->curso_division_turno_id : null
        );
    }
}