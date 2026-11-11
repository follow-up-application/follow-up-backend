<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\Admin;
use App\Domain\User\ValueObjects\UserId;

interface AdminRepositoryInterface
{   
    public function save(Admin $user): Admin;
    public function findById(UserId $id): ?Admin;
    public function findByUsername(string $username): ?Admin;
    public function all(): array;
    public function inhabilitar(UserId $id): void;
}