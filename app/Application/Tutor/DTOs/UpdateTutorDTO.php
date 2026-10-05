<?php

namespace App\Application\Tutor\DTOs;

class UpdateTutorDTO
{
    public function __construct(
        public readonly ?string $first_name,
        public readonly ?string $last_name,
        public readonly ?string $telephone,
        public readonly ?string $email,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            first_name: $data['first_name'] ?? null,
            last_name: $data['last_name'] ?? null,
            telephone: $data['telephone'] ?? null,
            email: $data['email'] ?? null,
        );
    }

    public function isEmpty(): bool
    {
        return $this->first_name === null
            && $this->last_name === null
            && $this->telephone === null
            && $this->email === null;
    }
}