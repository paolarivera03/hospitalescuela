<?php

namespace App\Providers;

use App\Support\AuthenticatedUser;
use App\Support\Capacidad;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer(['layouts.app', 'usuarios.lista', 'modulos.pacientes.*'], function ($view) {
            $token   = request()->cookie('jwt_token') ?: session('jwt_token');
            $usuario = AuthenticatedUser::fromToken($token);

            $moduleIds       = [];
            $actionsByModule = [];
            $capacidades     = [];

            if (!empty($usuario['id'])) {
                try {
                    $rows = DB::table('tbl_seg_permisos')
                        ->select('id_formulario', 'accion')
                        ->where('id_usuario', $usuario['id'])
                        ->get();

                    $moduleIds = $rows
                        ->pluck('id_formulario')
                        ->map(fn($v) => (int) $v)
                        ->unique()
                        ->values()
                        ->toArray();

                    $actionsByModule = $rows
                        ->groupBy('id_formulario')
                        ->map(function ($group) {
                            return collect($group)
                                ->pluck('accion')
                                ->map(fn($a) => strtoupper((string) $a))
                                ->unique()
                                ->values()
                                ->toArray();
                        })
                        ->toArray();
                } catch (\Throwable $e) {}
            }

            if (!empty($usuario['id_rol'])) {
                $capacidades = Capacidad::getParaRol((int) $usuario['id_rol']);
            }

            $view->with('currentUserModules',        $moduleIds);
            $view->with('currentUserActionsByModule', $actionsByModule);
            $view->with('currentUserCapacidades',     $capacidades);
        });
    }
}
