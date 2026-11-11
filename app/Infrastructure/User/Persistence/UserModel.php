<?php

namespace App\Infrastructure\User\Persistence;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class UserModel extends Authenticatable
{
    use HasApiTokens;
    use HasUuids;
    use SoftDeletes;
    use Notifiable;

    protected $table = 'users';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'dni',
        'username',
        'password',
        'role',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'dni' => 'integer',
        'must_change_password' => 'boolean',
    ];

    public function alumno()
    {
        return $this->hasOne(AlumnoModel::class, 'id', 'id');
    }

    public function docente()
    {
        return $this->hasOne(DocenteModel::class, 'id', 'id');
    }

    public function tutor()
    {
        return $this->hasOne(TutorModel::class, 'id', 'id');
    }
}