<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\CursoDivisionTurno;

interface CursoDivisionTurnoRepositoryInterface
{   
    public function save(CursoDivisionTurno $user): CursoDivisionTurno;
    public function findById(int $id): ?CursoDivisionTurno;
    public function findByName(string $name): ?CursoDivisionTurno;
    public function all(): array;
    public function inhabilitar(int $id): void;
    public function findByAttributes(int $curso, string $division, string $turno): ?CursoDivisionTurno;
}