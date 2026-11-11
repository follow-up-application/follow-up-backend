<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\Materia;
use App\Domain\User\ValueObjects\UserId;

interface MateriaRepositoryInterface
{
    public function save(Materia $materia): Materia;
    public function findById(int $id): ?Materia;
    public function findByNombre(string $nombre): ?Materia;

    /**
     * Un docente es jefe de 0 a muchas materias.
     *
     * @return Materia[]
     */
    public function findByDocente(UserId $docenteId): array;

    /**
     * Un curso division turno tiene 0 a muchas materias vinculadas.
     *
     * @return Materia[]
     */
    public function findByCursoDivisionTurno(int $cursoDivisionTurnoId): array;

    /** @return Materia[] */
    public function all(): array;
    public function inhabilitar(int $id): void;
}
