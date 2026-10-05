<?php

namespace App\Application\Tutor\DTOs;

class StoreTutorDTO
{
    public function __construct(
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly int $dni,
        public readonly string $telephone,
        public readonly string $email,
        public readonly string $password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            first_name: $data['first_name'],
            last_name: $data['last_name'],
            dni: (int) $data['dni'],
            telephone: $data['telephone'],
            email: $data['email'],
            // Sin contraseña informada se usa el DNI, igual que el seeder.
            password: $data['password'] ?? (string) $data['dni'],
        );
    }
}