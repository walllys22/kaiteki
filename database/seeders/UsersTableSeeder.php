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
                'email' => 'admin@soluciondigital.dev',
                'avatar' => 'users/default.png',
                'email_verified_at' => NULL,
                'password' => '$2y$10$PpHOSYpilWVZYwTeZauiAu6//cV3IQiBuBk9B.88.lrVMwm5HFL5i',
                'remember_token' => 'azLJcVEfye4XZZUdpaFzqD8sbSRHrJ13b54wVtz0PJ4eAV5DaMIZdZs0QBCx',
                'settings' => '{"locale":"es"}',
                'created_at' => '2024-10-18 10:28:45',
                'updated_at' => '2026-05-03 13:05:48',
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
                'id' => 2,
                'role_id' => 2,
                'name' => 'Walter Landivar Limpias',
                'email' => 'wallys@admin.com',
                'avatar' => 'users/default.png',
                'email_verified_at' => NULL,
                'password' => '$2y$10$rW04pRMR4ZoyUkSIo4JYU.CT/pPblzYROkHptJgH5LU3oIjtPHZF2',
                'remember_token' => '3qRloAAbytJzc3S3DYEajz3bXLRffFjwlA5Dbjd6erbStMg99xicQMLflqNI',
                'settings' => NULL,
                'created_at' => '2026-05-03 13:07:11',
                'updated_at' => '2026-05-03 13:07:11',
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
        ));
        
        
    }
}
