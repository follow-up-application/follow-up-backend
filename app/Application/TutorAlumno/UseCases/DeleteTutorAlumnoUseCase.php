<?php

namespace App\Application\TutorAlumno\UseCases;

use App\Domain\User\Repositories\TutorAlumnoRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

class DeleteTutorAlumnoUseCase
{
    public function __construct(
        private readonly TutorAlumnoRepositoryInterface $tutorAlumnoRepository,
    ) {}

    public function execute(string $id): void
    {
        $this->tutorAlumnoRepository->delete(new UserId($id));
    }
}
