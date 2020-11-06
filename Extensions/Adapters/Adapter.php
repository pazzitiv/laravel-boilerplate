<?php


namespace Extensions\Adapters;


class Adapter implements IAdapter
{
    public static function do($data): array
    {
        return self::transform($data);
    }

    protected static function isLastIndex($index, array $array): bool
    {
        $keys = array_keys($array);
        return $index === end($keys);
    }

    protected static function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    private static function transform(array $data)
    {
        return $data;
    }
}
