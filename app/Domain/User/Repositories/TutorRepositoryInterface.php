<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\Tutor;
use App\Domain\User\ValueObjects\UserId;

interface TutorRepositoryInterface
{   
    public function save(Tutor $user): Tutor;
    public function findById(UserId $id): ?Tutor;
    public function findByDni(int $dni): ?Tutor;
    public function findByUsername(string $username): ?Tutor;
    public function all(): array;
    public function inhabilitar(UserId $id): void;
    public function totalTutores(): int;
}