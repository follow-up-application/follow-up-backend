<?php

namespace App\Infrastructure\User\Persistence;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TutorAlumnoModel extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'tutor_alumno';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'alumno_id',
        'tutor_id',
        'relationship',
        'otra_relacion',
        'codigo_institucional',
    ];

    public function alumno()
    {
        return $this->belongsTo(AlumnoModel::class, 'alumno_id', 'id');
    }

    public function tutor()
    {
        return $this->belongsTo(TutorModel::class, 'tutor_id', 'id');
    }
}