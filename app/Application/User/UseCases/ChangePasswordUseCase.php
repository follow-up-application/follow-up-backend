<?php

namespace App\Application\User\UseCases;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use Illuminate\Support\Facades\Hash;

class ChangePasswordUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function execute(UserId $id, string $plainPassword): void
    {
        $user = $this->userRepository->findById($id);

        if ($user === null) {
            throw new \RuntimeException('El usuario autenticado no existe.');
        }

        // El cambio es explícito: la bandera de "debe cambiarla" ya no aplica.
        $user->updatePassword(Hash::make($plainPassword), false);

        $this->userRepository->save($user);
    }
}