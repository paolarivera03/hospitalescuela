<?php

namespace App\Http\Controllers;

use App\Support\AuthenticatedUser;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BitacoraController extends Controller
{
    private string $apiUrl = 'http://localhost:3000/api';

    private function getToken(): ?string
    {
        return request()->cookie('jwt_token') ?: session('jwt_token');
    }

    private function requireToken()
    {
        $token = $this->getToken();

        if ($token && !session()->has('jwt_token')) {
            session(['jwt_token' => $token]);
        }

        if (!$token) {
            return redirect()->route('login');
        }

        try {
            $perfil = Http::withToken($token)->get("{$this->apiUrl}/perfil");
            if (in_array($perfil->status(), [401, 403], true)) {
                session()->forget(['jwt_token', 'security_verified', 'user_id', 'usuario', 'correo']);
                return redirect()->route('login')
                    ->withCookie(cookie()->forget('jwt_token'))
                    ->withErrors(['error' => 'Tu sesión expiró. Inicia sesión nuevamente.']);
            }
        } catch (\Throwable $e) {
        }

        return $token;
    }

    /**
     * Genera y descarga un PDF con los registros de la bitácora (con filtros opcionales).
     */
    public function reporte(Request $request)
    {
        $token = $this->requireToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) {
            return $token;
        }

        $params = array_filter([
            'usuario'     => $request->query('usuario'),
            'accion'      => $request->query('accion'),
            'fecha_desde' => $request->query('fecha_desde'),
            'fecha_hasta' => $request->query('fecha_hasta'),
        ]);

        try {
            $response = Http::withToken($token)->get("{$this->apiUrl}/bitacora", $params);
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Error de comunicación con el backend.']);
        }

        if (!$response->successful()) {
            return back()->withErrors(['error' => 'No se pudo generar el reporte de bitácora.']);
        }

        $registros = $response->json() ?: [];
        $perfil    = Http::withToken($token)->get("{$this->apiUrl}/perfil");
        $admin     = $perfil->json()['datos_del_token']['usuario'] ?? 'Sistema';

        $pdf = Pdf::loadView('bitacora_pdf', [
            'registros'   => $registros,
            'admin'       => $admin,
            'fecha'       => now()->translatedFormat('d \d\e F \d\e Y'),
            'filtros'     => $params,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Bitacora_Auditoria_' . now()->format('Ymd_His') . '.pdf');
    }
}
