<?php

namespace App\Application\User\DTOs;

use App\Domain\User\ValueObjects\UserRole;

class CreateUserDTO
{
    public function __construct(
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly ?int $dni,
        public readonly ?string $username,
        public readonly string $password,
        public readonly string $role,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            first_name: $data['first_name'],
            last_name: $data['last_name'],
            dni: isset($data['dni']) && $data['dni'] !== '' ? (int) $data['dni'] : null,
            username: isset($data['username']) && $data['username'] !== '' ? $data['username'] : null,
            password: $data['password'],
            role: UserRole::from(strtolower($data['role'] ?? UserRole::ALUMNO->value))->value,
        );
    }
}