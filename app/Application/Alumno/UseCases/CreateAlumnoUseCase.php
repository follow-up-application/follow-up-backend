<?php

namespace App\Application\Alumno\UseCases;

use App\Application\Alumno\DTOs\CreateAlumnoDTO as DTOsCreateAlumnoDTO;
use App\Domain\Shared\ValueObjects\FechaFormateada;
use App\Domain\User\Entities\Alumno;
use App\Domain\User\Entities\CursoDivisionTurno;
use App\Domain\User\Entities\Tutor;
use App\Domain\User\Entities\TutorAlumno;
use App\Domain\User\Exceptions\CDTNoEncontradoException;
use App\Domain\User\Exceptions\TutorNoEncontradoException;
use App\Domain\User\Repositories\AlumnoRepositoryInterface;
use App\Domain\User\Repositories\CursoDivisionTurnoRepositoryInterface;
use App\Domain\User\Repositories\TutorAlumnoRepositoryInterface;
use App\Domain\User\Repositories\TutorRepositoryInterface;
use App\Domain\User\ValueObjects\CodigoInstitucional;
use App\Domain\User\ValueObjects\TutorAlumnoRelation;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\UserName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateAlumnoUseCase
{
    public function __construct(
        private readonly AlumnoRepositoryInterface $alumnoRepository,
        private readonly CursoDivisionTurnoRepositoryInterface $cursoDivisionTurnoRepository,
        private readonly TutorRepositoryInterface $tutorRepository,
        private readonly TutorAlumnoRepositoryInterface $tutorAlumnoRepository,
    ) {}

    public function execute(DTOsCreateAlumnoDTO $dto): Alumno
    {
        return DB::transaction(function () use ($dto) {
            $cdt = $this->cursoDivisionTurnoRepository->findById($dto->cursoDivisionTurnoId);

            if ($cdt === null) {
                throw new CDTNoEncontradoException($dto->cursoDivisionTurnoId);
            }

            $tutor = $this->resolverTutor($dto);

            if ($tutor === null) {
                throw new TutorNoEncontradoException;
            }

            $alumno = $this->buildAlumno($dto, $cdt);
            $this->alumnoRepository->save($alumno);

            $this->tutorAlumnoRepository->save(new TutorAlumno(
                id: UserId::generate(),
                alumno_id: $alumno->id(),
                tutor_id: $tutor->id(),
                relationship: TutorAlumnoRelation::from($dto->relationship),
                otra_relacion: $dto->otraRelacion,
                codigo_institucional: new CodigoInstitucional,
            ));

            return $alumno;
        });
    }

    private function buildAlumno(DTOsCreateAlumnoDTO $dto, CursoDivisionTurno $cdt): Alumno
    {
        $anioIngreso = (int) date('Y');

        return new Alumno(
            id: UserId::generate(),
            first_name: $dto->firstName,
            last_name: $dto->lastName,
            dni: $dto->dni,
            password: Hash::make((string) $dto->dni), // 💡 password inicial = DNI
            username: new UserName('EST', $dto->dni, $anioIngreso),
            fecha_nacimiento: new FechaFormateada($dto->fechaNacimiento),
            curso_division_turno: $cdt,
        );
    }

    private function resolverTutor(DTOsCreateAlumnoDTO $dto): ?Tutor
    {
        if ($dto->tutorId !== null) {
            return $this->tutorRepository->findById(new UserId($dto->tutorId));
        }

        if ($dto->tutorDni !== null) {
            $existente = $this->tutorRepository->findByDni($dto->tutorDni);

            if ($existente !== null) {
                return $existente;
            }
        }

        if ($dto->tutorFirstName !== null) {
            return $this->crearNuevoTutor($dto);
        }

        return null;
    }

    private function crearNuevoTutor(DTOsCreateAlumnoDTO $dto): Tutor
    {
        $tutor = new Tutor(
            id: UserId::generate(),
            first_name: $dto->tutorFirstName,
            last_name: $dto->tutorLastName,
            dni: (int) $dto->tutorDni,
            telephone: $dto->tutorTelephone,
            email: null,
        );

        $this->tutorRepository->save($tutor);

        return $tutor;
    }
}
