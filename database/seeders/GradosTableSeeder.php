<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GradosTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('grados')->delete();
        
        \DB::table('grados')->insert(array (
            0 => 
            array (
                'id' => 1,
                'tipo' => 'Kyu',
                'numero' => '10mo.',
                'nombre' => 'Cinturon Blanco',
                'created_at' => '2026-04-23 13:02:59',
                'updated_at' => '2026-04-23 21:36:43',
                'registerUser_id' => 1,
                'registerRole' => 'admin',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
                'puntas' => 3,
                'dias' => 90,
                'status' => 1,
            ),
            1 => 
            array (
                'id' => 2,
                'tipo' => 'Kyu',
                'numero' => '9no-',
                'nombre' => 'Cinturon Blanco Franja Amarilla',
                'created_at' => '2026-04-23 21:28:29',
                'updated_at' => '2026-04-23 21:28:29',
                'registerUser_id' => 1,
                'registerRole' => 'admin',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
                'puntas' => 3,
                'dias' => 90,
                'status' => 1,
            ),
            2 => 
            array (
                'id' => 3,
                'tipo' => 'Kyu',
                'numero' => '8vo.',
                'nombre' => 'Cinturon Amarillo',
                'created_at' => '2026-04-23 21:29:37',
                'updated_at' => '2026-04-23 21:30:38',
                'registerUser_id' => 1,
                'registerRole' => 'admin',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
                'puntas' => 4,
                'dias' => 120,
                'status' => 1,
            ),
            3 => 
            array (
                'id' => 4,
                'tipo' => 'Kyu',
                'numero' => '7mo.',
                'nombre' => 'Cinturon Naranja',
                'created_at' => '2026-04-23 21:30:20',
                'updated_at' => '2026-04-23 21:36:49',
                'registerUser_id' => 1,
                'registerRole' => 'admin',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
                'puntas' => 6,
                'dias' => 180,
                'status' => 0,
            ),
        ));
        
        
    }
}