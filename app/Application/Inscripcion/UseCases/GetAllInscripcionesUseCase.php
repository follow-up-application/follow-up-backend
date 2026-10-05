<?php

namespace App\Application\Inscripcion\UseCases;

use App\Domain\User\Entities\Inscripcion;
use App\Domain\User\Repositories\InscripcionRepositoryInterface;

class GetAllInscripcionesUseCase
{
    public function __construct(
        private readonly InscripcionRepositoryInterface $inscripcionRepository
    ) {}

    /**
     * @return Inscripcion[]
     */
    public function execute(): array
    {
        return $this->inscripcionRepository->all();
    }
}
