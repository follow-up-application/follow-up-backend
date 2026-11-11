<?php

namespace App\Infrastructure\User\Persistence;

use Illuminate\Database\Eloquent\Model;

class MateriaModel extends Model
{
    protected $table = 'materias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'curso_division_turno_id',
        'docente_id',
    ];

    public function docente()
    {
        return $this->belongsTo(DocenteModel::class, 'docente_id', 'id');
    }

    public function cursoDivisionTurno()
    {
        return $this->belongsTo(CursoDivisionTurnoModel::class, 'curso_division_turno_id', 'id');
    }

    public function inscripciones()
    {
        return $this->hasMany(InscripcionModel::class, 'materia_id', 'id');
    }
}
