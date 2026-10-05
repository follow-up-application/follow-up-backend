<?php

namespace App\Application\Tutor\UseCases;

use App\Application\Tutor\DTOs\StoreTutorDTO;
use App\Domain\User\Entities\Tutor;
use App\Domain\User\Repositories\TutorRepositoryInterface;
use App\Domain\User\ValueObjects\UserEmail;
use App\Domain\User\ValueObjects\UserId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StoreTutorUseCase
{
    public function __construct(
        private readonly TutorRepositoryInterface $tutorRepository,
    ) {}

    /**
     * Alta directa del tutor, sin pasar por el pre-registro del alumno.
     * El caso de uso se reserva al administrador; el alta del alumno sigue
     * pasando por RegisterTutorUseCase.
     */
    public function execute(StoreTutorDTO $dto): Tutor
    {
        return DB::transaction(function () use ($dto) {
            $tutor = new Tutor(
                id: UserId::generate(),
                first_name: $dto->first_name,
                last_name: $dto->last_name,
                dni: $dto->dni,
                telephone: $dto->telephone,
                email: new UserEmail($dto->email),
            );

            // El constructor delega el username en el email, pero la entidad
            // nace sin hash: se define acá la contraseña inicial.
            $tutor->updatePassword(Hash::make($dto->password), true);

            // save() es un upsert: crea users + tutores con el mismo id.
            return $this->tutorRepository->save($tutor);
        });
    }
}