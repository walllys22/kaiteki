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
                'puntas' => 3,
                'dias' => 90,
                'status' => 1,
                'orden' => 1,
                'created_at' => '2026-04-23 09:02:59',
                'updated_at' => '2026-04-23 17:36:43',
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
                'tipo' => 'Kyu',
                'numero' => '9no.',
                'nombre' => 'Cinturon Blanco Franja Amarilla',
                'puntas' => 3,
                'dias' => 90,
                'status' => 1,
                'orden' => 2,
                'created_at' => '2026-04-23 17:28:29',
                'updated_at' => '2026-04-24 15:01:32',
                'registerUser_id' => NULL,
                'registerRole' => NULL,
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            2 =>
            array (
                'id' => 3,
                'tipo' => 'Kyu',
                'numero' => '8vo.',
                'nombre' => 'Cinturon Amarillo',
                'puntas' => 4,
                'dias' => 120,
                'status' => 1,
                'orden' => 3,
                'created_at' => '2026-04-23 17:29:37',
                'updated_at' => '2026-04-23 17:30:38',
                'registerUser_id' => NULL,
                'registerRole' => NULL,
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            3 =>
            array (
                'id' => 4,
                'tipo' => 'Kyu',
                'numero' => '7mo.',
                'nombre' => 'Cinturon Naranja',
                'puntas' => 4,
                'dias' => 120,
                'status' => 1,
                'orden' => 4,
                'created_at' => '2026-04-23 17:30:20',
                'updated_at' => '2026-04-24 19:01:57',
                'registerUser_id' => NULL,
                'registerRole' => NULL,
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            4 =>
            array (
                'id' => 5,
                'tipo' => 'Kyu',
                'numero' => '6to',
                'nombre' => 'Cinturon Azul',
                'puntas' => 6,
                'dias' => 180,
                'status' => 1,
                'orden' => 5,
                'created_at' => '2026-04-24 19:00:31',
                'updated_at' => '2026-04-24 19:00:31',
                'registerUser_id' => NULL,
                'registerRole' => NULL,
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            5 =>
            array (
                'id' => 6,
                'tipo' => 'Kyu',
                'numero' => '5to',
                'nombre' => 'Cinturon Lila',
                'puntas' => 6,
                'dias' => 180,
                'status' => 1,
                'orden' => 6,
                'created_at' => '2026-04-24 19:02:40',
                'updated_at' => '2026-04-24 19:02:40',
                'registerUser_id' => NULL,
                'registerRole' => NULL,
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            6 =>
            array (
                'id' => 7,
                'tipo' => 'Kyu',
                'numero' => '4to',
                'nombre' => 'Cinturon Verde',
                'puntas' => 6,
                'dias' => 180,
                'status' => 1,
                'orden' => 7,
                'created_at' => '2026-04-24 19:21:17',
                'updated_at' => '2026-04-24 19:21:17',
                'registerUser_id' => NULL,
                'registerRole' => NULL,
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            7 =>
            array (
                'id' => 8,
                'tipo' => 'Kyu',
                'numero' => '3er',
                'nombre' => 'Cinturon Marron Tres Puntas',
                'puntas' => 3,
                'dias' => 180,
                'status' => 1,
                'orden' => 8,
                'created_at' => '2026-04-24 19:23:35',
                'updated_at' => '2026-04-24 19:23:35',
                'registerUser_id' => NULL,
                'registerRole' => NULL,
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            8 =>
            array (
                'id' => 9,
                'tipo' => 'Kyu',
                'numero' => '2do',
                'nombre' => 'Cinturon Marron Dos Puntas',
                'puntas' => 3,
                'dias' => 360,
                'status' => 1,
                'orden' => 9,
                'created_at' => '2026-04-24 19:24:06',
                'updated_at' => '2026-04-24 19:24:06',
                'registerUser_id' => NULL,
                'registerRole' => NULL,
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            9 =>
            array (
                'id' => 10,
                'tipo' => 'Kyu',
                'numero' => '1er',
                'nombre' => 'Cinturon Marron Una Punta',
                'puntas' => 2,
                'dias' => 359,
                'status' => 1,
                'orden' => 10,
                'created_at' => '2026-04-24 19:24:38',
                'updated_at' => '2026-04-24 19:24:38',
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