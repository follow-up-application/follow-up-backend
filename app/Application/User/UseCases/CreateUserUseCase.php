<?php
namespace App\Application\User\UseCases;

use App\Domain\User\Entities\User as UserEntity;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Application\User\DTOs\CreateUserDTO;
use App\Domain\User\ValueObjects\UserRole;


class CreateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function execute(CreateUserDTO $dto): UserEntity
    {
        // La entidad User no tiene telephone ni address: la tabla users sólo
        // guarda id, nombres, dni, username, password, role y
        // must_change_password. Pasarlos como named arguments rompía el alta
        // con "Unknown named parameter $telephone".
        $user = new UserEntity(
            id: UserId::generate(),
            first_name: $dto->first_name,
            last_name: $dto->last_name,
            dni: $dto->dni,
            username: $dto->username,
            password: bcrypt($dto->password),
            role: UserRole::from(strtolower($dto->role)),
            must_change_password: false,
        );

        return $this->userRepository->save($user);
    }
}