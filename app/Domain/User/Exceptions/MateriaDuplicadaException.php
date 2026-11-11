<?php

namespace App\Domain\User\Exceptions;


class MateriaDuplicadaException extends DomainNotFoundException
{
    public function __construct(string $message = "La materia ya se encuentra registrada en ese curso, división y turno.")
    {
        parent::__construct($message);
    }
}
