<?php

namespace App\Interfaces\Http\User\Controllers;

use App\Interfaces\Http\Controller;
use App\Application\Alumno\UseCases\CreateAlumnoUseCase;
use App\Application\Alumno\UseCases\GetAllAlumnosUseCase;
use App\Application\Alumno\UseCases\UpdateAlumnoUseCase;
use App\Application\Alumno\DTOs\CreateAlumnoDTO;
use App\Application\Alumno\DTOs\UpdateAlumnoDTO;
use App\Domain\User\Repositories\AlumnoRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Interfaces\Http\User\Requests\CreateAlumnoRequest;
use App\Interfaces\Http\User\Requests\UpdateAlumnoRequest;
use Illuminate\Http\JsonResponse;
use App\Interfaces\Http\User\Resources\AlumnoResource;

class AlumnoController extends Controller
{
    public function __construct(
        private readonly CreateAlumnoUseCase  $createAlumnoUseCase,
        private readonly GetAllAlumnosUseCase $getAllAlumnosUseCase,
        private readonly UpdateAlumnoUseCase  $updateAlumnoUseCase,
        private readonly AlumnoRepositoryInterface $alumnoRepository
    ) {}

    public function store(CreateAlumnoRequest $request): JsonResponse
    {
        $dto  = CreateAlumnoDTO::fromArray($request->validated());
        $user = $this->createAlumnoUseCase->execute($dto);

        return response()->json(new AlumnoResource($user), 201);
    }

    public function index(): JsonResponse
    {
        $users = $this->getAllAlumnosUseCase->execute();

        return response()->json(AlumnoResource::collection($users), 200);
    }

    public function show(string $id): JsonResponse
    {
        $alumno = $this->alumnoRepository->findById(new UserId($id));

        if (!$alumno) {
            return response()->json(['message' => 'Alumno no encontrado'], 404);
        }

        return response()->json(new AlumnoResource($alumno));
    }

    public function update(UpdateAlumnoRequest $request, string $id): JsonResponse
    {
        $dto = UpdateAlumnoDTO::fromArray($request->validated());

        if ($dto->isEmpty()) {
            return response()->json(['message' => 'No se informó ningún campo para actualizar.'], 422);
        }

        $alumno = $this->updateAlumnoUseCase->execute($id, $dto);

        return response()->json(new AlumnoResource($alumno), 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->alumnoRepository->destroy(new UserId($id));

        return response()->json('Alumno eliminado', 204);
    }
}