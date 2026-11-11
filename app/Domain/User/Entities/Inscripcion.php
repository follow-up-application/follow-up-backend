<?php

namespace App\Domain\User\Entities;

use App\Domain\Shared\ValueObjects\FechaFormateada;
use App\Domain\User\ValueObjects\EstadoInscripcion;
use App\Domain\User\ValueObjects\UserId;

class Inscripcion
{
    public function __construct(
        private readonly int $id,
        private UserId $alumnoId,
        private int $materiaId,
        private FechaFormateada $fechaInscripcion,
        private EstadoInscripcion $estado,
    ) {}

    public function id(): int { return $this->id; }

    public function alumnoId(): UserId { return $this->alumnoId; }

    public function materiaId(): int { return $this->materiaId; }

    public function fechaInscripcion(): FechaFormateada { return $this->fechaInscripcion; }

    public function estado(): EstadoInscripcion { return $this->estado; }

    public function estaAprobada(): bool { return $this->estado === EstadoInscripcion::APROBADO; }

    public function cambiarEstado(EstadoInscripcion $estado): void { $this->estado = $estado; }

    public function setFechaInscripcion(FechaFormateada $fechaInscripcion): void
    {
        $this->fechaInscripcion = $fechaInscripcion;
    }

    public function setAlumnoId(UserId $alumnoId): void { $this->alumnoId = $alumnoId; }

    public function setMateriaId(int $materiaId): void { $this->materiaId = $materiaId; }
}
