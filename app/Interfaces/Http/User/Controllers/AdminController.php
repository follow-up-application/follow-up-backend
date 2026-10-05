<?php

namespace App\Interfaces\Http\User\Controllers;

use App\Application\Admin\DTOs\CreateAdminDTO;
use App\Application\Admin\UseCases\CreateAdminUseCase;
use App\Application\Admin\UseCases\GetAllAdminsUseCase;
use App\Application\User\DTOs\UpdateAdminDTO;
use App\Application\User\UseCases\UpdateAdminUseCase;
use App\Domain\User\Repositories\AdminRepositoryInterface;
use App\Domain\User\Repositories\AlumnoRepositoryInterface;
use App\Domain\User\Repositories\TutorRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Interfaces\Http\Controller;
use App\Interfaces\Http\User\Requests\CreateAdminRequest;
use App\Interfaces\Http\User\Requests\UpdateAdminRequest;
use App\Interfaces\Http\User\Resources\AdminResource;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function __construct(
        private readonly CreateAdminUseCase  $createAdminUseCase,
        private readonly GetAllAdminsUseCase $getAllAdminsUseCase,
        private readonly UpdateAdminUseCase  $updateAdminUseCase,
        private readonly AdminRepositoryInterface $adminRepository,
        public readonly AlumnoRepositoryInterface $alumnoRepository,
        public readonly TutorRepositoryInterface $tutorRepository,
    ) {}

    public function store(CreateAdminRequest $request): JsonResponse
    {
        $dto  = CreateAdminDTO::fromArray($request->validated());
        $user = $this->createAdminUseCase->execute($dto);

        return response()->json(new AdminResource($user), 201);
    }

    public function index(): JsonResponse
    {
        $users = $this->getAllAdminsUseCase->execute();

        return response()->json(AdminResource::collection($users), 200);
    }

    public function show(string $id): JsonResponse
    {
        $admin = $this->adminRepository->findById(new UserId($id));

        if (!$admin) {
            return response()->json(['message' => 'Admin no encontrado'], 404);
        }

        return response()->json(new AdminResource($admin));
    }

    public function update(UpdateAdminRequest $request, string $id): JsonResponse
    {
        $dto = UpdateAdminDTO::fromArray($request->validated());

        if ($dto->isEmpty()) {
            return response()->json(['message' => 'No se informó ningún campo para actualizar.'], 422);
        }

        $admin = $this->updateAdminUseCase->execute($id, $dto);

        return response()->json(new AdminResource($admin), 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $admin = $this->adminRepository->findById(new UserId($id));

        if ($admin === null) {
            return response()->json(['message' => 'Admin no encontrado'], 404);
        }

        $this->adminRepository->delete(new UserId($id));

        return response()->json('Admin eliminado', 204);
    }

    public function statistics(): JsonResponse
    {
        $totalAlumnos = $this->alumnoRepository->totalAlumnos();
        $totalTutores = $this->tutorRepository->totalTutores();

        return response()->json([
            'total_alumnos' => $totalAlumnos,
            'total_tutores' => $totalTutores,
        ], 200);
    }
}