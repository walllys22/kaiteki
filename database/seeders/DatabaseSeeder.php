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
        $this->call(DataTypesTableSeeder::class);
        $this->call(DataRowsTableSeeder::class);
        $this->call(MenuItemsTableSeeder::class);
        $this->call(GradosTableSeeder::class);
        $this->call(ParentescosTableSeeder::class);
        $this->call(DojosTableSeeder::class);
        $this->call(PeopleTableSeeder::class);
        $this->call(SettingsTableSeeder::class);
        $this->call(ArancelesTableSeeder::class);
        $this->call(HorariosTableSeeder::class);
        $this->call(HorarioReponsablesTableSeeder::class);
    }
}
