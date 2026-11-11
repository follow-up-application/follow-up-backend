<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\User;
use App\Domain\User\ValueObjects\UserEmail;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\UserRole;

interface UserRepositoryInterface
{   
    public function save(User $user): User;
    public function findById(UserId $id): ?User;
    public function findByUsername(string $username): ?User;
    public function all(): array;
    public function inhabilitar(UserId $id): void;
}