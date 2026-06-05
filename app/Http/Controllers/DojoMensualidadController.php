<?php

namespace App\Http\Controllers;

use App\Models\Dojo;
use App\Models\DojoMensualidad;
use App\Models\DojoMensualidadPago;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DojoMensualidadController extends Controller
{
    public function index()
    {
        $this->custom_authorize('browse_dojo_mensualidades');

        $userDojoId = Auth::user()->dojo_id;

        if ($userDojoId) {
            $dojo = Dojo::findOrFail($userDojoId);
            $mensualidades = DojoMensualidad::with(['pagos.registerUser'])
                ->withCount('pagos')
                ->where('dojo_id', $userDojoId)
                ->orderBy('fecha_inicio', 'desc')
                ->get();
            $dojos   = null;
            $dojoId  = $userDojoId;
        } else {
            $dojoId = request('dojo_id');
            $dojos  = Dojo::whereNull('deleted_at')->orderBy('nombre')->get();
            $dojo   = $dojoId ? Dojo::find($dojoId) : null;
            $mensualidades = $dojoId
                ? DojoMensualidad::with(['pagos.registerUser'])
                    ->withCount('pagos')
                    ->where('dojo_id', $dojoId)
                    ->orderBy('fecha_inicio', 'desc')
                    ->get()
                : collect();
        }

        return view('dojos.mensualidades.index', compact('mensualidades', 'dojo', 'dojos', 'dojoId'));
    }

    private function authorizeGlobalAdmin()
    {
        if (Auth::user()->dojo_id) {
            abort(403);
        }
    }

    public function list($dojoId)
    {
        $this->custom_authorize('browse_dojo_mensualidades');

        $userDojoId = Auth::user()->dojo_id;
        if ($userDojoId && (int) $userDojoId !== (int) $dojoId) {
            abort(403);
        }

        $mensualidades = DojoMensualidad::with(['pagos.registerUser'])
            ->withCount('pagos')
            ->where('dojo_id', $dojoId)
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return view('dojos.mensualidades.list', compact('mensualidades', 'dojoId'));
    }

    public function store(Request $request, $dojoId)
    {
        $this->custom_authorize('edit_dojo_mensualidades');
        $this->authorizeGlobalAdmin();

        $validated = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
            'monto'        => 'required|numeric|min:0',
            'observacion'  => 'nullable|string|max:500',
        ]);

        $solapamiento = DojoMensualidad::where('dojo_id', $dojoId)
            ->where('fecha_inicio', '<=', $validated['fecha_fin'])
            ->where('fecha_fin', '>=', $validated['fecha_inicio'])
            ->exists();

        if ($solapamiento) {
            return back()->with(['message' => 'El rango se solapa con una mensualidad existente. La fecha inicio debe ser posterior al fin de la última mensualidad.', 'alert-type' => 'error']);
        }

        DojoMensualidad::create([
            'dojo_id'      => $dojoId,
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin'    => $validated['fecha_fin'],
            'monto'        => $validated['monto'],
            'monto_pagado' => 0,
            'observacion'  => $validated['observacion'] ?? null,
        ]);

        return back()->with(['message' => 'Mensualidad registrada correctamente.', 'alert-type' => 'success']);
    }

    public function pagar(Request $request, $id)
    {
        $this->custom_authorize('edit_dojo_mensualidades');
        $this->authorizeGlobalAdmin();

        $mensualidad = DojoMensualidad::findOrFail($id);
        $saldo = $mensualidad->saldo();

        if ($saldo <= 0) {
            return response()->json(['error' => 'La mensualidad ya está completamente pagada.'], 422);
        }

        $request->validate([
            'monto_pago'  => ['required', 'numeric', 'min:0.01', 'max:' . $saldo],
            'observacion' => ['nullable', 'string', 'max:500'],
        ]);

        $pago = DojoMensualidadPago::create([
            'dojo_mensualidad_id' => $mensualidad->id,
            'monto'               => $request->monto_pago,
            'fecha'               => Carbon::today(),
            'observacion'         => $request->observacion ?? null,
            'registerUser_id'     => Auth::id(),
            'registerRole'        => Auth::user()->role?->name,
        ]);

        $mensualidad->increment('monto_pagado', $request->monto_pago);

        $nuevo_saldo = $mensualidad->fresh()->saldo();
        $message = $nuevo_saldo <= 0
            ? 'Pago registrado. Mensualidad completamente pagada.'
            : 'Pago parcial registrado. Saldo restante: Bs ' . number_format($nuevo_saldo, 2);

        return response()->json([
            'success'         => true,
            'message'         => $message,
            'pago_id'         => $pago->id,
            'comprobante_url' => route('dojo.mensualidades.pago.comprobante', $pago->id),
        ]);
    }

    public function pagosList($id)
    {
        $this->custom_authorize('browse_dojo_mensualidades');

        $mensualidad = DojoMensualidad::with(['pagos.registerUser', 'dojo'])->findOrFail($id);

        $userDojoId = Auth::user()->dojo_id;
        if ($userDojoId && (int) $userDojoId !== (int) $mensualidad->dojo_id) {
            abort(403);
        }

        return view('dojos.mensualidades.pagos', compact('mensualidad'));
    }

    public function comprobante($pagoId)
    {
        $this->custom_authorize('browse_dojo_mensualidades');

        $pago = DojoMensualidadPago::with(['mensualidad.dojo', 'registerUser'])->findOrFail($pagoId);

        $userDojoId = Auth::user()->dojo_id;
        if ($userDojoId && (int) $userDojoId !== (int) $pago->mensualidad->dojo_id) {
            abort(403);
        }

        return view('dojos.mensualidades.comprobantePago', compact('pago'));
    }

    public function enviarComprobanteWhatsapp($pagoId)
    {
        $this->custom_authorize('browse_dojo_mensualidades');

        $pago = DojoMensualidadPago::with(['mensualidad.dojo', 'registerUser'])->findOrFail($pagoId);

        $mensualidad = $pago->mensualidad;
        if (!$mensualidad) {
            return $this->whatsappResponse(false, 'No se encontró la mensualidad del pago.');
        }

        $userDojoId = Auth::user()->dojo_id;
        if ($userDojoId && (int) $userDojoId !== (int) $mensualidad->dojo_id) {
            abort(403);
        }

        $dojo = $mensualidad->dojo;
        if (!$dojo) {
            return $this->whatsappResponse(false, 'No se encontró el dojo del pago.');
        }

        $phone = $this->normalizeWhatsappPhone($dojo->phone);
        if (!$phone) {
            return $this->whatsappResponse(false, 'El dojo no tiene un teléfono válido para WhatsApp.');
        }

        $server = rtrim((string) setting('whatsapp.servidores'), '/');
        $session = (string) setting('whatsapp.session');

        if (!$server || !$session) {
            return $this->whatsappResponse(false, 'Configure el servidor y la sesión de WhatsApp en Ajustes.');
        }

        if (!env('WHATSAPP_SEND_KEY')) {
            return $this->whatsappResponse(false, 'Configure WHATSAPP_SEND_KEY en el archivo .env.');
        }

        try {
            $isPdf = true;
            $fileName = 'Comprobante-Dojo-' . str_pad($pago->id, 6, '0', STR_PAD_LEFT) . '-' . now()->format('YmdHis') . '.pdf';
            $path = 'dojos/mensualidades/comprobantes/' . $fileName;
            $pdf = Pdf::loadView('dojos.mensualidades.comprobantePago', compact('pago', 'isPdf'))
                ->setPaper('letter');
            Storage::disk('public')->put($path, $pdf->output());

            $documentUrl = asset('storage/' . $path);
            $message = $this->buildWhatsappMessage($pago);

            $status = Http::timeout(15)->get($server . '/status?id=' . $session)->json();

            if (!($status['success'] ?? false)) {
                return $this->whatsappResponse(false, 'El servidor de WhatsApp no respondió correctamente.');
            }

            if (!($status['status'] ?? false)) {
                return $this->whatsappResponse(false, 'WhatsApp está desconectado. Conecte la sesión antes de enviar.');
            }

            $sendUrl = $server . '/send?id=' . $session . '&token=' . null;
            $response = Http::withHeaders(['X-Api-Key' => env('WHATSAPP_SEND_KEY')])
                ->timeout(25)
                ->post($sendUrl, [
                    'phone'        => '+' . $phone,
                    'text'         => $message,
                    'image_url'    => null,
                    'document_url' => $documentUrl,
                    'file_name'    => $fileName,
                ])
                ->json();
        } catch (\Throwable $e) {
            Log::error('Error enviando comprobante de dojo por WhatsApp', [
                'pago_id' => $pago->id,
                'message' => $e->getMessage(),
            ]);

            return $this->whatsappResponse(false, 'No se pudo enviar por WhatsApp: ' . $e->getMessage());
        }

        if (!($response['success'] ?? false)) {
            return $this->whatsappResponse(false, 'WhatsApp respondió que no pudo enviar el comprobante.');
        }

        return $this->whatsappResponse(true, 'Comprobante enviado por WhatsApp correctamente.');
    }

    private function normalizeWhatsappPhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        $digits = ltrim($digits, '0');

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '591')) {
            return strlen($digits) >= 10 ? $digits : null;
        }

        return strlen($digits) >= 7 ? '591' . $digits : null;
    }

    private function buildWhatsappMessage(DojoMensualidadPago $pago): string
    {
        $mensualidad = $pago->mensualidad;
        $dojo = $mensualidad->dojo;
        $periodo = optional($mensualidad->fecha_inicio)->format('d/m/Y') . ' al ' . optional($mensualidad->fecha_fin)->format('d/m/Y');

        return 'Hola ' . $dojo->nombre . ', le enviamos su comprobante de pago de mensualidad.' . "\n"
            . 'Periodo: ' . $periodo . "\n"
            . 'Gracias por su preferencia.';
    }

    private function whatsappResponse(bool $success, string $message)
    {
        if (request()->expectsJson()) {
            return response()->json(['success' => $success, 'message' => $message], $success ? 200 : 422);
        }

        return back()->with([
            'message' => $message,
            'alert-type' => $success ? 'success' : 'error',
        ]);
    }

    public function updateFechaFin(Request $request, $id)
    {
        $this->custom_authorize('edit_dojo_mensualidades');
        $this->authorizeGlobalAdmin();

        $mensualidad = DojoMensualidad::findOrFail($id);

        $ultima = DojoMensualidad::where('dojo_id', $mensualidad->dojo_id)
            ->orderBy('fecha_inicio', 'desc')
            ->value('id');

        if ((int) $ultima !== (int) $mensualidad->id) {
            return response()->json(['error' => 'Solo se puede editar la última mensualidad.'], 422);
        }

        $request->validate([
            'fecha_fin' => 'required|date|after_or_equal:' . $mensualidad->fecha_inicio->format('Y-m-d'),
        ]);

        $mensualidad->update(['fecha_fin' => $request->fecha_fin]);

        return response()->json(['success' => true, 'message' => 'Fecha fin actualizada correctamente.']);
    }

    public function destroy($id)
    {
        $this->custom_authorize('edit_dojo_mensualidades');
        $this->authorizeGlobalAdmin();

        $mensualidad = DojoMensualidad::findOrFail($id);
        $mensualidad->delete();

        return response()->json(['success' => true, 'message' => 'Mensualidad eliminada.']);
    }
}
