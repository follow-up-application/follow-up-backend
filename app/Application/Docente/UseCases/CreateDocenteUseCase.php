<?php

namespace App\Application\Docente\UseCases;

use App\Application\Docente\DTOs\CreateDocenteDTO;
use App\Domain\Shared\ValueObjects\FechaFormateada;
use App\Domain\User\Entities\Docente;
use App\Domain\User\Exceptions\CDTNoEncontradoException;
use App\Domain\User\Repositories\CursoDivisionTurnoRepositoryInterface;
use App\Domain\User\Repositories\DocenteRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\UserName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateDocenteUseCase
{
    public function __construct(
        private readonly DocenteRepositoryInterface $docenteRepository,
        private readonly CursoDivisionTurnoRepositoryInterface $cursoDivisionTurnoRepository,
    ) {}

    public function execute(CreateDocenteDTO $dto): Docente
    {
        return DB::transaction(function () use ($dto) {
            $cdt = $this->cursoDivisionTurnoRepository->findById($dto->cursoDivisionTurnoId);

            if ($cdt === null) {
                throw new CDTNoEncontradoException($dto->cursoDivisionTurnoId);
            }

            $fechaIngreso = new FechaFormateada($dto->fechaIngreso);
            $anio = (int) $fechaIngreso->toYears();

            $docente = new Docente(
                id: UserId::generate(),
                first_name: $dto->firstName,
                last_name: $dto->lastName,
                dni: $dto->dni,
                username: new UserName('DOC', (string) $dto->dni, strval($anio))->value(),
                password: Hash::make((string) $dto->dni),
                telephone: $dto->telephone,
                fecha_ingreso: $fechaIngreso,
                curso_division_turno_id: $cdt->id(),
            );

            $this->docenteRepository->save($docente);

            return $docente;
        });
    }
}
