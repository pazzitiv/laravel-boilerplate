<?php

namespace Modules\Role\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\SubModules\Transformers\SubmoduleResource;

class RoleResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'modules' => SubmoduleResource::collection($this->modules),
        ];
    }
}
