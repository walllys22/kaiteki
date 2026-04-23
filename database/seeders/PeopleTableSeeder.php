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
                'registerUser_id' => 2,
                'registerRole' => 'administrador',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
        ));
        
        
    }
}