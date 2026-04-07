<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumite Temporizador</title>
    <!-- CDN para Tailwind y Alpine.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>



<body class="bg-slate-900 flex items-center justify-center h-screen">

<!-- MARCADOR AZUL -->
    <div class="marcador-ao">
        <div class="pantalla-puntos">
            <div class="etiqueta-ao">AZUL  -  AO</div>
            <div id="indicador-senshu-azul" class="circulo-senshu"></div>
            <div id="puntosAzul" class="puntos-gigantes">0</div>
        </div>
        <div class="panel-control">
            <div class="fila">
                <button class="btn-personalizadoAzul suma" onclick="cambiarPuntos(1)">+ YUKO</button>
                <button class="btn-personalizadoAzul suma" onclick="cambiarPuntos(2)">+ WAZARI</button>
                <button class="btn-personalizadoAzul suma" onclick="cambiarPuntos(3)">+ IPPON</button>
                <button id="btn-senshu-azul" class="btn-personalizadosenshuazul" onclick="logicaSenshuAzul()">Senshu</button>
                
            </div>

            <div class="fila">
                <button class="btn-personalizadoAzul resta" onclick="cambiarPuntos(-1)">- YUKO</button>
                <button class="btn-personalizadoAzul resta" onclick="cambiarPuntos(-2)">- WAZARI</button>
                <button class="btn-personalizadoAzul resta" onclick="cambiarPuntos(-3)">- IPPON</button>
                <button id="btn-senshu-azul" class="btn-personalizadosenshuazul" onclick="logicahanteiazul()">
                    Hantei
                </button>
            </div>

            <div class="fila">
                <button id="btn-c1-azul" class="btn-falta" onclick="logicaC1()">C1</button>
                <button id="btn-c2-azul" class="btn-falta" onclick="logicaC2()">C2</button>
                <button id="btn-c3-azul" class="btn-falta" onclick="logicaC3()">C3</button>
                <button id="btn-hc-azul" class="btn-falta" onclick="logicaHC()">HC</button>
                <button id="btn-c-azul" class="btn-falta" onclick="logicaC()">C</button>
            </div>
            <br>
            <span style="color: white; font-weight: bold; display: block; text-align: Left; width: 100%;">
                Competidor: Walter Landivar Limpias
            </span>
            <span style="color: white; font-weight: bold; display: block; text-align: center; width: 100%;">
                L.J.P. ZABALA DOJO
            </span>
        </div>
    </div>

    <div class="bg-white p-2 rounded-3xl shadow-2xl text-center w-full max-w-md" x-data="timer()">
        <!-- Pantalla del Temporizador -->
        <div class="font-mono mb-10 bg-gray-100 text-red-600 py-12 w-full max-w-5xl mx-auto rounded-3xl shadow-inner border-8 border-gray-200 text-center" 
            style="font-size: 9rem; line-height: 1;">
            <span x-text="format(minutes)">00</span>:<span x-text="format(seconds)">00</span>
        </div>
        <!-- Controles de Ejecución -->
        <div class="grid grid-cols-2 gap-4 mb-8">
            <button @click="start" x-show="!isRunning && minutes == 0 && seconds == 0" class="bg-green-600 text-white py-3 rounded-xl font-bold hover:bg-green-700 transition-colors shadow-md">INICIO</button>
            <button @click="start" x-show="!isRunning && (minutes > 0 || seconds > 0)" class="bg-indigo-600 text-white py-3 rounded-xl font-bold hover:bg-indigo-700 transition-colors shadow-md">CONTINUAR</button>
            <button @click="pause" x-show="isRunning" class="bg-yellow-500 text-white py-3 rounded-xl font-bold hover:bg-yellow-600 transition-colors shadow-md">PAUSA</button>
            <button @click="reset" class="bg-red-600 text-white py-3 rounded-xl font-bold hover:bg-red-700 transition-colors shadow-md">RESET</button>
        </div>

        <!-- Controles de Ajuste -->
        <div class="grid grid-cols-3 gap-6 mb-6">
            <!-- Minutos -->
            <div class="flex flex-col gap-3">
                <span class="text-xl font-bold text-gray-800 uppercase">Minutos</span>
                <button @click="incrementMin" :disabled="isRunning" class="btn-Reloj ">+</button>
                <button @click="decrementMin" :disabled="isRunning" class="btn-Reloj ">-</button>
                
            </div>
            <!-- Segundos -->
            <div class="flex flex-col gap-3">
                <span class="text-xl font-bold text-gray-800 uppercase">Segundos</span>
                <button @click="incrementSec" :disabled="isRunning" class="btn-Reloj ">+</button>
                <button @click="decrementSec" :disabled="isRunning" class="btn-Reloj ">-</button>   
            </div>
            <div class="flex flex-col gap-3">
                <span class="text-xl font-bold text-gray-100 uppercase">.</span>
                <button id="btnMuestraGanador" class="btn-personalizado" onclick="declararGanador()" disabled>GANADOR</button>
                <button onclick="window.history.back()" class="btn-personalizado">Cerrar</button>
            </div>
        </div>
        <br>
        <br>
        <br>
    </div>


