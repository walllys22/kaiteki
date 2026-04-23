<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PeopleTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('people')->delete();
        
        \DB::table('people')->insert(array (
            0 => 
            array (
                'id' => 1,
                'documentType' => 'Ci',
                'dojo_id' => NULL,
                'ci' => '7633685',
                'first_name' => 'Ignacio Molina Guzman',
                'birth_date' => '1997-03-08',
                'email' => 'ignaciomolinaguzman20@gmail.com',
                'country_code' => '591',
                'phone' => NULL,
                'address' => NULL,
                'gender' => 'Masculino',
                'image' => NULL,
                'status' => 1,
                'created_at' => '2026-04-23 10:46:26',
                'updated_at' => '2026-04-23 10:46:26',
                'registerUser_id' => 1,
                'registerRole' => 'admin',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'documentType' => 'Ci',
                'dojo_id' => NULL,
                'ci' => '244234sd',
                'first_name' => 'walter',
                'birth_date' => NULL,
                'email' => NULL,
                'country_code' => '591',
                'phone' => NULL,
                'address' => NULL,
                'gender' => 'Masculino',
                'image' => NULL,
                'status' => 1,
                'created_at' => '2026-04-23 12:25:47',
                'updated_at' => '2026-04-23 12:25:47',
                'registerUser_id' => 1,
                'registerRole' => 'admin',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'documentType' => 'Nit',
                'dojo_id' => 1,
                'ci' => '122333sdfsdf',
                'first_name' => 'juan',
                'birth_date' => NULL,
                'email' => NULL,
                'country_code' => '591',
                'phone' => NULL,
                'address' => NULL,
                'gender' => 'Masculino',
                'image' => NULL,
                'status' => 1,
                'created_at' => '2026-04-23 12:34:02',
                'updated_at' => '2026-04-23 12:34:02',
                'registerUser_id' => 3,
                'registerRole' => 'administrador',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            3 => 
            array (
                'id' => 4,
                'documentType' => 'Nit',
                'dojo_id' => 2,
                'ci' => '4234fds',
                'first_name' => 'Daniel',
                'birth_date' => NULL,
                'email' => NULL,
                'country_code' => '591',
                'phone' => NULL,
                'address' => NULL,
                'gender' => 'Masculino',
                'image' => NULL,
                'status' => 1,
                'created_at' => '2026-04-23 12:34:49',
                'updated_at' => '2026-04-23 12:34:49',
                'registerUser_id' => 4,
                'registerRole' => 'administrador',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            4 => 
            array (
                'id' => 5,
                'documentType' => 'Nit',
                'dojo_id' => NULL,
                'ci' => '12341234',
                'first_name' => 'Prueba administrador',
                'birth_date' => NULL,
                'email' => NULL,
                'country_code' => '591',
                'phone' => NULL,
                'address' => NULL,
                'gender' => 'Masculino',
                'image' => NULL,
                'status' => 1,
                'created_at' => '2026-04-23 12:35:14',
                'updated_at' => '2026-04-23 12:35:14',
                'registerUser_id' => 1,
                'registerRole' => 'admin',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            5 => 
            array (
                'id' => 6,
                'documentType' => 'Ci',
                'dojo_id' => 1,
                'ci' => '345432fdsgsdfg',
                'first_name' => 'Junior',
                'birth_date' => '2026-04-23',
                'email' => NULL,
                'country_code' => '591',
                'phone' => NULL,
                'address' => NULL,
                'gender' => 'Masculino',
                'image' => NULL,
                'status' => 1,
                'created_at' => '2026-04-23 12:47:40',
                'updated_at' => '2026-04-23 12:47:40',
                'registerUser_id' => 3,
                'registerRole' => 'administrador',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
        ));
        
        
    }
}