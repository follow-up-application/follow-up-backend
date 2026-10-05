<?php

use App\Infrastructure\User\Providers\AdminServiceProvider;
use App\Infrastructure\User\Providers\AlumnoServiceProvider;
use App\Infrastructure\User\Providers\CDTServiceProvider;
use App\Infrastructure\User\Providers\DocenteServiceProvider;
use App\Infrastructure\User\Providers\InscripcionServiceProvider;
use App\Infrastructure\User\Providers\MateriaServiceProvider;
use App\Infrastructure\User\Providers\TutorAlumnoServiceProvider;
use App\Infrastructure\User\Providers\TutorServiceProvider;
use App\Providers\AppServiceProvider;
use App\Infrastructure\User\Providers\UserServiceProvider;

return [
    AppServiceProvider::class,
    AdminServiceProvider::class,
    CDTServiceProvider::class,
    MateriaServiceProvider::class,
    TutorAlumnoServiceProvider::class,
    UserServiceProvider::class,
    AlumnoServiceProvider::class,
    DocenteServiceProvider::class,
    TutorServiceProvider::class,
    InscripcionServiceProvider::class,
];
