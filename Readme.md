# follow-up-backend

API REST desarrollada con **Laravel 13** siguiendo **arquitectura hexagonal**, como proyecto universitario para Programación III — UTN.

El sistema gestiona usuarios con distintos roles (alumnos, docentes, tutores y administradores), cursos/divisiones/turnos, materias, inscripciones y el vínculo entre tutores y alumnos.

## Stack

- **PHP 8.3+** (CI prueba 8.3, 8.4 y 8.5; la imagen Docker usa 8.4)
- **Laravel 13**
- **PostgreSQL** (Supabase en producción)
- **Laravel Sanctum** — autenticación stateless vía tokens Bearer
- **ramsey/uuid** — UUIDs como primary keys
- **Pest 4** — tests unitarios y de feature
- **Laravel Pint** — estilo de código
- **Docker** — imagen de despliegue con Nginx + Supervisor

---

## Estructura del proyecto

```
app/
├── Application/              # Casos de uso y DTOs
│   ├── Admin/
│   ├── Alumno/
│   ├── CursoDivisionTurno/
│   ├── Docente/
│   ├── Inscripcion/
│   ├── Materia/
│   ├── Tutor/
│   ├── TutorAlumno/
│   └── User/
├── Domain/                   # Entidades, interfaces, value objects y excepciones
│   ├── Shared/
│   │   └── ValueObjects/
│   └── User/
│       ├── Entities/
│       ├── Exceptions/
│       ├── Repositories/
│       └── ValueObjects/
├── Infrastructure/           # Implementaciones Eloquent y providers
│   └── User/
│       ├── Persistence/
│       │   └── Eloquent/
│       └── Providers/
└── Interfaces/               # Controllers, Requests, Resources y middleware
    └── Http/
        ├── Middleware/
        └── User/
            ├── Controllers/
            ├── Requests/
            └── Resources/
```

---

## Modelo de dominio

La tabla `users` concentra los datos comunes de todos los roles. Las tablas `alumnos`, `docentes` y `tutores` son especializaciones 1:1 que comparten el `id` con `users`.

```
users
├── id (uuid)
├── first_name
├── last_name
├── dni (nullable, unique)
├── username (nullable, unique)
├── password (nullable)
├── role (alumno | docente | tutor | administrador)
├── must_change_password
└── timestamps + softDeletes

alumnos (1:1 users)
├── id (uuid, FK users.id)
├── fecha_nacimiento
└── curso_division_turno_id (FK)

docentes (1:1 users)
├── id (uuid, FK users.id)
├── telephone
├── fecha_ingreso
└── curso_division_turno_id (FK)

tutores (1:1 users)
├── id (uuid, FK users.id)
├── telephone
└── email (nullable, unique)

curso_division_turno
├── id
├── curso (1..7)
├── division (A..Z)
├── turno (Mañana | Tarde)
├── capacidad_maxima (default 30)
└── UNIQUE(curso, division, turno)

materias
├── id
├── nombre
├── descripcion
├── curso_division_turno_id (FK)
├── docente_id (uuid, FK)
└── UNIQUE(nombre, curso_division_turno_id)

inscripciones (N:N alumno ↔ materia)
├── id
├── alumno_id (uuid, FK)
├── materia_id (FK)
├── fecha_inscripcion
├── estado (pendiente | aprobado | rechazado)
└── UNIQUE(alumno_id, materia_id)

tutor_alumno (N:N tutor ↔ alumno)
├── id (uuid)
├── alumno_id (uuid, FK)
├── tutor_id (uuid, FK)
├── relationship (padre | madre | otra)
├── otra_relacion (nullable)
├── codigo_institucional (autogenerado: ALU-XXXXXX-XXXXXX)
└── UNIQUE(alumno_id, tutor_id)
```

### Bajas lógicas

No se permite la eliminación física de registros. Todos los recursos usan **soft deletes** y exponen un endpoint `PATCH .../{id}/inhabilitar` que marca el registro como eliminado (`deleted_at`).

---

## Roles disponibles

Los roles se manejan con un PHP Enum (`UserRole`) en el Domain layer.

| Value (enviar en requests) | Label |
|---|---|
| `alumno` | Alumno |
| `docente` | Docente |
| `tutor` | Tutor |
| `administrador` | Administrador |

### Otros enums del dominio

| Enum | Valores |
|---|---|
| `EstadoInscripcion` | `pendiente`, `aprobado`, `rechazado` |
| `TurnoEscolar` | `Mañana`, `Tarde` |
| `TutorAlumnoRelation` | `padre`, `madre`, `otra` |

---

## Instalación

### 1. Clonar el repositorio

