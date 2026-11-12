<?php

use App\Domain\User\ValueObjects\CodigoInstitucional;

test('genera un codigo institucional con el formato esperado', function () {
    $codigo = new CodigoInstitucional;

    expect($codigo->value())->toMatch('/^ALU-[A-F0-9]{6}-[A-F0-9]{6}$/');
});

test('genera codigos distintos', function () {
    $primero = new CodigoInstitucional;
    $segundo = new CodigoInstitucional;

    expect($primero->value())->not->toBe($segundo->value());
});

test('acepta un codigo existente', function () {
    $codigo = CodigoInstitucional::fromString('ALU-ABC123-DEF456');

    expect($codigo->value())->toBe('ALU-ABC123-DEF456');
});

test('rechaza un codigo vacio', function () {
    new CodigoInstitucional('   ');
})->throws(InvalidArgumentException::class);
