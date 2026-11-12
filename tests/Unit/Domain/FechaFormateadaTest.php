<?php

use App\Domain\Shared\ValueObjects\FechaFormateada;

test('acepta una fecha en formato yyyy-mm-dd', function () {
    $fecha = new FechaFormateada('2020-03-01');

    expect($fecha->toDatabase())->toBe('2020-03-01');
    expect($fecha->toDisplay())->toBe('01/03/2020');
    expect($fecha->toYears())->toBe('2020');
});

test('acepta una fecha en formato dd/mm/yyyy', function () {
    $fecha = new FechaFormateada('01/03/2020');

    expect($fecha->toDatabase())->toBe('2020-03-01');
    expect($fecha->toDisplay())->toBe('01/03/2020');
});

test('rechaza fechas con formato invalido', function () {
    new FechaFormateada('2020/03/01');
})->throws(InvalidArgumentException::class);

test('rechaza fechas inexistentes', function () {
    new FechaFormateada('31/02/2020');
})->throws(InvalidArgumentException::class);

test('rechaza dias fuera de rango', function () {
    new FechaFormateada('2020-02-31');
})->throws(InvalidArgumentException::class);

test('se puede construir desde la base de datos', function () {
    $fecha = FechaFormateada::fromDatabase('2020-03-01');

    expect($fecha->toDisplay())->toBe('01/03/2020');
});

test('se puede construir desde un objeto datetime', function () {
    $fecha = FechaFormateada::fromDatabase(new DateTimeImmutable('2020-03-01 15:30:00'));

    expect($fecha->toDatabase())->toBe('2020-03-01');
});
