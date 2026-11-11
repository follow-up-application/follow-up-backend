<?php

namespace App\Interfaces\Http\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMateriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Mismos límites que CreateMateriaRequest: docentes.id es un UUID, no un entero.
        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:150'],
            'descripcion' => ['sometimes', 'required', 'string', 'max:1000'],
            'curso_division_turno_id' => ['sometimes', 'required', 'integer', 'exists:curso_division_turno,id'],
            'docente_id' => ['sometimes', 'required', 'uuid', 'exists:docentes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'curso_division_turno_id.exists' => 'El curso/división/turno seleccionado no existe.',
            'docente_id.exists' => 'El docente seleccionado no existe.',
            'docente_id.uuid' => 'El identificador del docente no es válido.',
        ];
    }
}