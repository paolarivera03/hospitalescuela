<?php

namespace App\Http\Controllers;

use App\Support\AuthenticatedUser;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;

class PreguntaController extends Controller
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

        if (! $token) {
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

    public function index()
    {
        $token = $this->requireToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) {
            return $token;
        }

        try {
            $perPage = (int) request('per_page', 10);
            if (!in_array($perPage, [5, 10, 15], true)) {
                $perPage = 10;
            }
            $currentPage = max(1, (int) request('page', 1));

            $response = Http::withToken($token)->get("{$this->apiUrl}/preguntas");
            if (in_array($response->status(), [401, 403], true)) {
                session()->forget(['jwt_token', 'security_verified', 'user_id', 'usuario', 'correo']);
                return redirect()->route('login')
                    ->withCookie(cookie()->forget('jwt_token'))
                    ->withErrors(['error' => 'Tu sesión expiró. Inicia sesión nuevamente.']);
            }
            if (! $response->successful()) {
                return redirect()->route('dashboard')->withErrors([
                    'error' => $response->json('message') ?: 'No se pudieron cargar las preguntas.',
                ]);
            }

            $payload = $response->json();
            $allPreguntas = is_array($payload) && isset($payload['data']) && is_array($payload['data'])
                ? array_values($payload['data'])
                : (is_array($payload) ? array_values($payload) : []);

            $total = count($allPreguntas);
            $offset = ($currentPage - 1) * $perPage;
            $pageItems = array_slice($allPreguntas, $offset, $perPage);

            $preguntas = new LengthAwarePaginator(
                $pageItems,
                $total,
                $perPage,
                $currentPage,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]
            );

            return view('preguntas.index', [
                'preguntas' => $preguntas,
                'usuario' => AuthenticatedUser::fromToken($token),
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->withErrors([
                'error' => 'Error de comunicacion con el backend de preguntas.',
            ]);
        }
    }

    public function store(Request $request)
    {
        $token = $this->requireToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) {
            return $token;
        }

        $payload = $request->validate([
            'pregunta' => 'required|string|max:255',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        try {
            $response = Http::withToken($token)->post("{$this->apiUrl}/preguntas", $payload);
            if ($response->successful()) {
                return redirect()->route('preguntas.index')->with('success', $response->json('message') ?: 'Pregunta creada correctamente.');
            }

            return back()->withErrors([
                'error' => $response->json('message') ?: 'No se pudo crear la pregunta.',
            ])->withInput()->with('open_pregunta_modal', true);
        } catch (\Throwable $e) {
            return back()->withErrors([
                'error' => 'Error de comunicacion al crear la pregunta.',
            ])->withInput()->with('open_pregunta_modal', true);
        }
    }

    public function update(Request $request, int $id)
    {
        $token = $this->requireToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) {
            return $token;
        }

        $payload = $request->validate([
            'pregunta' => 'required|string|max:255',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        try {
            $response = Http::withToken($token)->put("{$this->apiUrl}/preguntas/{$id}", $payload);
            if ($response->successful()) {
                return redirect()->route('preguntas.index')->with('success', $response->json('message') ?: 'Pregunta actualizada correctamente.');
            }

            return back()->withErrors([
                'error' => $response->json('message') ?: 'No se pudo actualizar la pregunta.',
            ]);
        } catch (\Throwable $e) {
            return back()->withErrors([
                'error' => 'Error de comunicacion al actualizar la pregunta.',
            ]);
        }
    }
}
