<?php

namespace App\Domain\User\Entities;

use App\Domain\Shared\ValueObjects\FechaFormateada;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\UserName;
use App\Domain\User\ValueObjects\UserRole;

class Alumno extends User
{
    public function __construct(
        UserId $id,
        string $first_name,
        string $last_name,
        int $dni,
        string $password,
        private UserName $username,
        private FechaFormateada $fecha_nacimiento,
        private CursoDivisionTurno $curso_division_turno,
    ) {
        parent::__construct($id, $first_name, $last_name, $dni, $username->value(), $password, UserRole::ALUMNO, true);
    }

    public function username(): string { return $this->username->value(); }

    public function fechaNacimiento(): FechaFormateada { return $this->fecha_nacimiento; }

    public function cursoDivisionTurno(): CursoDivisionTurno { return $this->curso_division_turno; }

    public function setUsername(string $dni, string $anio_ingreso): void
    {
        $this->username = new UserName('EST', $dni, $anio_ingreso);
    }

    public function setFechaNacimiento(FechaFormateada $fecha_nacimiento): void
    {
        $this->fecha_nacimiento = $fecha_nacimiento;
    }

    public function setCursoDivisionTurno(CursoDivisionTurno $curso_division_turno): void
    {
        $this->curso_division_turno = $curso_division_turno;
    }
}