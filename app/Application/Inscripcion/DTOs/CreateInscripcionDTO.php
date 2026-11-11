<?php

namespace App\Application\Inscripcion\DTOs;

class CreateInscripcionDTO
{
    public function __construct(
        public readonly string $alumnoId,
        public readonly int $materiaId,
        public readonly ?string $fechaInscripcion = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            alumnoId: $data['alumno_id'],
            materiaId: (int) $data['materia_id'],
            fechaInscripcion: $data['fecha_inscripcion'] ?? null,
        );
    }
}
