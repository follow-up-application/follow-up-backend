<?php

namespace App\Infrastructure\User\Persistence;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocenteModel extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'docentes';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'telephone',
        'fecha_ingreso',
        'curso_division_turno_id',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'id', 'id');
    }

    public function cursoDivisionTurno()
    {
        return $this->belongsTo(CursoDivisionTurnoModel::class, 'curso_division_turno_id', 'id');
    }

    public function materias()
    {
        return $this->hasMany(MateriaModel::class, 'docente_id', 'id');
    }
}