<?php

namespace App\Application\CursoDivisionTurno\UseCases;

use App\Application\CursoDivisionTurno\DTOs\CreateCDTdto;
use App\Domain\User\Entities\CursoDivisionTurno;
use App\Domain\User\Exceptions\CursoDivisionTurnoDuplicadoException;
use App\Domain\User\Repositories\CursoDivisionTurnoRepositoryInterface;
use App\Domain\User\ValueObjects\TurnoEscolar;
use Illuminate\Support\Facades\DB;

class CreateCDTUseCase
{
    public function __construct(
        private readonly CursoDivisionTurnoRepositoryInterface $cursoDivisionTurnoRepository,
    ) {}

    public function execute(CreateCDTdto $dto): CursoDivisionTurno
    {
        return DB::transaction(function () use ($dto) {
            $existente = $this->cursoDivisionTurnoRepository->findByAttributes(
                $dto->curso,
                $dto->division,
                $dto->turno,
            );

            if ($existente !== null) {
                throw new CursoDivisionTurnoDuplicadoException($dto->curso, $dto->division, $dto->turno);
            }

            $cdt = new CursoDivisionTurno(
                id: null,
                curso: $dto->curso,
                division: $dto->division,
                turno: TurnoEscolar::from($dto->turno),
                capacidad_maxima: $dto->capacidadMaxima,
            );

            $saved = $this->cursoDivisionTurnoRepository->save($cdt);

            return $saved;
        });
    }
}
