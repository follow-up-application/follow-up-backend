<?php

namespace App\Application\Materia\UseCases;

use App\Domain\User\Entities\Materia;
use App\Domain\User\Exceptions\DocenteNoEncontradoException;
use App\Domain\User\Repositories\DocenteRepositoryInterface;
use App\Domain\User\Repositories\MateriaRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

class GetMateriasByDocenteUseCase
{
    public function __construct(
        private readonly MateriaRepositoryInterface $materiaRepository,
        private readonly DocenteRepositoryInterface $docenteRepository,
    ) {}

    /**
     * Un docente es jefe de 0 a muchas materias.
     *
     * @return Materia[]
     */
    public function execute(UserId $docenteId): array
    {
        if ($this->docenteRepository->findById($docenteId) === null) {
            throw new DocenteNoEncontradoException();
        }

        return $this->materiaRepository->findByDocente($docenteId);
    }
}
