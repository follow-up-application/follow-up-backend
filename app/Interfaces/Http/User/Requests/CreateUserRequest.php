<?php
namespace App\Interfaces\Http\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Domain\User\ValueObjects\UserRole;

class CreateUserRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'dni'        => ['nullable', 'integer', 'digits_between:7,8', 'unique:users,dni'],
            // El login es por username, así que sin él el usuario queda
            // creado pero sin forma de autenticarse.
            'username'   => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'password'   => 'required|string|min:8',
            'role'       => ['sometimes', 'string', 'in:' . implode(',', array_column(UserRole::cases(), 'value'))],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'Ese nombre de usuario ya está en uso.',
            'dni.unique'      => 'Ya existe un usuario con ese DNI.',
        ];
    }
}