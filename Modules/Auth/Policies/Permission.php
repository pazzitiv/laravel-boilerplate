<?php

namespace Modules\Auth\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Modules\Auth\Http\Controllers\AuthController;
use Modules\User\Entities\User;

class Permission
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function login(User $User, AuthController $Auth): Response
    {
        return $User->active ? $this->allow() : $this->deny();
    }
}
