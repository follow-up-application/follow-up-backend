<?php

namespace App\Interfaces\Http\User\Requests\Rules;

use App\Domain\Shared\ValueObjects\FechaFormateada;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;

class FechaValida implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            new FechaFormateada((string) $value);
        } catch (InvalidArgumentException $e) {
            $fail($e->getMessage());
        }
    }
}
