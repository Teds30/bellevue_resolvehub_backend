<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Department;
use App\Models\Issue;
use App\Models\Permission;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);


        Department::factory()->count(3)->sequence(
            ['name' => 'Front-Office'],
            ['name' => 'IT Department'],
            ['name' => 'Housekeeping'],
        )->create();

        Position::factory()->count(4)->sequence(
            ['name' => 'Receptionist', "department_id" => 1],
            ['name' => 'Guest Relations Supervisor', "department_id" => 1],
            ['name' => 'Software Engineer', "department_id" => 2],
            ['name' => 'IT Officer', "department_id" => 2],
        )->create();

        Issue::factory()->count(2)->sequence(
            ['name' => 'Network: No internet'],
            ['name' => 'Network: Slow internet'],
        )->create();

        Permission::factory()->count(2)->sequence(
            ['position_id' => 1, 'access_name' => 'metrics'],
            ['position_id' => 2, 'access_name' => 'can_createissue'],
        )->create();

        User::factory()->count(1)->create();
    }
}
