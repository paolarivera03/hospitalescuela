<?php

namespace App\Http\Controllers;

use App\Support\AuthenticatedUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ConfiguracionAccesosController extends Controller
{
    private string $apiUrl = 'http://localhost:3000/api';

    private const TIPOS = [
        'CONSECUENCIAS_REACCION',
        'DESENLACE_REACCION',
        'ESTADO_REACCION',
    ];

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

    private function normalizeTipo(string $tipo): string
    {
        return strtoupper(preg_replace('/[^A-Z_]/', '', trim($tipo)) ?? '');
    }

    private function validateTipo(string $tipo): string
    {
        $tipo = $this->normalizeTipo($tipo);
        if (!in_array($tipo, self::TIPOS, true)) {
            abort(400, 'Tipo de configuración inválido.');
        }

        return $tipo;
    }

    public function index()
    {
        $token = $this->requireToken();
        if ($token instanceof RedirectResponse) {
            return $token;
        }

        try {
            $response = Http::withToken($token)->get("{$this->apiUrl}/configuraciones/accesos");
            if (!$response->successful()) {
                return redirect()->route('dashboard')->withErrors([
                    'error' => $response->json('message') ?: 'No se pudo cargar la configuración de accesos.',
                ]);
            }

            $catalogo = (array) $response->json();

            return view('modulos.configuraciones_accesos', [
                'usuario' => AuthenticatedUser::fromToken($token),
                'catalogo' => $catalogo,
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->withErrors([
                'error' => 'Error de comunicación con el backend de configuraciones.',
            ]);
        }
    }

    public function store(Request $request, string $tipo)
    {
        $token = $this->requireToken();
        if ($token instanceof RedirectResponse) {
            return $token;
        }

        $tipo = $this->validateTipo($tipo);

        $payload = $request->validate([
            'valor_objeto' => 'required|string|max:255',
            'estado' => 'required|in:ACTIVO,INACTIVO',
            'orden' => 'nullable|integer|min:0',
        ]);

        try {
            $response = Http::withToken($token)->post("{$this->apiUrl}/configuraciones/accesos/{$tipo}", $payload);
            if ($response->successful()) {
                return redirect()->route('configuraciones.accesos.index')->with('success', $response->json('message') ?: 'Opción creada.');
            }

            return back()->withErrors([
                'error' => $response->json('message') ?: 'No se pudo crear la opción.',
            ])->withInput();
        } catch (\Throwable $e) {
            return back()->withErrors([
                'error' => 'Error de comunicación al crear la opción.',
            ])->withInput();
        }
    }

    public function update(Request $request, string $tipo, int $id)
    {
        $token = $this->requireToken();
        if ($token instanceof RedirectResponse) {
            return $token;
        }

        $tipo = $this->validateTipo($tipo);

        $payload = $request->validate([
            'valor_objeto' => 'required|string|max:255',
            'estado' => 'required|in:ACTIVO,INACTIVO',
            'orden' => 'nullable|integer|min:0',
        ]);

        try {
            $response = Http::withToken($token)->put("{$this->apiUrl}/configuraciones/accesos/{$tipo}/{$id}", $payload);
            if ($response->successful()) {
                return redirect()->route('configuraciones.accesos.index')->with('success', $response->json('message') ?: 'Opción actualizada.');
            }

            return back()->withErrors([
                'error' => $response->json('message') ?: 'No se pudo actualizar la opción.',
            ])->withInput();
        } catch (\Throwable $e) {
            return back()->withErrors([
                'error' => 'Error de comunicación al actualizar la opción.',
            ])->withInput();
        }
    }

    public function destroy(string $tipo, int $id)
    {
        $token = $this->requireToken();
        if ($token instanceof RedirectResponse) {
            return $token;
        }

        $tipo = $this->validateTipo($tipo);

        try {
            $response = Http::withToken($token)->delete("{$this->apiUrl}/configuraciones/accesos/{$tipo}/{$id}");
            if ($response->successful()) {
                return redirect()->route('configuraciones.accesos.index')->with('success', $response->json('message') ?: 'Opción eliminada.');
            }

            return back()->withErrors([
                'error' => $response->json('message') ?: 'No se pudo eliminar la opción.',
            ]);
        } catch (\Throwable $e) {
            return back()->withErrors([
                'error' => 'Error de comunicación al eliminar la opción.',
            ]);
        }
    }
}
