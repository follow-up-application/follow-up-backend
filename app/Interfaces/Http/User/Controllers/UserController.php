<?php

namespace App\Interfaces\Http\User\Controllers;

use App\Application\User\DTOs\CreateUserDTO;
use App\Application\User\DTOs\UpdateUserDTO;
use App\Application\User\UseCases\CreateUserUseCase;
use App\Application\User\UseCases\GetAllUsersUseCase;
use App\Application\User\UseCases\UpdateUserUseCase;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Interfaces\Http\Controller;
use App\Interfaces\Http\User\Requests\CreateUserRequest;
use App\Interfaces\Http\User\Requests\UpdateUserRequest;
use App\Interfaces\Http\User\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly CreateUserUseCase $createUserUseCase,
        private readonly GetAllUsersUseCase $getAllUsersUseCase,
        private readonly UpdateUserUseCase $updateUserUseCase,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function store(CreateUserRequest $request): JsonResponse
    {
        $dto = CreateUserDTO::fromArray($request->validated());
        $user = $this->createUserUseCase->execute($dto);

        return response()->json(new UserResource($user), 201);
    }

    public function index(): JsonResponse
    {
        $users = $this->getAllUsersUseCase->execute();

        return response()->json(UserResource::collection($users), 200);
    }

    public function show(string $id): JsonResponse
    {
        $user = $this->userRepository->findById(new UserId($id));

        if (! $user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        return response()->json(new UserResource($user));
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $dto = UpdateUserDTO::fromArray($request->validated());

        if ($dto->isEmpty()) {
            return response()->json(['message' => 'No se informó ningún campo para actualizar.'], 422);
        }

        $user = $this->updateUserUseCase->execute($id, $dto);

        return response()->json(new UserResource($user), 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $user = $this->userRepository->findById(new UserId($id));

        if ($user === null) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $this->userRepository->delete(new UserId($id));

        return response()->json('Usuario eliminado', 204);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        return response()->json(new UserResource($user));
    }
}
