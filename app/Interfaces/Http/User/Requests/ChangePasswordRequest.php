<?php

namespace App\Interfaces\Http\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password'              => ['required', 'string', 'min:8'],
            // Opcional: si el cliente la manda, tiene que coincidir.
            'password_confirmation' => ['sometimes', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $password = $this->input('password');
            $confirmacion = $this->input('password_confirmation');

            if ($confirmacion !== null && $confirmacion !== $password) {
                $validator->errors()->add(
                    'password_confirmation',
                    'La confirmación no coincide con la contraseña.'
                );
            }
        });
    }
}