<?php


namespace Extensions\Adapters;

use Extensions\Polymatica\Api;

/**
 * Class PolymaticaAdapter
 * @package Extensions\Adapters
 *
 * Адаптирует данные из Полиматики в представление Relative Table
 */
class PolymaticaAdapter extends Adapter
{
    private static Api $api;
    private static array $fulldata;

    private static array $top;
    private static array $left;
    private static array $facts;
    private static array $dimensions;
    private static array $data;

    /**
     * PolymaticaAdapter constructor.
     * @param Api $api
     */
    public function __construct(Api $api)
    {
        self::$api = $api;
    }

    public static function do($data): array
    {
        return self::transform($data);
    }

    public static function transform(Api $api = null): array
    {
        self::$api = $api instanceof Api ? $api : self::$api;

        throw_if(!self::$api instanceof Api, \InvalidArgumentException::class, 'Не инициализирован API Полиматики');

        self::$fulldata = current(self::$api->getRawData());
        self::$data = self::$fulldata['data'];

        self::$top = self::$fulldata['top'];
        self::$left = self::$fulldata['left'];
        self::$facts = self::$api->getFacts();

        return self::fill();
    }

    private static function fill()
    {
        $result = [];

        $rowNum = 0;
        foreach (self::$left as $rowId => $row) {
            $topValues = [];
            $topparts = [];
            foreach (self::$top as $topId => $top) {
                if (self::isLastIndex($topId, self::$top)) {
                    foreach ($top as $factIndex => $fact) {
                        $factName = current(array_filter(self::$facts, fn($item) => $item['id'] === $fact['fact_id']))['name'];

                        foreach ($topValues[$factIndex] as $key => $value)
                        {
                            $topparts[$factIndex]['dimensions'][$key] = $value;
                        }

                        $topparts[$factIndex]['fact'][$factName] = self::$data[$rowId][$factIndex];
                    }
                    break;
                }

                foreach ($top as $dimIndex => $topdim) {
                    if(count(self::$fulldata['top_dims']) === 0) {
                        $dim = ['name' => 'Всего'];
                    } else {
                        $dim = current(array_filter(self::$api->getDims(), fn($item) => $item['id'] === self::$fulldata['top_dims'][$topId]));
                    }

                    $topValues[$dimIndex] = $topValues[$dimIndex] ?? [];

                    switch ($topdim['type']) {
                        case 1:
                            $topValues[$dimIndex][$dim['name']] = self::checkMergedCell($dimIndex, $top) ?? 'Объединение';
                            break;
                        case 2:
                        default:
                            $topValues[$dimIndex][$dim['name']] = $topdim['value'];
                            break;
                        case 5:
                            $topValues[$dimIndex][$dim['name']] = 'Всего';
                            break;
                    }
                }
            }

            foreach ($row as $leftId => $left) {
                switch ($left['type']) {
                    case 1:
                        $dim = current(array_filter(self::$api->getDims(), fn($item) => $item['id'] === self::$fulldata['left_dims'][$leftId]));
                        $value = self::checkMergedCell($rowId, array_map(fn($item) => $item[$leftId], self::$left));
                        $leftpart[$dim['name']] = $value ?? 'Всего';
                        break;
                    case 2:
                    default:
                        $dim = current(array_filter(self::$api->getDims(), fn($item) => $item['id'] === self::$fulldata['left_dims'][$leftId]));
                    $leftpart[$dim['name']] = $left['value'];
                        break;
                    case 5:
                        $dim = current(array_filter(self::$api->getDims(), fn($item) => $item['id'] === self::$fulldata['left_dims'][$leftId]));
                        $leftpart[$dim['name']] = 'Всего';
                        break;
                }
            }

            $toppart = [];
            $t = 0;
            foreach ($topparts as $key => $value)
            {
                if(isset($topparts[$key - 1]) && $topparts[$key - 1]['dimensions'] === $value['dimensions']) {
                    $toppart[$t - 1] = array_merge_recursive($toppart[$t - 1], $value['fact']);
                } else {
                    $toppart[$t] = $value['dimensions'];
                    $toppart[$t] = array_merge_recursive($toppart[$t], $value['fact']);
                    $t++;
                }
            }

            foreach ($toppart as $part)
            {
                $result[$rowNum] = array_merge_recursive($leftpart, $part);
                $rowNum++;
            }

            $rowNum++;
        }

        return array_values($result);
    }

    private static function checkMergedCell($index, $array)
    {
        $previousIndex = $index - 1;

        if (!isset($array[$previousIndex])) return null;

        switch ($array[$previousIndex]['type']) {
            case 1:
                return self::checkMergedCell($previousIndex, $array);
            case 2:
            default:
                return $array[$previousIndex]['value'] ?? null;
            case 5:
                return 'Всего';
        }
    }
}
