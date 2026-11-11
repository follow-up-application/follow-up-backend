<?php

namespace App\Infrastructure\User\Persistence\Eloquent;

use App\Domain\User\Entities\TutorAlumno;
use App\Domain\User\Repositories\TutorAlumnoRepositoryInterface;
use App\Domain\User\ValueObjects\CodigoInstitucional;
use App\Domain\User\ValueObjects\TutorAlumnoRelation;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\User\Persistence\TutorAlumnoModel;

class EloquentTutorAlumnoRepository implements TutorAlumnoRepositoryInterface
{
    public function save(TutorAlumno $tutorAlumno): void
    {
        TutorAlumnoModel::updateOrCreate(
            ['id' => $tutorAlumno->id()->value()],
            [
                'alumno_id' => $tutorAlumno->alumnoId()->value(),
                'tutor_id' => $tutorAlumno->tutorId()->value(),
                'relationship' => $tutorAlumno->relationship()->value,
                'otra_relacion' => $tutorAlumno->otraRelacion(),
                'codigo_institucional' => $tutorAlumno->codigoInstitucional()->value(),
            ]
        );
    }

    public function findById(UserId $id): ?TutorAlumno
    {
        $model = TutorAlumnoModel::find($id->value());

        return $model ? $this->toDomain($model) : null;
    }

    public function findPreRegistroPendiente(string $codigoInstitucional): ?TutorAlumno
    {
        $model = TutorAlumnoModel::query()
            ->where('codigo_institucional', $codigoInstitucional)
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByAlumnoId(UserId $alumnoId): array
    {
        return TutorAlumnoModel::where('alumno_id', $alumnoId->value())
            ->get()
            ->map(fn (TutorAlumnoModel $m) => $this->toDomain($m))
            ->toArray();
    }

    public function findByTutorId(UserId $tutorId): array
    {
        return TutorAlumnoModel::where('tutor_id', $tutorId->value())
            ->get()
            ->map(fn (TutorAlumnoModel $m) => $this->toDomain($m))
            ->toArray();
    }

    public function inhabilitar(UserId $id): void
    {
        $model = TutorAlumnoModel::find($id->value());

        if ($model) {
            $model->delete();
        }
    }

    private function toDomain(TutorAlumnoModel $model): TutorAlumno
    {
        return new TutorAlumno(
            id: new UserId($model->id),
            alumno_id: new UserId($model->alumno_id),
            tutor_id: new UserId($model->tutor_id),
            relationship: TutorAlumnoRelation::from($model->relationship),
            otra_relacion: $model->otra_relacion,
            codigo_institucional: CodigoInstitucional::fromString($model->codigo_institucional),
        );
    }
}
