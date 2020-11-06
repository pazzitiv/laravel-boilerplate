<?php

namespace Modules\Group\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Group\Entities\Group;

class GroupDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        Group::create([
            'name' => 'Тестовая группа',
            'description' => 'Тестовая группа для разработки',
        ]);
        // $this->call("OthersTableSeeder");
    }
}
