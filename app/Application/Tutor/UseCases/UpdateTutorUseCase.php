<?php

namespace App\Application\Tutor\UseCases;

use App\Application\Tutor\DTOs\UpdateTutorDTO;
use App\Domain\User\Entities\Tutor;
use App\Domain\User\Exceptions\TutorNoEncontradoException;
use App\Domain\User\Repositories\TutorRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

class UpdateTutorUseCase
{
    public function __construct(
        private readonly TutorRepositoryInterface $tutorRepository,
    ) {}

    public function execute(string $id, UpdateTutorDTO $dto): Tutor
    {
        $tutor = $this->tutorRepository->findById(new UserId($id));

        if ($tutor === null) {
            throw new TutorNoEncontradoException("No se encontró el tutor {$id}.");
        }

        if ($dto->first_name !== null || $dto->last_name !== null) {
            $tutor->updateUser(
                $dto->first_name ?? $tutor->firstName(),
                $dto->last_name ?? $tutor->lastName(),
                $tutor->dni(),
            );
        }

        if ($dto->telephone !== null) {
            $tutor->setTelephone($dto->telephone);
        }

        if ($dto->email !== null) {
            // setEmail() actualiza también el username, que es con lo que
            // el tutor inicia sesión.
            $tutor->setEmail($dto->email);
        }

        return $this->tutorRepository->save($tutor);
    }
}