<!-- MARCADOR ROJO -->
 <div class="marcador-aka">
    <div id="contenedor-s-rojo"></div>
    <div class="pantalla-puntos">
        <div class="etiqueta-aka">AKA  -  ROJO</div>
        <div id="indicador-senshu-rojo" class="circulo-senshu"></div>
        <div id="puntosRoo" class="puntos-gigantes">0</div>
    </div>
    <div class="panel-control-rojo">
        <div class="fila">
            <button class="btn-personalizadoRojo suma" onclick="gestionarPuntosAka(1)">+ YUKO</button>
            <button class="btn-personalizadoRojo suma" onclick="gestionarPuntosAka(2)">+ WAZARI</button>
            <button class="btn-personalizadoRojo suma" onclick="gestionarPuntosAka(3)">+ IPPON</button>
           <button id="btn-senshu-rojo" class="btn-personalizadosenshurojo" onclick="logicaSenshuRojo()">Senshu</button>
        </div>
        <div class="fila">
            <button class="btn-personalizadoRojo resta" onclick="gestionarPuntosAka(-1)">- YUKO</button>
            <button class="btn-personalizadoRojo resta" onclick="gestionarPuntosAka(-2)">- WAZARI</button>
            <button class="btn-personalizadoRojo resta" onclick="gestionarPuntosAka(-3)">- IPPON</button>
            <button id="btn-senshu-azul" class="btn-personalizadosenshuazul" onclick="logicahanteirojo()">Hantei</button>
        </div>
        <div class="fila">
            <button id="btn-c1-rojo" class="btn-falta" onclick="logicaC1aka()">C1</button>
            <button id="btn-c2-rojo" class="btn-falta" onclick="logicaC2aka()">C2</button>
            <button id="btn-c3-rojo" class="btn-falta" onclick="logicaC3aka()">C3</button>
            <button id="btn-hc-rojo" class="btn-falta" onclick="logicaHCaka()">HC</button>
            <button id="btn-c-rojo" class="btn-falta" onclick="logicaCaka()">C</button>
        </div>
        <br>
        <span style="color: white; font-weight: bold; display: block; text-align: Left; width: 100%;">
            Competidor: Walter Landivar Limpias
        </span>
        <span style="color: white; font-weight: bold; display: block; text-align: center; width: 100%;">
            L.J.P. ZABALA DOJO
        </span>
    </div> 
</div>



    <script>
        function timer() {
            return {
                minutes: 0,
                seconds: 0,
                isRunning: false,
                interval: null,
                start() {
                    if (this.isRunning) return;
                    if (this.minutes === 0 && this.seconds === 0) return;
                    
                    this.isRunning = true;
                    this.interval = setInterval(() => {
                        if (this.seconds > 0) {
                            this.seconds--;
                        } else if (this.minutes > 0) {
                            this.minutes--;
                            this.seconds = 59;
                        } else {
                            this.pause();
                        }
                    }, 1000);
                },
                pause() {
                    clearInterval(this.interval);
                    this.isRunning = false;
                },
                reset() {
                    this.pause();
                    this.minutes = 0;
                    this.seconds = 0;
                },
                incrementMin() { 
                    this.minutes++; 
                },
                decrementMin() { 
                    if (this.minutes > 0) this.minutes--; 
                },
                incrementSec() { 
                    this.seconds++; 
                    if (this.seconds >= 60) {
                        this.seconds = 0;
                        this.minutes++;
                    }
                },
                decrementSec() { 
                    if (this.seconds > 0) {
                        this.seconds--;
                    } else if (this.minutes > 0) {
                        this.minutes--;
                        this.seconds = 59;
                    }
                },
                format(num) {
                    return num.toString().padStart(2, '0');
                }
            }
        }

        function cerrarMensaje() {
        // Buscamos el contenedor que cubre la pantalla
        const pantalla = document.getElementById('pantalla-ganador');
        
        if (pantalla) {
            // Cambiamos el display a 'none' para hacerlo desaparecer
            pantalla.style.display = "none";
        }
}
    </script>

</body>
</html>


