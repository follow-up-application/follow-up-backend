<?php

namespace App\Interfaces\Http\User\Controllers;

use App\Application\User\DTOs\LoginUserDTO;
use App\Application\User\UseCases\ChangePasswordUseCase;
use App\Application\User\UseCases\LoginUserUseCase;
use App\Domain\User\ValueObjects\UserId;
use App\Interfaces\Http\Controller;
use App\Interfaces\Http\User\Requests\ChangePasswordRequest;
use App\Interfaces\Http\User\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly LoginUserUseCase $loginUserUseCase,
        private readonly ChangePasswordUseCase $changePasswordUseCase
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $dto    = LoginUserDTO::fromArray($request->validated());
        $result = $this->loginUserUseCase->execute($dto);

        return response()->json($result, 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.'], 200);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->changePasswordUseCase->execute(
            new UserId($request->user()->id),
            (string) $request->validated()['password'],
        );

        return response()->json(['message' => 'Contraseña actualizada correctamente.'], 200);
    }
}