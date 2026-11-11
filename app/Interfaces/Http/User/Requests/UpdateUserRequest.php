<?php

namespace App\Interfaces\Http\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'dni' => ['sometimes', 'required', 'integer', 'digits_between:7,8', 'unique:users,dni,'.$this->route('id')],
            'username' => ['sometimes', 'required', 'string', 'max:255', 'unique:users,username,'.$this->route('id')],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'Ese nombre de usuario ya está en uso.',
            'dni.unique' => 'Ya existe un usuario con ese DNI.',
        ];
    }
}
