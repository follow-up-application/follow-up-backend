<?php

namespace App\Application\Inscripcion\UseCases;

use App\Domain\User\Entities\Inscripcion;
use App\Domain\User\Exceptions\MateriaNoEncontradaException;
use App\Domain\User\Repositories\InscripcionRepositoryInterface;
use App\Domain\User\Repositories\MateriaRepositoryInterface;
use App\Domain\User\ValueObjects\EstadoInscripcion;

class GetInscripcionesByMateriaUseCase
{
    public function __construct(
        private readonly InscripcionRepositoryInterface $inscripcionRepository,
        private readonly MateriaRepositoryInterface $materiaRepository,
    ) {}

    /**
     * @return Inscripcion[]
     */
    public function execute(int $materiaId, ?EstadoInscripcion $estado = null): array
    {
        if ($this->materiaRepository->findById($materiaId) === null) {
            throw new MateriaNoEncontradaException();
        }

        return $this->inscripcionRepository->findByMateria($materiaId, $estado);
    }
}
