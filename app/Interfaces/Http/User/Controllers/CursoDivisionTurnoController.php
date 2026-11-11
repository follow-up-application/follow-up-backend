<?php

namespace App\Interfaces\Http\User\Controllers;

use App\Application\CursoDivisionTurno\DTOs\CreateCDTdto;
use App\Application\CursoDivisionTurno\DTOs\UpdateCDTDTO;
use App\Application\CursoDivisionTurno\UseCases\CreateCDTUseCase;
use App\Application\CursoDivisionTurno\UseCases\GetAllCDTUseCase;
use App\Application\CursoDivisionTurno\UseCases\UpdateCDTUseCase;
use App\Domain\User\Repositories\CursoDivisionTurnoRepositoryInterface;
use App\Interfaces\Http\Controller;
use App\Interfaces\Http\User\Requests\CreateCDTRequest;
use App\Interfaces\Http\User\Requests\UpdateCDTRequest;
use App\Interfaces\Http\User\Resources\CDTResource;
use Illuminate\Http\JsonResponse;

class CursoDivisionTurnoController extends Controller
{
    public function __construct(
        private readonly CreateCDTUseCase  $createCDTUseCase,
        private readonly GetAllCDTUseCase $getAllAlumnosUseCase,
        private readonly UpdateCDTUseCase  $updateCDTUseCase,
        private readonly CursoDivisionTurnoRepositoryInterface $cursoDivisionTurnoRepository
    ) {}

    public function store(CreateCDTRequest $request): JsonResponse
    {
        $dto  = CreateCDTdto::fromArray($request->validated());
        $cdt = $this->createCDTUseCase->execute($dto);

        return response()->json(new CDTResource($cdt), 201);
    }

    public function index(): JsonResponse
    {
        $users = $this->getAllAlumnosUseCase->execute();

        return response()->json(CDTResource::collection($users), 200);
    }

    public function show(string $id): JsonResponse
    {
        $alumno = $this->cursoDivisionTurnoRepository->findById(intval($id));

        if (!$alumno) {
            return response()->json(['message' => 'Curso Division Turno no encontrado'], 404);
        }

        return response()->json(new CDTResource($alumno));
    }

    public function update(UpdateCDTRequest $request, string $id): JsonResponse
    {
        $dto = UpdateCDTDTO::fromArray($request->validated());

        if ($dto->isEmpty()) {
            return response()->json(['message' => 'No se informó ningún campo para actualizar.'], 422);
        }

        $cdt = $this->updateCDTUseCase->execute(intval($id), $dto);

        return response()->json(new CDTResource($cdt), 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->cursoDivisionTurnoRepository->destroy(intval($id));

        return response()->json('Curso Division Turno eliminado', 204);
    }
}