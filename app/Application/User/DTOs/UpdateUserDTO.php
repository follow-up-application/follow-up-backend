<?php

namespace App\Application\User\DTOs;

class UpdateUserDTO
{
    public function __construct(
        public readonly ?string $first_name,
        public readonly ?string $last_name,
        public readonly ?int $dni,
        public readonly ?string $username,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            first_name: $data['first_name'] ?? null,
            last_name: $data['last_name'] ?? null,
            dni: isset($data['dni']) && $data['dni'] !== '' ? (int) $data['dni'] : null,
            username: $data['username'] ?? null,
        );
    }

    public function isEmpty(): bool
    {
        return $this->first_name === null
            && $this->last_name === null
            && $this->dni === null
            && $this->username === null;
    }
}
