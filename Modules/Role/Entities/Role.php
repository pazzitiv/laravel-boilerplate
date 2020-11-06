<?php

namespace Modules\Role\Entities;

use Extensions\Database\Eloquent\PivotRelationship;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Modules\SubModules\Entities\Submodule;

class Role extends PivotRelationship
{
    use Notifiable, SoftDeletes, hasTimestamps;

    protected $table = 'roles';

    protected $fillable = [
        'code',
        'name',
    ];

    public function modules(): HasManyThrough
    {
        return $this->hasManyThrough(Submodule::class,RolePermission::class,  'fk_role_id', 'id', 'id','fk_module_id');
    }

    public function isDuplicate(string $rolecode): bool
    {
        $somerole = Role::where('code', $rolecode)->whereNull($this->getDeletedAtColumn())->where('id', '<>', $this->id)->get();
        if ($somerole->count() !== 0) {
            return true;
        }
        return false;
    }
}
