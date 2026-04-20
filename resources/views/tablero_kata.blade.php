<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tablero Kata</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-transparent flex flex-col items-center justify-center h-screen">

    <div class="flex gap-4 mb-4">
        <!-- Botón para abrir el modal -->
        <button onclick="abrirModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-2xl shadow-lg transition-all uppercase">
            Preparación
        </button>

        <!-- Botón para cerrar la vista -->
        <button onclick="window.history.back()" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-4 px-8 rounded-2xl shadow-lg transition-all uppercase">
            Cerrar
        </button>
    </div>

    <div id="modal-kata" class="fixed inset-0 bg-black bg-opacity-80 hidden items-center justify-center z-50">
        <div class="bg-yellow-400 w-full max-w-2xl p-10 rounded-3xl border-8 border-yellow-600 shadow-[0_0_50px_rgba(250,204,21,0.4)] text-center">
            
            <h2 class="text-black text-5xl font-black mb-8 uppercase tracking-widest">Tiempo de Kata</h2>
            
            <!-- Pantalla del Temporizador -->
            <div id="timer-display" class="bg-black text-yellow-400 font-mono text-9xl py-12 rounded-2xl mb-10 shadow-inner border-4 border-yellow-700">
                00:35
            </div>

            <!-- Controles -->
            <div class="flex justify-center">
                <button id="btn-stop" onclick="cerrarModal()" class="bg-red-700 hover:bg-red-800 text-white text-3xl font-bold py-6 px-16 rounded-2xl shadow-lg transition-transform active:scale-95">
                    STOP
                </button>
            </div>

        </div>
    </div>

    <script>
        let timerInterval = null;
        let secondsRemaining = 35;

        function abrirModal() {
            $('#modal-kata').css('display', 'flex');
            resetTimer();
            iniciarTimer();
        }

        function cerrarModal() {
            detenerTimer();
            $('#modal-kata').hide();
        }

        function updateDisplay() {
            const m = Math.floor(secondsRemaining / 60).toString().padStart(2, '0');
            const s = (secondsRemaining % 60).toString().padStart(2, '0');
            $('#timer-display').text(`${m}:${s}`);
            
            // Efecto visual cuando queda poco tiempo
            if (secondsRemaining <= 5) {
                $('#timer-display').addClass('text-red-500').removeClass('text-yellow-400');
            } else {
                $('#timer-display').addClass('text-yellow-400').removeClass('text-red-500');
            }
        }

        function iniciarTimer() {
            if (timerInterval) return;

            timerInterval = setInterval(() => {
                if (secondsRemaining > 0) {
                    secondsRemaining--;
                    updateDisplay();
                } else {
                    detenerTimer();
                    alert("TIEMPO AGOTADO");
                }
            }, 1000);
        }

        function detenerTimer() {
            clearInterval(timerInterval);
            timerInterval = null;
        }

        function resetTimer() {
            detenerTimer();
            secondsRemaining = 35;
            updateDisplay();
        }
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@700&display=swap');
        #timer-display { font-family: 'Orbitron', sans-serif; }
    </style>
</body>
</html>