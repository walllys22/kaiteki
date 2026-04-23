<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DojoUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('users')->insert(array(
            0 =>
            array(
                'id' => 3,
                'role_id' => 2,
                'name' => 'Ignacio Molina Guzman',
                'email' => 'ignacio@admin.com',
                'avatar' => 'users/default.png',
                'email_verified_at' => NULL,
                'password' => '$2y$10$VMpOHgQeYWWJFsxfDgEtyei3r3XijA5sBO9NfLNtbr9adC.Oovkvi',
                'remember_token' => NULL,
                'settings' => NULL,
                'created_at' => '2026-04-23 11:32:39',
                'updated_at' => '2026-04-23 11:32:39',
                'status' => 1,
                'person_id' => 1,
                'dojo_id' => 1,
                'registerUser_id' => 1,
                'registerRole' => 'admin',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
        ));
    }
}
