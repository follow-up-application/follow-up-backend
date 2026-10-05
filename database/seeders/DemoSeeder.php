<?php

namespace Database\Seeders;

use App\Application\Admin\DTOs\CreateAdminDTO;
use App\Application\Admin\UseCases\CreateAdminUseCase;
use App\Application\Alumno\DTOs\CreateAlumnoDTO;
use App\Application\Alumno\UseCases\CreateAlumnoUseCase;
use App\Application\CursoDivisionTurno\DTOs\CreateCDTdto;
use App\Application\CursoDivisionTurno\UseCases\CreateCDTUseCase;
use App\Application\Docente\DTOs\CreateDocenteDTO;
use App\Application\Docente\UseCases\CreateDocenteUseCase;
use App\Application\Inscripcion\DTOs\CambiarEstadoInscripcionDTO;
use App\Application\Inscripcion\DTOs\CreateInscripcionDTO;
use App\Application\Inscripcion\UseCases\CambiarEstadoInscripcionUseCase;
use App\Application\Inscripcion\UseCases\CreateInscripcionUseCase;
use App\Application\Materia\DTOs\CreateMateriaDTO;
use App\Application\Materia\UseCases\CreateMateriaUseCase;
use App\Domain\User\ValueObjects\EstadoInscripcion;
use App\Domain\User\ValueObjects\UserRole;
use App\Infrastructure\User\Persistence\CursoDivisionTurnoModel;
use App\Infrastructure\User\Persistence\DocenteModel;
use App\Infrastructure\User\Persistence\MateriaModel;
use App\Infrastructure\User\Persistence\TutorAlumnoModel;
use App\Infrastructure\User\Persistence\TutorModel;
use App\Infrastructure\User\Persistence\UserModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    private array $cdtIds = [];

    private array $docentes = [];

    private array $credenciales = [];

    private array $vinculos = [];

    private array $materias = [];

    private array $inscripciones = [];

    public function run(): void
    {
        $this->seedCursos();
        $this->seedAdmin();
        $this->seedDocentes();
        $this->seedMaterias();
        $this->seedAlumnos();
        $this->seedInscripciones();

        $this->imprimirResumen();
    }

    private function seedCursos(): void
    {
        $useCase = app(CreateCDTUseCase::class);

        $cursos = [
            ['curso' => 1, 'division' => 'A', 'turno' => 'Mañana'],
            ['curso' => 1, 'division' => 'B', 'turno' => 'Tarde'],
            ['curso' => 2, 'division' => 'A', 'turno' => 'Mañana'],
            ['curso' => 2, 'division' => 'B', 'turno' => 'Tarde'],
            ['curso' => 3, 'division' => 'A', 'turno' => 'Mañana'],
        ];

        foreach ($cursos as $c) {
            $key = $this->cdtKey($c['curso'], $c['division'], $c['turno']);

            if (isset($this->cdtIds[$key])) {
                continue;
            }

            $existente = CursoDivisionTurnoModel::where('curso', $c['curso'])
                ->where('division', $c['division'])
                ->where('turno', $c['turno'])
                ->first();

            if ($existente) {
                $this->cdtIds[$key] = $existente->id;

                continue;
            }

            $cdt = $useCase->execute(new CreateCDTdto(
                curso: $c['curso'],
                division: $c['division'],
                turno: $c['turno'],
                capacidadMaxima: 30,
            ));

            $this->cdtIds[$key] = $cdt->id();
        }
    }

    private function seedAdmin(): void
    {
        $usuario = 'admin';
        $password = 'Admin123!';

        if (UserModel::where('username', $usuario)->exists()) {
            $this->credenciales[] = [
                'rol' => 'Administrador (ya existente)',
                'usuario' => $usuario,
                'contraseña' => $password,
            ];

            return;
        }

        $useCase = app(CreateAdminUseCase::class);

        $admin = $useCase->execute(new CreateAdminDTO(
            first_name: 'Carlos',
            last_name: 'Rodríguez',
            username: $usuario,
            password: $password,
        ));

        $this->credenciales[] = [
            'rol' => 'Administrador',
            'usuario' => $admin->username(),
            'contraseña' => $password,
        ];
    }

    private function seedDocentes(): void
    {
        $useCase = app(CreateDocenteUseCase::class);

        $datos = [
            ['Gabriela', 'Fernández', 27555111, '3512345678', '15/03/2024', $this->cdtKey(1, 'A', 'Mañana')],
            ['Pablo', 'Martínez', 30123456, '3518765432', '01/02/2023', $this->cdtKey(2, 'A', 'Mañana')],
            ['Laura', 'Álvarez', 32987654, '3419988776', '10/04/2025', $this->cdtKey(2, 'B', 'Tarde')],
            ['Jorge', 'Medina', 24112233, '3854455667', '05/03/2022', $this->cdtKey(3, 'A', 'Mañana')],
        ];

        foreach ($datos as $d) {
            $existente = UserModel::where('dni', $d[2])
                ->where('role', UserRole::DOCENTE->value)
                ->first();

            if ($existente) {
                $docenteModel = DocenteModel::where('id', $existente->id)->first();

                $this->docentes[$d[2]] = [
                    'id' => $existente->id,
                    'firstName' => $existente->first_name,
                    'lastName' => $existente->last_name,
                ];

                $this->credenciales[] = [
                    'rol' => 'Docente ('.$existente->first_name.' '.$existente->last_name.') (ya existente)',
                    'usuario' => $existente->username,
                    'contraseña' => (string) $d[2],
                ];

                continue;
            }

            $docente = $useCase->execute(new CreateDocenteDTO(
                firstName: $d[0],
                lastName: $d[1],
                dni: $d[2],
                telephone: $d[3],
                fechaIngreso: $d[4],
                cursoDivisionTurnoId: $this->cdtIds[$d[5]],
            ));

            $this->docentes[$d[2]] = [
                'id' => $docente->id()->value(),
                'firstName' => $docente->firstName(),
                'lastName' => $docente->lastName(),
            ];

            $this->credenciales[] = [
                'rol' => 'Docente ('.$docente->firstName().' '.$docente->lastName().')',
                'usuario' => $docente->username(),
                'contraseña' => (string) $d[2],
            ];
        }
    }

    private function seedMaterias(): void
    {
        $useCase = app(CreateMateriaUseCase::class);

        // [nombre, descripcion, cdtKey, dniDocente]
        $datos = [
            ['Matemática', 'Álgebra y análisis matemático.', $this->cdtKey(1, 'A', 'Mañana'), 27555111],
            ['Lengua y Literatura', 'Lectura, escritura y análisis de textos.', $this->cdtKey(1, 'A', 'Mañana'), 30123456],
            ['Ciencias Sociales', 'Historia, geografía y formación ciudadana.', $this->cdtKey(2, 'A', 'Mañana'), 30123456],
            ['Inglés', 'Nivel inicial e intermedio de inglés.', $this->cdtKey(2, 'B', 'Tarde'), 32987654],
            ['Tecnología de la Información', 'Ofimática y herramientas digitales.', $this->cdtKey(2, 'B', 'Tarde'), 32987654],
            ['Educación Física', 'Actividades deportivas y recreativas.', $this->cdtKey(3, 'A', 'Mañana'), 24112233],
        ];

        foreach ($datos as $d) {
            $cdtId = $this->cdtIds[$d[2]];

            $existente = MateriaModel::where('nombre', $d[0])
                ->where('curso_division_turno_id', $cdtId)
                ->first();

            $docente = $this->docentes[$d[3]];

            $materia = $existente ?? $useCase->execute(new CreateMateriaDTO(
                nombre: $d[0],
                descripcion: $d[1],
                cursoDivisionTurnoId: $cdtId,
                docenteId: $docente['id'],
            ));

            $this->materias[] = [
                'id' => $materia->id(),
                'nombre' => $materia->nombre(),
                'cdt' => $d[2],
                'docente' => $docente['firstName'].' '.$docente['lastName'],
            ];
        }
    }

    private function seedInscripciones(): void
    {
        $useCase = app(CreateInscripcionUseCase::class);
        $useCaseEstado = app(CambiarEstadoInscripcionUseCase::class);

        // Un alumno se inscribe en las materias de su propio curso; el estado lo define el docente.
        // [dniAlumno, indiceMateria, estado]
        $datos = [
            [46111222, 0, EstadoInscripcion::APROBADO],
            [46111222, 1, EstadoInscripcion::APROBADO],
            [46111222, 2, EstadoInscripcion::PENDIENTE],
            [47222333, 0, EstadoInscripcion::APROBADO],
            [47222333, 1, EstadoInscripcion::RECHAZADO],
            [48444555, 3, EstadoInscripcion::APROBADO],
            [48444555, 4, EstadoInscripcion::APROBADO],
            [48444555, 5, EstadoInscripcion::PENDIENTE],
            [49566777, 0, EstadoInscripcion::PENDIENTE],
            [49566777, 2, EstadoInscripcion::PENDIENTE],
            [50888999, 3, EstadoInscripcion::APROBADO],
            [51999000, 5, EstadoInscripcion::APROBADO],
        ];

        foreach ($datos as $d) {
            $alumno = UserModel::where('dni', $d[0])
                ->where('role', UserRole::ALUMNO->value)
                ->first();

            if ($alumno === null || !isset($this->materias[$d[1]])) {
                continue;
            }

            $materia = $this->materias[$d[1]];

            $inscripcion = $useCase->execute(new CreateInscripcionDTO(
                alumnoId: $alumno->id,
                materiaId: $materia['id'],
            ));

            if ($d[2] !== EstadoInscripcion::PENDIENTE) {
                $useCaseEstado->execute(new CambiarEstadoInscripcionDTO(
                    inscripcionId: $inscripcion->id(),
                    estado: $d[2]->value,
                ));
            }

            $this->inscripciones[] = $alumno->first_name.' '.$alumno->last_name
                .' → '.$materia['nombre'].' ('.$materia['cdt'].') - '.$d[2]->label();
        }
    }

    private function seedAlumnos(): void
    {
        $useCase = app(CreateAlumnoUseCase::class);

        // [alumno first, last, dni, fechaNac, cdtKey, relationship, otraRel, tutorFirst, tutorLast, tutorDni, tutorTel, registrarTutor]
        $datos = [
            ['Ana', 'López', 46111222, '05/06/2012', $this->cdtKey(1, 'A', 'Mañana'), 'madre', null, 'Marta', 'López', 20111222, '3512001111', true],
            ['Bruno', 'Pérez', 47222333, '12/11/2011', $this->cdtKey(1, 'A', 'Mañana'), 'padre', null, 'Roberto', 'Pérez', 22333444, '3512002222', true],
            ['Carla', 'Suárez', 48444555, '03/09/2012', $this->cdtKey(1, 'B', 'Tarde'), 'madre', null, 'Inés', 'Suárez', 23444555, '3512003333', true],
            ['Diego', 'García', 49566777, '20/02/2011', $this->cdtKey(2, 'A', 'Mañana'), 'otra', 'Tutor legal', 'Osvaldo', 'García', 25555666, '3512004444', true],
            ['Ema', 'Torres', 50888999, '14/07/2011', $this->cdtKey(2, 'B', 'Tarde'), 'madre', null, 'Patricia', 'Torres', 26666777, '3512005555', true],
            ['Fidel', 'Herrera', 51999000, '25/10/2012', $this->cdtKey(3, 'A', 'Mañana'), 'padre', null, 'Carlos', 'Herrera', 27777888, '3512006666', false],
        ];

        foreach ($datos as $d) {
            $alumnoUser = UserModel::where('dni', $d[2])
                ->where('role', UserRole::ALUMNO->value)
                ->first();

            if ($alumnoUser) {
                $vinculo = TutorAlumnoModel::where('alumno_id', $alumnoUser->id)->first();

                $this->vinculos[$alumnoUser->username] = [
                    'alumno' => $alumnoUser->first_name.' '.$alumnoUser->last_name,
                    'curso' => $d[4],
                    'codigo' => $vinculo?->codigo_institucional,
                    'tutor' => $d[7].' '.$d[8],
                    'tutorDni' => $d[9],
                    'tutorRegistrado' => $d[11],
                ];

                $this->credenciales[] = [
                    'rol' => 'Alumno ('.$alumnoUser->first_name.' '.$alumnoUser->last_name.') (ya existente)',
                    'usuario' => $alumnoUser->username,
                    'contraseña' => (string) $d[2],
                ];

                $this->credenciales[] = $this->registrarODetectarTutor($d[7], $d[8], $d[9], $d[11]);

                continue;
            }

            $alumno = $useCase->execute(new CreateAlumnoDTO(
                firstName: $d[0],
                lastName: $d[1],
                dni: $d[2],
                fechaNacimiento: $d[3],
                cursoDivisionTurnoId: $this->cdtIds[$d[4]],
                relationship: $d[5],
                otraRelacion: $d[6],
                tutorFirstName: $d[7],
                tutorLastName: $d[8],
                tutorDni: $d[9],
                tutorTelephone: $d[10],
            ));

            $vinculo = TutorAlumnoModel::where('alumno_id', $alumno->id()->value())->first();

            $this->vinculos[$alumno->username()] = [
                'alumno' => $alumno->firstName().' '.$alumno->lastName(),
                'curso' => $d[4],
                'codigo' => $vinculo?->codigo_institucional,
                'tutor' => $d[7].' '.$d[8],
                'tutorDni' => $d[9],
                'tutorRegistrado' => $d[11],
            ];

            $this->credenciales[] = [
                'rol' => 'Alumno ('.$alumno->firstName().' '.$alumno->lastName().')',
                'usuario' => $alumno->username(),
                'contraseña' => (string) $d[2],
            ];

            $this->credenciales[] = $this->registrarODetectarTutor($d[7], $d[8], $d[9], $d[11]);
        }
    }

    private function registrarODetectarTutor(string $firstName, string $lastName, int $dni, bool $registrar): array
    {
        $user = UserModel::where('dni', $dni)->first();

        if ($registrar && $user !== null && $user->username !== null) {
            return [
                'rol' => 'Tutor ('.$firstName.' '.$lastName.') (ya registrado)',
                'usuario' => $user->username,
                'contraseña' => (string) $dni,
            ];
        }

        if (! $registrar) {
            return [
                'rol' => 'Tutor ('.$firstName.' '.$lastName.') - pendiente de registro',
                'usuario' => 'Debe registrarse con su código',
                'contraseña' => 'MIRAR código institucional abajo',
            ];
        }

        if ($user === null) {
            throw new \RuntimeException("No se encontró el tutor pre-registrado con DNI {$dni}");
        }

        $email = 'tutor'.$dni.'@demo.com';

        $user->username = $email;
        $user->password = Hash::make((string) $dni);
        $user->must_change_password = false;
        $user->save();

        $tutorModel = TutorModel::find($user->id);
        $tutorModel->email = $email;
        $tutorModel->save();

        return [
            'rol' => 'Tutor ('.$firstName.' '.$lastName.')',
            'usuario' => $email,
            'contraseña' => (string) $dni,
        ];
    }

    private function cdtKey(int $curso, string $division, string $turno): string
    {
        return $curso.'-'.$division.'-'.$turno;
    }

    private function imprimirResumen(): void
    {
        $line = str_repeat('-', 72);

        echo "\n$line\n";
        echo "FollowUp DemoSeeder - credenciales de acceso\n";
        echo "$line\n";

        foreach ($this->credenciales as $c) {
            echo "- [{$c['rol']}] usuario: {$c['usuario']} | contraseña: {$c['contraseña']}\n";
        }

        echo "$line\n";
        echo "Vínculos tutor-alumno (códigos institucionales):\n";

        foreach ($this->vinculos as $v) {
            $estado = $v['tutorRegistrado'] ? 'tutor ya registrado' : 'código activo para registro';
            echo "- {$v['alumno']} ({$v['curso']}) | tutor: {$v['tutor']} | código: {$v['codigo']} ($estado)\n";
        }

        echo "$line\n";
        echo "Materias por curso división turno:\n";

        foreach ($this->materias as $materia) {
            echo "- {$materia['nombre']} ({$materia['cdt']}) - {$materia['docente']}\n";
        }

        echo "$line\n";
        echo "Inscripciones:\n";

        foreach ($this->inscripciones as $inscripcion) {
            echo "- {$inscripcion}\n";
        }

        echo "$line\n";
    }
}
