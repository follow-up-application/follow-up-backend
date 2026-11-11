<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\Docente;
use App\Domain\User\ValueObjects\UserId;

interface DocenteRepositoryInterface
{   
    public function save(Docente $user): Docente;
    public function findById(UserId $id): ?Docente;
    public function findByUsername(string $username): ?Docente;
    public function all(): array;
    public function inhabilitar(UserId $id): void;
}