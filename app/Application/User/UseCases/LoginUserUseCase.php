<?php

namespace App\Application\User\UseCases;

use App\Application\User\DTOs\LoginUserDTO;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\UserEmail;
use App\Domain\User\ValueObjects\UserRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use \App\Infrastructure\User\Persistence\UserModel;

class LoginUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function execute(LoginUserDTO $dto): array
    {
        $user = $this->userRepository->findByUsername($dto->username);

        if (!$user) {
            $trashedModel = UserModel::onlyTrashed()->where('username', $dto->username)->first();
            if ($trashedModel && Hash::check($dto->password, $trashedModel->password)) {
                throw ValidationException::withMessages([
                    'username' => ['Tu cuenta se encuentra inhabilitada.'],
                ]);
            }

            throw ValidationException::withMessages([
                'username' => ['Las credenciales son incorrectas.'],
            ]);
        }

        if (!Hash::check($dto->password, $user->password())) {
            throw ValidationException::withMessages([
                'username' => ['Las credenciales son incorrectas.'],
            ]);
        }

        $model = UserModel::find($user->id()->value()); // ajustar accessor según tu UserId VO
        $model->tokens()->delete();
        $token = $model->createToken('api-token')->plainTextToken;

        return [
            'token' => $token,
            'rol'   => $user->role()->value,
            'first_name' => $user->firstName(),
            'last_name' => $user->lastName(),
            'username' => $user->username(),
        ];
    }
}