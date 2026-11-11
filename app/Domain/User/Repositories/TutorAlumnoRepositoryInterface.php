<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\TutorAlumno;
use App\Domain\User\ValueObjects\UserId;

interface TutorAlumnoRepositoryInterface
{
    public function save(TutorAlumno $tutorAlumno): void;

    public function findById(UserId $id): ?TutorAlumno;

    /**
     * Busca un pre-registro sin tutor vinculado todavía, por alumno + DNI del tutor.
     * Usado en el registro real del tutor para matchear con lo pre-cargado.
     */
    public function findPreRegistroPendiente(string $codigoInstitucional): ?TutorAlumno;

    /** @return TutorAlumno[] */
    public function findByAlumnoId(UserId $alumnoId): array;

    /** @return TutorAlumno[] */
    public function findByTutorId(UserId $tutorId): array;

    public function delete(UserId $id): void;
}
