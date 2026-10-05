<?php

namespace App\Domain\User\Exceptions;


class InscripcionNoEncontradaException extends DomainNotFoundException
{
    public function __construct(string $message = "No se encontró la inscripción especificada.")
    {
        parent::__construct($message);
    }
}
