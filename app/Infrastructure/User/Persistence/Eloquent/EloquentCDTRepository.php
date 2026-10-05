<?php

namespace App\Infrastructure\User\Persistence\Eloquent;

use App\Domain\User\Entities\CursoDivisionTurno;
use App\Domain\User\Repositories\CursoDivisionTurnoRepositoryInterface;
use App\Domain\User\ValueObjects\TurnoEscolar;
use App\Infrastructure\User\Persistence\CursoDivisionTurnoModel;

class EloquentCDTRepository implements CursoDivisionTurnoRepositoryInterface
{
    public function save(CursoDivisionTurno $cursoDivisionTurno): CursoDivisionTurno
    {
        $valores = [
            'curso' => $cursoDivisionTurno->curso(),
            'division' => $cursoDivisionTurno->division(),
            'turno' => $cursoDivisionTurno->turno()->value,
            'capacidad_maxima' => $cursoDivisionTurno->capacidadMaxima(),
        ];

        if ($cursoDivisionTurno->id() === null) {
            $cdtModel = CursoDivisionTurnoModel::create($valores);
        } else {
            $cdtModel = CursoDivisionTurnoModel::updateOrCreate(
                ['id' => $cursoDivisionTurno->id()],
                $valores,
            );
        }

        return $this->toDomain($cdtModel);
    }

    public function findById(int $id): ?CursoDivisionTurno
    {
        $cdtModel = CursoDivisionTurnoModel::find($id);

        return $cdtModel ? $this->toDomain($cdtModel) : null;
    }

    public function findByName(string $name): ?CursoDivisionTurno
    {
        $cdtModel = CursoDivisionTurnoModel::where('anio', $name)->first();

        return $cdtModel ? $this->toDomain($cdtModel) : null;
    }

    public function all(): array
    {
        return CursoDivisionTurnoModel::all()
            ->map(fn (CursoDivisionTurnoModel $model) => $this->toDomain($model))
            ->toArray();
    }

    public function destroy(int $id): void
    {
        // cascadeOnDelete en la migración se encarga de borrar alumnos también
        CursoDivisionTurnoModel::destroy($id);
    }

    private function toDomain(CursoDivisionTurnoModel $cdtModel): CursoDivisionTurno
    {
        return new CursoDivisionTurno(
            id: $cdtModel->id,
            curso: $cdtModel->curso,
            division: $cdtModel->division,
            turno: TurnoEscolar::from($cdtModel->turno)
        );
    }

    public function findByAttributes(int $curso, string $division, string $turno): ?CursoDivisionTurno
    {
        $cdtModel = CursoDivisionTurnoModel::where('curso', $curso)
            ->where('division', ctype_upper($division))
            ->where('turno', ctype_upper($turno))
            ->first();

        return $cdtModel ? $this->toDomain($cdtModel) : null;
    }
}
