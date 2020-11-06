<?php


namespace Extensions\Adapters;

/**
 * Class HighchartsAdapter
 * @package Extensions\Adapters
 *
 * Адаптирует данные из представления Relative table в представления для Highcharts
 */
class HighchartsAdapter extends Adapter
{
    public static function do($data): array
    {
        return self::transform($data);
    }

    /**
     * @param object $data
     *     data - массив данных,
     *     category - колонка Категории,
     *     serie - колонка Серий
     *     value - колонка Значения
     * @return array
     */
    public static function transform(object $data): array
    {
        $categories = property_exists($data, 'category') ? array_values(array_unique(array_map(fn($item) => $item[$data->category], $data->data))) : [];

        $serieNames = array_unique(array_values(array_map(fn($item) => $item[$data->serie], $data->data)));

        $series = [];
        foreach ($serieNames as $serieName)
        {
            $serieData = [];
            $values = array_filter($data->data, fn($item) => $item[$data->serie] === $serieName);

            if(count($categories) === 0) {
                $serieData[] = current($values)[$data->value];
            } else {
                foreach ($values as $value) {
                    $serieData[array_search($value[$data->category], $categories)] = $value[$data->value];
                }
            }

            $series[] = [
                'name' => $serieName,
                'data' => $serieData
            ];
        }

        return [
            'categories' => $categories,
            'series' => $series
        ];
    }
}
