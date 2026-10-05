<?php

namespace App\Domain\User\ValueObjects;

enum EstadoInscripcion: string
{
    case PENDIENTE = 'pendiente';
    case APROBADO  = 'aprobado';
    case RECHAZADO = 'rechazado';

    public function label(): string
    {
        return match ($this) {
            EstadoInscripcion::PENDIENTE => 'Pendiente',
            EstadoInscripcion::APROBADO  => 'Aprobado',
            EstadoInscripcion::RECHAZADO => 'Rechazado',
        };
    }
}
