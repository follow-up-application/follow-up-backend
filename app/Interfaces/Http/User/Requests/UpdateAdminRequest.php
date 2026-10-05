<?php

namespace App\Interfaces\Http\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminRequest extends FormRequest
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
            'username' => ['sometimes', 'required', 'string', 'max:255', 'unique:users,username,' . request()->route('id')],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'Ese nombre de usuario ya está en uso.',
        ];
    }
}