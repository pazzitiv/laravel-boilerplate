<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Group\Transformers\GroupResource;
use Modules\Role\Transformers\RoleResource;

class UserResource extends JsonResource
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
            'id' => $this->id,
            'login' => $this->login,
            'email' => $this->email,
            'role' => RoleResource::make($this->userrole),
            'groups' => GroupResource::collection($this->groups),
            'lastName' => $this->lastname,
            'firstName' => $this->firstname,
            'secondName' => $this->secondname,
            'fullName' => "$this->lastname $this->firstname $this->secondname",
            'shortName' => $this->lastname . ' ' . ucfirst(mb_substr($this->firstname, 0, 1)) . '. ' . ucfirst(mb_substr($this->secondname, 0, 1)) . '.',
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'active' => $this->active,
        ];
    }
}
