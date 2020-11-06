<?php

namespace Modules\Group\Entities;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Group extends Model
{
    use Notifiable, SoftDeletes, hasTimestamps;

    protected $table = 'groups';

    protected $fillable = [
        'name',
        'description',
    ];

    public function isDuplicate(string $groupname): bool
    {
        $somegroup = Group::where('name', $groupname)->whereNull($this->getDeletedAtColumn())->where('id', '<>', $this->id)->get();
        if ($somegroup->count() !== 0) {
            return true;
        }
        return false;
    }
}
