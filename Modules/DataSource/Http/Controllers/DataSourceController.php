<?php

namespace Modules\DataSource\Http\Controllers;

use Extensions\Adapters\HighchartsAdapter;
use Extensions\Adapters\PolymaticaAdapter;
use Extensions\Polymatica\Api;
use Extensions\Polymatica\PolymaticaModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DataSourceController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private string $module = 'DataSource';

    private $driver;

    public function __construct()
    {
        $driver = 'Modules\\DataSource\\Drivers\\' . Config::get('datasource.driver');
        $this->driver = new $driver;
    }


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
        throw_if($user->cannot('read', $this), AccessDeniedHttpException::class, 'accessDenied');

        $result = null;

        $driver = $this->driver;

        $dimensions = ["Дата", "Дата месяц", "Дата год"];
        $measures = ["Премия", "Соц. Выплата"];
        $groupDimensions = ["Отдел", "Регион", "Город"];

        $driver->init('Тест')
            ->data(
                $dimensions,
                $measures,
                $groupDimensions
            );
        $driver->disconnect();

        $collection = new Collection(PolymaticaAdapter::do($driver->getProvider()));

        $data = $collection
            //->where('Город', 'Москва')
            //->where('Регион', 'Центральный')
            ->where('Дата год', '2018');

        return response()->json(
            HighchartsAdapter::do(
                (object)[
                    'data' => $data->toArray(),
                    //'category' => current($groupDimensions),
                    'serie' => current($dimensions),
                    'value' => $measures[0]
                ])
        );
    }

    /**
     * Store a newly created resource in storage.
     * @return JsonResponse
     */
    public function store(): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('create', $this), AccessDeniedHttpException::class, 'accessDenied');

        return response()->json(null);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('read', $this), AccessDeniedHttpException::class, 'accessDenied');

        return response()->json(null);
    }

    /**
     * Update the specified resource in storage.
     * @param int $id
     * @return JsonResponse
     */
    public function update(int $id): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('update', $this), AccessDeniedHttpException::class, 'accessDenied');

        return response()->json(null);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $user = request()->user();
        throw_if($user->cannot('delete', $this), AccessDeniedHttpException::class, 'accessDenied');

        return response()->json(null);
    }
}