```bash
git clone <repo-url>
cd follow-up-backend
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar variables de entorno

```bash
cp .env.example .env
```

Editá `.env` con tus credenciales de PostgreSQL/Supabase:

```env
APP_NAME=follow-up-backend
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=db.<tu-proyecto>.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=<tu-password>

SANCTUM_STATEFUL_DOMAINS=localhost
```

### 4. Generar clave de aplicación

```bash
php artisan key:generate
```

### 5. Ejecutar migraciones y seeders

```bash
php artisan migrate
php artisan db:seed
```

O en un solo paso (reinicia la base):

```bash
php artisan migrate:fresh --seed
```

Las migraciones crean las tablas:

- `users`, `alumnos`, `docentes`, `tutores`
- `curso_division_turno`, `materias`, `inscripciones`, `tutor_alumno`
- `personal_access_tokens`, `password_reset_tokens`, `sessions`

El `DemoSeeder` carga cursos, docentes, materias, alumnos, tutores e inscripciones de ejemplo, e imprime por consola las credenciales generadas. Datos destacados:

| Rol | Usuario | Contraseña |
|---|---|---|
| Administrador | `admin` | `Admin123!` |
| Docente | `DOC-<dni>-<año>` | `<dni>` |
| Alumno | `EST-<dni>-<año>` | `<dni>` |
| Tutor registrado | `tutor<dni>@demo.com` | `<dni>` |

Los tutores que quedan pendientes de registro deben usar el `codigo_institucional` del vínculo para registrarse.

### 6. Levantar el servidor

```bash
php artisan serve
```

La API queda disponible en `http://127.0.0.1:8000/api/v1`.

---

## Extensiones PHP requeridas

Verificá que estén habilitadas en `php.ini`:

```
extension=pdo_pgsql
extension=fileinfo
extension=zip
```

---

## Docker

El proyecto incluye una imagen lista para desplegar con Nginx y Supervisor:

```bash
docker build -t follow-up-backend .
docker run -p 8080:8080 --env-file .env follow-up-backend
```

- La imagen expone el puerto `8080` (configurable con `PORT`).
- **No ejecuta migraciones automáticamente**; hay que correrlas manualmente contra la base configurada.
- Archivos de configuración en `docker/` (`nginx.conf`, `supervisord.conf`, `entrypoint.sh`).

---

## Endpoints

Base URL: `/api/v1`

🔒 = requiere header `Authorization: Bearer <token>`.

### Autenticación y perfil

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| `POST` | `/login` | Público | Inicia sesión y devuelve un token |
| `POST` | `/logout` | 🔒 Todos | Elimina el token actual |
| `PUT` | `/password` | 🔒 Todos | Cambia la contraseña del usuario autenticado |
| `GET` | `/users/me` | 🔒 Todos | Datos del usuario autenticado |

#### `POST /api/v1/login`

El login es por **username** (no por email).

```json
{
    "username": "admin",
    "password": "Admin123!"
}
```

**Respuesta `200`:**

```json
{
    "token": "1|abc123...",
    "rol": "administrador",
    "first_name": "Carlos",
    "last_name": "Rodríguez",
    "username": "admin"
}
```

> Nota: la respuesta de login usa la clave `rol`, mientras que los recursos usan `role`.

#### `PUT /api/v1/password`

```json
{
    "password": "NuevaClave123",
    "password_confirmation": "NuevaClave123"
}
```

`password_confirmation` es opcional, pero si se envía debe coincidir.

---

### Usuarios (CRUD base)

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| `POST` | `/users` | Público | Registra un usuario base |
| `GET` | `/users` | 🔒 Admin | Lista usuarios |
| `GET` | `/users/{id}` | 🔒 Admin | Obtiene un usuario |
| `PUT` | `/users/{id}` | 🔒 Admin | Actualiza un usuario |
| `PATCH` | `/users/{id}/inhabilitar` | 🔒 Admin | Baja lógica |

**Body de creación:**

```json
{
    "first_name": "Pablo",
    "last_name": "Ramirez",
    "dni": 30123456,
    "username": "pablo.ramirez",
    "password": "12345678",
    "role": "alumno"
}
```

`dni` y `username` son opcionales pero únicos. `role` es opcional (por defecto `alumno`).

**Respuesta (UserResource):**

```json
{
    "id": "aa758b77-0f8d-42a8-973d-4aa1a5a40384",
    "firstName": "Pablo",
    "lastName": "Ramirez",
    "role": "alumno"
}
```

> Nota: `UserResource` devuelve `firstName`/`lastName` en camelCase; el resto de los recursos usan snake_case.

