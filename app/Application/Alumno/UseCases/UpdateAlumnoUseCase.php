<?php

namespace App\Application\Alumno\UseCases;

use App\Application\Alumno\DTOs\UpdateAlumnoDTO;
use App\Domain\Shared\ValueObjects\FechaFormateada;
use App\Domain\User\Entities\Alumno;
use App\Domain\User\Exceptions\AlumnoNoEncontradoException;
use App\Domain\User\Exceptions\CDTNoEncontradoException;
use App\Domain\User\Repositories\AlumnoRepositoryInterface;
use App\Domain\User\Repositories\CursoDivisionTurnoRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

class UpdateAlumnoUseCase
{
    public function __construct(
        private readonly AlumnoRepositoryInterface $alumnoRepository,
        private readonly CursoDivisionTurnoRepositoryInterface $cursoDivisionTurnoRepository,
    ) {}

    public function execute(string $id, UpdateAlumnoDTO $dto): Alumno
    {
        $userId = new UserId($id);

        $alumno = $this->alumnoRepository->findById($userId);

        if ($alumno === null) {
            throw new AlumnoNoEncontradoException("No se encontró el alumno {$id}.");
        }

        if ($dto->first_name !== null || $dto->last_name !== null || $dto->dni !== null) {
            $alumno->updateUser(
                $dto->first_name ?? $alumno->firstName(),
                $dto->last_name ?? $alumno->lastName(),
                $dto->dni ?? $alumno->dni(),
            );
        }

        if ($dto->fecha_nacimiento !== null) {
            $alumno->setFechaNacimiento(new FechaFormateada($dto->fecha_nacimiento));
        }

        if ($dto->curso_division_turno_id !== null) {
            $cdt = $this->cursoDivisionTurnoRepository->findById($dto->curso_division_turno_id);

            if ($cdt === null) {
                throw new CDTNoEncontradoException("No se encontró el curso {$dto->curso_division_turno_id}.");
            }

            $alumno->setCursoDivisionTurno($cdt);
        }

        // save() es un upsert: actualiza el users y el alumnos existentes.
        return $this->alumnoRepository->save($alumno);
    }
}