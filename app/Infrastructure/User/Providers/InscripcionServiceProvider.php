<?php

namespace App\Infrastructure\User\Providers;

use App\Domain\User\Repositories\InscripcionRepositoryInterface;
use App\Infrastructure\User\Persistence\Eloquent\EloquentInscripcionRepository;
use Illuminate\Support\ServiceProvider;

class InscripcionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            InscripcionRepositoryInterface::class,
            EloquentInscripcionRepository::class
        );
    }
}
