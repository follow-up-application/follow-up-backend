<?php

namespace App\Application\Materia\UseCases;

use App\Application\Materia\DTOs\UpdateMateriaDTO;
use App\Domain\User\Entities\Materia;
use App\Domain\User\Exceptions\CDTNoEncontradoException;
use App\Domain\User\Exceptions\DocenteNoEncontradoException;
use App\Domain\User\Exceptions\MateriaNoEncontradaException;
use App\Domain\User\Repositories\CursoDivisionTurnoRepositoryInterface;
use App\Domain\User\Repositories\DocenteRepositoryInterface;
use App\Domain\User\Repositories\MateriaRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

class UpdateMateriaUseCase
{
    public function __construct(
        private readonly MateriaRepositoryInterface $materiaRepository,
        private readonly DocenteRepositoryInterface $docenteRepository,
        private readonly CursoDivisionTurnoRepositoryInterface $cursoDivisionTurnoRepository,
    ) {}

    public function execute(int $id, UpdateMateriaDTO $dto): Materia
    {
        $materia = $this->materiaRepository->findById($id);

        if ($materia === null) {
            throw new MateriaNoEncontradaException("No se encontró la materia {$id}.");
        }

        if ($dto->nombre !== null) {
            $materia->setNombre($dto->nombre);
        }

        if ($dto->descripcion !== null) {
            $materia->setDescripcion($dto->descripcion);
        }

        if ($dto->curso_division_turno_id !== null) {
            $cdt = $this->cursoDivisionTurnoRepository->findById($dto->curso_division_turno_id);

            if ($cdt === null) {
                throw new CDTNoEncontradoException("No se encontró el curso {$dto->curso_division_turno_id}.");
            }

            $materia->setCursoDivisionTurno($cdt);
        }

        if ($dto->docente_id !== null) {
            $docente = $this->docenteRepository->findById(UserId::fromString((string) $dto->docente_id));

            if ($docente === null) {
                throw new DocenteNoEncontradoException("No se encontró el docente {$dto->docente_id}.");
            }

            $materia->setDocente($docente->id());
        }

        return $this->materiaRepository->save($materia);
    }
}