<?php

namespace App\Interfaces\Http\User\Controllers;

use App\Application\Materia\UseCases\GetAllMateriasUseCase;
use App\Application\Materia\UseCases\GetMateriasByCursoDivisionTurnoUseCase;
use App\Application\Materia\UseCases\GetMateriasByDocenteUseCase;
use App\Application\Materia\DTOs\CreateMateriaDTO;
use App\Application\Materia\DTOs\UpdateMateriaDTO;
use App\Application\Materia\UseCases\CreateMateriaUseCase;
use App\Application\Materia\UseCases\UpdateMateriaUseCase;
use App\Domain\User\Repositories\MateriaRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Interfaces\Http\Controller;
use App\Interfaces\Http\User\Requests\CreateMateriaRequest;
use App\Interfaces\Http\User\Requests\UpdateMateriaRequest;
use App\Interfaces\Http\User\Resources\MateriaResource;
use Illuminate\Http\JsonResponse;

class MateriaController extends Controller
{
    public function __construct(
        private readonly CreateMateriaUseCase  $createMateriaUseCase,
        private readonly GetAllMateriasUseCase $getAllMateriasUseCase,
        private readonly GetMateriasByDocenteUseCase $getMateriasByDocenteUseCase,
        private readonly GetMateriasByCursoDivisionTurnoUseCase $getMateriasByCursoDivisionTurnoUseCase,
        private readonly UpdateMateriaUseCase $updateMateriaUseCase,
        private readonly MateriaRepositoryInterface $materiaRepository
    ) {}

    public function store(CreateMateriaRequest $request): JsonResponse
    {
        $dto  = CreateMateriaDTO::fromArray($request->validated());
        $materia = $this->createMateriaUseCase->execute($dto);

        return response()->json(new MateriaResource($materia), 201);
    }

    public function index(): JsonResponse
    {
        $materias = $this->getAllMateriasUseCase->execute();

        return response()->json(MateriaResource::collection($materias), 200);
    }

    public function show(string $id): JsonResponse
    {
        $materia = $this->materiaRepository->findById(intval($id));

        if (!$materia) {
            return response()->json(['message' => 'Materia no encontrada'], 404);
        }

        return response()->json(new MateriaResource($materia));
    }

    public function byDocente(string $id): JsonResponse
    {
        $materias = $this->getMateriasByDocenteUseCase->execute(new UserId($id));

        return response()->json(MateriaResource::collection($materias), 200);
    }

    public function byCursoDivisionTurno(string $id): JsonResponse
    {
        $materias = $this->getMateriasByCursoDivisionTurnoUseCase->execute(intval($id));

        return response()->json(MateriaResource::collection($materias), 200);
    }

    public function update(UpdateMateriaRequest $request, string $id): JsonResponse
    {
        $dto = UpdateMateriaDTO::fromArray($request->validated());

        if ($dto->isEmpty()) {
            return response()->json(['message' => 'No se informó ningún campo para actualizar.'], 422);
        }

        $materia = $this->updateMateriaUseCase->execute(intval($id), $dto);

        return response()->json(new MateriaResource($materia), 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->materiaRepository->destroy(intval($id));

        return response()->json('Materia eliminada', 204);
    }
}
