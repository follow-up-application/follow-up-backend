<?php

namespace App\Application\Inscripcion\UseCases;

use App\Domain\User\Exceptions\InscripcionNoEncontradaException;
use App\Domain\User\Repositories\InscripcionRepositoryInterface;

class DeleteInscripcionUseCase
{
    public function __construct(
        private readonly InscripcionRepositoryInterface $inscripcionRepository
    ) {}

    public function execute(int $id): void
    {
        if ($this->inscripcionRepository->findById($id) === null) {
            throw new InscripcionNoEncontradaException();
        }

        $this->inscripcionRepository->destroy($id);
    }
}
