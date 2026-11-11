<?php

namespace App\Application\Inscripcion\UseCases;

use App\Application\Inscripcion\DTOs\CreateInscripcionDTO;
use App\Domain\Shared\ValueObjects\FechaFormateada;
use App\Domain\User\Entities\Inscripcion;
use App\Domain\User\Exceptions\AlumnoNoEncontradoException;
use App\Domain\User\Exceptions\AlumnoYaInscriptoException;
use App\Domain\User\Exceptions\MateriaNoEncontradaException;
use App\Domain\User\Repositories\AlumnoRepositoryInterface;
use App\Domain\User\Repositories\InscripcionRepositoryInterface;
use App\Domain\User\Repositories\MateriaRepositoryInterface;
use App\Domain\User\ValueObjects\EstadoInscripcion;
use App\Domain\User\ValueObjects\UserId;
use Illuminate\Support\Facades\DB;

class CreateInscripcionUseCase
{
    public function __construct(
        private readonly InscripcionRepositoryInterface $inscripcionRepository,
        private readonly MateriaRepositoryInterface $materiaRepository,
        private readonly AlumnoRepositoryInterface $alumnoRepository,
    ) {}

    public function execute(CreateInscripcionDTO $dto): Inscripcion
    {
        return DB::transaction(function () use ($dto) {
            $alumnoId = new UserId($dto->alumnoId);

            if ($this->alumnoRepository->findById($alumnoId) === null) {
                throw new AlumnoNoEncontradoException();
            }

            if ($this->materiaRepository->findById($dto->materiaId) === null) {
                throw new MateriaNoEncontradaException();
            }

            if ($this->inscripcionRepository->findByAlumnoYMateria($alumnoId, $dto->materiaId) !== null) {
                throw new AlumnoYaInscriptoException();
            }

            $inscripcion = new Inscripcion(
                id: 0, // autoincremental, se resuelve en el repositorio al guardar
                alumnoId: $alumnoId,
                materiaId: $dto->materiaId,
                fechaInscripcion: FechaFormateada::fromString($dto->fechaInscripcion ?? date('d/m/Y')),
                estado: EstadoInscripcion::PENDIENTE,
            );

            return $this->inscripcionRepository->save($inscripcion);
        });
    }
}
