<?php

namespace App\Infrastructure\User\Persistence;

use App\Domain\User\ValueObjects\EstadoInscripcion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InscripcionModel extends Model
{
    use SoftDeletes;
    protected $table = 'inscripciones';

    protected $fillable = [
        'alumno_id',
        'materia_id',
        'fecha_inscripcion',
        'estado',
    ];

    protected $casts = [
        'fecha_inscripcion' => 'date',
        'estado' => EstadoInscripcion::class,
    ];

    public function alumno()
    {
        return $this->belongsTo(AlumnoModel::class, 'alumno_id', 'id');
    }

    public function materia()
    {
        return $this->belongsTo(MateriaModel::class, 'materia_id', 'id');
    }
}
