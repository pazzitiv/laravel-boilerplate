<?php

namespace Modules\SubModules\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Role\Entities\RolePermission;
use Modules\Role\Transformers\RolePermissionResource;

class SubmoduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'permissions' => $this->laravel_through_key ?
                RolePermissionResource::collection($this->permissions->where('fk_role_id', $this->laravel_through_key))
                : RolePermissionResource::collection($this->permissions),
        ];
    }
}
