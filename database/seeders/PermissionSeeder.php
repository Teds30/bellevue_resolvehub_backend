<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        // Permission::factory()->count(2)->sequence(
        //     ['position_id' => 1, 'access_name' => 'view_department_tasks'],
        //     ['position_id' => 2, 'access_name' => 'view_my_tasks'],
        //     ['position_id' => 3, 'access_name' => 'can_createissue'],
        //     ['position_id' => 4, 'access_name' => 'can_createissue'],
        //     ['position_id' => 5, 'access_name' => 'can_createissue'],
        //     ['position_id' => 6, 'access_name' => 'can_createissue'],
        //     ['position_id' => 7, 'access_name' => 'can_createissue'],
        // )->create();
    }
}
