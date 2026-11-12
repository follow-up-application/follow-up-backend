<?php

namespace App\Interfaces\Http\User\Requests;

use App\Interfaces\Http\User\Requests\Rules\FechaValida;
use Illuminate\Foundation\Http\FormRequest;

class CreateInscripcionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // el middleware de roles define quién puede inscribir
    }

    public function rules(): array
    {
        return [
            'alumno_id' => ['required', 'uuid', 'exists:alumnos,id'],
            'materia_id' => ['required', 'integer', 'exists:materias,id'],
            'fecha_inscripcion' => ['nullable', 'string', 'max:12', new FechaValida],
        ];
    }

    public function messages(): array
    {
        return [
            'alumno_id.exists' => 'El alumno seleccionado no existe.',
            'alumno_id.uuid' => 'El identificador del alumno no es válido.',
            'materia_id.exists' => 'La materia seleccionada no existe.',
            'fecha_inscripcion.date_format' => 'La fecha de inscripción debe tener el formato dd/mm/yyyy.',
        ];
    }
}
