<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Group\Entities\Group;
use Modules\User\Entities\UsersGroup;

class UsersGroupDatabaseSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        UsersGroup::create([
            'fk_user_id' => 1,
            'fk_group_id' => 1,
        ]);
        // $this->call("OthersTableSeeder");
    }
}
