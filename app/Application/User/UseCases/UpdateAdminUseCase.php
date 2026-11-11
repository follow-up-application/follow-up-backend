<?php

namespace App\Application\User\UseCases;

use App\Application\User\DTOs\UpdateAdminDTO;
use App\Domain\User\Entities\Admin;
use App\Domain\User\Exceptions\AdminNoEncontradoException;
use App\Domain\User\Repositories\AdminRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

class UpdateAdminUseCase
{
    public function __construct(
        private readonly AdminRepositoryInterface $adminRepository,
    ) {}

    public function execute(string $id, UpdateAdminDTO $dto): Admin
    {
        $admin = $this->adminRepository->findById(new UserId($id));

        if ($admin === null) {
            throw new AdminNoEncontradoException("No se encontró el administrador {$id}.");
        }

        if ($dto->first_name !== null || $dto->last_name !== null) {
            $admin->updateUser(
                $dto->first_name ?? $admin->firstName(),
                $dto->last_name ?? $admin->lastName(),
                $admin->dni(),
            );
        }

        if ($dto->username !== null) {
            $admin->setUsername($dto->username);
        }

        return $this->adminRepository->save($admin);
    }
}