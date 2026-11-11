<?php

namespace App\Infrastructure\User\Persistence\Eloquent;

use App\Domain\User\Entities\Tutor;
use App\Domain\User\Repositories\TutorRepositoryInterface;
use App\Domain\User\ValueObjects\UserEmail;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\User\Persistence\TutorModel;
use App\Infrastructure\User\Persistence\UserModel;

class EloquentTutorRepository implements TutorRepositoryInterface
{
    public function save(Tutor $tutor): Tutor
    {
        $userModel = UserModel::updateOrCreate(
            ['id' => $tutor->id()->value()],
            [
                'first_name' => $tutor->firstName(),
                'last_name' => $tutor->lastName(),
                'dni' => $tutor->dni(),
                // El tutor inicia sesión con su email: sin esta columna el
                // alta directa quedaba con username NULL y no podía loguearse.
                'username' => $tutor->username(),
                'password' => $tutor->password(),
                'role' => $tutor->role()->value,
                'must_change_password' => $tutor->mustChangePassword(),
            ]
        );

        $tutorModel = TutorModel::updateOrCreate(
            ['id' => $userModel->id],
            [
                'email' => $tutor->email()?->value(),
                'telephone' => $tutor->telephone(),
            ]
        );

        return $this->toDomain($userModel, $tutorModel);
    }

    public function findById(UserId $id): ?Tutor
    {
        $userModel = UserModel::find($id->value());

        if (! $userModel) {
            return null;
        }

        $tutorModel = TutorModel::where('id', $userModel->id)->first();

        return $tutorModel ? $this->toDomain($userModel, $tutorModel) : null;
    }

    public function findByDni(int $dni): ?Tutor
    {
        $userModel = UserModel::where('dni', $dni)->first();

        if (! $userModel) {
            return null;
        }

        $tutorModel = TutorModel::where('id', $userModel->id)->first();

        return $tutorModel ? $this->toDomain($userModel, $tutorModel) : null;
    }

    public function findByUsername(string $username): ?Tutor
    {
        // username vive en users, no en tutores.
        $userModel = UserModel::where('username', $username)->first();

        if (! $userModel) {
            return null;
        }

        $tutorModel = TutorModel::where('id', $userModel->id)->first();

        return $tutorModel ? $this->toDomain($userModel, $tutorModel) : null;
    }

    public function all(): array
    {
        return TutorModel::with('user')
            ->get()
            ->map(fn (TutorModel $model) => $this->toDomain($model->user, $model))
            ->toArray();
    }

    public function delete(UserId $id): void
    {
        // Se borra el users y no el tutor: el cascadeOnDelete de la migración
        // va de tutores.id a users.id, así que al borrar la fila hija quedaba
        // un usuario huérfano con DNI, email y hash de contraseña.
        UserModel::destroy($id->value());
    }

    public function totalTutores(): int
    {
        return TutorModel::count();
    }

    private function toDomain(UserModel $userModel, TutorModel $tutorModel): Tutor
    {
        $tutor = new Tutor(
            new UserId($userModel->id),
            $userModel->first_name,
            $userModel->last_name,
            (int) $userModel->dni,
            $tutorModel->telephone,
            $tutorModel->email
                ? new UserEmail($tutorModel->email)
                : null
        );

        // El constructor de Tutor no recibe el hash y deja password en null.
        // Sin restaurarlo acá, cualquier save() posterior (por ejemplo un
        // PUT /tutores/{id}) escribía password = NULL y el tutor se quedaba
        // sin poder iniciar sesión.
        $tutor->updatePassword(
            (string) $userModel->password,
            (bool) $userModel->must_change_password,
        );

        return $tutor;
    }
}
