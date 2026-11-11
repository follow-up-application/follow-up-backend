<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\Alumno;
use App\Domain\User\ValueObjects\UserId;

interface AlumnoRepositoryInterface
{   
    public function save(Alumno $user): Alumno;
    public function findById(UserId $id): ?Alumno;
    public function findByUsername(string $username): ?Alumno;
    public function all(): array;
    public function destroy(UserId $id): void;
    public function totalAlumnos(): int;
}