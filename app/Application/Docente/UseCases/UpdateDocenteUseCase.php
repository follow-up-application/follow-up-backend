<?php

namespace App\Application\Docente\UseCases;

use App\Application\Docente\DTOs\UpdateDocenteDTO;
use App\Domain\User\Entities\Docente;
use App\Domain\User\Exceptions\CDTNoEncontradoException;
use App\Domain\User\Exceptions\DocenteNoEncontradoException;
use App\Domain\User\Repositories\CursoDivisionTurnoRepositoryInterface;
use App\Domain\User\Repositories\DocenteRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

class UpdateDocenteUseCase
{
    public function __construct(
        private readonly DocenteRepositoryInterface $docenteRepository,
        private readonly CursoDivisionTurnoRepositoryInterface $cursoDivisionTurnoRepository,
    ) {}

    public function execute(string $id, UpdateDocenteDTO $dto): Docente
    {
        $docente = $this->docenteRepository->findById(new UserId($id));

        if ($docente === null) {
            throw new DocenteNoEncontradoException("No se encontró el docente {$id}.");
        }

        if ($dto->first_name !== null || $dto->last_name !== null || $dto->dni !== null) {
            $docente->updateUser(
                $dto->first_name ?? $docente->firstName(),
                $dto->last_name ?? $docente->lastName(),
                $dto->dni ?? $docente->dni(),
            );
        }

        if ($dto->telephone !== null) {
            $docente->setTelephone($dto->telephone);
        }

        if ($dto->fecha_ingreso !== null) {
            $docente->setFechaIngreso($dto->fecha_ingreso);
        }

        if ($dto->curso_division_turno_id !== null) {
            $cdt = $this->cursoDivisionTurnoRepository->findById($dto->curso_division_turno_id);

            if ($cdt === null) {
                throw new CDTNoEncontradoException("No se encontró el curso {$dto->curso_division_turno_id}.");
            }

            $docente->setCursoDivisionTurno($cdt->id());
        }

        return $this->docenteRepository->save($docente);
    }
}