---

### Alumnos

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| `POST` | `/alumnos` | 🔒 Admin, Docente | Crea un alumno (y opcionalmente su tutor) |
| `GET` | `/alumnos` | 🔒 Admin, Docente | Lista alumnos |
| `GET` | `/alumnos/{id}` | 🔒 Admin, Docente, Tutor, Alumno | Detalle del alumno |
| `PUT/PATCH` | `/alumnos/{id}` | 🔒 Admin, Docente | Actualiza un alumno |
| `PATCH` | `/alumnos/{id}/inhabilitar` | 🔒 Admin, Docente | Baja lógica |
| `GET` | `/alumnos/{id}/inscripciones` | 🔒 Admin, Alumno | Inscripciones del alumno |
| `GET` | `/alumnos/{id}/tutores` | 🔒 Admin, Tutor, Alumno | Tutores del alumno |

**Body de creación:**

```json
{
    "first_name": "Ana",
    "last_name": "López",
    "dni": 46111222,
    "fecha_nacimiento": "05/06/2012",
    "curso_division_turno_id": 1,
    "relationship": "madre",
    "otra_relacion": null,
    "tutor_id": null,
    "tutor_first_name": "Marta",
    "tutor_last_name": "López",
    "tutor_dni": 20111222,
    "tutor_telephone": "3512001111"
}
```

- Se puede vincular un tutor existente con `tutor_id` (UUID).
- Si no se informa `tutor_id`, se busca al tutor por `tutor_dni`; si no existe, se crea con los datos `tutor_*`.
- `otra_relacion` es obligatorio cuando `relationship` es `otra`.
- Las fechas aceptan `dd/mm/yyyy` o `yyyy-mm-dd`.

**Respuesta (AlumnoResource):**

```json
{
    "id": "aa758b77-0f8d-42a8-973d-4aa1a5a40384",
    "first_name": "Ana",
    "last_name": "López",
    "dni": 46111222,
    "username": "EST-46111222-2026",
    "curso_division": "1°A - Mañana",
    "fecha_nacimiento": "05/06/2012",
    "role": "alumno"
}
```

---

### Docentes

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| `POST` | `/docentes` | 🔒 Admin | Crea un docente |
| `GET` | `/docentes` | 🔒 Admin | Lista docentes |
| `GET` | `/docentes/{id}` | 🔒 Admin | Detalle del docente |
| `PUT/PATCH` | `/docentes/{id}` | 🔒 Admin | Actualiza un docente |
| `PATCH` | `/docentes/{id}/inhabilitar` | 🔒 Admin | Baja lógica |
| `GET` | `/docentes/{id}/materias` | 🔒 Admin, Docente | Materias a cargo |

**Body de creación:**

```json
{
    "first_name": "Gabriela",
    "last_name": "Fernández",
    "dni": 27555111,
    "telephone": "3512345678",
    "fecha_ingreso": "15/03/2024",
    "curso_division_turno_id": 1
}
```

**Respuesta (DocenteResource):**

```json
{
    "id": "aa758b77-0f8d-42a8-973d-4aa1a5a40384",
    "first_name": "Gabriela",
    "last_name": "Fernández",
    "dni": 27555111,
    "telephone": "3512345678",
    "username": "DOC-27555111-2024",
    "fecha_ingreso": "15/03/2024"
}
```

---

### Tutores

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| `POST` | `/tutores` | 🔒 Admin | Alta directa de tutor |
| `GET` | `/tutores` | 🔒 Admin | Lista tutores |
| `GET` | `/tutores/{id}` | 🔒 Admin, Tutor | Detalle del tutor |
| `PUT/PATCH` | `/tutores/{id}` | 🔒 Admin | Actualiza un tutor |
| `PATCH` | `/tutores/{id}/inhabilitar` | 🔒 Admin | Baja lógica |
| `GET` | `/tutores/dni/{dni}` | 🔒 Admin, Tutor | Busca por DNI |
| `GET` | `/tutores/{id}/alumnos` | 🔒 Admin, Tutor | Alumnos a cargo |
| `GET` | `/tutores/validarCodigo/{codigo}` | Público | Valida un código institucional |
| `POST` | `/tutores/register` | Público | Registro de tutor con código |

**Alta directa por admin:**

```json
{
    "first_name": "Marta",
    "last_name": "López",
    "dni": 20111222,
    "telephone": "3512001111",
    "email": "marta@mail.com",
    "password": "12345678"
}
```

`password` es opcional; si no se informa se usa el DNI como contraseña inicial.

**Registro público de tutor:**

