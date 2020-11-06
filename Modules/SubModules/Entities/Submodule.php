<?php

namespace Modules\SubModules\Entities;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Modules\Role\Entities\RolePermission;

class Submodule extends Model
{
    use Notifiable, hasTimestamps;

    protected $table = 'modules';

    protected $fillable = [
        'id',
        'name',
        'code',
        'parent_code',
    ];

    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermission::class, 'fk_module_id', 'id');
    }
}
