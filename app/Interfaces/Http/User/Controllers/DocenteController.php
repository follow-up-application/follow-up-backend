<?php

namespace App\Interfaces\Http\User\Controllers;


use App\Application\Docente\DTOs\CreateDocenteDTO;
use App\Application\Docente\DTOs\UpdateDocenteDTO;
use App\Application\Docente\UseCases\CreateDocenteUseCase;
use App\Application\Docente\UseCases\GetAllDocentesUseCase;
use App\Application\Docente\UseCases\UpdateDocenteUseCase;
use App\Domain\User\Repositories\DocenteRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Interfaces\Http\Controller;
use App\Interfaces\Http\User\Requests\CreateDocenteRequest;
use App\Interfaces\Http\User\Requests\UpdateDocenteRequest;
use App\Interfaces\Http\User\Resources\DocenteResource;
use Illuminate\Http\JsonResponse;

class DocenteController extends Controller
{
    public function __construct(
        private readonly CreateDocenteUseCase  $createDocenteUseCase,
        private readonly GetAllDocentesUseCase $getAllDocentesUseCase,
        private readonly UpdateDocenteUseCase  $updateDocenteUseCase,
        private readonly DocenteRepositoryInterface $docenteRepository
    ) {}

    public function store(CreateDocenteRequest $request): JsonResponse
    {
        $dto  = CreateDocenteDTO::fromArray($request->validated());
        $user = $this->createDocenteUseCase->execute($dto);

        return response()->json(new DocenteResource($user), 201);
    }

    public function index(): JsonResponse
    {
        $users = $this->getAllDocentesUseCase->execute();

        return response()->json(DocenteResource::collection($users), 200);
    }

    public function show(string $id): JsonResponse
    {
        $docente = $this->docenteRepository->findById(new UserId($id));

        if (!$docente) {
            return response()->json(['message' => 'Docente no encontrado'], 404);
        }

        return response()->json(new DocenteResource($docente));
    }

    public function update(UpdateDocenteRequest $request, string $id): JsonResponse
    {
        $dto = UpdateDocenteDTO::fromArray($request->validated());

        if ($dto->isEmpty()) {
            return response()->json(['message' => 'No se informó ningún campo para actualizar.'], 422);
        }

        $docente = $this->updateDocenteUseCase->execute($id, $dto);

        return response()->json(new DocenteResource($docente), 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->docenteRepository->destroy(new UserId($id));

        return response()->json('Docente eliminado', 204);
    }
}