```json
{
    "codigo_institucional": "ALU-A1B2C3-D4E5F6",
    "email": "marta@mail.com",
    "password": "12345678"
}
```

**Respuesta (TutorResource):**

```json
{
    "id": "aa758b77-0f8d-42a8-973d-4aa1a5a40384",
    "first_name": "Marta",
    "last_name": "López",
    "dni": 20111222,
    "telephone": "3512001111",
    "email": "marta@mail.com"
}
```

---

### Vínculos Tutor-Alumno

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| `POST` | `/tutor-alumno` | 🔒 Admin | Crea un vínculo tutor-alumno |
| `PATCH` | `/tutor-alumno/{id}/inhabilitar` | 🔒 Admin | Baja lógica del vínculo |

**Body:**

```json
{
    "alumno_id": "aa758b77-0f8d-42a8-973d-4aa1a5a40384",
    "tutor_id": "bb758b77-0f8d-42a8-973d-4aa1a5a40384",
    "relationship": "padre",
    "otra_relacion": null
}
```

**Respuesta `201`:**

```json
{
    "id": "cc758b77-0f8d-42a8-973d-4aa1a5a40384",
    "alumno_id": "aa758b77-0f8d-42a8-973d-4aa1a5a40384",
    "tutor_id": "bb758b77-0f8d-42a8-973d-4aa1a5a40384",
    "relationship": "padre",
    "otra_relacion": null,
    "codigo_institucional": "ALU-A1B2C3-D4E5F6"
}
```

---

### Curso / División / Turno

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| `POST` | `/cursoDivisionTurno` | 🔒 Admin | Crea un curso/división/turno |
| `GET` | `/cursoDivisionTurno` | 🔒 Admin | Lista todos |
| `GET` | `/cursoDivisionTurno/{id}` | 🔒 Admin | Detalle |
| `PUT/PATCH` | `/cursoDivisionTurno/{id}` | 🔒 Admin | Actualiza |
| `PATCH` | `/cursoDivisionTurno/{id}/inhabilitar` | 🔒 Admin | Baja lógica |
| `GET` | `/cursoDivisionTurno/{id}/materias` | 🔒 Admin, Docente | Materias del CDT |

**Body de creación:**

```json
{
    "curso": 1,
    "division": "A",
    "turno": "Mañana",
    "capacidad_maxima": 30
}
```

`curso` va de 1 a 7, `division` es una sola letra A-Z y `turno` es `Mañana` o `Tarde`.

**Respuesta (CDTResource):**

```json
{
    "id": 1,
    "curso": 1,
    "division": "A",
    "turno": "Mañana",
    "capacidad_maxima": 30
}
```

---

### Materias

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| `POST` | `/materias` | 🔒 Admin | Crea una materia |
| `GET` | `/materias` | 🔒 Admin | Lista materias |
| `GET` | `/materias/{id}` | 🔒 Admin | Detalle |
| `PUT/PATCH` | `/materias/{id}` | 🔒 Admin | Actualiza |
| `PATCH` | `/materias/{id}/inhabilitar` | 🔒 Admin | Baja lógica |
| `GET` | `/materias/{id}/inscripciones` | 🔒 Admin, Docente | Inscripciones de la materia |

**Body de creación:**

```json
{
    "nombre": "Matemática",
    "descripcion": "Álgebra y análisis matemático.",
    "curso_division_turno_id": 1,
    "docente_id": "bb758b77-0f8d-42a8-973d-4aa1a5a40384"
}
```

**Respuesta (MateriaResource):**

```json
{
    "id": 1,
    "nombre": "Matemática",
    "descripcion": "Álgebra y análisis matemático.",
    "curso_division_turno_id": 1,
    "turno": "Mañana",
    "docente_id": "bb758b77-0f8d-42a8-973d-4aa1a5a40384"
}
```

---

### Inscripciones

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| `POST` | `/inscripciones` | 🔒 Admin, Alumno | Crea una inscripción |
| `GET` | `/inscripciones` | 🔒 Admin, Alumno | Lista inscripciones |
| `GET` | `/inscripciones/{id}` | 🔒 Admin, Alumno | Detalle |
| `PUT` | `/inscripciones/{id}/estado` | 🔒 Admin, Docente | Cambia el estado |
| `GET` | `/alumnos/{id}/inscripciones` | 🔒 Admin, Alumno | Inscripciones por alumno |
| `GET` | `/materias/{id}/inscripciones` | 🔒 Admin, Docente | Inscripciones por materia |

Los listados por alumno y por materia aceptan el filtro opcional `?estado=pendiente|aprobado|rechazado`.

**Body de creación:**

