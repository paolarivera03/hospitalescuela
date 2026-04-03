<?php

namespace App\Http\Controllers;

use App\Support\AuthenticatedUser;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;

class BackupController extends Controller
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

    /** Aplica el filtro de fecha exacta sobre la lista de backups. */
    private function applyFilters(array $backups, array $filtros): array
    {
        return array_values(array_filter($backups, function ($b) use ($filtros) {
            if (!empty($filtros['fecha'])) {
                $ts = isset($b['createdAt']) ? strtotime($b['createdAt']) : 0;
                if (date('Y-m-d', $ts) !== $filtros['fecha']) return false;
            }

            return true;
        }));
    }

    public function index()
    {
        $token = $this->requireToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) {
            return $token;
        }

        try {
            $response = Http::withToken($token)->get("{$this->apiUrl}/backups");
            if (in_array($response->status(), [401, 403], true)) {
                session()->forget(['jwt_token', 'security_verified', 'user_id', 'usuario', 'correo']);
                return redirect()->route('login')
                    ->withCookie(cookie()->forget('jwt_token'))
                    ->withErrors(['error' => 'Tu sesión expiró. Inicia sesión nuevamente.']);
            }
            if (!$response->successful()) {
                if (request()->wantsJson()) {
                    return response()->json([], 200);
                }
                return redirect()->route('dashboard')->withErrors([
                    'error' => $response->json('message') ?: 'No se pudo cargar el listado de backups.',
                ]);
            }

            $allBackups = $response->json() ?: [];

            if (request()->wantsJson()) {
                return response()->json($allBackups);
            }

            $filtros = array_filter([
                'fecha' => request('fecha'),
            ]);

            $filtered = $this->applyFilters($allBackups, $filtros);

            $perPage     = in_array((int) request('per_page', 10), [5, 10, 15], true) ? (int) request('per_page', 10) : 10;
            $currentPage = max(1, (int) request('page', 1));
            $total       = count($filtered);
            $pageItems   = array_slice($filtered, ($currentPage - 1) * $perPage, $perPage);

            $backups = new LengthAwarePaginator($pageItems, $total, $perPage, $currentPage, [
                'path'  => request()->url(),
                'query' => request()->query(),
            ]);

            return view('backup.index', [
                'backups' => $backups,
                'filtros' => $filtros,
                'usuario' => AuthenticatedUser::fromToken($token),
            ]);
        } catch (\Throwable $e) {
            if (request()->wantsJson()) {
                return response()->json([], 200);
            }
            return redirect()->route('dashboard')->withErrors([
                'error' => 'Error de comunicación con el backend de backups.',
            ]);
        }
    }

    public function reporte()
    {
        $token = $this->requireToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) {
            return $token;
        }

        try {
            $response = Http::withToken($token)->get("{$this->apiUrl}/backups");
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Error de comunicación con el backend.']);
        }

        if (!$response->successful()) {
            return back()->withErrors(['error' => 'No se pudo generar el reporte de backups.']);
        }

        $filtros = array_filter([
            'fecha' => request('fecha'),
        ]);

        $backups = $this->applyFilters($response->json() ?: [], $filtros);

        $perfil = Http::withToken($token)->get("{$this->apiUrl}/perfil");
        $admin  = $perfil->json()['datos_del_token']['usuario'] ?? 'Sistema';

        return view('backup.pdf', [
            'backups' => $backups,
            'admin'   => $admin,
            'fecha'   => now()->translatedFormat('d \d\e F \d\e Y'),
            'filtros' => $filtros,
            'htmlPreview' => true,
            'backUrl' => route('backup.index', request()->query()),
        ]);
    }

    public function create(Request $request)
    {
        $token = $this->requireToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) {
            return $token;
        }

        try {
            $response = Http::withToken($token)->post("{$this->apiUrl}/backups");
            if ($response->successful()) {
                return redirect()->route('backup.index')->with('success', $response->json('message') ?: 'Backup generado correctamente.');
            }

            return redirect()->route('backup.index')->withErrors([
                'error' => $response->json('message') ?: 'No se pudo generar el backup.',
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('backup.index')->withErrors([
                'error' => 'Error de comunicación al generar el backup.',
            ]);
        }
    }

    public function download(string $fileName)
    {
        $token = $this->requireToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) {
            return $token;
        }

        try {
            $encoded  = rawurlencode($fileName);
            $response = Http::withToken($token)
                ->accept('*/*')
                ->get("{$this->apiUrl}/backups/{$encoded}/download");

            if (!$response->successful()) {
                return redirect()->route('backup.index')->withErrors([
                    'error' => $response->json('message') ?: 'No se pudo descargar el backup solicitado.',
                ]);
            }

            return response($response->body(), 200, [
                'Content-Type'        => $response->header('Content-Type', 'application/sql'),
                'Content-Disposition' => 'attachment; filename="' . basename($fileName) . '"',
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('backup.index')->withErrors([
                'error' => 'Error de comunicación al descargar el backup.',
            ]);
        }
    }

    public function restore(string $fileName)
    {
        $token = $this->requireToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) {
            return $token;
        }

        try {
            $encoded  = rawurlencode($fileName);
            $response = Http::withToken($token)->post("{$this->apiUrl}/backups/{$encoded}/restore");

            if ($response->successful()) {
                return redirect()->route('backup.index')
                    ->with('success', $response->json('message') ?: 'Backup restaurado correctamente.');
            }

            return redirect()->route('backup.index')->withErrors([
                'error' => $response->json('message') ?: 'No se pudo restaurar el backup.',
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('backup.index')->withErrors([
                'error' => 'Error de comunicación al restaurar el backup.',
            ]);
        }
    }

    public function forceLogout()
    {
        $token = $this->requireToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) {
            return $token;
        }

        try {
            $response = Http::withToken($token)->post("{$this->apiUrl}/backups/force-logout");

            if ($response->successful()) {
                session()->forget(['jwt_token', 'security_verified', 'user_id', 'usuario', 'correo']);
                return redirect()->route('login')
                    ->withCookie(cookie()->forget('jwt_token'))
                    ->with('success', 'Todas las sesiones han sido cerradas. Inicie sesión nuevamente.');
            }

            return redirect()->route('backup.index')->withErrors([
                'error' => $response->json('message') ?: 'No se pudo forzar el cierre de sesiones.',
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('backup.index')->withErrors([
                'error' => 'Error de comunicación al forzar cierre de sesiones.',
            ]);
        }
    }
}
