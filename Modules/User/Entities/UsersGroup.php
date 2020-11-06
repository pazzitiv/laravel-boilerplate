<?php

namespace Modules\User\Entities;

use Extensions\Database\Eloquent\Pivot;
use Modules\Group\Entities\Group;

class UsersGroup extends Pivot
{
    protected $table = 'users_group';
    public $timestamps = false;

    protected string $pivotParentClassname = User::class;
    protected string $pivotTargetClassname = Group::class;
    protected $foreignKey = 'fk_user_id';
    protected $relatedKey = 'fk_group_id';


    protected $fillable = [];
}