```json
{
    "alumno_id": "aa758b77-0f8d-42a8-973d-4aa1a5a40384",
    "materia_id": 1,
    "fecha_inscripcion": "01/03/2026"
}
```

`fecha_inscripcion` es opcional (por defecto se usa la fecha actual).

**Cambio de estado:**

```json
{
    "estado": "aprobado"
}
```

**Respuesta (InscripcionResource):**

```json
{
    "id": 1,
    "alumno_id": "aa758b77-0f8d-42a8-973d-4aa1a5a40384",
    "materia_id": 1,
    "fecha_inscripcion": "2026-03-01",
    "estado": "aprobado",
    "estado_label": "Aprobado"
}
```

> No existe `DELETE /inscripciones/{id}`. La baja se representa cambiando el estado a `rechazado`.

---

### Administradores

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| `POST` | `/admins/register` | Público | Registro inicial de administrador |
| `POST` | `/admins` | 🔒 Admin | Crea un administrador |
| `GET` | `/admins` | 🔒 Admin | Lista administradores |
| `GET` | `/admins/{id}` | 🔒 Admin | Detalle |
| `PUT/PATCH` | `/admins/{id}` | 🔒 Admin | Actualiza |
| `PATCH` | `/admins/{id}/inhabilitar` | 🔒 Admin | Baja lógica |
| `GET` | `/admins/estadisticas` | 🔒 Admin | Totales de alumnos y tutores |

**Body de creación:**

```json
{
    "first_name": "Carlos",
    "last_name": "Rodríguez",
    "username": "admin",
    "password": "Admin123!"
}
```

**Respuesta de estadísticas:**

```json
{
    "total_alumnos": 6,
    "total_tutores": 6
}
```

---

## Códigos de error

| Código | Situación |
|---|---|
| `401` | No autenticado o credenciales inválidas |
| `403` | El rol del usuario no tiene permisos |
| `404` | Recurso no encontrado |
| `409` | Duplicado (CDT, materia, inscripción o tutor ya registrado) |
| `422` | Error de validación o ningún campo para actualizar |
| `405` | Operación no permitida (por ejemplo, eliminar una inscripción) |

Ejemplo de validación `422`:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "username": ["Ese nombre de usuario ya está en uso."]
    }
}
```

---

## Usar el token

1. Realizá el `POST /api/v1/login` con `username` y `password`.
2. Copiá el `token` de la respuesta.
3. En los requests protegidos 🔒, agregá el header:
   - Key: `Authorization`
   - Value: `Bearer <token>`

En Postman también podés importar la colección autocontenida incluida en [`docs/postman-collection.json`](docs/postman-collection.json).

---

## Expiración de tokens

Configurable en `config/sanctum.php`:

```php
'expiration' => 60, // 60 minutos
```

`null` significa que el token nunca expira.

---

## Tests y calidad de código

El proyecto usa **Pest** con base SQLite en memoria para los tests.

```bash
# Toda la suite (limpia config, corre Pint y Pest)
composer test

# Solo tests
php artisan test
./vendor/bin/pest

# Tests por suite
./vendor/bin/pest tests/Feature
./vendor/bin/pest tests/Unit

# Estilo de código
composer lint          # aplica Pint
composer lint:check    # solo verifica
```

Cobertura de tests actual:

- **Feature** (`tests/Feature/Api`): autenticación, autorización por roles, usuarios, alumnos, docentes, tutores, materias, CDT, inscripciones y vínculos tutor-alumno.
- **Unit** (`tests/Unit/Domain`): value objects y reglas de dominio (código institucional, CDT, fechas).

Integración continua en `.github/workflows/`:

- `tests.yml` — Pest sobre PHP 8.3, 8.4 y 8.5.
- `lint.yml` — Laravel Pint y ESLint.

---

## Notas de implementación

- Las fechas se aceptan en `dd/mm/yyyy` o `yyyy-mm-dd` y se devuelven en `dd/mm/yyyy` (excepto `fecha_inscripcion`, que se devuelve en `yyyy-mm-dd`).
- Los usuarios creados por el sistema generan su `username` automáticamente:
  - Alumnos: `EST-<dni>-<año de ingreso>`
  - Docentes: `DOC-<dni>-<año de ingreso>`
- La contraseña inicial de alumnos y docentes es su DNI.
- El `codigo_institucional` se genera automáticamente con formato `ALU-XXXXXX-XXXXXX` al crear el vínculo tutor-alumno.
- Las bajas son lógicas (`deleted_at`); no hay endpoints `DELETE`.
- Los errores de dominio se traducen a respuestas JSON desde `bootstrap/app.php` (404 para no encontrado, 409 para duplicados).
