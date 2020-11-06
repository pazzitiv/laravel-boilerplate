<?php

namespace Modules\DataSource\Transformers;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PlmCubesResource extends JsonResource
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
            'id' => $this->resource['uuid'],
            'name' => $this->resource['name'],
            'creator' => $this->resource['creator'],
            'createAt' => Carbon::createFromTimestampMs((int) $this->resource['creation_time'] / 1000),
            'updateAt' => Carbon::createFromTimestampMs((int) $this->resource['update_time'] / 1000),
        ];
    }
}
