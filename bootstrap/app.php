<?php

use App\Domain\User\Exceptions\AlumnoYaInscriptoException;
use App\Domain\User\Exceptions\CursoDivisionTurnoDuplicadoException;
use App\Domain\User\Exceptions\DomainNotFoundException;
use App\Domain\User\Exceptions\MateriaDuplicadaException;
use App\Domain\User\Exceptions\TutorYaRegistradoException;
use App\Interfaces\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (DomainNotFoundException $e, Request $request) {
            $status = $e instanceof CursoDivisionTurnoDuplicadoException
                || $e instanceof MateriaDuplicadaException
                || $e instanceof AlumnoYaInscriptoException
                || $e instanceof TutorYaRegistradoException
                    ? 409
                    : 404;

            return response()->json(['message' => $e->getMessage()], $status);
        });
    })
    ->create();
