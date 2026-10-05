<?php

namespace App\Application\Inscripcion\UseCases;

use App\Application\Inscripcion\DTOs\CambiarEstadoInscripcionDTO;
use App\Domain\User\Entities\Inscripcion;
use App\Domain\User\Exceptions\InscripcionNoEncontradaException;
use App\Domain\User\Repositories\InscripcionRepositoryInterface;
use App\Domain\User\ValueObjects\EstadoInscripcion;

class CambiarEstadoInscripcionUseCase
{
    public function __construct(
        private readonly InscripcionRepositoryInterface $inscripcionRepository,
    ) {}

    public function execute(CambiarEstadoInscripcionDTO $dto): Inscripcion
    {
        $inscripcion = $this->inscripcionRepository->findById($dto->inscripcionId);

        if ($inscripcion === null) {
            throw new InscripcionNoEncontradaException();
        }

        $inscripcion->cambiarEstado(EstadoInscripcion::from($dto->estado));

        return $this->inscripcionRepository->save($inscripcion);
    }
}
