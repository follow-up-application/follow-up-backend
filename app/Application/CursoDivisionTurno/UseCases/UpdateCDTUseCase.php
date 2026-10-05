<?php

namespace App\Application\CursoDivisionTurno\UseCases;

use App\Application\CursoDivisionTurno\DTOs\UpdateCDTDTO;
use App\Domain\User\Entities\CursoDivisionTurno;
use App\Domain\User\Exceptions\CDTNoEncontradoException;
use App\Domain\User\Exceptions\CursoDivisionTurnoDuplicadoException;
use App\Domain\User\Repositories\CursoDivisionTurnoRepositoryInterface;
use App\Domain\User\ValueObjects\TurnoEscolar;

class UpdateCDTUseCase
{
    public function __construct(
        private readonly CursoDivisionTurnoRepositoryInterface $cursoDivisionTurnoRepository,
    ) {}

    public function execute(int $id, UpdateCDTDTO $dto): CursoDivisionTurno
    {
        $actual = $this->cursoDivisionTurnoRepository->findById($id);

        if ($actual === null) {
            throw new CDTNoEncontradoException("No se encontró el curso {$id}.");
        }

        $curso = $dto->curso ?? $actual->curso();
        $division = $dto->division ?? $actual->division();
        $turno = $dto->turno !== null ? TurnoEscolar::from($dto->turno) : $actual->turno();
        $capacidad = $dto->capacidad_maxima ?? $actual->capacidadMaxima();

        // El constructor valida año 1..7 y división A-Z: la request ya cubre
        // esos casos, pero el dominio sigue siendo la última línea de defensa.
        $actualizado = new CursoDivisionTurno(
            id: $actual->id(),
            curso: $curso,
            division: $division,
            turno: $turno,
            capacidad_maxima: $capacidad,
        );

        $duplicado = $this->cursoDivisionTurnoRepository->findByAttributes($curso, $division, $turno->value);

        if ($duplicado !== null && $duplicado->id() !== $actual->id()) {
            throw new CursoDivisionTurnoDuplicadoException($curso, $division, $turno->value);
        }

        // save() es un upsert sobre curso_division_turno.
        return $this->cursoDivisionTurnoRepository->save($actualizado);
    }
}