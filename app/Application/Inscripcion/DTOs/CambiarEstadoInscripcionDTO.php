<?php

namespace App\Application\Inscripcion\DTOs;

class CambiarEstadoInscripcionDTO
{
    public function __construct(
        public readonly int $inscripcionId,
        public readonly string $estado,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            inscripcionId: (int) $data['id'],
            estado: $data['estado'],
        );
    }
}
