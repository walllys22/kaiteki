<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MicroServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function tokenGenerator()
    {   
        $data = [
            'systemId' => 'https://whatsapp.soluciondigital.dev', //Solicitante
            'microservice' => 'https://whatsapp-api.soluciondigital.dev',
        ];

        $token = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        // ])->post('https://token-api.soluciondigital.dev/api/tokens/generate', $data);
        ])->post('http://127.0.0.1:3005/api/tokens/generate', $data);

        $token = json_decode($token, true);
        
        return $token['token'];
    }



    public function message()
    {        

        $token = $this->tokenGenerator();
        $servidor = 'http://127.0.0.1:3002';

        $id = 'dev';
        Http::post($servidor.'/send?id='.$id.'&token='.$token, [
                    'phone' => '59167285914',
                    'text' => 'Gracias por su preferencia!',
                ]);

        return true;

        



    }

}
