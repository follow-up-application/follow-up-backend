<?php

namespace App\Domain\User\Entities;

use App\Domain\User\ValueObjects\UserId;

class Materia
{
    public function __construct(
        private readonly int $id,
        private string $nombre,
        private string $descripcion,
        private CursoDivisionTurno $cursoDivisionTurno,
        private UserId $docente,
    ) {}

    public function id(): int { return $this->id; }
    public function nombre(): string { return $this->nombre; }
    public function descripcion(): string { return $this->descripcion; }
    public function cursoDivisionTurno(): CursoDivisionTurno { return $this->cursoDivisionTurno; }
    public function turno(): string { return $this->cursoDivisionTurno->turno()->value; }
    public function docente(): UserId { return $this->docente; }

    public function setNombre(string $nombre): void { $this->nombre = $nombre; }
    public function setDescripcion(string $descripcion): void { $this->descripcion = $descripcion;}
    public function setCursoDivisionTurno(CursoDivisionTurno $cursoDivisionTurno): void
    {
        $this->cursoDivisionTurno = $cursoDivisionTurno;
    }
    public function setDocente(UserId $docente): void { $this->docente = $docente; }

}
