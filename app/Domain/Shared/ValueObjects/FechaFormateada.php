<?php

namespace App\Domain\Shared\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

class FechaFormateada
{
    private DateTimeImmutable $fecha;

    public function __construct(string $fecha)
    {
        // Acepta dd/mm/yyyy (frontend) y yyyy-mm-dd (API/Postman).
        $parsed = $this->parse('!d/m/Y', $fecha)
            ?? $this->parse('!Y-m-d', $fecha);

        if (! $parsed) {
            throw new InvalidArgumentException(
                "Fecha inválida: {$fecha}. Formatos esperados: dd/mm/yyyy o yyyy-mm-dd"
            );
        }

        $this->fecha = $parsed;
    }

    private function parse(string $format, string $fecha): ?DateTimeImmutable
    {
        $parsed = DateTimeImmutable::createFromFormat($format, $fecha);
        $errors = DateTimeImmutable::getLastErrors();

        if ($parsed === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return null;
        }

        return $parsed;
    }

    // Para guardar en la DB
    public function toDatabase(): string
    {
        return $this->fecha->format('Y-m-d');
    }

    // Para devolver al frontend
    public function toDisplay(): string
    {
        return $this->fecha->format('d/m/Y');
    }

    // Para trabajar con la fecha en el dominio
    public function toDateTime(): DateTimeImmutable
    {
        return $this->fecha;
    }

    public function toYears(): string
    {
        return strval($this->fecha->format('Y'));
    }

    // Para construir desde la DB (yyyy-mm-dd)
    public static function fromDatabase(string|\DateTimeInterface $fecha): self
    {
        $instance = new self('01/01/2000'); // instancia temporal

        if ($fecha instanceof \DateTimeInterface) {
            $instance->fecha = DateTimeImmutable::createFromInterface($fecha);

            return $instance;
        }

        // Acepta tanto "Y-m-d" como "Y-m-d H:i:s" por si Eloquent devuelve con hora
        $parsed = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $fecha)
            ?: DateTimeImmutable::createFromFormat('Y-m-d', $fecha);

        if (! $parsed) {
            throw new InvalidArgumentException("Fecha inválida desde DB: {$fecha}");
        }

        $instance->fecha = $parsed;

        return $instance;
    }

    public static function fromString(string $fecha): self
    {
        return new self($fecha);
    }
}
