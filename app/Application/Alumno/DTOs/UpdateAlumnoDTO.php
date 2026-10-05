<?php

namespace App\Application\Alumno\DTOs;

class UpdateAlumnoDTO
{
    public function __construct(
        public readonly ?string $first_name,
        public readonly ?string $last_name,
        public readonly ?int $dni,
        public readonly ?string $fecha_nacimiento,
        public readonly ?int $curso_division_turno_id,
    ) {}

    /** Sólo se toma lo que venga informado: el update es parcial. */
    public static function fromArray(array $data): self
    {
        return new self(
            first_name: $data['first_name'] ?? null,
            last_name: $data['last_name'] ?? null,
            dni: isset($data['dni']) && $data['dni'] !== '' ? (int) $data['dni'] : null,
            fecha_nacimiento: $data['fecha_nacimiento'] ?? null,
            curso_division_turno_id: isset($data['curso_division_turno_id']) && $data['curso_division_turno_id'] !== ''
                ? (int) $data['curso_division_turno_id']
                : null,
        );
    }

    public function isEmpty(): bool
    {
        return $this->first_name === null
            && $this->last_name === null
            && $this->dni === null
            && $this->fecha_nacimiento === null
            && $this->curso_division_turno_id === null;
    }
}