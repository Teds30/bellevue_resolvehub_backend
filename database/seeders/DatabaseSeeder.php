<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Department;
use App\Models\Issue;
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

        Position::factory()->count(2)->sequence(
            ['name' => 'Receptionist'],
            ['name' => 'Guest Relations Supervisor'],
        )->create();

        Department::factory()->count(3)->sequence(
            ['name' => 'Front-Office'],
            ['name' => 'IT Department'],
            ['name' => 'Housekeeping'],
        )->create();

        Issue::factory()->count(2)->sequence(
            ['name' => 'Network: No internet'],
            ['name' => 'Network: Slow internet'],
        )->create();


        User::factory()->count(1)->create();
    }
}
