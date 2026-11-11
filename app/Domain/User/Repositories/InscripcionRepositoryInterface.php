<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\Inscripcion;
use App\Domain\User\ValueObjects\EstadoInscripcion;
use App\Domain\User\ValueObjects\UserId;

interface InscripcionRepositoryInterface
{
    public function save(Inscripcion $inscripcion): Inscripcion;

    public function findById(int $id): ?Inscripcion;

    /**
     * Busca el vinculo alumno + materia. Se usa para evitar inscripciones duplicadas.
     */
    public function findByAlumnoYMateria(UserId $alumnoId, int $materiaId): ?Inscripcion;

    /** @return Inscripcion[] */
    public function findByAlumno(UserId $alumnoId, ?EstadoInscripcion $estado = null): array;

    /** @return Inscripcion[] */
    public function findByMateria(int $materiaId, ?EstadoInscripcion $estado = null): array;

    /** @return Inscripcion[] */
    public function all(): array;

    public function inhabilitar(int $id): void;
}
