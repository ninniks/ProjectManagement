<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(9)->create();

        //Seeding one user with 'password' for test purpose because passwords are hashed
        User::factory()->create([
             'password' => Hash::make('password'),
             'email' => 'admin@fakemail.com'
        ]);

        Project::factory(10)->create();
        Task::factory(10)->create();
    }
}
