<?php

namespace App\Domain\User\Exceptions;


class AlumnoYaInscriptoException extends DomainNotFoundException
{
    public function __construct(string $message = "El alumno ya se encuentra inscripto en esa materia.")
    {
        parent::__construct($message);
    }
}
