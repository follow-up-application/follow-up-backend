<?php

namespace App\Infrastructure\User\Persistence\Eloquent;

use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\UserRole;
use App\Infrastructure\User\Persistence\UserModel;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function save(User $user): User
    {
        $model = UserModel::updateOrCreate(
            ['id' => $user->id()->value()],
            [
                'first_name'           => $user->firstName(),
                'last_name'            => $user->lastName(),
                'dni'                  => $user->dni(),
                'username'             => $user->username(),
                'password'             => $user->password(),
                'role'                 => $user->role()->value,
                'must_change_password' => $user->mustChangePassword(),
            ]
        );

        return $this->toDomain($model);
    }

    public function findByUsername(string $username): ?User
    {
        $model = UserModel::where('username', $username)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findById(UserId $id): ?User
    {
        $model = UserModel::find($id->value());

        return $model ? $this->toDomain($model) : null;
    }

    public function inhabilitar(UserId $id): void
    {
        $model = UserModel::find($id->value());

        if ($model) {
            $model->delete();
        }
    }

    public function all(): array
    {
        return UserModel::all()
            ->map(fn (UserModel $model) => $this->toDomain($model))
            ->toArray();
    }

    private function toDomain(UserModel $model): User
    {
        return new User(
            new UserId($model->id),
            $model->first_name,
            $model->last_name,
            $model->dni !== null ? (int) $model->dni : null,
            $model->username,
            $model->password,
            UserRole::from($model->role),
            (bool) $model->must_change_password,
        );
    }
}