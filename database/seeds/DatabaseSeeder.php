<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(\Modules\Group\Database\Seeders\GroupDatabaseSeeder::class)
            ->call(\Modules\Role\Database\Seeders\RoleDatabaseSeeder::class)
            ->call(\Modules\User\Database\Seeders\UserDatabaseSeeder::class)
            ->call(\Modules\Polymatica\Database\Seeders\PolymaticaDatabaseSeeder::class)
            ;
    }
}
