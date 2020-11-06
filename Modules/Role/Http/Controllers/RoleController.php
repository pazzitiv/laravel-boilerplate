<?php

namespace Modules\Role\Http\Controllers;

use App\Exceptions\AlreadyExistsException;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Role\Entities\Role;
use Modules\Role\Entities\RolePermission;
use Modules\Role\Transformers\RoleResource;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RoleController extends Controller
{
    use Authorizable, AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private string $module = 'Role';

    /**
     * @return string
     */
    public function getModule(): string
    {
        return $this->module;
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('read', $this ), AccessDeniedHttpException::class, 'accessDenied');

        return response()->json(RoleResource::collection(Role::all()));
    }

    /**
     * Store a newly created resource in storage.
     * @return JsonResponse
     */
    public function store(): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('create', $this ), AccessDeniedHttpException::class, 'accessDenied');

        $attributes = Validator::make(request()->all(),
            [
                'name' => ['string', 'required'],
                'modules' => ['array', 'required'],
                'modules.*.id' => ['integer', 'required'],
                'modules.*.permissions' => ['array', 'required'],
            ])->validate();

        $attributes['code'] = Str::slug($attributes['name']);

        throw_if((new Role)->isDuplicate($attributes['code']), AlreadyExistsException::class);

        $modules = $attributes['modules'];
        unset($attributes['modules']);

        $newRole = Role::Create($attributes);

        foreach ($modules as $module)
        {
            RolePermission::updateOrCreate([
                'fk_role_id' => $newRole->id,
                'fk_module_id' => $module['id'],
                'create' => $module['permissions']['create'],
                'read' => $module['permissions']['read'],
                'update' => $module['permissions']['update'],
                'delete' => $module['permissions']['delete'],
            ]);
        }

        return response()->json($newRole, 201);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('read', $this ), AccessDeniedHttpException::class, 'Forbidden');

        return response()->json(RoleResource::make(Role::find($id)));
    }

    /**
     * Update the specified resource in storage.
     * @param int $id
     * @return JsonResponse
     */
    public function update(int $id): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('update', $this ), AccessDeniedHttpException::class, 'accessDenied');

        $attributes = Validator::make(request()->all(),
            [
                'name' => ['string', 'required'],
                'modules' => ['array', 'required'],
                'modules.*.id' => ['integer', 'required'],
                'modules.*.permissions' => ['array', 'required'],
            ])->validate();

        $attributes['code'] = Str::slug($attributes['name']);

        $Role = Role::find($id);

        throw_if($Role->isDuplicate($attributes['code']), AlreadyExistsException::class);

        $modules = $attributes['modules'];
        unset($attributes['modules']);

        $Role->update($attributes);

        $removeModules = RolePermission::where('fk_role_id', $id)->whereNotIn('fk_module_id', [1])->get()->pluck('fk_module_id')->toArray();
        RolePermission::where('fk_role_id', $id)->whereIn('fk_module_id', $removeModules)->delete();
        foreach ($modules as $module)
        {
            RolePermission::updateOrCreate([
                'fk_role_id' => $id,
                'fk_module_id' => $module['id'],
                'create' => $module['permissions']['create'],
                'read' => $module['permissions']['read'],
                'update' => $module['permissions']['update'],
                'delete' => $module['permissions']['delete'],
            ]);
        }

        return response()->json($Role, 200);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('delete', $this ), AccessDeniedHttpException::class, 'accessDenied');

        RolePermission::where('fk_role_id', $id)->delete();
        Role::find($id)->delete();
        return response()->json(null, 204);
    }

    /**
     * Вычисляет элементы для добавления и для удаления
     *
     * @param array $newArray Массив новых значений
     * @param array $nowArray Массив текущих значений
     * @return object
     */
    private static function diff(array $newArray, array $nowArray): object
    {
        $new = [];
        foreach ($newArray as $item) {
            if (array_search($item, $nowArray) === false) {
                $new[] = $item;
            }
        }
        $deleted = [];
        foreach ($nowArray as $item) {
            if (array_search($item, $newArray) === false) {
                $deleted[] = $item;
            }
        }

        return (object)[
            'new' => $new,
            'removed' => $deleted
        ];
    }
}
