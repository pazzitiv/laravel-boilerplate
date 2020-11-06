<?php

namespace Modules\Role\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class RolePermissionResource extends JsonResource
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
            'role' => $this->role->name,
            'create' => $this->create,
            'read' => $this->read,
            'update' => $this->update,
            'delete' => $this->delete,
        ];
    }
}
