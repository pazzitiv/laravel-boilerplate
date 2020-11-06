<?php

namespace Modules\SubModules\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Routing\Controller;
use Modules\Role\Entities\RolePermission;
use Modules\Role\Http\Controllers\RoleController;
use Modules\SubModules\Entities\Submodule;
use Modules\SubModules\Helpers\ModuleSystem;
use Modules\User\Entities\User;
use Modules\User\Transformers\UserResource;

class Permissions
{
    use HandlesAuthorization;

    private static array $policyInterface = ['controller' => null, 'module' => null];

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function create(User $User, Controller $controller): Response
    {
        $permissions = $this->getPermissions($User, $controller->getModule());

        if ($permissions !== null && $permissions->create) return $this->allow();

        return $this->deny();
    }

    public function read(User $User, Controller $controller): Response
    {
        $permissions = $this->getPermissions($User, $controller->getModule());

        if ($permissions !== null && $permissions->read) return $this->allow();

        return $this->deny();
    }


    public function update(User $User, Controller $controller): Response
    {
        $permissions = $this->getPermissions($User, $controller->getModule());

        if ($permissions !== null && $permissions->update) return $this->allow();

        return $this->deny();
    }

    public function delete(User $User, Controller $controller): Response
    {
        $permissions = $this->getPermissions($User, $controller->getModule());

        if ($permissions !== null && $permissions->delete) return $this->allow();

        return $this->deny();
    }

    private function getPermissions(User $user, string $moduleName)
    {
        $Permission = (object) [
            'create' => false,
            'read' => false,
            'update' => false,
            'delete' => false,
        ];

        $moduleCode = ModuleSystem::getModuleCode($moduleName);

        $module = Submodule::where('code', $moduleCode)->first();
        $permissions = RolePermission::where('fk_module_id', $module->id)
            ->where('fk_role_id', $user->id)->first();

        if ($permissions === null) return null;

        foreach ($permissions->toArray() as $permission => $can) {
            if(property_exists($Permission, $permission)) $Permission->{$permission} = $can;
        }
        return $Permission;
    }
}
