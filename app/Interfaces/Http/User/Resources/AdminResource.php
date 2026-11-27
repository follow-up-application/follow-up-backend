<?php

namespace App\Interfaces\Http\User\Resources;

use app\Domain\User\Entities\Admin;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Admin
 */
class AdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Admin $admin */
        $admin = $this->resource;

        return [
            'id'                  => $admin->id()->value(),
            'first_name'          => $admin->firstName(),
            'last_name'           => $admin->lastName(),
            'username'            => $admin->username(),
            'role'                => $admin->role()->value,
            'activo'              => $admin->activo(),
        ];
    }
}