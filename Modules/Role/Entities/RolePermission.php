<?php

namespace Modules\Role\Entities;

use Extensions\Database\Eloquent\Pivot;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Modules\SubModules\Entities\Submodule;

class RolePermission extends Pivot
{
    use Notifiable, hasTimestamps, SoftDeletes;

    protected $table = 'roles_permission';

    public string $pivotParentClassname = Role::class;
    public string $pivotTargetClassname = Submodule::class;

    protected $foreignKey = 'fk_role_id';
    protected $relatedKey = 'fk_module_id';

    protected $fillable = [
        'fk_role_id',
        'fk_module_id',
        'create',
        'read',
        'update',
        'delete',
    ];

    public function role(): HasOne
    {
        return $this->hasOne(Role::class, 'id', 'fk_role_id');
    }
}
