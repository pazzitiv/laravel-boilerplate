<?php


namespace Extensions\Adapters;


interface IAdapter
{
    public static function do($data): array;
}
