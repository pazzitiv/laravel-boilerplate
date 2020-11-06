<?php

namespace Modules\Group\Http\Controllers;

use App\Exceptions\AlreadyExistsException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Group\Entities\Group;
use Modules\Group\Transformers\GroupResource;
use Modules\User\Entities\UsersGroup;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class GroupController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private string $module = 'Group';

    /**
     * Показать список групп
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('read', $this ), AccessDeniedHttpException::class, 'accessDenied');

        return response()->json(GroupResource::collection(Group::orderBy('id')->get()), 200);
    }

    /**
     * Создание группы
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('create', $this ), AccessDeniedHttpException::class, 'accessDenied');

        $attributes = Validator::make(\request()->all(), [
            'name' => [
                'string',
                'required'
            ],
            'description' => [
                'string',
                'nullable'
            ],
            'users' => [
                'array',
                'present'
            ]
        ])->validate();

        if ((new Group())->fill($attributes)->isDuplicate($attributes['name'])) throw new AlreadyExistsException();

        $users = $attributes['users'];
        unset($attributes['users']);

        try {
            $newGroup = Group::Create($attributes);
        } catch (QueryException $queryException) {
            throw new QueryException($queryException->getSql(), $queryException->getBindings(), $queryException);
        }

        $this->setusers($newGroup->id, $users);

        response()->json(null, 201);
    }

    /**
     * Показать группу по ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('read', $this ), AccessDeniedHttpException::class, 'accessDenied');

        return response()->json(GroupResource::collection(Group::where('id', (int)$id)->get()), 200);
    }

    /**
     * Изменение группы
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(int $id): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('update', $this ), AccessDeniedHttpException::class, 'accessDenied');

        $attributes = Validator::make(\request()->all(), [
            'name' => [
                'string',
                'required'
            ],
            'description' => [
                'string',
                'nullable'
            ],
            'users' => [
                'array',
                'present'
            ]
        ])->validate();

        $group = Group::find($id);

        if ($group->isDuplicate($attributes['name'])) throw new AlreadyExistsException();

        if ($group === null) throw new NotFoundResourceException('groupNotFound');

        $users = $attributes['users'];
        unset($attributes['users']);

        try {
            $savedData = $group->update($attributes);
            $this->setusers($id, $users);
        } catch (QueryException $queryException) {
            throw new QueryException($queryException->getSql(), $queryException->getBindings(), $queryException);
        }

        response()->json($savedData, 200);
    }

    /**
     * Удаление группы
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('delete', $this ), AccessDeniedHttpException::class, 'accessDenied');

        $group = Group::find($id);

        if ($group === null) throw new NotFoundResourceException('groupNotFound');

        UsersGroup::where('fk_group_id', $group->id)->delete();

        return response()->json($group->delete(), 204);
    }

    /**
     * Добавление пользователей в группу
     *
     * @param int $id
     * @param array|null $users
     * @return array|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function setusers(int $id, array $users = null)
    {
        $user = request()->user();
        throw_if($user->cannot('create', $this ) && $user->cannot('update', $this), AccessDeniedHttpException::class, 'accessDenied');

        if($users) {
            $attributes = [
                'users' => $users
            ];
        } else {
            $attributes = Validator::make(\request()->all(), [
                'users' => [
                    'array',
                    'present'
                ],
            ])->validate();
        }

        $nowUsers = UsersGroup::where('fk_group_id', (int)$id)->lockForUpdate()->get();

        $diffUsers = self::diff(array_map(fn($item) => (int)$item, $attributes['users']), $nowUsers->pluck('fk_user_id')->toArray());

        $GroupUsers= (object) [
            'added' => [],
            'removed' => []
        ];
        foreach ($diffUsers->new as $item) {
            UsersGroup::updateOrCreate([
                'fk_user_id' => $item,
                'fk_group_id' => $id
            ]);
            $GroupUsers->added[] = $item;
        }

        foreach ($diffUsers->removed as $item) {
            UsersGroup::where('fk_user_id', $item)->where('fk_group_id', $id)->delete();
            $GroupUsers->removed[] = $item;
        }

        UsersGroup::where('fk_group_id', (int)$id)->sharedLock()->get();
        return $users ?? response()->json($GroupUsers);
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