<!--********************* Estilos ***********************-->
<style>
    .contenedor-botones {
        display: flex !important;          /* Activa el modo flexible obligatoriamente */
        flex-direction: row !important;    /* Fuerza a que los hijos estén en FILA, no columna */
        justify-content: space-between !important; /* Separa uno a cada extremo */
        align-items: center;               /* Los alinea verticalmente */
        width: 100%;                       /* Ocupa todo el ancho de la pantalla */
        box-sizing: border-box;
        padding: 10px 20px;                /* Espacio para que no toquen los bordes */
    }

    .btn-personalizado {
        display: inline-block;             /* Asegura que el botón no ocupe toda la línea */
        margin: 0;                         /* Quita márgenes que puedan empujarlos */
    }
    /* Capa de fondo oscuro que cubre toda la pantalla */
    .overlay-ganador {
        /* ESTA LÍNEA ES LA QUE CORRIGE EL ERROR */
        display: none; 
        
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    /* Caja blanca que contiene el texto (Media pantalla) */
    .mensaje-contenedor {
        background-color: white;
        padding: 60px;
        border: 10px solid #ff0000; /* Borde rojo grueso */
        border-radius: 20px;
        width: 70%; /* Ocupa gran parte de la pantalla */
        box-shadow: 0 0 50px rgba(255, 0, 0, 0.5);
    }

    .texto-arriba {
        color: #ff0000; /* Rojo */
        font-size: 80px; /* Tamaño gigante */
        font-weight: 900;
        margin: 0;
        font-family: 'Arial Black', sans-serif;
    }

    .texto-debajo {
        color: #000000; /* Negro */
        font-size: 30px; /* Tamaño grande */
        font-weight: bold;
        margin-top: 20px;
        text-transform: uppercase;
    }

    /* Estilo del botón de cerrar */
    .btn-cerrar-anuncio {
        margin-top: 40px;
        padding: 15px 40px;
        font-size: 20px;
        font-weight: bold;
        color: white;
        background-color: #333;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-cerrar-anuncio:hover {
        background-color: #000;
    }

/* Btotnes de falta */
    .btn-falta {
        width: 20px;
        color: #1a1a1a; /* Negro casi puro para mejor contraste */
        
        /* Borde suave con un toque de profundidad */
        border: 1px solid #9ca3af; 
        border-radius: 10px; /* Bordes un poco más curvos son más elegantes */
        
        font-weight: 600;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        text-transform: uppercase; /* Da un aire más profesional/deportivo */
        letter-spacing: 0.5px;
        
        cursor: pointer;
        padding: 10px 10px;
        min-width: 70px;
        height: 40px;
        
        display: inline-flex;
        align-items: center;
        justify-content: center;
        
        /* Sombra suave para que el botón "flote" sobre el marcador */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease-in-out;
    }

    /* Clase para el estado activo (amarillo) */
    .falta-activa {
       background-color: #ffff00 !important; /* Amarillo */
        color: black; /* Asegura que la letra siga siendo negra */
    }

    .marcador-ao {
        background-color: #004a99; /* Azul de competencia */
        width: 450px;
        
        /* --- OPCIÓN DE ALTO --- */
        min-height: 380px;         /* Ajusta este valor (ej: 300px, 400px) para aumentar el alto */
        
        /* --- CENTRADO VERTICAL (Opcional pero recomendado) --- */
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        
        border-radius: 15px;
        padding: 15px;
        color: white;
        font-family: 'Arial Black', sans-serif;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        border: 4px solid #ffffff;
        text-align: center;
        position: relative;
        /* Cambié display inline-block por flex para manejar mejor el alto */
    }

        .marcador-aka {
            background-color: #cc0000; /* Rojo reglamentario AKA */
            width: 450px;
            /* --- OPCIÓN DE ALTO --- */
            min-height: 380px;         /* Ajusta este valor (ej: 300px, 400px) para aumentar el alto */
            /* --- CENTRADO VERTICAL (Opcional pero recomendado) --- */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            
            border-radius: 15px;
            padding: 15px;
            color: white;
            font-family: 'Arial Black', sans-serif;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            border: 4px solid #ffffff;
            text-align: center;
            position: relative;
        }




    .etiqueta-ao {
        font-size: 1.5rem;
        letter-spacing: 3px;
        margin-bottom: -10px;
    }

    .puntos-gigantes {
        font-size: 300px; /* Tamaño grande para legibilidad */
        line-height: 1;
        margin: 10px 0;
        text-shadow: 4px 4px 0px rgba(0,0,0,0.3);
    }

    /* Contenedor principal */
    .panel-control {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 15px;
    }
    
    .panel-control-rojo {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 15px;
    }


    .contenedor-botones {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 15px;
    }

    /* Filas de botones */
    .fila {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }


    /* Botones Azules */
    .btn-personalizadoAzul {
        background: linear-gradient(145deg, #eeeeee, #d1d5db);
        color: #000000 !important; /* Negro puro */
        font-weight: bold !important; /* Negrilla */
        border: 1px solid #2f2f30;
        border-radius: 10px;
        font-family: sans-serif;
        text-transform: uppercase;
        cursor: pointer;
        min-width: 90px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 3px 3px 6px #bebebe, -2px -2px 5px #ffffff;
        transition: all 0.1s;
        /* Aseguramos que el botón sea cliqueable */
        position: relative;
        z-index: 5;
        pointer-events: auto;
    }

    .btn-personalizadoRojo:active {
        transform: translateY(2px);
        box-shadow: inset 2px 2px 5px #bcbcbc, inset -2px -2px 5px #ffffff;
    }



    /* Botones rojos */
    .btn-personalizadoRojo {
        background: linear-gradient(145deg, #eeeeee, #d1d5db);
        color: #000000 !important; /* Negro puro */
        font-weight: bold !important; /* Negrilla */
        border: 1px solid #2f2f30;
        border-radius: 10px;
        font-family: sans-serif;
        text-transform: uppercase;
        cursor: pointer;
        min-width: 90px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 3px 3px 6px #bebebe, -2px -2px 5px #ffffff;
        transition: all 0.1s;
        /* Aseguramos que el botón sea cliqueable */
        position: relative;
        z-index: 5;
        pointer-events: auto;
    }

    .btn-personalizadoRojo:active {
        transform: translateY(2px);
        box-shadow: inset 2px 2px 5px #bcbcbc, inset -2px -2px 5px #ffffff;
    }

    /* Estilo Base: Fondo Gris, Letra Negra, Borde Suave Negro */
    .btn-personalizado {
        /* Degradado sutil para dar volumen (de gris claro a un poco más oscuro) */
        background: linear-gradient(145deg, #eeeeee, #d1d5db);
        color: #1a1a1a; /* Negro casi puro para mejor contraste */
        
        /* Borde suave con un toque de profundidad */
        border: 1px solid #9ca3af; 
        border-radius: 10px; /* Bordes un poco más curvos son más elegantes */
        
        font-weight: 500;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        text-transform: uppercase; /* Da un aire más profesional/deportivo */
        letter-spacing: 0.5px;
        
        cursor: pointer;
        padding: 5px 5px;
        min-width: 100px;
        height: 40px;
        
        display: inline-flex;
        align-items: center;
        justify-content: center;
        
        /* Sombra suave para que el botón "flote" sobre el marcador */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease-in-out;
    }


    /* Boton Senshu Azul */
    .btn-personalizadosenshuazul {
        width: 20px;
        color: #1a1a1a; /* Negro casi puro para mejor contraste */
        
        /* Borde suave con un toque de profundidad */
        border: 1px solid #9ca3af; 
        border-radius: 10px; /* Bordes un poco más curvos son más elegantes */
        
        font-weight: 600;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        text-transform: uppercase; /* Da un aire más profesional/deportivo */
        letter-spacing: 0.5px;
        
        cursor: pointer;
        padding: 10px 10px;
        min-width: 90px;
        height: 40px;
        
        display: inline-flex;
        align-items: center;
        justify-content: center;
        
        /* Sombra suave para que el botón "flote" sobre el marcador */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease-in-out;
    }


    /* Estilos para cuando el botón está "activo" (gris y borde negro) */
    .btn-personalizado.activo {
        background-color: #9ca3af; /* bg-gray-400 equivalent */
        border-color: black;
    }


    /* Estilo Base: Fondo Gris, Letra Negra, Borde Suave Negro */
    .btn-Reloj {
        /* Degradado sutil para dar volumen (de gris claro a un poco más oscuro) */
        background: linear-gradient(145deg, #eeeeee, #d1d5db);
        color: #1a1a1a; /* Negro casi puro para mejor contraste */
        
        /* Borde suave con un toque de profundidad */
        border: 1px solid #9ca3af; 
        border-radius: 10px; /* Bordes un poco más curvos son más elegantes */
        
        font-size: 30px; /* Tamaño grande para legibilidad */
        font-weight: 600;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        text-transform: uppercase; /* Da un aire más profesional/deportivo */
        letter-spacing: 0.5px;
        
        cursor: pointer;
        padding: 10px 10px;
        min-width: 40px;
        height: 40px;
        
        display: inline-flex;
        align-items: center;
        justify-content: center;
        
        /* Sombra suave para que el botón "flote" sobre el marcador */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease-in-out;
    }

    /* Efecto al pasar el mouse (Hover) */
    .btn-personalizado:hover {
        background: linear-gradient(145deg, #d1d5db, #eeeeee); /* Invierte el degradado */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        transform: translateY(-1px); /* Elevación ligera */
    }

    /* Efecto al hacer clic (Active) */
    .btn-personalizado:active {
        transform: translateY(1px); /* Se hunde ligeramente */
        box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.1);
    }


    .btn-personalizado:hover {
        background-color: #9ca3af; /* Gris más oscuro al pasar el mouse */
    }

    /* Estado Activo (Para Senshu o Faltas marcadas) */
    .bg-amarillo {
        background-color: #facc15 !important;
        border-color: #000 !important;
    }


    .btn-punto {
        padding: 15px 5px;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        font-size: 0.85rem;
        cursor: pointer;
        color: white;
        transition: transform 0.1s, filter 0.2s;
    }

    /* Colores de botones */
    .suma {
        background-color: #5d5d5e; /* Verde para sumar */
        border-bottom: 4px solid #5d5d5e;
    }

    .resta {
        background-color: #5d5d5e; /* Rojo para restar */
        border-bottom: 4px solid #5d5d5e;
    }




    .etiqueta-aka {
        font-size: 1.5rem;
        letter-spacing: 3px;
        margin-bottom: -10px;
    }

    .puntos-gigantes {
        font-size: 300px; /* Tamaño máximo para visibilidad */
        line-height: 0.9;
        margin: 15px 0;
        font-weight: 900;
        text-shadow: 5px 5px 0px rgba(0,0,0,0.2);
        transition: transform 0.1s ease;
    }


    .panel-control-aka {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 15px;
    }

    .btn-aka {
        padding: 18px 5px;
        border: none;
        border-radius: 10px;
        font-weight: bold;
        font-size: 0.8rem;
        cursor: pointer;
        color: white;
        text-transform: uppercase;
        transition: all 0.2s;
    }

    /* Estilos por función del botón */
    .btn-sumar {
        background-color: #5d5d5e; /* Gris neutro */
        border-bottom: 5px solid #5d5d5e; /* Gris neutro */
    }

    .btn-restar {
        background-color: #5d5d5e; /* Gris oscuro para restar (corrección) */
        border-bottom: 5px solid #5d5d5e;
    }

    /* Efectos de interacción */
    .btn-aka:active {
        transform: translateY(4px);
        border-bottom-width: 1px;
    }

    .btn-aka:hover {
        filter: brightness(1.2);
    }


    .display-segundos {
        background-color: #374151; /* Gris oscuro elegante (tipo pizarra) */
        color: #ffffff;            /* Letras blancas para alto contraste */
        padding: 10px 20px;
        border-radius: 8px;        /* Borde suave como tus botones */
        font-family: 'Courier New', Courier, monospace; /* Fuente tipo digital */
        font-size: 3rem;           /* Tamaño grande (ajustable) */
        font-weight: 800;
        display: inline-block;
        min-width: 120px;
        text-align: center;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.3); /* Sombra interna para profundidad */
        border: 2px solid #1f2937;
    }

    .etiqueta-segundos {
        display: block;
        font-size: 0.8rem;
        color: #9ca3af; /* Gris claro para el texto pequeño superior */
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 4px;
        font-weight: bold;
    }

    /* Clase para el estado activo (Fondo Amarillo) */
    .senshu-activo {
        background-color: #ffff00 !important;
    }


    .texto-negro-bold {
        /* Color Negro Puro (Hexadecimal absoluto) */
        color: #000000 !important; 
        
        /* Grosor Extra (Negrillas fuertes) */
        font-weight: 900 !important; 
        
        /* Tamaño Grande (Ajustado a 50px) */
        font-size: 50px !important; 
        
        /* Tipografía sin remates para mayor claridad */
        font-family: 'Arial Black', Gadget, sans-serif; 
        
        /* Ajustes de espacio */
        margin: 0; 
        padding: 0;
        line-height: 1;
        
        /* Opcional: Suavizado de fuente para pantallas modernas */
        -webkit-font-smoothing: antialiased;
    }

    .btn-cerrar {
        margin-top: 30px;
        padding: 10px 25px;
        
        /* Estilo 3D Gris */
        background: linear-gradient(145deg, #ffffff, #d1d5db);
        color: #000000;
        font-weight: bold;
        font-size: 16px;
        text-transform: uppercase;
        
        border: 2px solid #000000;
        border-radius: 8px;
        cursor: pointer;
        
        /* Efectos */
        box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
        transition: all 0.1s ease;
    }

    /* Efecto de clic (se hunde) */
    .btn-cerrar:active {
        transform: translateY(2px);
        box-shadow: inset 1px 1px 3px rgba(0,0,0,0.4);
    }

    /* Estilo para centrar el contenido del mensaje */
    .overlay-ganador {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.85); /* Fondo oscuro */
        z-index: 1000;
        display: none; /* Se activa con logicaC() */
        justify-content: center;
        align-items: center;
    }

        .btn-personalizadosenshurojo {
        width: 20px;
        color: #1a1a1a; /* Negro casi puro para mejor contraste */
        
        /* Borde suave con un toque de profundidad */
        border: 1px solid #9ca3af; 
        border-radius: 10px; /* Bordes un poco más curvos son más elegantes */
        
        font-weight: 600;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        text-transform: uppercase; /* Da un aire más profesional/deportivo */
        letter-spacing: 0.5px;
        
        cursor: pointer;
        padding: 10px 10px;
        min-width: 90px;
        height: 40px;
        
        display: inline-flex;
        align-items: center;
        justify-content: center;
        
        /* Sombra suave para que el botón "flote" sobre el marcador */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease-in-out;
    }
</style>




<script>
    /* ******************** Marcadores ******************** */

    function cambiarPuntos(valor) {
        // 1. Verificación de tiempo: Si ambos están en 0, mostramos alerta y salimos
        if (minutos === 0 && segundos === 0) {
            alert("NO SE PUEDE DAR PUNTAJE SI EL TIEMPO ESTA EN 00:00");
            return; // Esto detiene la función aquí mismo
        }

        // Lee el contenido del cronómetro desde el HTML
        const tiempoActual = document.getElementById('tu-id-del-reloj').innerText;
        
        if (tiempoActual === "00:00" || tiempoActual === "0:00") {
            alert("NO SE PUEDE DAR PUNTAJE SI EL TIEMPO ESTA EN 00:00");
            return;
        }
        // 2. Si el tiempo es mayor a cero, procedemos con la suma
        puntajeAo += valor;

        // Validación: No permitir puntos negativos
        if (puntajeAo < 0) {
            puntajeAo = 0;
        }

        // 3. Actualización del marcador visual
        const display = document.getElementById('puntosAzul');
        if (display) {
            display.innerText = puntajeAo;

            // Efecto visual de confirmación (pequeño salto)
            display.style.transform = "scale(1.1)";
            setTimeout(() => {
                display.style.transform = "scale(1)";
            }, 100);
        }
    }

/* ******************** controla puntos rojo ******************** */
    let puntajeAo = 0; 

    function cambiarPuntos(valor) {
        // 1. Verificamos que el clic entra a la función
        console.log("Pulsado botón con valor: " + valor);

        // 2. Buscamos el display
        const display = document.getElementById('puntosAzul');
        
        // 3. Calculamos el puntaje
        puntajeAo += valor;
        if (puntajeAo < 0) puntajeAo = 0;

        // 4. Actualizamos la pantalla solo si el elemento existe
        if (display) {
            display.innerText = puntajeAo;
            
            // Animación rápida de latido
            display.style.transform = "scale(1.2)";
            setTimeout(() => {
                display.style.transform = "scale(1)";
            }, 100);
        } else {
            console.error("Error: No encuentro el ID 'puntosAzul' en el HTML");
        }
    }



/* ******************** controla puntos rojo ******************** */
    let puntajeRojo = 0; 

    function gestionarPuntosAka(valor) {
        // 1. Verificamos que el clic entra a la función
        console.log("Pulsado botón con valor: " + valor);

        // 2. Buscamos el display
        const display = document.getElementById('puntosRojo');
        
        // 3. Calculamos el puntaje
        puntajeRojo += valor;
        if (puntajeRojo < 0) puntajeRojo = 0;

        // 4. Actualizamos la pantalla solo si el elemento existe
        if (display) {
            display.innerText = puntajeRojo;
            
            // Animación rápida de latido
            display.style.transform = "scale(1.2)";
            setTimeout(() => {
                display.style.transform = "scale(1)";
            }, 100);
        } else {
            console.error("Error: No encuentro el ID 'puntosRojo' en el HTML");
        }
    }

/* ******************** faltas ******************** */
    /* ******************** Chuy 1 Azul ******************** */
    function logicaC1() {
        const btnC1 = document.getElementById('btn-c1-azul');
        const btnC2 = document.getElementById('btn-c2-azul');

        // Función para verificar si un botón tiene el fondo amarillo
        const esAmarillo = (el) => el && el.classList.contains('falta-activa');

        // CASO A: El botón C1 ya está en amarillo (queremos quitarlo)
        if (esAmarillo(btnC1)) {
            // Regla: Si C2 está amarillo, no se puede quitar C1
            if (esAmarillo(btnC2)) {
                alert("PRIMERO DEBE QUITAR EL CHUY 2");
            } else {
                // Si C2 está transparente, permitimos quitar el fondo amarillo
                btnC1.classList.remove('falta-activa');
            }
        } 
        // CASO B: El botón C1 está transparente (queremos activarlo)
        else {
            btnC1.classList.add('falta-activa');
        }
    }
    /* ******************** Chuy 2 Azul ******************** */
    function logicaC2() {
        const btnC1 = document.getElementById('btn-c1-azul');
        const btnC2 = document.getElementById('btn-c2-azul');
        const btnC3 = document.getElementById('btn-c3-azul');

        // Función auxiliar para saber si un botón está "activo" (amarillo)
        const esAmarillo = (el) => el && el.classList.contains('falta-activa');

        // CASO A: El botón C2 ya está en amarillo (queremos quitarlo)
        if (esAmarillo(btnC2)) {
            // Regla: Si C3 está amarillo, no se puede quitar C2
            if (esAmarillo(btnC3)) {
                alert("PRIMERO DEBE QUITAR EL CHUY 3");
            } else {
                // Si C3 está transparente, entonces sí podemos quitar C2
                btnC2.classList.remove('falta-activa');
            }
        } 
        // CASO B: El botón C2 está transparente (queremos activarlo)
        else {
            // Regla: Solo se activa si C1 ya tiene el fondo amarillo
            if (esAmarillo(btnC1)) {
                btnC2.classList.add('falta-activa');
            } else {
                // Opcional: podrías poner un alert aquí indicando que falta el C1
                console.log("Debe marcar C1 primero");
            }
        }
    }

        /* ******************** Chuy 3 Azul ******************** */
    function logicaC3() {
        const btnC2 = document.getElementById('btn-c2-azul');
        const btnC3 = document.getElementById('btn-c3-azul');
        const btnHC = document.getElementById('btn-hc-azul');

        // Función para verificar si un botón tiene la clase de fondo amarillo
        const esAmarillo = (el) => el && el.classList.contains('falta-activa');

        // CASO A: El botón C3 ya está en amarillo (queremos quitarlo / ponerlo transparente)
        if (esAmarillo(btnC3)) {
            // Regla: Si HC está amarillo, no se puede quitar C3
            if (esAmarillo(btnHC)) {
                alert("PRIMERO DEBE QUITAR EL HANSOKU CHUY");
            } else {
                // Si HC está transparente, permitimos volver a transparente
                btnC3.classList.remove('falta-activa');
            }
        } 
        // CASO B: El botón C3 está transparente (queremos activarlo / ponerlo amarillo)
        else {
            // Regla: Solo se activa si C2 ya tiene el fondo amarillo
            if (esAmarillo(btnC2)) {
                btnC3.classList.add('falta-activa');
            } else {
                // Mensaje opcional en consola para el usuario
                console.log("Debe marcar C2 primero");
            }
        }
    }

    /* ******************** Hansoku Chuy Azul ******************** */    
    // Variable global para recordar qué botones activó el HC en su último clic
/*    let botonesActivadosPorHC = any[]; */

    function logicaHC() {
        const btnC1 = document.getElementById('btn-c1-azul');
        const btnC2 = document.getElementById('btn-c2-azul');
        const btnC3 = document.getElementById('btn-c3-azul');
        const btnHC = document.getElementById('btn-hc-azul');
        const btnC  = document.getElementById('btn-c-azul');

        const esAmarillo = (el) => el && el.classList.contains('falta-activa');

        // CASO A: El botón HC ya está en amarillo (Queremos desactivar)
        if (esAmarillo(btnHC)) {
            // REGLA: Solo si el botón C (Hansoku) está transparente
            if (esAmarillo(btnC)) {
                alert("PRIMERO DEBE QUITAR EL HANSOKU");
            } else {
                // REGLA ESPECIAL: Solo volvemos a transparente los botones 
                // que el HC encendió en el paso anterior.
                botonesActivadosPorHC.forEach(boton => {
                    if (boton) boton.classList.remove('falta-activa');
                });
                // Limpiamos el recuerdo para la próxima vez
                botonesActivadosPorHC = [];
            }
        } 
        // CASO B: El botón HC está transparente (Queremos activar)
        else {
            const grupoFaltas = [btnC1, btnC2, btnC3, btnHC];
            botonesActivadosPorHC = []; // Reiniciamos la lista de seguimiento

            grupoFaltas.forEach(boton => {
                if (boton && !esAmarillo(boton)) {
                    // Si está transparente, lo encendemos y lo guardamos en la lista
                    boton.classList.add('falta-activa');
                    botonesActivadosPorHC.push(boton);
                }
            });
        }
    }

    /* ******************** Hansoku Chuy Azul ******************** */    
    function logicaC() {
        const btnC = document.getElementById('btn-c-azul');
        const btnHC = document.getElementById('btn-hc-azul');

        // 1. Verificamos si el botón HC ya está en amarillo (falta-activa)
        const hcEsAmarillo = btnHC.classList.contains('falta-activa');
        const cYaEsAmarillo = btnC.classList.contains('falta-activa');

        if (!cYaEsAmarillo) {
            // REGLA: Solo se activa C si HC ya es amarillo
            if (hcEsAmarillo) {
                btnC.classList.add('falta-activa');
                console.log("Falta C marcada correctamente.");
            } else {
                alert("DEBE MARCAR PRIMERO EL HANSOKU CHUI (HC)");
            }
        } else {
            // Si ya es amarillo, al hacer clic vuelve a su color original (transparente/blanco)
            btnC.classList.remove('falta-activa');
            btnC.style.backgroundColor = "transparent";
        }
    }


    function kumiteSystem() {
        return {
            senshu: null, 

            setSenshu(competidor) {
                // Si el competidor ya lo tiene, se quita (Toggle)
                if (this.senshu === competidor) {
                    this.senshu = null;
                    return;
                }

                // Si el otro lo tiene, error
                if (this.senshu !== null && this.senshu !== competidor) {
                    alert("EL OTRO COMPETIDOR YA TIENE EL SENSHU");
                    return;
                }

                // Si nadie lo tiene, se asigna
                this.senshu = competidor;
            }
        }
    }

    /* ******************** Senshu Azul ******************** */   

    function logicaSenshuAzul() {
        const btnAzul = document.getElementById('btn-senshu-azul');
        const btnRojo = document.getElementById('btn-senshu-rojo');
        const contenedorSAzul = document.getElementById('contenedor-s-azul');

        // Colores para comparar (El navegador suele devolver RGB)
        const AMARILLO = "rgb(255, 255, 0)";
        const TRANSPARENTE = "rgba(0, 0, 0, 0)";

        // 1. Obtener estilos actuales
        const estiloAzul = window.getComputedStyle(btnAzul).backgroundColor;
        const estiloRojo = window.getComputedStyle(btnRojo).backgroundColor;

        // CASO A: El botón azul ya está amarillo (QUITAR SENSHU)
        if (estiloAzul === AMARILLO || btnAzul.style.backgroundColor === "yellow") {
            btnAzul.style.backgroundColor = "transparent";
            contenedorSAzul.innerHTML = ""; // Quitamos la S
            return; // Salimos de la función
        }

        // CASO B: El competidor Rojo ya tiene Senshu (ERROR)
        if (estiloRojo === AMARILLO || btnRojo.style.backgroundColor === "yellow") {
            alert("EL OTRO COMPETIDOR YA TINE SENSHU, NO SE PUEDE DAR SENSHU A LOS DOS COMPETIDORES");
            btnAzul.style.backgroundColor = "transparent";
            return;
        }

        // CASO C: El azul no lo tiene y el rojo está transparente (ACTIVAR)
        // Cambiamos fondo del botón
        btnAzul.style.backgroundColor = "yellow";

        // Creamos la letra S circular en la parte superior derecha de .marcador-ao
        contenedorSAzul.innerHTML = `
            <div id="circulo-s-azul" style="
                position: absolute;
                top: 5px;
                right: 5px;
                width: 80px;
                height: 80px;
                background-color: yellow;
                color: black;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Arial Black', sans-serif;
                font-weight: bold;
                font-size: 50px;
                border: 2px solid #000;
                box-shadow: 0 0 5px rgba(0,0,0,0.5);
                z-index: 100;
            ">
                S
            </div>
        `;
    }

    function logicaSenshuRojo() {
        const btnRojo = document.getElementById('btn-senshu-rojo');
        const btnAzul = document.getElementById('btn-senshu-azul');
        const contenedorSRojo = document.getElementById('contenedor-s-rojo');

        // Definición de colores para comparación
        const AMARILLO = "rgb(255, 255, 0)";

        // 1. Obtener estilos actuales (Calculados por el navegador)
        const estiloRojo = window.getComputedStyle(btnRojo).backgroundColor;
        const estiloAzul = window.getComputedStyle(btnAzul).backgroundColor;

        // --- CASO 1: El botón rojo YA tiene senshu (QUITARLO) ---
        if (estiloRojo === AMARILLO || btnRojo.style.backgroundColor === "yellow") {
            btnRojo.style.backgroundColor = "transparent";
            contenedorSRojo.innerHTML = ""; // Quita la imagen circular con la S
            return; // Detiene la ejecución
        }

        // --- CASO 2: El competidor AZUL ya tiene senshu (ERROR) ---
        if (estiloAzul === AMARILLO || btnAzul.style.backgroundColor === "yellow") {
            alert("EL OTRO COMPETIDOR YA TINE SENSHU, NO SE PUEDE DAR SENSHU A LOS DOS COMPETIDORES");
            btnRojo.style.backgroundColor = "transparent";
            return; // Detiene la ejecución
        }

        // --- CASO 3: Nadie tiene senshu (ACTIVAR PARA ROJO) ---
        // Cambiar fondo del botón a amarillo
        btnRojo.style.backgroundColor = "yellow";

        // Crear la "S" circular en la parte superior derecha de .marcador-aka
        contenedorSRojo.innerHTML = `
            <div id="circulo-s-rojo" style="
                position: absolute;
                top: 5px;
                right: 15px;
                width: 80px;
                height: 80px;
                background-color: yellow;
                color: black;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Arial Black', sans-serif;
                font-weight: bold;
                font-size: 50px;
                border: 2px solid #000;
                z-index: 100;
            ">
                S
            </div>
        `;
    }
    /* ******************** Chuy 1 Rojo ******************** */
    function logicaC1aka() {
        const btnC1aka = document.getElementById('btn-c1-rojo');
        const btnC2aka = document.getElementById('btn-c2-rojo');

        // Función para verificar si un botón tiene el fondo amarillo
        const esAmarillo = (el) => el && el.classList.contains('falta-activa');

        // CASO A: El botón C1 ya está en amarillo (queremos quitarlo)
        if (esAmarillo(btnC1aka)) {
            // Regla: Si C2 está amarillo, no se puede quitar C1
            if (esAmarillo(btnC2aka)) {
                alert("PRIMERO DEBE QUITAR EL CHUY 2");
            } else {
                // Si C2 está transparente, permitimos quitar el fondo amarillo
                btnC1aka.classList.remove('falta-activa');
            }
        } 
        // CASO B: El botón C1 está transparente (queremos activarlo)
        else {
            btnC1aka.classList.add('falta-activa');
        }
    }

    /* ******************** Chuy 2 Rojo ******************** */
    function logicaC2aka() {
        const btnC1aka = document.getElementById('btn-c1-rojo');
        const btnC2aka = document.getElementById('btn-c2-rojo');
        const btnC3aka = document.getElementById('btn-c3-rojo');

        // Función auxiliar para saber si un botón está "activo" (amarillo)
        const esAmarillo = (el) => el && el.classList.contains('falta-activa');

        // CASO A: El botón C2 ya está en amarillo (queremos quitarlo)
        if (esAmarillo(btnC2aka)) {
            // Regla: Si C3 está amarillo, no se puede quitar C2
            if (esAmarillo(btnC3aka)) {
                alert("PRIMERO DEBE QUITAR EL CHUY 3");
            } else {
                // Si C3 está transparente, entonces sí podemos quitar C2
                btnC2aka.classList.remove('falta-activa');
            }
        } 
        // CASO B: El botón C2 está transparente (queremos activarlo)
        else {
            // Regla: Solo se activa si C1 ya tiene el fondo amarillo
            if (esAmarillo(btnC1aka)) {
                btnC2aka.classList.add('falta-activa');
            } else {
                // Opcional: podrías poner un alert aquí indicando que falta el C1
                console.log("Debe Marcar C1 Primero");
            }
        }
    }

    /* ******************** Chuy 3 Rojo ******************** */
    function logicaC3aka() {
        const btnC2aka = document.getElementById('btn-c2-rojo');
        const btnC3aka = document.getElementById('btn-c3-rojo');
        const btnHCaka = document.getElementById('btn-hc-rojo');

        // Función para verificar si un botón tiene la clase de fondo amarillo
        const esAmarillo = (el) => el && el.classList.contains('falta-activa');

        // CASO A: El botón C3 ya está en amarillo (queremos quitarlo / ponerlo transparente)
        if (esAmarillo(btnC3aka)) {
            // Regla: Si HC está amarillo, no se puede quitar C3
            if (esAmarillo(btnHCaka)) {
                alert("PRIMERO DEBE QUITAR EL HANSOKU CHUY");
            } else {
                // Si HC está transparente, permitimos volver a transparente
                btnC3aka.classList.remove('falta-activa');
            }
        } 
        // CASO B: El botón C3 está transparente (queremos activarlo / ponerlo amarillo)
        else {
            // Regla: Solo se activa si C2 ya tiene el fondo amarillo
            if (esAmarillo(btnC2aka)) {
                btnC3aka.classList.add('falta-activa');
            } else {
                // Mensaje opcional en consola para el usuario
                console.log("Debe marcar C2 primero");
            }
        }
    }

    // Variable global para recordar qué botones activó el HC en su último clic
    let botonesActivadosPorHCaka  = [];

    /* ******************** Hansoku Chuy Rojo ******************** */    
    // Variable global para recordar qué botones activó el HC en su último clic
/*    let botonesActivadosPorHC = any[]; */

    function logicaHCaka() {
        const btnC1aka = document.getElementById('btn-c1-rojo');
        const btnC2aka = document.getElementById('btn-c2-rojo');
        const btnC3aka = document.getElementById('btn-c3-rojo');
        const btnHCaka = document.getElementById('btn-hc-rojo');
        const btnCaka  = document.getElementById('btn-c-rojo');

        const esAmarillo = (el) => el && el.classList.contains('falta-activa');

        // CASO A: El botón HC ya está en amarillo (Queremos desactivar)
        if (esAmarillo(btnHCaka )) {
            // REGLA: Solo si el botón C (Hansoku) está transparente
            if (esAmarillo(btnCaka )) {
                alert("PRIMERO DEBE QUITAR EL HANSOKU");
            } else {
                // REGLA ESPECIAL: Solo volvemos a transparente los botones 
                // que el HC encendió en el paso anterior.
                botonesActivadosPorHCaka .forEach(boton => {
                    if (boton) boton.classList.remove('falta-activa');
                });
                // Limpiamos el recuerdo para la próxima vez
                botonesActivadosPorHCaka  = [];
            }
        } 
        // CASO B: El botón HC está transparente (Queremos activar)
        else {
            const grupoFaltas = [btnC1aka , btnC2aka , btnC3aka , btnHCaka ];
            botonesActivadosPorHCaka  = []; // Reiniciamos la lista de seguimiento

            grupoFaltas.forEach(boton => {
                if (boton && !esAmarillo(boton)) {
                    // Si está transparente, lo encendemos y lo guardamos en la lista
                    boton.classList.add('falta-activa');
                    botonesActivadosPorHCaka .push(boton);
                }
            });
        }
    }

    /* ******************** Hansoku Chuy Rojo ******************** */    
    function logicaCaka() {
        const btnCaka = document.getElementById('btn-c-rojo');
        const btnHCaka = document.getElementById('btn-hc-rojo');

        // 1. Verificamos si el botón HC ya está en amarillo (falta-activa)
        const hcEsAmarillo = btnHCaka.classList.contains('falta-activa');
        const cYaEsAmarillo = btnCaka.classList.contains('falta-activa');

        if (!cYaEsAmarillo) {
            // REGLA: Solo se activa C si HC ya es amarillo
            if (hcEsAmarillo) {
                btnCaka.classList.add('falta-activa');
                console.log("Falta C marcada correctamente.");
            } else {
                alert("DEBE MARCAR PRIMERO EL HANSOKU CHUI (HC)");
            }
        } else {
            // Si ya es amarillo, al hacer clic vuelve a su color original (transparente/blanco)
            btnCaka.classList.remove('falta-activa');
            btnCaka.style.backgroundColor = "transparent";
        }
    }

</script>
