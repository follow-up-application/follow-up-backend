<?php

namespace App\Interfaces\Http\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name' => ['sometimes', 'required', 'string', 'max:100'],
            'dni' => ['sometimes', 'required', 'integer', 'digits_between:7,8'],
            'telephone' => ['sometimes', 'required', 'string', 'max:20'],
            'fecha_ingreso' => ['sometimes', 'required', 'string', 'max:12'],
            'curso_division_turno_id' => ['sometimes', 'required', 'integer', 'exists:curso_division_turno,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'curso_division_turno_id.exists' => 'El curso/división/turno seleccionado no existe.',
        ];
    }
}