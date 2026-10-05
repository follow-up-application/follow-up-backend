<?php

namespace App\Application\Materia\UseCases;

use App\Domain\User\Entities\Materia;
use App\Domain\User\Exceptions\CDTNoEncontradoException;
use App\Domain\User\Repositories\CursoDivisionTurnoRepositoryInterface;
use App\Domain\User\Repositories\MateriaRepositoryInterface;

class GetMateriasByCursoDivisionTurnoUseCase
{
    public function __construct(
        private readonly MateriaRepositoryInterface $materiaRepository,
        private readonly CursoDivisionTurnoRepositoryInterface $cursoDivisionTurnoRepository,
    ) {}

    /**
     * Un curso division turno tiene 0 a muchas materias vinculadas.
     *
     * @return Materia[]
     */
    public function execute(int $cursoDivisionTurnoId): array
    {
        if ($this->cursoDivisionTurnoRepository->findById($cursoDivisionTurnoId) === null) {
            throw new CDTNoEncontradoException();
        }

        return $this->materiaRepository->findByCursoDivisionTurno($cursoDivisionTurnoId);
    }
}
