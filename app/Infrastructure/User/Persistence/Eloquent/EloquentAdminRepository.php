<?php

namespace App\Infrastructure\User\Persistence\Eloquent;

use App\Domain\User\Entities\Admin;
use App\Domain\User\Repositories\AdminRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\UserRole;
use App\Infrastructure\User\Persistence\UserModel;

class EloquentAdminRepository implements AdminRepositoryInterface
{
    public function save(Admin $admin): Admin
    {
        $userModel = UserModel::updateOrCreate(
            ['id' => $admin->id()->value()],
            [
                'first_name'           => $admin->firstName(),
                'last_name'            => $admin->lastName(),
                'username'             => $admin->username(),
                'password'             => $admin->password(),
                'role'                 => $admin->role()->value,
                'must_change_password' => $admin->mustChangePassword(),
            ]
        );

        return $this->toDomain($userModel);
    }

    public function findById(UserId $id): ?Admin
    {
        $userModel = UserModel::where('id', $id->value())
            ->where('role', UserRole::ADMINISTRADOR->value)
            ->first();

        return $userModel ? $this->toDomain($userModel) : null;
    }

    public function findByUsername(string $username): ?Admin
    {
        $userModel = UserModel::where('username', $username)
            ->where('role', UserRole::ADMINISTRADOR->value)
            ->first();

        return $userModel ? $this->toDomain($userModel) : null;
    }

    public function all(): array
    {
        return UserModel::where('role', UserRole::ADMINISTRADOR->value)
            ->get()
            ->map(fn (UserModel $model) => $this->toDomain($model))
            ->toArray();
    }

    public function inhabilitar(UserId $id): void
    {
        $model = UserModel::where('id', $id->value())
            ->where('role', UserRole::ADMINISTRADOR->value)
            ->first();

        if ($model) {
            $model->delete();
        }
    }

    private function toDomain(UserModel $userModel): Admin
    {
        return new Admin(
            new UserId($userModel->id),
            $userModel->first_name,
            $userModel->last_name,
            $userModel->password,
            $userModel->username,
        );
    }
}