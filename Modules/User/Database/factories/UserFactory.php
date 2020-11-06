<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Modules\User\Entities\User;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| PolymaticaModel Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker): array {
    return [
        'login' => $faker->unique()->userName,
        'email' => $faker->safeEmail,
        'role' => 1,
        'lastname' => $faker->lastName,
        'firstname' => random_int(0, 1) ? $faker->firstNameFemale : $faker->firstNameMale,
        'secondname' => $faker->middleName,
        'password' => Hash::make('123qwe'),
        'active' => (bool) random_int(0, 1),
    ];
});
