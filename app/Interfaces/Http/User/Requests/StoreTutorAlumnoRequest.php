<?php

namespace App\Interfaces\Http\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTutorAlumnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'alumno_id' => ['required', 'string', 'exists:alumnos,id'],
            'tutor_id' => ['required', 'string', 'exists:tutores,id'],
            'relationship' => ['required', 'string', 'in:padre,madre,otra'],
            'otra_relacion' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'alumno_id.exists' => 'El alumno no existe.',
            'tutor_id.exists' => 'El tutor no existe.',
            'relationship.in' => 'La relación debe ser padre, madre u otra.',
        ];
    }
}
