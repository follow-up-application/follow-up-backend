<?php

namespace App\Interfaces\Http\User\Requests;

use App\Interfaces\Http\User\Requests\Rules\FechaValida;
use Illuminate\Foundation\Http\FormRequest;

class CreateDocenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'dni' => ['required', 'integer', 'digits_between:7,8', 'unique:users,dni'],
            'telephone' => ['required', 'string', 'max:20'],
            'fecha_ingreso' => ['required', 'string', 'max:12', new FechaValida],
            'curso_division_turno_id' => ['required', 'integer', 'exists:curso_division_turno,id'],
        ];
    }
}
