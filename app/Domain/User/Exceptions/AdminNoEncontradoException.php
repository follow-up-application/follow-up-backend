<?php

namespace App\Domain\User\Exceptions;

class AdminNoEncontradoException extends DomainNotFoundException
{
    public function __construct(string $message = "No se encontró el administrador especificado.")
    {
        parent::__construct($message);
    }
}