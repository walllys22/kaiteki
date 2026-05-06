<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ParentescosTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('parentescos')->delete();
        
        \DB::table('parentescos')->insert(array (
            0 => 
            array (
                'id' => 1,
                'nombre' => 'Tio',
                'created_at' => '2026-04-23 19:44:38',
                'updated_at' => '2026-04-23 19:44:38',
                'deleted_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'nombre' => 'Abuelo',
                'created_at' => '2026-04-23 19:44:43',
                'updated_at' => '2026-04-23 19:44:52',
                'deleted_at' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'nombre' => 'Abuela',
                'created_at' => '2026-04-23 19:44:48',
                'updated_at' => '2026-04-23 19:44:48',
                'deleted_at' => NULL,
            ),
            3 => 
            array (
                'id' => 4,
                'nombre' => 'Hermano',
                'created_at' => '2026-04-23 19:45:00',
                'updated_at' => '2026-04-23 19:45:00',
                'deleted_at' => NULL,
            ),
            4 => 
            array (
                'id' => 5,
                'nombre' => 'Hermana',
                'created_at' => '2026-04-23 19:45:05',
                'updated_at' => '2026-04-23 19:45:05',
                'deleted_at' => NULL,
            ),
            5 => 
            array (
                'id' => 6,
                'nombre' => 'Tia',
                'created_at' => '2026-04-23 19:45:10',
                'updated_at' => '2026-04-23 19:45:10',
                'deleted_at' => NULL,
            ),
            6 => 
            array (
                'id' => 7,
                'nombre' => 'Padre',
                'created_at' => '2026-04-23 19:45:21',
                'updated_at' => '2026-04-23 19:45:21',
                'deleted_at' => NULL,
            ),
            7 => 
            array (
                'id' => 8,
                'nombre' => 'Madre',
                'created_at' => '2026-04-23 19:45:26',
                'updated_at' => '2026-04-23 19:45:26',
                'deleted_at' => NULL,
            ),
            8 => 
            array (
                'id' => 9,
                'nombre' => 'Otro',
                'created_at' => '2026-04-23 19:45:41',
                'updated_at' => '2026-04-23 19:45:41',
                'deleted_at' => NULL,
            ),
        ));
        
        
    }
}