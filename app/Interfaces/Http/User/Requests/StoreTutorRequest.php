<?php

namespace App\Interfaces\Http\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTutorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'dni' => ['required', 'integer', 'digits_between:7,8', 'unique:users,dni'],
            'telephone' => ['required', 'string', 'max:20'],
            // El email es el username del tutor.
            'email' => ['required', 'string', 'email', 'max:255', 'unique:tutores,email'],
            // Opcional: si no se informa se usa el DNI como contraseña inicial.
            'password' => ['sometimes', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Ese email ya está registrado como tutor.',
            'dni.unique' => 'Ya existe un usuario con ese DNI.',
        ];
    }
}