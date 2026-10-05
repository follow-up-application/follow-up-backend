<?php

namespace App\Interfaces\Http\User\Controllers;

use App\Application\Inscripcion\DTOs\CambiarEstadoInscripcionDTO;
use App\Application\Inscripcion\DTOs\CreateInscripcionDTO;
use App\Application\Inscripcion\UseCases\CambiarEstadoInscripcionUseCase;
use App\Application\Inscripcion\UseCases\CreateInscripcionUseCase;
use App\Application\Inscripcion\UseCases\DeleteInscripcionUseCase;
use App\Application\Inscripcion\UseCases\GetAllInscripcionesUseCase;
use App\Application\Inscripcion\UseCases\GetInscripcionesByAlumnoUseCase;
use App\Application\Inscripcion\UseCases\GetInscripcionesByMateriaUseCase;
use App\Domain\User\Repositories\InscripcionRepositoryInterface;
use App\Domain\User\ValueObjects\EstadoInscripcion;
use App\Domain\User\ValueObjects\UserId;
use App\Interfaces\Http\Controller;
use App\Interfaces\Http\User\Requests\CambiarEstadoInscripcionRequest;
use App\Interfaces\Http\User\Requests\CreateInscripcionRequest;
use App\Interfaces\Http\User\Resources\InscripcionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InscripcionController extends Controller
{
    public function __construct(
        private readonly CreateInscripcionUseCase $createInscripcionUseCase,
        private readonly GetAllInscripcionesUseCase $getAllInscripcionesUseCase,
        private readonly GetInscripcionesByAlumnoUseCase $getInscripcionesByAlumnoUseCase,
        private readonly GetInscripcionesByMateriaUseCase $getInscripcionesByMateriaUseCase,
        private readonly CambiarEstadoInscripcionUseCase $cambiarEstadoInscripcionUseCase,
        private readonly DeleteInscripcionUseCase $deleteInscripcionUseCase,
        private readonly InscripcionRepositoryInterface $inscripcionRepository,
    ) {}

    public function store(CreateInscripcionRequest $request): JsonResponse
    {
        $dto = CreateInscripcionDTO::fromArray($request->validated());
        $inscripcion = $this->createInscripcionUseCase->execute($dto);

        return response()->json(new InscripcionResource($inscripcion), 201);
    }

    public function index(): JsonResponse
    {
        $inscripciones = $this->getAllInscripcionesUseCase->execute();

        return response()->json(InscripcionResource::collection($inscripciones), 200);
    }

    public function show(string $id): JsonResponse
    {
        $inscripcion = $this->inscripcionRepository->findById(intval($id));

        if (!$inscripcion) {
            return response()->json(['message' => 'Inscripción no encontrada'], 404);
        }

        return response()->json(new InscripcionResource($inscripcion));
    }

    public function byAlumno(Request $request, string $id): JsonResponse
    {
        $inscripciones = $this->getInscripcionesByAlumnoUseCase->execute(
            new UserId($id),
            $this->estadoFromQuery($request),
        );

        return response()->json(InscripcionResource::collection($inscripciones), 200);
    }

    public function byMateria(Request $request, string $id): JsonResponse
    {
        $inscripciones = $this->getInscripcionesByMateriaUseCase->execute(
            intval($id),
            $this->estadoFromQuery($request),
        );

        return response()->json(InscripcionResource::collection($inscripciones), 200);
    }

    public function updateEstado(CambiarEstadoInscripcionRequest $request, string $id): JsonResponse
    {
        $dto = new CambiarEstadoInscripcionDTO(
            inscripcionId: intval($id),
            estado: $request->validated('estado'),
        );

        $inscripcion = $this->cambiarEstadoInscripcionUseCase->execute($dto);

        return response()->json(new InscripcionResource($inscripcion));
    }

    public function destroy(string $id): JsonResponse
    {
        $this->deleteInscripcionUseCase->execute(intval($id));

        return response()->json('Inscripción eliminada', 204);
    }

    /**
     * Filtro opcional ?estado=pendiente|aprobado|rechazado
     */
    private function estadoFromQuery(Request $request): ?EstadoInscripcion
    {
        $estado = $request->query('estado');

        if ($estado === null) {
            return null;
        }

        return EstadoInscripcion::tryFrom($estado)
            ?? abort(422, 'El estado debe ser pendiente, aprobado o rechazado.');
    }
}
