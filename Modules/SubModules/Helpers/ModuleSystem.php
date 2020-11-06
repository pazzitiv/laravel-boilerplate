<?php


namespace Modules\SubModules\Helpers;


use Illuminate\Support\Arr;
use Modules\SubModules\Entities\Submodule;

class ModuleSystem
{
    /**
     * Получение списка модулей
     *
     * @return object[]
     */
    public static function getModules(): array
    {
        $modules = [];
        foreach (Config()->all() as $module) {
            if (Arr::has($module, 'moduleSystem')) {
                $modules[] = $module;
            }
        }

        return array_map(fn($module) => (object)[
            'name' => $module['name'],
            'code' => $module['moduleSystem']['module'],
            'parentCode' => $module['moduleSystem']['parentModule'],
        ], $modules);
    }

    /**
     * Получение списка модулей в виде дерева
     *
     * @param null|array $mods
     * @return object[]
     */
    public static function getModulesAsTree($mods = null): array
    {
        $sysmodules = $mods ?? self::getModules();

        foreach ($sysmodules as $key => $module) {
            if ($module->parentCode) {
                $modules = array_map(fn($item) => $item->code, $sysmodules);
                $parentKey = array_search($module->parentCode, $modules);

                if ($parentKey !== false) {
                    if (!property_exists($sysmodules[$parentKey], 'submodules')) $sysmodules[$parentKey]->submodules = [];
                    $sysmodules[$parentKey]->submodules[] = $module;
                }
            }
        }

        foreach ($sysmodules as $key => $module) {
            if ($module->parentCode !== null) unset($sysmodules[$key]);
            unset($module->{'parentCode'});
        }

        return array_values($sysmodules);
    }

    /**
     * Проверка родительского модуля
     *
     * @param array $module
     * @return bool
     */
    private static function isParentModule(array $module): bool
    {
        return $module['moduleSystem']['parentModule'] === null;
    }

    public static function getModuleCode(string $moduleName): string
    {
        $modules = self::getModules();

        foreach ($modules as $module) {
            if ($module->name === $moduleName) return $module->code;
        }

        return '';
    }
}
