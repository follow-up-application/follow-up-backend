<?php

namespace App\Interfaces\Http\User\Controllers;

use App\Application\Tutor\UseCases\GetAllTutoresUseCase;
use App\Application\Tutor\DTOs\RegisterTutorDTO;
use App\Application\Tutor\DTOs\StoreTutorDTO;
use App\Application\Tutor\DTOs\UpdateTutorDTO;
use App\Application\Tutor\UseCases\RegisterTutorUseCase;
use App\Application\Tutor\UseCases\StoreTutorUseCase;
use App\Application\Tutor\UseCases\UpdateTutorUseCase;
use App\Domain\User\Repositories\AlumnoRepositoryInterface;
use App\Domain\User\Repositories\TutorAlumnoRepositoryInterface;
use App\Domain\User\Repositories\TutorRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Interfaces\Http\Controller;
use App\Interfaces\Http\User\Requests\CreateTutorRequest;
use App\Interfaces\Http\User\Requests\StoreTutorRequest;
use App\Interfaces\Http\User\Requests\UpdateTutorRequest;
use App\Interfaces\Http\User\Resources\AlumnoResource;
use App\Interfaces\Http\User\Resources\TutorResource;
use Illuminate\Http\JsonResponse;

class TutorController extends Controller
{
    public function __construct(
        private readonly RegisterTutorUseCase  $registerTutorUseCase,
        private readonly StoreTutorUseCase  $storeTutorUseCase,
        private readonly UpdateTutorUseCase  $updateTutorUseCase,
        private readonly GetAllTutoresUseCase $getAllTutoresUseCase,
        private readonly TutorRepositoryInterface $tutorRepository,
        private readonly TutorAlumnoRepositoryInterface $tutorAlumnoRepository,
        private readonly AlumnoRepositoryInterface $alumnoRepository,
    ) {}

    public function register(CreateTutorRequest $request): JsonResponse
    {
        $dto  = RegisterTutorDTO::fromArray($request->validated());
        $user = $this->registerTutorUseCase->execute($dto);

        return response()->json(new TutorResource($user), 201);
    }

    /** Alta directa del administrador, sin pre-registro previo. */
    public function store(StoreTutorRequest $request): JsonResponse
    {
        $dto  = StoreTutorDTO::fromArray($request->validated());
        $user = $this->storeTutorUseCase->execute($dto);

        return response()->json(new TutorResource($user), 201);
    }

    public function index(): JsonResponse
    {
        $users = $this->getAllTutoresUseCase->execute();

        return response()->json(TutorResource::collection($users), 200);
    }

    public function show(string $id): JsonResponse
    {
        $tutor = $this->tutorRepository->findById(new UserId($id));

        if (!$tutor) {
            return response()->json(['message' => 'Tutor no encontrado'], 404);
        }

        return response()->json(new TutorResource($tutor));
    }

    public function update(UpdateTutorRequest $request, string $id): JsonResponse
    {
        $dto = UpdateTutorDTO::fromArray($request->validated());

        if ($dto->isEmpty()) {
            return response()->json(['message' => 'No se informó ningún campo para actualizar.'], 422);
        }

        $tutor = $this->updateTutorUseCase->execute($id, $dto);

        return response()->json(new TutorResource($tutor), 200);
    }

    /** Alumnos a cargo del tutor, usando los vínculos tutor_alumno. */
    public function alumnos(string $id): JsonResponse
    {
        $vinculos = $this->tutorAlumnoRepository->findByTutorId(new UserId($id));

        $alumnos = [];

        foreach ($vinculos as $vinculo) {
            $alumno = $this->alumnoRepository->findById($vinculo->alumnoId());

            if ($alumno !== null) {
                $alumnos[] = new AlumnoResource($alumno);
            }
        }

        return response()->json($alumnos, 200);
    }

    public function inhabilitar(string $id): JsonResponse
    {
        $tutor = $this->tutorRepository->findById(new UserId($id));

        if (!$tutor) {
            return response()->json(['message' => 'Tutor no encontrado'], 404);
        }

        $this->tutorRepository->inhabilitar(new UserId($id));

        return response()->json('Tutor inhabilitado', 204);
    }

    public function findByDni(string $dni): JsonResponse
    {
        $tutor = $this->tutorRepository->findByDni(intval($dni));

        if (!$tutor) {
            return response()->json(['message' => 'Tutor no encontrado'], 404);
        }

        return response()->json(new TutorResource($tutor));
    }

    public function validateCode(string $codigo): JsonResponse
    {

        if (!$codigo) {
            return response()->json(['message' => 'Código no proporcionado'], 400);
        }

        $tutorAlumno = $this->tutorAlumnoRepository->findPreRegistroPendiente($codigo);

        if (!$tutorAlumno) {
            return response()->json(['message' => 'Código inválido'], 404);
        }

        $alumno = $this->alumnoRepository->findById($tutorAlumno->alumnoId());

        if (!$alumno) {
            return response()->json(['message' => 'Código inválido'], 404);
        }

        return response()->json(new AlumnoResource($alumno));
    }
}