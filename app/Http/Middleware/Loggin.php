<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Auth;

class Loggin
{
    public function handle(Request $request, Closure $next)
    {
        // Primero, permite que la petición se complete para poder obtener la respuesta.
        $response = $next($request);

        // Excluir rutas que no queremos registrar
        if ($request->is('admin/compass*') || $request->is('admin/voyager-assets*')) {
            return $response;
        }

        // Crear una instancia del agente para analizar el User-Agent
        $agent = new Agent();

        // Preparar los datos base de la petición.
        $data = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'status_code' => $response->getStatusCode(),
            'device' => [
                'type'     => $agent->isDesktop() ? 'Desktop' : ($agent->isTablet() ? 'Tablet' : ($agent->isPhone() ? 'Phone' : 'Other')),
                'platform' => $agent->platform() ?: 'Unknown',
                'browser'  => $agent->browser() ?: 'Unknown',
                'version'  => $agent->version($agent->browser()) ?: 'Unknown',
            ],
            'user_agent' => $request->userAgent(),
            'input' => $request->except(['password', 'password_confirmation', '_token', '_method']),
            'timestamp' => now()->toISOString(),
            'execution_time' => round(microtime(true) - LARAVEL_START, 3) . 's',
        ];

        // Si el usuario está autenticado, añadir su información.
        if (Auth::check()) {
            $user = Auth::user();
            $data['user'] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name ?? 'N/A',
            ];
        }

        // Determinar el nivel de log basado en el status code
        $logLevel = $this->getLogLevel($response->getStatusCode());
        
        // Mensaje personalizado con tabulación
        $logMessage = $this->getFormattedLogMessage($request, $response, $data);

        // Registrar con el nivel apropiado
        Log::channel('requests')->$logLevel($logMessage);

        return $response;
    }

    /**
     * Determina el nivel de log basado en el código de estado HTTP
     */
    private function getLogLevel(int $statusCode): string
    {
        return match(true) {
            $statusCode >= 500 => 'error',    // Errores del servidor
            $statusCode >= 400 => 'warning',  // Errores del cliente
            $statusCode >= 300 => 'info',     // Redirecciones
            default => 'info',                // Éxito
        };
    }

    /**
     * Genera un mensaje de log formateado con tabulación
     */
    private function getFormattedLogMessage(Request $request, $response, array $data): string
    {
        $statusCode = $response->getStatusCode();
        $method = $request->method();
        $path = $request->path();
        
        // Icono según el método HTTP
        $methodIcon = match($method) {
            'GET' => '📄',
            'POST' => '➕',
            'PUT', 'PATCH' => '✏️',
            'DELETE' => '🗑️',
            default => '🔹'
        };

        // Icono y color según el status code
        [$statusIcon, $statusText] = $this->getStatusInfo($statusCode);

        $logLines = [
            // "┌────────────────────────────────────────────────────────────",
            "{$methodIcon} PETICIÓN HTTP",
            // "┌────────────────────────────────────────────────────────────",
            "│ 📍 URL: {$data['url']}",
            "│ ⚡ MÉTODO: {$method}",
            "│ 🔢 STATUS: {$statusIcon} {$statusCode} - {$statusText}",
            "│ 🌐 IP: {$data['ip']}",
            "│ ⏱️  TIEMPO: {$data['execution_time']}",
        ];

        // Información del usuario si está autenticado
        if (isset($data['user'])) {
            $logLines[] = "├─────────────── 👤 USUARIO ──────────────────────────────";
            $logLines[] = "│   ID: {$data['user']['id']}";
            $logLines[] = "│   Nombre: {$data['user']['name']}";
            $logLines[] = "│   Email: {$data['user']['email']}";
            $logLines[] = "│   Rol: {$data['user']['role']}";
        }

        // Información del dispositivo
        $logLines[] = "├─────────────── 💻 DISPOSITIVO ───────────────────────────";
        $logLines[] = "│   Tipo: {$data['device']['type']}";
        $logLines[] = "│   Plataforma: {$data['device']['platform']}";
        $logLines[] = "│   Navegador: {$data['device']['browser']} v{$data['device']['version']}";

        // Input data (si existe)
        if (!empty($data['input'])) {
            $logLines[] = "├─────────────── 📥 INPUT DATA ───────────────────────────";
            foreach ($data['input'] as $key => $value) {
                $formattedValue = is_array($value) ? json_encode($value) : $value;
                $logLines[] = "│   {$key}: {$formattedValue}";
            }
        }

        $logLines[] = "└────────────────────────────────────────────────────────────";
        $logLines[] = "";

        return implode(PHP_EOL, $logLines);
    }

    /**
     * Obtiene el icono y texto para el status code
     */
    private function getStatusInfo(int $statusCode): array
    {
        return match(true) {
            $statusCode >= 500 => ['🔴', 'ERROR DEL SERVIDOR'],
            $statusCode >= 400 => ['🟡', 'ERROR DEL CLIENTE'],
            $statusCode >= 300 => ['🔵', 'REDIRECCIÓN'],
            $statusCode >= 200 => ['🟢', 'EXITOSO'],
            default => ['⚪', 'DESCONOCIDO']
        };
    }
}