<?php

namespace App\Application\Materia\DTOs;

class UpdateMateriaDTO
{
    public function __construct(
        public readonly ?string $nombre,
        public readonly ?string $descripcion,
        public readonly ?int $curso_division_turno_id,
        public readonly ?string $docente_id,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            nombre: $data['nombre'] ?? null,
            descripcion: $data['descripcion'] ?? null,
            curso_division_turno_id: isset($data['curso_division_turno_id']) && $data['curso_division_turno_id'] !== ''
                ? (int) $data['curso_division_turno_id']
                : null,
            docente_id: $data['docente_id'] ?? null,
        );
    }

    public function isEmpty(): bool
    {
        return $this->nombre === null
            && $this->descripcion === null
            && $this->curso_division_turno_id === null
            && $this->docente_id === null;
    }
}