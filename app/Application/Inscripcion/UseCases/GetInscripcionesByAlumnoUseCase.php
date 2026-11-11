<?php

namespace App\Application\Inscripcion\UseCases;

use App\Domain\User\Entities\Inscripcion;
use App\Domain\User\Exceptions\AlumnoNoEncontradoException;
use App\Domain\User\Repositories\AlumnoRepositoryInterface;
use App\Domain\User\Repositories\InscripcionRepositoryInterface;
use App\Domain\User\ValueObjects\EstadoInscripcion;
use App\Domain\User\ValueObjects\UserId;

class GetInscripcionesByAlumnoUseCase
{
    public function __construct(
        private readonly InscripcionRepositoryInterface $inscripcionRepository,
        private readonly AlumnoRepositoryInterface $alumnoRepository,
    ) {}

    /**
     * @return Inscripcion[]
     */
    public function execute(UserId $alumnoId, ?EstadoInscripcion $estado = null): array
    {
        if ($this->alumnoRepository->findById($alumnoId) === null) {
            throw new AlumnoNoEncontradoException();
        }

        return $this->inscripcionRepository->findByAlumno($alumnoId, $estado);
    }
}
