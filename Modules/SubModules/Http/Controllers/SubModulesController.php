<?php

namespace Modules\SubModules\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\SubModules\Entities\Submodule;
use Modules\SubModules\Helpers\ModuleSystem;
use Modules\SubModules\Transformers\SubmoduleResource;

class SubModulesController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Список модулей системы
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return \response()->json(SubmoduleResource::collection(Submodule::all()));
    }

    /**
     * Список исходных модулей древовидно
     * @return JsonResponse
     */
    public function tree(): JsonResponse
    {
        return \response()->json(ModuleSystem::getModulesAsTree());
    }
}
