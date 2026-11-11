<?php

namespace App\Infrastructure\User\Persistence;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TutorModel extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'tutores';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'telephone',
        'email',
    ];

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'id', 'id');
    }

    public function tutorAlumnos()
    {
        return $this->hasMany(TutorAlumnoModel::class, 'tutor_id', 'id');
    }
}