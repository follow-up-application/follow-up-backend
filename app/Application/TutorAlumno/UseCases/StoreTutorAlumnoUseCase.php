<?php

namespace App\Application\TutorAlumno\UseCases;

use App\Application\TutorAlumno\DTOs\StoreTutorAlumnoDTO;
use App\Domain\User\Entities\TutorAlumno;
use App\Domain\User\Repositories\TutorAlumnoRepositoryInterface;
use App\Domain\User\ValueObjects\CodigoInstitucional;
use App\Domain\User\ValueObjects\TutorAlumnoRelation;
use App\Domain\User\ValueObjects\UserId;

class StoreTutorAlumnoUseCase
{
    public function __construct(
        private readonly TutorAlumnoRepositoryInterface $tutorAlumnoRepository,
    ) {}

    public function execute(StoreTutorAlumnoDTO $dto): TutorAlumno
    {
        $tutorAlumno = new TutorAlumno(
            id: UserId::generate(),
            alumno_id: new UserId($dto->alumno_id),
            tutor_id: new UserId($dto->tutor_id),
            relationship: TutorAlumnoRelation::from($dto->relationship),
            otra_relacion: $dto->otra_relacion,
            codigo_institucional: new CodigoInstitucional,
        );

        $this->tutorAlumnoRepository->save($tutorAlumno);

        return $tutorAlumno;
    }
}
