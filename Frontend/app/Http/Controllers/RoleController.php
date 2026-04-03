<?php

namespace App\Http\Controllers;

use App\Support\AuthenticatedUser;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RoleController extends Controller
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
                    ->withErrors(['error' => 'Tu sesion expiro. Inicia sesion nuevamente.']);
            }
        } catch (\Throwable $e) {
        }

        return $token;
    }

    private function getUserIdFromToken(string $token): int
    {
        $usuario = AuthenticatedUser::fromToken($token);
        return (int) ($usuario['id'] ?? 0);
    }

    private function hasRolesAction(int $userId, string $action): bool
    {
        if ($userId <= 0) {
            return false;
        }

        return DB::table('tbl_seg_permisos')
            ->where('id_usuario', $userId)
            ->where('id_formulario', 8)
            ->whereRaw('UPPER(accion) = ?', [strtoupper(trim($action))])
            ->exists();
    }

    private function canUseRoles(string $token, string $action): bool
    {
        $userId = $this->getUserIdFromToken($token);
        return $this->hasRolesAction($userId, $action);
    }

    private function denyByPermission(string $message)
    {
        return redirect()->route('dashboard')->withErrors(['error' => $message]);
    }

    public function index(Request $request)
    {
        $token = $this->requireToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) {
            return $token;
        }

        if (! $this->canUseRoles($token, 'VISUALIZAR')) {
            return $this->denyByPermission('No tienes permiso para visualizar el módulo de Roles.');
        }

        $canGuardar = $this->canUseRoles($token, 'GUARDAR');
        $canActualizar = $this->canUseRoles($token, 'ACTUALIZAR');

        $search = strtoupper(trim((string) $request->input('search', '')));
        $perPage = (int) $request->input('limit', 10);
        if (!in_array($perPage, [5, 10, 15], true)) {
            $perPage = 10;
        }
        $currentPage = max(1, (int) $request->input('page', 1));

        try {
            $response = Http::withToken($token)->get("{$this->apiUrl}/roles", [
                'include_all' => 1,
            ]);

            if (in_array($response->status(), [401, 403], true)) {
                session()->forget(['jwt_token', 'security_verified', 'user_id', 'usuario', 'correo']);
                return redirect()->route('login')
                    ->withCookie(cookie()->forget('jwt_token'))
                    ->withErrors(['error' => 'Tu sesion expiro. Inicia sesion nuevamente.']);
            }

            if (! $response->successful()) {
                return redirect()->route('dashboard')->withErrors([
                    'error' => $response->json('message') ?: 'No se pudieron cargar los roles.',
                ]);
            }

            $allRoles = $response->json();
            $allRoles = is_array($allRoles) ? array_values($allRoles) : [];

            if ($search !== '') {
                $allRoles = array_values(array_filter($allRoles, function ($rol) use ($search) {
                    $nombre = strtoupper((string) ($rol['nombre'] ?? ''));
                    $descripcion = strtoupper((string) ($rol['descripcion'] ?? ''));
                    return str_contains($nombre, $search) || str_contains($descripcion, $search);
                }));
            }

            $total = count($allRoles);
            $offset = ($currentPage - 1) * $perPage;
            $pageItems = array_slice($allRoles, $offset, $perPage);

            $roles = new LengthAwarePaginator(
                $pageItems,
                $total,
                $perPage,
                $currentPage,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]
            );

            return view('roles.index', [
                'roles' => $roles,
                'usuario' => AuthenticatedUser::fromToken($token),
                'rolePermissions' => [
                    'guardar' => $canGuardar,
                    'actualizar' => $canActualizar,
                ],
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->withErrors([
                'error' => 'Error de comunicacion con el backend de roles.',
            ]);
        }
    }

    public function store(Request $request)
    {
        $token = $this->requireToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) {
            return $token;
        }

        if (! $this->canUseRoles($token, 'GUARDAR')) {
            return $this->denyByPermission('No tienes permiso para crear roles.');
        }

        $payload = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:100',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        $payload['nombre'] = strtoupper(trim($payload['nombre']));
        $payload['descripcion'] = isset($payload['descripcion']) ? trim((string) $payload['descripcion']) : null;

        try {
            $response = Http::withToken($token)->post("{$this->apiUrl}/roles", $payload);

            if ($response->successful()) {
                return redirect()->route('roles.index')->with('success', $response->json('message') ?: 'Rol creado correctamente.');
            }

            return back()->withErrors([
                'error' => $response->json('message') ?: 'No se pudo crear el rol.',
            ])->withInput()->with('open_role_modal', true);
        } catch (\Throwable $e) {
            return back()->withErrors([
                'error' => 'Error de comunicacion al crear el rol.',
            ])->withInput()->with('open_role_modal', true);
        }
    }

    public function toggleStatus(Request $request, int $id)
    {
        $token = $this->requireToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) {
            return $token;
        }

        if (! $this->canUseRoles($token, 'ACTUALIZAR')) {
            return $this->denyByPermission('No tienes permiso para actualizar roles.');
        }

        $payload = $request->validate([
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        try {
            $response = Http::withToken($token)->patch("{$this->apiUrl}/roles/{$id}/estado", $payload);

            if ($response->successful()) {
                $accion = $payload['estado'] === 'ACTIVO' ? 'activado' : 'desactivado';
                return redirect()->route('roles.index')->with('success', "Rol {$accion} correctamente.");
            }

            return back()->withErrors([
                'error' => $response->json('message') ?: 'No se pudo actualizar el estado del rol.',
            ]);
        } catch (\Throwable $e) {
            return back()->withErrors([
                'error' => 'Error de comunicacion al actualizar el estado del rol.',
            ]);
        }
    }

    public function update(Request $request, int $id)
    {
        $token = $this->requireToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) {
            return $token;
        }

        if (! $this->canUseRoles($token, 'ACTUALIZAR')) {
            return $this->denyByPermission('No tienes permiso para actualizar roles.');
        }

        $payload = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:100',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        $payload['nombre'] = strtoupper(trim($payload['nombre']));
        $payload['descripcion'] = isset($payload['descripcion']) ? trim((string) $payload['descripcion']) : null;

        try {
            $response = Http::withToken($token)->put("{$this->apiUrl}/roles/{$id}", $payload);

            if ($response->successful()) {
                return redirect()->route('roles.index')->with('success', $response->json('message') ?: 'Rol actualizado correctamente.');
            }

            return back()->withErrors([
                'error' => $response->json('message') ?: 'No se pudo actualizar el rol.',
            ])->withInput()->with([
                'open_edit_role_modal' => true,
                'editing_role_id' => $id,
            ]);
        } catch (\Throwable $e) {
            return back()->withErrors([
                'error' => 'Error de comunicacion al actualizar el rol.',
            ])->withInput()->with([
                'open_edit_role_modal' => true,
                'editing_role_id' => $id,
            ]);
        }
    }
}
