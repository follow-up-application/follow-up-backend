<?php

namespace App\Application\Materia\UseCases;

use App\Application\Materia\DTOs\CreateMateriaDTO;
use App\Domain\User\Entities\Materia;
use App\Domain\User\Exceptions\CDTNoEncontradoException;
use App\Domain\User\Exceptions\DocenteNoEncontradoException;
use App\Domain\User\Exceptions\MateriaDuplicadaException;
use App\Domain\User\Repositories\CursoDivisionTurnoRepositoryInterface;
use App\Domain\User\Repositories\DocenteRepositoryInterface;
use App\Domain\User\Repositories\MateriaRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use Illuminate\Support\Facades\DB;

class CreateMateriaUseCase
{
    public function __construct(
        private readonly MateriaRepositoryInterface $materiaRepository,
        private readonly DocenteRepositoryInterface $docenteRepository,
        private readonly CursoDivisionTurnoRepositoryInterface $cursoDivisionTurnoRepository,
    ) {}

    public function execute(CreateMateriaDTO $dto): Materia
    {
        return DB::transaction(function () use ($dto) {
            $docente = $this->docenteRepository->findById(UserId::fromString($dto->docenteId));

            if ($docente === null) {
                throw new DocenteNoEncontradoException();
            }

            $cursoDivisionTurno = $this->cursoDivisionTurnoRepository->findById($dto->cursoDivisionTurnoId);

            if ($cursoDivisionTurno === null) {
                throw new CDTNoEncontradoException();
            }

            $yaRegistrada = collect($this->materiaRepository->findByCursoDivisionTurno($dto->cursoDivisionTurnoId))
                ->first(fn (Materia $materia) => $materia->nombre() === $dto->nombre);

            if ($yaRegistrada !== null) {
                throw new MateriaDuplicadaException();
            }

            $materia = new Materia(
                id: 0, // autoincremental, se resuelve en el repositorio al guardar
                nombre: $dto->nombre,
                descripcion: $dto->descripcion,
                cursoDivisionTurno: $cursoDivisionTurno,
                docente: $docente->id(),
            );

            // save() devuelve la materia ya persistida, con el id generado por la BD.
            return $this->materiaRepository->save($materia);
        });
    }
}