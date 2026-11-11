<?php

namespace App\Interfaces\Http\User\Requests;

use App\Domain\User\ValueObjects\TurnoEscolar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCDTRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Todos opcionales: el use case conserva lo que no se informe.
            'curso' => ['sometimes', 'required', 'integer', 'min:1', 'max:7'],
            'division' => ['sometimes', 'required', 'string', 'regex:/^[A-Z]$/'],
            'turno' => ['sometimes', 'required', 'string', Rule::enum(TurnoEscolar::class)],
            'capacidad_maxima' => ['sometimes', 'required', 'integer', 'min:1', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'curso.integer' => 'El curso debe ser un año entre 1 y 7.',
            'curso.min' => 'El curso debe ser un año entre 1 y 7.',
            'curso.max' => 'El curso debe ser un año entre 1 y 7.',
            'division.regex' => 'La división debe ser una sola letra mayúscula (A-Z).',
            'turno.*' => 'El turno debe ser Mañana o Tarde.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('division')) {
            $this->merge(['division' => strtoupper(trim((string) $this->input('division')))]);
        }
    }
}