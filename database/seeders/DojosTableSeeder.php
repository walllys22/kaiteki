<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DojosTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('dojos')->delete();
        
        \DB::table('dojos')->insert(array (
            0 => 
            array (
                'id' => 1,
                'nombre' => 'DOJO TRINIDAD',
                'logo' => 'dojos/April2026/htdwglnrnjrCeFy1oKHpday23am.jpg',
                'person_id' => 1,
                'ciudad_id' => 1,
                'phone' => NULL,
                'address' => NULL,
                'email' => NULL,
                'status' => 1,
                'created_at' => '2026-04-23 11:31:47',
                'updated_at' => '2026-04-23 11:57:13',
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
                'nombre' => 'DOJO SANTA CRUZ',
                'logo' => NULL,
                'person_id' => NULL,
                'ciudad_id' => 2,
                'phone' => NULL,
                'address' => NULL,
                'email' => NULL,
                'status' => 1,
                'created_at' => '2026-04-23 12:21:36',
                'updated_at' => '2026-04-23 12:21:36',
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