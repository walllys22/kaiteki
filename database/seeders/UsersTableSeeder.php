<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'role_id' => 1,
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'avatar' => 'users/default.png',
                'email_verified_at' => NULL,
                'password' => '$2y$10$ILLZfhsbwinK3235ceVa7O0mj3M5fr33wb3z28aDqiBWLwBfSUzYy',
                'remember_token' => 's50ePpNF7KmjeTDifOaandO0P5mYpF8IAlmuMCGcIk3mjVn0OCITiXtb6xxj',
                'settings' => '{"locale":"es"}',
                'created_at' => '2024-10-18 14:28:45',
                'updated_at' => '2024-10-18 14:33:30',
                'status' => 1,
                'person_id' => NULL,
                'dojo_id' => NULL,
                'registerUser_id' => NULL,
                'registerRole' => NULL,
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            1 => 
            array (
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
            2 => 
            array (
                'id' => 4,
                'role_id' => 2,
                'name' => 'walter',
                'email' => 'walter@admin.com',
                'avatar' => 'users/default.png',
                'email_verified_at' => NULL,
                'password' => '$2y$10$5L6SoEXuQ2SjCQyBMD1z6.g15MdY1cHFpvW154ZMgFcoRXjITUWt6',
                'remember_token' => NULL,
                'settings' => NULL,
                'created_at' => '2026-04-23 12:26:09',
                'updated_at' => '2026-04-23 12:26:09',
                'status' => 1,
                'person_id' => 2,
                'dojo_id' => 2,
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