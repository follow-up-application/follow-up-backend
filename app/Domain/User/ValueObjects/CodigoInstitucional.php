<?php

namespace App\Domain\User\ValueObjects;

use InvalidArgumentException;

class CodigoInstitucional
{
    private string $value;

    // 💡 Hacemos que reciba el valor opcionalmente en el constructor
    public function __construct(?string $value = null)
    {
        // Si no se provee un valor, se autogenera (caso de un nuevo vínculo tutor/alumno)
        $codigoInst = $value ?? $this->generateCodigo();

        if (empty(trim($codigoInst))) {
            throw new InvalidArgumentException('El código institucional no puede estar vacío');
        }

        $this->value = $codigoInst;
    }

    // 💡 Ahora el Named Constructor es súper simple y no necesita Reflection
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string { return $this->value; }

    public function equals(self $other): bool { return $this->value === $other->value; }

    public function __toString(): string { return $this->value; }

    private function generateCodigo(): string
    {
        // Formato ALU-XXXXXX-XXXXXX: prefijo fijo + dos bloques aleatorios de 6
        // caracteres hexadecimales, por ejemplo ALU-A1B2C3-A1ECD.
        return sprintf(
            'ALU-%s-%s',
            strtoupper(bin2hex(random_bytes(3))),
            strtoupper(bin2hex(random_bytes(3))),
        );
    }
}