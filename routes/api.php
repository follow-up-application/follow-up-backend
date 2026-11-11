<?php

use App\Interfaces\Http\User\Controllers\AdminController;
use App\Interfaces\Http\User\Controllers\AlumnoController;
use App\Interfaces\Http\User\Controllers\AuthController;
use App\Interfaces\Http\User\Controllers\CursoDivisionTurnoController;
use App\Interfaces\Http\User\Controllers\DocenteController;
use App\Interfaces\Http\User\Controllers\InscripcionController;
use App\Interfaces\Http\User\Controllers\MateriaController;
use App\Interfaces\Http\User\Controllers\TutorAlumnoController;
use App\Interfaces\Http\User\Controllers\TutorController;
use App\Interfaces\Http\User\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Públicas
    Route::post('login', [AuthController::class, 'login']);
    Route::post('users', [UserController::class, 'store']);
    Route::get('tutores/validarCodigo/{codigo}', [TutorController::class, 'validateCode']);
    Route::post('tutores/register', [TutorController::class, 'register']);
    Route::post('admins/register', [AdminController::class, 'store']);

    // Protegidas — requieren estar autenticado
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('logout', [AuthController::class, 'logout']);
        Route::put('password', [AuthController::class, 'changePassword']); // cualquier rol autenticado

        // ── Alumnos ──
        // Admin y Docente gestionan el CRUD completo de alumnos.
        // show() queda fuera porque más abajo se registra con los cuatro roles:
        // si el apiResource lo declarara, Laravel matchearía esa ruta primero y
        // el alumno y el tutor recibirían 403 al ver su propio registro.
        Route::middleware('role:administrador,docente')->group(function () {
            Route::apiResource('alumnos', AlumnoController::class)->except(['show']);
        });

        // ── Curso Division Turno ──
        // Solo Admin gestiona el CRUD completo de Curso Division Turno
        Route::middleware('role:administrador')->group(function () {
            Route::apiResource('cursoDivisionTurno', CursoDivisionTurnoController::class);
        });

        // El propio alumno, su tutor, docente o admin pueden ver el detalle
        Route::middleware('role:administrador,docente,tutor,alumno')->group(function () {
            Route::get('alumnos/{id}', [AlumnoController::class, 'show']);
        });

        // Listado de materias por curso division turno (0 a muchas materias por CDT)
        Route::middleware('role:administrador,docente')->group(function () {
            Route::get('cursoDivisionTurno/{id}/materias', [MateriaController::class, 'byCursoDivisionTurno']);
        });

        // ── Docentes ──
        // Solo Admin gestiona altas/bajas/modificaciones de docentes
        Route::middleware('role:administrador')->group(function () {
            Route::apiResource('docentes', DocenteController::class);
        });

        // ── Materias ──
        // Solo Admin gestiona altas/bajas/modificaciones de materias
        Route::middleware('role:administrador')->group(function () {
            Route::apiResource('materias', MateriaController::class);
        });

        // Un docente es jefe de 0 a muchas materias
        Route::middleware('role:administrador,docente')->group(function () {
            Route::get('docentes/{id}/materias', [MateriaController::class, 'byDocente']);
        });

        // ── Inscripciones ──
        // El alumno se inscribe en una o muchas materias; el admin y el docente
        // gestionan las inscripciones (alta, estado y baja).
        Route::middleware('role:administrador,alumno')->group(function () {
            Route::post('inscripciones', [InscripcionController::class, 'store']);
            Route::get('inscripciones', [InscripcionController::class, 'index']);
            Route::get('inscripciones/{id}', [InscripcionController::class, 'show']);
            Route::get('alumnos/{id}/inscripciones', [InscripcionController::class, 'byAlumno']);
        });

        Route::middleware('role:administrador,docente')->group(function () {
            Route::get('materias/{id}/inscripciones', [InscripcionController::class, 'byMateria']);
            Route::put('inscripciones/{id}/estado', [InscripcionController::class, 'updateEstado']);
        });

        Route::middleware('role:administrador')->group(function () {
            Route::delete('inscripciones/{id}', [InscripcionController::class, 'destroy']);
        });

        // ── Tutores ──
        // Solo Admin gestiona tutores (salvo el registro público ya definido arriba).
        // show() queda fuera por la misma razón que en alumnos: la ruta
        // generalizada de abajo debe ser la que matchee.
        Route::middleware('role:administrador')->group(function () {
            Route::apiResource('tutores', TutorController::class)->except(['show']);
        });
        // El propio tutor o un admin pueden ver el detalle y los alumnos asociados
        Route::middleware('role:administrador,tutor')->group(function () {
            Route::get('tutores/{id}', [TutorController::class, 'show']);
            Route::get('tutores/dni/{dni}', [TutorController::class, 'findByDni']);
            Route::get('tutores/{id}/alumnos', [TutorController::class, 'alumnos']);
        });

        // ── Admins ──
        // Solo un Admin gestiona otros admins (salvo el registro público ya definido arriba)
        Route::middleware('role:administrador')->group(function () {
            Route::get('admins/estadisticas', [AdminController::class, 'statistics']);
            Route::get('admins/{id}', [AdminController::class, 'show']);
            Route::get('admins', [AdminController::class, 'index']);
            Route::apiResource('admins', AdminController::class);
        });

        // ── Usuarios (CRUD base) ──
        Route::middleware('role:administrador')->group(function () {
            Route::get('users/{id}', [UserController::class, 'show']);
            Route::put('users/{id}', [UserController::class, 'update']);
            Route::delete('users/{id}', [UserController::class, 'destroy']);
        });

        // Perfil del usuario autenticado
        Route::middleware('role:administrador,docente,tutor,alumno')->group(function () {
            Route::get('users/me', [UserController::class, 'me']);
        });

        // ── Vínculos Tutor-Alumno ──
        Route::middleware('role:administrador')->group(function () {
            Route::post('tutor-alumno', [TutorAlumnoController::class, 'store']);
            Route::delete('tutor-alumno/{id}', [TutorAlumnoController::class, 'destroy']);
        });

        Route::middleware('role:administrador,tutor,alumno')->group(function () {
            Route::get('alumnos/{id}/tutores', [TutorAlumnoController::class, 'byAlumno']);
        });
    });
});
