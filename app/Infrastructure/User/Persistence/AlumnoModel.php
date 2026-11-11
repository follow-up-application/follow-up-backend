<?php

namespace App\Infrastructure\User\Persistence;

use App\Domain\User\Entities\Alumno;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\User\Persistence\Eloquent\EloquentAlumnoRepository;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlumnoModel extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'alumnos';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'fecha_nacimiento',
        'curso_division_turno_id',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'id', 'id');
    }

    public function cursoDivisionTurno()
    {
        return $this->belongsTo(CursoDivisionTurnoModel::class, 'curso_division_turno_id', 'id');
    }

    public function inscripciones()
    {
        return $this->hasMany(InscripcionModel::class, 'alumno_id', 'id');
    }

    public function toDomain(): Alumno
    {
        return app(EloquentAlumnoRepository::class)
            ->findById(new UserId($this->id));
    }
}
