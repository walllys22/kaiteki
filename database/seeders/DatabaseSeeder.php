<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        $this->call(VoyagerDatabaseSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(CiudadsTableSeeder::class);
        $this->call(PeopleTableSeeder::class);
        $this->call(DojosTableSeeder::class);
        $this->call(DojoUsersTableSeeder::class);
    }
}
