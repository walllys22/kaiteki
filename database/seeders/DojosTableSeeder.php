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
                'nombre' => 'JMA - Juana Moreno Aulo',
                'logo' => 'dojos/May2026/74H0QJ9Lt4Y3IFKcMrkKday02pm.avif',
                'grado_responsable' => 'Sensei 2do Dan',
                'person_id' => 1,
                'ciudad_id' => 1,
                'phone' => '60207868',
                'address' => 'Calle Felix Satori nro 658',
                'email' => 'dojojma@gmail.com',
                'status' => 1,
                'created_at' => '2026-04-24 08:19:58',
                'updated_at' => '2026-05-12 16:23:18',
                'registerUser_id' => NULL,
                'registerRole' => '',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'nombre' => 'Gusuku Dojo',
                'logo' => 'dojos/May2026/6AvzZd6mUHhkkPcgx2gtday02pm.avif',
                'grado_responsable' => 'Sensei 1er Dan',
                'person_id' => 2,
                'ciudad_id' => 3,
                'phone' => '73678424 / 77848840',
                'address' => 'Av. Juan de la Rosa esq. Av. América, Torre Rocris planta baja.',
                'email' => 'gusukudojo25@gmail.com',
                'status' => 1,
                'created_at' => '2026-04-24 08:20:27',
                'updated_at' => '2026-05-12 16:24:09',
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
                'nombre' => 'L.J.P. Zabala Dojo',
                'logo' => 'dojos/May2026/NcAuuJeWzTTb5csqzLLCday02pm.avif',
                'grado_responsable' => 'Renshi 5to Dan',
                'person_id' => 7,
                'ciudad_id' => 1,
                'phone' => '71148006',
                'address' => 'Calle Felix Satori nro 658',
                'email' => 'dojo.ljp.zabala@gmail.com',
                'status' => 1,
                'created_at' => '2026-04-24 14:27:11',
                'updated_at' => '2026-05-12 16:22:48',
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
                'nombre' => 'E.K.A. Dojo',
                'logo' => 'dojos/May2026/kMrmUtYWo2DG27Tvg4J4day02pm.avif',
                'grado_responsable' => 'Sensei 2do Dan',
                'person_id' => 6,
                'ciudad_id' => 1,
                'phone' => '75890956',
                'address' => 'Calle Felix Satori nro 658',
                'email' => NULL,
                'status' => 1,
                'created_at' => '2026-04-24 14:28:53',
                'updated_at' => '2026-05-12 16:23:36',
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
                'nombre' => 'Arakaki Dojo',
                'logo' => 'dojos/May2026/rLTWmjjZFoTTwAfpOpzNday02pm.avif',
                'grado_responsable' => 'Sensei 4to Dan',
                'person_id' => 5,
                'ciudad_id' => 1,
                'phone' => '73900174',
                'address' => 'Calle Julio Céspedes 172, Trinidad',
                'email' => NULL,
                'status' => 1,
                'created_at' => '2026-04-24 14:30:22',
                'updated_at' => '2026-05-12 16:23:50',
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
                'nombre' => 'Shinsei Dojo',
                'logo' => 'dojos/May2026/IG451SLNPAILSkMS1oytday02pm.avif',
                'grado_responsable' => 'Sensei 4to Dan',
                'person_id' => 15,
                'ciudad_id' => 1,
                'phone' => '72824170',
                'address' => 'Calle Felix Satori nro 658',
                'email' => NULL,
                'status' => 1,
                'created_at' => '2026-04-25 00:36:23',
                'updated_at' => '2026-05-12 16:22:08',
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
                'nombre' => 'Shorinkan Bolivia',
                'logo' => 'dojos/May2026/V8R7XL2eF4kjGqrQrb5pday07am.avif',
                'grado_responsable' => 'Renshi 5to Dan',
                'person_id' => 7,
                'ciudad_id' => 1,
                'phone' => '71148006',
                'address' => 'Calle Felix Satori nro 658',
                'email' => 'shorinkan.bolivia@gmail.com',
                'status' => 1,
                'created_at' => '2026-05-06 20:03:54',
                'updated_at' => '2026-05-12 16:22:32',
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