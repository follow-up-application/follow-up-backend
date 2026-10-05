<?php

namespace App\Application\CursoDivisionTurno\DTOs;

class UpdateCDTDTO
{
    public function __construct(
        public readonly ?int $curso,
        public readonly ?string $division,
        public readonly ?string $turno,
        public readonly ?int $capacidad_maxima,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            curso: isset($data['curso']) && $data['curso'] !== '' ? (int) $data['curso'] : null,
            division: isset($data['division']) && $data['division'] !== '' ? $data['division'] : null,
            turno: $data['turno'] ?? null,
            capacidad_maxima: isset($data['capacidad_maxima']) && $data['capacidad_maxima'] !== ''
                ? (int) $data['capacidad_maxima']
                : null,
        );
    }

    public function isEmpty(): bool
    {
        return $this->curso === null
            && $this->division === null
            && $this->turno === null
            && $this->capacidad_maxima === null;
    }
}