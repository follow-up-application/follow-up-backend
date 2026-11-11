<?php

namespace App\Infrastructure\User\Persistence;

use App\Domain\User\Entities\CursoDivisionTurno;
use App\Domain\User\ValueObjects\TurnoEscolar;
use Illuminate\Database\Eloquent\Model;

class CursoDivisionTurnoModel extends Model
{
    protected $table = 'curso_division_turno';

    protected $fillable = [
        'curso',
        'division',
        'turno',
        'capacidad_maxima',
    ];

    protected $casts = [
        'curso' => 'integer',
        'capacidad_maxima' => 'integer',
    ];

    public function alumnos()
    {
        return $this->hasMany(AlumnoModel::class, 'curso_division_turno_id', 'id');
    }

    public function docentes()
    {
        return $this->hasMany(DocenteModel::class, 'curso_division_turno_id', 'id');
    }

    public function materias()
    {
        return $this->hasMany(MateriaModel::class, 'curso_division_turno_id', 'id');
    }

    public function toDomain(): CursoDivisionTurno
    {
        return new CursoDivisionTurno(
            id: $this->id,
            curso: $this->curso,
            division: $this->division,
            turno: TurnoEscolar::from($this->turno),
            capacidad_maxima: $this->capacidad_maxima,
        );
    }
}