<?php

namespace Modules\User\Http\Controllers;

use App\Exceptions\AlreadyExistsException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\User\Entities\User;
use Modules\User\Entities\UsersGroup;
use Modules\User\Transformers\UserResource;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class UserController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private string $module = 'User';

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('read', $this ), AccessDeniedHttpException::class, 'accessDenied');

        return response()->json(UserResource::collection(User::orderBy('id')->get()), 200);
    }

    /**
     * Store a newly created resource in storage.
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('create', $this ), AccessDeniedHttpException::class, 'accessDenied');

        $attributes = Validator::make(\request()->all(), [
            'login' => [
                'string',
                'required'
            ],
            'password' => [
                'string',
                'nullable'
            ],
            'email' => [
                'string',
                'required'
            ],
            'role' => [
                'integer',
                'required'
            ],
            'lastname' => [
                'string',
                'nullable'
            ],
            'firstname' => [
                'string',
                'nullable'
            ],
            'secondname' => [
                'string',
                'nullable'
            ],
        ])->validate();

        if ($attributes['password']) {
            $attributes['password'] = User::hashPassword($attributes['password']);
        } else {
            unset($attributes['password']);
        }

        if ((new User)->fill($attributes)->isDuplicate($attributes['login'])) throw new AlreadyExistsException();

        try {
            $newUser = User::Create($attributes);
        } catch (QueryException $queryException) {
            throw new QueryException($queryException->getSql(), $queryException->getBindings(), $queryException);
        }

        response()->json(null, 201);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('read', $this ), AccessDeniedHttpException::class, 'accessDenied');

        return response()->json(UserResource::collection(User::where('id', (int)$id)->get()), 200);
    }

    /**
     * Update the specified resource in storage.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(int $id): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('update', $this ), AccessDeniedHttpException::class, 'accessDenied');

        $attributes = Validator::make(\request()->all(), [
            'login' => [
                'string',
                'required'
            ],
            'password' => [
                'string',
                'nullable'
            ],
            'email' => [
                'string',
                'required'
            ],
            'role' => [
                'integer',
                'required'
            ],
            'lastname' => [
                'string',
                'nullable'
            ],
            'firstname' => [
                'string',
                'nullable'
            ],
            'secondname' => [
                'string',
                'nullable'
            ],
        ])->validate();

        $user = User::find($id);

        if ($user->isDuplicate($attributes['login'])) throw new AlreadyExistsException();

        if ($user === null) throw new NotFoundResourceException('userNotFound');

        if ($attributes['password']) {
            $attributes['password'] = User::hashPassword($attributes['password']);
        } else {
            unset($attributes['password']);
        }

        try {
            $savedData = $user->update($attributes);
        } catch (QueryException $queryException) {
            throw new QueryException($queryException->getSql(), $queryException->getBindings(), $queryException);
        }

        response()->json($savedData, 200);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('delete', $this ), AccessDeniedHttpException::class, 'accessDenied');

        $user = User::find($id);

        if ($user === null) throw new NotFoundResourceException('userNotFound');

        UsersGroup::where('fk_user_id', $user->id)->delete();

        return response()->json($user->delete(), 204);
    }
}
