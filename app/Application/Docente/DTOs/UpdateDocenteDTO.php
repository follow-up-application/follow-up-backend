<?php

namespace App\Application\Docente\DTOs;

class UpdateDocenteDTO
{
    public function __construct(
        public readonly ?string $first_name,
        public readonly ?string $last_name,
        public readonly ?int $dni,
        public readonly ?string $telephone,
        public readonly ?string $fecha_ingreso,
        public readonly ?int $curso_division_turno_id,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            first_name: $data['first_name'] ?? null,
            last_name: $data['last_name'] ?? null,
            dni: isset($data['dni']) && $data['dni'] !== '' ? (int) $data['dni'] : null,
            telephone: $data['telephone'] ?? null,
            fecha_ingreso: $data['fecha_ingreso'] ?? null,
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
            && $this->telephone === null
            && $this->fecha_ingreso === null
            && $this->curso_division_turno_id === null;
    }
}