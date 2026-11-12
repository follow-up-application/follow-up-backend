<?php

use App\Domain\User\Entities\CursoDivisionTurno;
use App\Domain\User\ValueObjects\TurnoEscolar;

test('construye un curso division turno con su capacidad', function () {
    $cdt = new CursoDivisionTurno(
        id: 1,
        curso: 5,
        division: 'A',
        turno: TurnoEscolar::MANANA,
        capacidad_maxima: 35,
    );

    expect($cdt->id())->toBe(1);
    expect($cdt->curso())->toBe(5);
    expect($cdt->division())->toBe('A');
    expect($cdt->turno())->toBe(TurnoEscolar::MANANA);
    expect($cdt->capacidadMaxima())->toBe(35);
});

test('usa capacidad 30 por defecto', function () {
    $cdt = new CursoDivisionTurno(
        id: null,
        curso: 1,
        division: 'B',
        turno: TurnoEscolar::TARDE,
    );

    expect($cdt->capacidadMaxima())->toBe(30);
});

test('rechaza un curso fuera de rango', function () {
    new CursoDivisionTurno(
        id: null,
        curso: 8,
        division: 'A',
        turno: TurnoEscolar::MANANA,
    );
})->throws(InvalidArgumentException::class);

test('rechaza una division invalida', function () {
    new CursoDivisionTurno(
        id: null,
        curso: 1,
        division: 'AB',
        turno: TurnoEscolar::MANANA,
    );
})->throws(InvalidArgumentException::class);

test('reconstruye un curso division turno desde su representacion', function () {
    $original = new CursoDivisionTurno(
        id: 1,
        curso: 3,
        division: 'C',
        turno: TurnoEscolar::TARDE,
        capacidad_maxima: 42,
    );

    $reconstruido = CursoDivisionTurno::fromString((string) $original);

    expect($reconstruido->curso())->toBe(3);
    expect($reconstruido->division())->toBe('C');
    expect($reconstruido->turno())->toBe(TurnoEscolar::TARDE);
    expect($reconstruido->capacidadMaxima())->toBe(30);
});
