<?php

namespace App\Interfaces\Http\User\Requests;

use App\Domain\User\ValueObjects\TutorAlumnoRelation;
use App\Infrastructure\User\Persistence\UserModel;
use App\Interfaces\Http\User\Requests\Rules\FechaValida;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAlumnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Datos del alumno
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'dni' => ['required', 'integer', 'digits_between:7,8', 'unique:users,dni'],
            'fecha_nacimiento' => ['required', 'string', 'max:12', new FechaValida],
            'curso_division_turno_id' => ['required', 'integer', 'exists:curso_division_turno,id'],

            // Relación con el tutor. El enum del dominio usa minúsculas
            // ('padre', 'madre', 'otra'): en mayúsculas pasaba la validación y
            // reventaba el use case con un ValueError.
            'relationship' => ['required', 'string', Rule::enum(TutorAlumnoRelation::class)],
            'otra_relacion' => ['nullable', 'string', 'max:100', 'required_if:relationship,otra'],

            // Tutor
            'tutor_id' => ['nullable', 'uuid', 'exists:tutores,id'],
            'tutor_first_name' => [
                'nullable',
                'string',
                'max:100',
                Rule::requiredIf(fn () => ! $this->tutorIdInformado() && ! $this->tutorExistePorDni()),
            ],
            'tutor_last_name' => [
                'nullable',
                'string',
                'max:100',
                Rule::requiredIf(fn () => ! $this->tutorIdInformado() && ! $this->tutorExistePorDni()),
            ],
            'tutor_dni' => [
                'nullable',
                'integer',
                'digits_between:7,8',
                Rule::requiredIf(fn () => ! $this->tutorIdInformado()),
            ],
            'tutor_telephone' => [
                'nullable',
                'string',
                'max:20',
                Rule::requiredIf(fn () => ! $this->tutorIdInformado() && ! $this->tutorExistePorDni()),
            ],
        ];
    }

    private function tutorIdInformado(): bool
    {
        return filled($this->input('tutor_id'));
    }

    private function tutorExistePorDni(): bool
    {
        $dni = $this->input('tutor_dni');

        return filled($dni)
            && UserModel::where('dni', (int) $dni)
                ->where('role', 'tutor')
                ->exists();
    }

    public function messages(): array
    {
        return [
            'curso_division_turno_id.exists' => 'El curso/división/turno seleccionado no existe.',
            'otra_relacion.required_if' => 'Debe especificar la relación cuando selecciona "Otra".',
            'tutor_first_name.required_if' => 'El nombre del tutor es obligatorio si no se seleccionó un tutor existente.',
            'tutor_last_name.required_if' => 'El apellido del tutor es obligatorio si no se seleccionó un tutor existente.',
            'tutor_telephone.required_if' => 'El teléfono del tutor es obligatorio si no se seleccionó un tutor existente.',
        ];
    }
}
