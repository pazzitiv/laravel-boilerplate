<?php

namespace Modules\User\Entities;

use Extensions\Database\Eloquent\PivotAuthenticatable;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Modules\Role\Entities\Role;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;

class User extends PivotAuthenticatable implements JWTSubject
{
    use Notifiable, SoftDeletes, HasTimestamps;

    protected $table = 'users';

    protected $fillable = [
        'login',
        'password',
        'email',
        'role',
        'lastname',
        'firstname',
        'secondname',
    ];

    protected $hidden = [
        'password'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function isDuplicate(string $login): bool
    {
        $someuser = User::where('login', $login)->whereNull($this->getDeletedAtColumn())->where('id', '<>', $this->id)->get();
        if ($someuser->count() !== 0) {
            return true;
        }
        return false;
    }

    public static function hashPassword(string $password): string
    {
        return Hash::make($password);
    }

    public function userrole(): HasOne
    {
        return $this->hasOne(Role::class, 'id', 'role');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            UsersGroup::class
        );
    }
}
