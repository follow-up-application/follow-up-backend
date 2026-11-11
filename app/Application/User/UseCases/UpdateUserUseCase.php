<?php

namespace App\Application\User\UseCases;

use App\Application\User\DTOs\UpdateUserDTO;
use App\Domain\User\Entities\User;
use App\Domain\User\Exceptions\UserNoEncontradoException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

class UpdateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function execute(string $id, UpdateUserDTO $dto): User
    {
        $user = $this->userRepository->findById(new UserId($id));

        if ($user === null) {
            throw new UserNoEncontradoException("No se encontró el usuario {$id}.");
        }

        if ($dto->first_name !== null || $dto->last_name !== null || $dto->dni !== null) {
            $user->updateUser(
                $dto->first_name ?? $user->firstName(),
                $dto->last_name ?? $user->lastName(),
                $dto->dni ?? $user->dni(),
            );
        }

        if ($dto->username !== null) {
            $user->updateUsername($dto->username);
        }

        return $this->userRepository->save($user);
    }
}
