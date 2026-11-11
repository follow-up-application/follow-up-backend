<?php

namespace App\Application\Materia\DTOs;

class CreateMateriaDTO
{
    public function __construct(
        public readonly string $nombre,
        public readonly string $descripcion,
        public readonly int $cursoDivisionTurnoId,
        public readonly string $docenteId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            nombre: $data['nombre'],
            descripcion: $data['descripcion'],
            cursoDivisionTurnoId: (int) $data['curso_division_turno_id'],
            docenteId: $data['docente_id'],
        );
    }
}
