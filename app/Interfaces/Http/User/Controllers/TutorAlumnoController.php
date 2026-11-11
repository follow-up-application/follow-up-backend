<?php

namespace App\Interfaces\Http\User\Controllers;

use App\Application\TutorAlumno\DTOs\StoreTutorAlumnoDTO;
use App\Application\TutorAlumno\UseCases\StoreTutorAlumnoUseCase;
use App\Domain\User\Repositories\AlumnoRepositoryInterface;
use App\Domain\User\Repositories\TutorAlumnoRepositoryInterface;
use App\Domain\User\Repositories\TutorRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Interfaces\Http\Controller;
use App\Interfaces\Http\User\Requests\StoreTutorAlumnoRequest;
use App\Interfaces\Http\User\Resources\TutorResource;
use Illuminate\Http\JsonResponse;

class TutorAlumnoController extends Controller
{
    public function __construct(
        private readonly StoreTutorAlumnoUseCase $storeTutorAlumnoUseCase,
        private readonly TutorAlumnoRepositoryInterface $tutorAlumnoRepository,
        private readonly TutorRepositoryInterface $tutorRepository,
        private readonly AlumnoRepositoryInterface $alumnoRepository,
    ) {}

    public function store(StoreTutorAlumnoRequest $request): JsonResponse
    {
        $dto = StoreTutorAlumnoDTO::fromArray($request->validated());
        $vinculo = $this->storeTutorAlumnoUseCase->execute($dto);

        return response()->json([
            'id' => $vinculo->id()->value(),
            'alumno_id' => $vinculo->alumnoId()->value(),
            'tutor_id' => $vinculo->tutorId()->value(),
            'relationship' => $vinculo->relationship()->value,
            'otra_relacion' => $vinculo->otraRelacion(),
            'codigo_institucional' => $vinculo->codigoInstitucional()->value(),
        ], 201);
    }

    public function inhabilitar(string $id): JsonResponse
    {
        $vinculo = $this->tutorAlumnoRepository->findById(new UserId($id));

        if (! $vinculo) {
            return response()->json(['message' => 'Vínculo tutor-alumno no encontrado'], 404);
        }

        $this->tutorAlumnoRepository->inhabilitar(new UserId($id));

        return response()->json('Vínculo inhabilitado', 204);
    }

    public function byAlumno(string $id): JsonResponse
    {
        $alumno = $this->alumnoRepository->findById(new UserId($id));

        if (! $alumno) {
            return response()->json(['message' => 'Alumno no encontrado'], 404);
        }

        $vinculos = $this->tutorAlumnoRepository->findByAlumnoId(new UserId($id));

        $tutores = [];
        foreach ($vinculos as $vinculo) {
            $tutor = $this->tutorRepository->findById($vinculo->tutorId());
            if ($tutor !== null) {
                $tutores[] = new TutorResource($tutor);
            }
        }

        return response()->json($tutores, 200);
    }
}
