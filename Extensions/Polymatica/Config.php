<?php

namespace Extensions\Polymatica;


trait Config
{
    private static function config(): object
    {
        return (object)[
            'host' => env('POLYMATICA_HOST', '127.0.0.1'),
            'port' => env('POLYMATICA_PORT', 8080),
            'odbcport' => env('POLYMATICA_ODBC_PORT', 9090),
            'ssl' => env('POLYMATICA_SSL', false),
            'login' => env('POLYMATICA_USER', 'admin'),
            'password' => env('POLYMATICA_PASSWORD', ''),
        ];
    }
}
