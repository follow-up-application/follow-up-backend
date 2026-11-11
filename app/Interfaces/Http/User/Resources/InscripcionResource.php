<?php

namespace App\Interfaces\Http\User\Resources;

use App\Domain\User\Entities\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Inscripcion
 */
class InscripcionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Inscripcion $inscripcion */
        $inscripcion = $this->resource;

        return [
            'id'                => $inscripcion->id(),
            'alumno_id'         => $inscripcion->alumnoId()->value(),
            'materia_id'        => $inscripcion->materiaId(),
            'fecha_inscripcion' => $inscripcion->fechaInscripcion()->toDatabase(),
            'estado'            => $inscripcion->estado()->value,
            'estado_label'      => $inscripcion->estado()->label(),
        ];
    }
}
