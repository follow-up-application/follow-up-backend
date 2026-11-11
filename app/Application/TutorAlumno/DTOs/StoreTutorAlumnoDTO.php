<?php

namespace App\Application\TutorAlumno\DTOs;

class StoreTutorAlumnoDTO
{
    public function __construct(
        public readonly string $alumno_id,
        public readonly string $tutor_id,
        public readonly string $relationship,
        public readonly ?string $otra_relacion,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            alumno_id: $data['alumno_id'],
            tutor_id: $data['tutor_id'],
            relationship: $data['relationship'],
            otra_relacion: $data['otra_relacion'] ?? null,
        );
    }
}
