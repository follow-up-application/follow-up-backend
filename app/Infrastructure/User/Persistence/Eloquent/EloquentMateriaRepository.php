<?php

namespace App\Infrastructure\User\Persistence\Eloquent;

use App\Domain\User\Entities\Materia;
use App\Domain\User\Repositories\CursoDivisionTurnoRepositoryInterface;
use App\Domain\User\Repositories\DocenteRepositoryInterface;
use App\Domain\User\Repositories\MateriaRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\User\Persistence\MateriaModel;

class EloquentMateriaRepository implements MateriaRepositoryInterface
{
    public function __construct(
        private readonly DocenteRepositoryInterface $docenteRepository,
        private readonly CursoDivisionTurnoRepositoryInterface $cursoDivisionTurnoRepository,
    ) {}

    public function save(Materia $materia): Materia
    {
        $valores = [
            'nombre' => $materia->nombre(),
            'descripcion' => $materia->descripcion(),
            'curso_division_turno_id' => $materia->cursoDivisionTurno()->id(),
            'docente_id' => $materia->docente()->value(),
        ];

        if (! $materia->id()) {
            $model = MateriaModel::create($valores);
        } else {
            $model = MateriaModel::updateOrCreate(
                ['id' => $materia->id()],
                $valores,
            );
        }

        return $this->toDomain($model);
    }

    public function findById(int $id): ?Materia
    {
        $model = MateriaModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByNombre(string $nombre): ?Materia
    {
        $model = MateriaModel::where('nombre', $nombre)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByDocente(UserId $docenteId): array
    {
        return MateriaModel::where('docente_id', $docenteId->value())
            ->get()
            ->map(fn (MateriaModel $model) => $this->toDomain($model))
            ->toArray();
    }

    public function findByCursoDivisionTurno(int $cursoDivisionTurnoId): array
    {
        return MateriaModel::where('curso_division_turno_id', $cursoDivisionTurnoId)
            ->get()
            ->map(fn (MateriaModel $model) => $this->toDomain($model))
            ->toArray();
    }

    public function all(): array
    {
        return MateriaModel::all()
            ->map(fn (MateriaModel $model) => $this->toDomain($model))
            ->toArray();
    }

    public function inhabilitar(int $id): void
    {
        $model = MateriaModel::find($id);

        if ($model) {
            $model->delete();
        }
    }

    private function toDomain(MateriaModel $model): Materia
    {
        $docente = $this->docenteRepository->findById(new UserId($model->docente_id));

        if ($docente === null) {
            throw new \RuntimeException(
                "Materia {$model->id} referencia un docente inexistente (integridad referencial rota)."
            );
        }

        $cursoDivisionTurno = $this->cursoDivisionTurnoRepository->findById($model->curso_division_turno_id);

        if ($cursoDivisionTurno === null) {
            throw new \RuntimeException(
                "Materia {$model->id} referencia un curso división turno inexistente (integridad referencial rota)."
            );
        }

        return new Materia(
            id: $model->id,
            nombre: $model->nombre,
            descripcion: $model->descripcion,
            cursoDivisionTurno: $cursoDivisionTurno,
            docente: $docente->id(),
        );
    }
}
