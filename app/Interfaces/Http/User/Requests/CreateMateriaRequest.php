<?php

namespace App\Interfaces\Http\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMateriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ajustar según tu middleware de roles (ej. solo Administrador)
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['required', 'string', 'max:1000'],
            'curso_division_turno_id' => ['required', 'integer', 'exists:curso_division_turno,id'],
            'docente_id' => ['required', 'uuid', 'exists:docentes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'curso_division_turno_id.exists' => 'El curso, división y turno seleccionado no existe.',
            'docente_id.exists' => 'El docente seleccionado no existe.',
            'docente_id.uuid' => 'El identificador del docente no es válido.',
        ];
    }
}