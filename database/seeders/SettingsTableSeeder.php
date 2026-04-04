<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('settings')->delete();
        
        \DB::table('settings')->insert(array (
            0 => 
            array (
                'id' => 1,
                'key' => 'site..title',
                'display_name' => 'Título del sitio',
                'value' => NULL,
                'details' => '',
                'type' => 'text',
                'order' => 1,
                'group' => 'Site',
            ),
            1 => 
            array (
                'id' => 2,
                'key' => 'site..description',
                'display_name' => 'Descripción del sitio',
                'value' => NULL,
                'details' => '',
                'type' => 'text',
                'order' => 2,
                'group' => 'Site',
            ),
            2 => 
            array (
                'id' => 3,
                'key' => 'site.logo',
                'display_name' => 'Logo del sitio',
                'value' => '',
                'details' => '',
                'type' => 'image',
                'order' => 3,
                'group' => 'Site',
            ),
            3 => 
            array (
                'id' => 5,
                'key' => 'admin.bg_image',
                'display_name' => 'Imagen de fondo del administrador',
                'value' => '',
                'details' => '',
                'type' => 'image',
                'order' => 5,
                'group' => 'Admin',
            ),
            4 => 
            array (
                'id' => 6,
                'key' => 'admin.title',
                'display_name' => 'Título del administrador',
                'value' => 'Solución Digital',
                'details' => '',
                'type' => 'text',
                'order' => 1,
                'group' => 'Admin',
            ),
            5 => 
            array (
                'id' => 7,
                'key' => 'admin.description',
                'display_name' => 'Descripción del administrador',
                'value' => 'Bienvenido a example',
                'details' => '',
                'type' => 'text',
                'order' => 2,
                'group' => 'Admin',
            ),
            6 => 
            array (
                'id' => 8,
                'key' => 'admin.loader',
                'display_name' => 'Imagen de carga del administrador',
                'value' => '',
                'details' => '',
                'type' => 'image',
                'order' => 3,
                'group' => 'Admin',
            ),
            7 => 
            array (
                'id' => 9,
                'key' => 'admin.icon_image',
                'display_name' => 'Ícono del administrador',
                'value' => '',
                'details' => '',
                'type' => 'image',
                'order' => 4,
                'group' => 'Admin',
            ),
            8 => 
            array (
                'id' => 11,
                'key' => 'system.development',
                'display_name' => 'Sistema en Mantenimiento 503',
                'value' => '0',
                'details' => NULL,
                'type' => 'checkbox',
                'order' => 1,
                'group' => 'System',
            ),
            9 => 
            array (
                'id' => 12,
                'key' => 'system.payment-alert',
                'display_name' => 'Alerta de Pago',
                'value' => '1',
                'details' => NULL,
                'type' => 'checkbox',
                'order' => 1,
                'group' => 'System',
            ),
            10 => 
            array (
                'id' => 13,
                'key' => 'system.code-system',
                'display_name' => 'Código del Sistema',
                'value' => 'demo',
                'details' => NULL,
                'type' => 'text',
                'order' => 2,
                'group' => 'System',
            ),
            11 => 
            array (
                'id' => 14,
                'key' => 'whatsapp.servidores',
                'display_name' => 'Servidor',
                'value' => NULL,
                'details' => NULL,
                'type' => 'text',
                'order' => 6,
                'group' => 'Whatsapp',
            ),
            12 => 
            array (
                'id' => 16,
                'key' => 'whatsapp.session',
                'display_name' => 'Session',
                'value' => NULL,
                'details' => NULL,
                'type' => 'text',
                'order' => 7,
                'group' => 'Whatsapp',
            ),
            13 => 
            array (
                'id' => 17,
                'key' => 'redes-sociales.whatsapp',
                'display_name' => 'Whatsapp',
                'value' => NULL,
                'details' => NULL,
                'type' => 'text',
                'order' => 8,
                'group' => 'Redes Sociales',
            ),
            14 => 
            array (
                'id' => 18,
                'key' => 'redes-sociales.facebook',
                'display_name' => 'Facebook',
                'value' => NULL,
                'details' => NULL,
                'type' => 'text',
                'order' => 9,
                'group' => 'Redes Sociales',
            ),
            15 => 
            array (
                'id' => 19,
                'key' => 'redes-sociales.instagram',
                'display_name' => 'Instagram',
                'value' => NULL,
                'details' => NULL,
                'type' => 'text',
                'order' => 10,
                'group' => 'Redes Sociales',
            ),
            16 => 
            array (
                'id' => 20,
                'key' => 'redes-sociales.tiktok',
                'display_name' => 'Tik Tok',
                'value' => NULL,
                'details' => NULL,
                'type' => 'text',
                'order' => 11,
                'group' => 'Redes Sociales',
            ),
            17 => 
            array (
                'id' => 21,
                'key' => 'redes-sociales.telegram',
                'display_name' => 'Telegram',
                'value' => NULL,
                'details' => NULL,
                'type' => 'text',
                'order' => 12,
                'group' => 'Redes Sociales',
            ),
            18 => 
            array (
                'id' => 22,
                'key' => 'redes-sociales.youtube',
                'display_name' => 'YouTube',
                'value' => NULL,
                'details' => NULL,
                'type' => 'text',
                'order' => 14,
                'group' => 'Redes Sociales',
            ),
            19 => 
            array (
                'id' => 23,
                'key' => 'redes-sociales.twitter',
                'display_name' => 'Twitter',
                'value' => NULL,
                'details' => NULL,
                'type' => 'text',
                'order' => 13,
                'group' => 'Redes Sociales',
            ),
            20 => 
            array (
                'id' => 24,
                'key' => 'servidor-imagen.image-from-url',
                'display_name' => 'Servidor',
                'value' => NULL,
                'details' => NULL,
                'type' => 'text',
                'order' => 15,
                'group' => 'Servidor Imagen',
            ),
        ));
        
        
    }
}