<?php

namespace App\Domain\User\Exceptions;


class CursoDivisionTurnoDuplicadoException extends DomainNotFoundException
{
    public function __construct(
        int|string|null $curso = null,
        int|string|null $division = null,
        int|string|null $turno = null,
        ?string $message = null,
    )
    {
        parent::__construct(
            $message ?? "El curso, división y turno ya se encuentra registrado."
                . ($curso !== null && $division !== null && $turno !== null
                    ? " ({$curso}° {$division} - {$turno})"
                    : '')
        );
    }
}