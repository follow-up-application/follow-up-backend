<?php

namespace App\Application\Alumno\DTOs;

class CreateAlumnoDTO
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly int $dni,
        public readonly string $fechaNacimiento,
        public readonly int $cursoDivisionTurnoId,
        public readonly string $relationship,
        public readonly ?string $otraRelacion,
        public readonly ?string $tutorId,
        public readonly ?string $tutorFirstName,
        public readonly ?string $tutorLastName,
        public readonly ?int $tutorDni,
        public readonly ?string $tutorTelephone,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            dni: (int) $data['dni'],
            fechaNacimiento: $data['fecha_nacimiento'],
            cursoDivisionTurnoId: (int) $data['curso_division_turno_id'],
            relationship: $data['relationship'],
            otraRelacion: $data['otra_relacion'] ?? null,
            tutorId: $data['tutor_id'] ?? null,
            tutorFirstName: $data['tutor_first_name'] ?? null,
            tutorLastName: $data['tutor_last_name'] ?? null,
            tutorDni: isset($data['tutor_dni']) ? (int) $data['tutor_dni'] : null,
            tutorTelephone: $data['tutor_telephone'] ?? null,
        );
    }
}
