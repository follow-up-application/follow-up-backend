<?php

test('las rutas protegidas exigen autenticacion', function () {
    $this->getJson('/api/v1/alumnos')->assertUnauthorized();
    $this->getJson('/api/v1/docentes')->assertUnauthorized();
    $this->getJson('/api/v1/materias')->assertUnauthorized();
    $this->getJson('/api/v1/inscripciones')->assertUnauthorized();
    $this->getJson('/api/v1/tutores')->assertUnauthorized();
    $this->getJson('/api/v1/admins')->assertUnauthorized();
    $this->getJson('/api/v1/users')->assertUnauthorized();
    $this->getJson('/api/v1/users/me')->assertUnauthorized();
});

test('el docente puede gestionar alumnos pero no usuarios ni cursos', function () {
    $docente = crearDocente();
    autenticarComo($docente['user']);

    $this->getJson('/api/v1/alumnos')->assertOk();
    $this->getJson('/api/v1/users')->assertForbidden();
    $this->getJson('/api/v1/cursoDivisionTurno')->assertForbidden();
    $this->getJson('/api/v1/admins')->assertForbidden();
    $this->getJson('/api/v1/tutores')->assertForbidden();
});

test('el alumno solo accede a su detalle y a sus inscripciones', function () {
    $alumno = crearAlumno();
    autenticarComo($alumno['user']);

    $this->getJson('/api/v1/alumnos/'.$alumno['alumno']->id)->assertOk();
    $this->getJson('/api/v1/alumnos/'.$alumno['alumno']->id.'/inscripciones')->assertOk();
    $this->getJson('/api/v1/alumnos')->assertForbidden();
    $this->getJson('/api/v1/materias')->assertForbidden();
    $this->getJson('/api/v1/users')->assertForbidden();
    $this->getJson('/api/v1/tutores')->assertForbidden();
});

test('el tutor accede a su detalle y a los alumnos a cargo', function () {
    $tutor = crearTutor();
    $alumno = crearAlumno();
    crearVinculoTutorAlumno($alumno['alumno'], $tutor['tutor']);

    autenticarComo($tutor['user']);

    $this->getJson('/api/v1/tutores/'.$tutor['tutor']->id)->assertOk();
    $this->getJson('/api/v1/alumnos/'.$alumno['alumno']->id.'/tutores')->assertOk();
    $this->getJson('/api/v1/alumnos')->assertForbidden();
    $this->getJson('/api/v1/inscripciones')->assertForbidden();
    $this->getJson('/api/v1/users')->assertForbidden();
});

test('el administrador accede a todos los modulos principales', function () {
    $admin = usuarioFixture('administrador');
    autenticarComo($admin);

    $this->getJson('/api/v1/alumnos')->assertOk();
    $this->getJson('/api/v1/docentes')->assertOk();
    $this->getJson('/api/v1/materias')->assertOk();
    $this->getJson('/api/v1/inscripciones')->assertOk();
    $this->getJson('/api/v1/tutores')->assertOk();
    $this->getJson('/api/v1/admins')->assertOk();
    $this->getJson('/api/v1/users')->assertOk();
    $this->getJson('/api/v1/users/me')->assertOk();
});
