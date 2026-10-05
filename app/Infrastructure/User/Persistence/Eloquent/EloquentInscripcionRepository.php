<?php

namespace App\Infrastructure\User\Persistence\Eloquent;

use App\Domain\Shared\ValueObjects\FechaFormateada;
use App\Domain\User\Entities\Inscripcion;
use App\Domain\User\Repositories\InscripcionRepositoryInterface;
use App\Domain\User\ValueObjects\EstadoInscripcion;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\User\Persistence\InscripcionModel;

class EloquentInscripcionRepository implements InscripcionRepositoryInterface
{
    public function save(Inscripcion $inscripcion): Inscripcion
    {
        $valores = [
            'alumno_id' => $inscripcion->alumnoId()->value(),
            'materia_id' => $inscripcion->materiaId(),
            'fecha_inscripcion' => $inscripcion->fechaInscripcion()->toDatabase(),
            'estado' => $inscripcion->estado()->value,
        ];

        if (! $inscripcion->id()) {
            $model = InscripcionModel::create($valores);
        } else {
            $model = InscripcionModel::updateOrCreate(
                ['id' => $inscripcion->id()],
                $valores,
            );
        }

        return $this->toDomain($model);
    }

    public function findById(int $id): ?Inscripcion
    {
        $model = InscripcionModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByAlumnoYMateria(UserId $alumnoId, int $materiaId): ?Inscripcion
    {
        $model = InscripcionModel::where('alumno_id', $alumnoId->value())
            ->where('materia_id', $materiaId)
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByAlumno(UserId $alumnoId, ?EstadoInscripcion $estado = null): array
    {
        $query = InscripcionModel::where('alumno_id', $alumnoId->value());

        if ($estado !== null) {
            $query->where('estado', $estado->value);
        }

        return $query->get()
            ->map(fn (InscripcionModel $model) => $this->toDomain($model))
            ->toArray();
    }

    public function findByMateria(int $materiaId, ?EstadoInscripcion $estado = null): array
    {
        $query = InscripcionModel::where('materia_id', $materiaId);

        if ($estado !== null) {
            $query->where('estado', $estado->value);
        }

        return $query->get()
            ->map(fn (InscripcionModel $model) => $this->toDomain($model))
            ->toArray();
    }

    public function all(): array
    {
        return InscripcionModel::all()
            ->map(fn (InscripcionModel $model) => $this->toDomain($model))
            ->toArray();
    }

    public function destroy(int $id): void
    {
        InscripcionModel::destroy($id);
    }

    private function toDomain(InscripcionModel $model): Inscripcion
    {
        return new Inscripcion(
            id: $model->id,
            alumnoId: new UserId($model->alumno_id),
            materiaId: (int) $model->materia_id,
            fechaInscripcion: FechaFormateada::fromDatabase(
                $model->fecha_inscripcion instanceof \DateTimeInterface
                    ? $model->fecha_inscripcion->format('Y-m-d')
                    : $model->fecha_inscripcion
            ),
            estado: $model->estado instanceof EstadoInscripcion
                ? $model->estado
                : EstadoInscripcion::from($model->estado),
        );
    }
}
