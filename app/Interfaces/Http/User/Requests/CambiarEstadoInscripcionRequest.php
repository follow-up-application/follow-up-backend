<?php

namespace App\Interfaces\Http\User\Requests;

use App\Domain\User\ValueObjects\EstadoInscripcion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CambiarEstadoInscripcionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // el middleware de roles define quién puede aprobar o rechazar
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', 'string', Rule::enum(EstadoInscripcion::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'estado.enum' => 'El estado debe ser pendiente, aprobado o rechazado.',
        ];
    }
}
