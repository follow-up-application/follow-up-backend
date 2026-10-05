<?php

namespace App\Domain\User\Exceptions;


class MateriaNoEncontradaException extends DomainNotFoundException
{
    public function __construct(string $message = "No se encontró la materia especificada.")
    {
        parent::__construct($message);
    }
}
