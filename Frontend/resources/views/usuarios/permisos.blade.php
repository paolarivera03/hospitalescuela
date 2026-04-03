@extends('layouts.app')

@section('title', 'Permisos de Usuarios')

@section('content')
    <style>
        .permission-card { max-width: 650px; margin: 0 auto; border-radius: 12px; border: 1px solid #e5e7eb; background: #fff; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .form-label-right { text-align: right; font-weight: 700; color: #4b5563; font-size: 0.95rem; }
        .form-check-label { color: #6b7280; font-weight: 600; font-size: 0.95rem; }
        .form-check-input:checked { background-color: #06b6d4; border-color: #06b6d4; }
        .module-item { border: 1px solid #e5e7eb; border-radius: 10px; background: #f9fafb; padding: 12px; }
        .module-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 14px; margin-top: 10px; }
        
        @media (max-width: 576px) {
            .form-label-right { text-align: left; }
            .module-actions { grid-template-columns: 1fr; }
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="fas fa-user-shield me-2" style="color: #8b5cf6;"></i> Configuración de Permisos
        </h4>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="permission-card mt-5 mb-5">
        <div class="card-header bg-white border-bottom pt-4 pb-3 px-4 d-flex justify-content-between align-items-center" style="border-radius: 12px 12px 0 0;">
            <h5 class="fw-bold mb-0 text-dark">Editar Permisos de Usuario</h5>
            <a href="{{ route('usuarios.permisos.index') }}" class="text-muted text-decoration-none fs-5" title="Cerrar">&times;</a>
        </div>

        <form action="{{ route('usuarios.permisos.store', $usuario['id_usuario']) }}" method="POST" data-unsaved-form="true">
            @csrf
            
            <div class="card-body p-5">
                <div class="row mb-4 align-items-center">
                    <label class="col-sm-3 col-form-label form-label-right">Descripción:</label>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control bg-light border-start-0 fw-bold text-muted" 
                                   value="{{ $usuario['rol_nombre'] ?? 'SIN ROL' }} - {{ $usuario['usuario'] ?? '' }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-sm-3 col-form-label form-label-right pt-0">Módulos:</label>
                    <div class="col-sm-9">
                        @php
                            $modsAssigned = array_map('intval', $modulosAsignados ?? []);
                            $accionesPorModulo = $accionesAsignadasPorModulo ?? [];
                            $modCatalog = $moduloCatalogo ?? [
                                1 => 'Inventario',
                                2 => 'Registro Paciente',
                                3 => 'Reacciones Adversas',
                                4 => 'Gestion',
                                5 => 'Bitácora',
                            ];
                            $accCatalog = $accionCatalogo ?? ['VISUALIZAR', 'GUARDAR', 'ACTUALIZAR', 'ELIMINAR'];
                        @endphp
                        <div class="d-flex flex-column gap-2">
                            @foreach($modCatalog as $idModulo => $nombreModulo)
                                @php
                                    $accionesActualesModulo = array_map('strtoupper', $accionesPorModulo[$idModulo] ?? []);
                                @endphp
                                <div class="module-item">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="mod_{{ $idModulo }}" name="modulos[]" value="{{ $idModulo }}" {{ in_array((int)$idModulo, $modsAssigned, true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="mod_{{ $idModulo }}"><strong>{{ $nombreModulo }}</strong></label>
                                    </div>
                                    <div class="module-actions">
                                        @foreach($accCatalog as $accion)
                                            @php
                                                $checked = in_array($accion, $accionesActualesModulo, true);
                                            @endphp
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="mod_{{ $idModulo }}_acc_{{ strtolower($accion) }}" name="permisos[{{ $idModulo }}][]" value="{{ $accion }}" {{ $checked ? 'checked' : '' }}>
                                                <label class="form-check-label" for="mod_{{ $idModulo }}_acc_{{ strtolower($accion) }}">{{ ucfirst(strtolower($accion)) }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-top d-flex justify-content-end gap-2 py-3 px-4" style="border-radius: 0 0 12px 12px;">
                <a href="{{ route('usuarios.permisos.index') }}" class="btn btn-light border fw-bold px-4" style="border-radius: 6px; color: #4b5563;">
                    Cancelar
                </a>
                <button type="submit" class="btn fw-bold text-white px-4" style="background-color: #22c55e; border-radius: 6px;">
                    Confirmar cambios
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const moduleChecks = document.querySelectorAll('input[name="modulos[]"]');

            moduleChecks.forEach((moduleCheck) => {
                const moduleId = moduleCheck.value;
                const actionChecks = document.querySelectorAll('input[name="permisos[' + moduleId + '][]"]');

                moduleCheck.addEventListener('change', function () {
                    if (!moduleCheck.checked) {
                        actionChecks.forEach((actionCheck) => {
                            actionCheck.checked = false;
                        });
                    }
                });

                actionChecks.forEach((actionCheck) => {
                    actionCheck.addEventListener('change', function () {
                        if (actionCheck.checked) {
                            moduleCheck.checked = true;
                            return;
                        }

                        const hasAnyChecked = Array.from(actionChecks).some((check) => check.checked);
                        if (!hasAnyChecked) {
                            moduleCheck.checked = false;
                        }
                    });
                });
            });
        });
    </script>
@endsection
