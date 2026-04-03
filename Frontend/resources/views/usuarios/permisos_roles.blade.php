@extends('layouts.app')

@section('title', 'Permisos de Roles')
@section('header', 'Administración de Permisos')

@push('styles')
<style>
    .select-round {
        border-radius: 999px;
        border: 1px solid #dbe3ef;
        background-color: #fff;
        color: #0f172a;
        font-weight: 700;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        padding: 0.48rem 2.2rem 0.48rem 0.9rem;
        min-width: 230px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
    }

    .select-round:focus {
        outline: none;
        border-color: #0b63c8;
        box-shadow: 0 0 0 0.16rem rgba(11, 99, 200, 0.18);
    }

    .card-table {
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        overflow: hidden;
    }

    .card-table thead th {
        background: #f8fafc;
        color: #1f2937;
        font-size: 0.78rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 800;
        white-space: nowrap;
    }

    .module-title-cell {
        min-width: 300px;
    }

    .role-summary-pill {
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1e3a8a;
        border-radius: 999px;
        padding: 6px 14px;
        font-weight: 800;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        font-size: 0.72rem;
    }

    .btn-perm-edit {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .perm-help {
        border-radius: 10px;
        border: 1px solid #dbeafe;
        background: #f8fbff;
        color: #1e3a8a;
        font-size: 0.78rem;
        padding: 10px 12px;
    }

    .perm-modal-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px 14px;
    }

    @media (max-width: 576px) {
        .perm-modal-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $modsAssigned = array_map('intval', $modulosAsignados ?? []);
    $accionesPorModulo = $accionesAsignadasPorModulo ?? [];
    $modCatalog = $moduloCatalogo ?? [];
    $accCatalog = $accionCatalogo ?? [];
    $roles = $roles ?? [];
@endphp

<div class="p-4 p-md-5">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-circle-exclamation me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h5 class="fw-bold text-uppercase mb-0" style="color:#1e3a6b; letter-spacing:.5px;">
            <i class="fas fa-shield-alt me-2" style="color:#2563eb;"></i>Configuración de Permisos por Rol
        </h5>
        <a href="{{ route('usuarios.permisos.reporte', ['rol_id' => (int) ($selectedRoleId ?? 0)]) }}"
           class="btn rounded-pill fw-bold text-white px-4 shadow-sm"
           style="background-color:#222c5e; border-color:#222c5e;"
           title="Generar reporte de permisos del rol seleccionado"
           target="_blank"
           rel="noopener">
            <i class="fas fa-file-pdf me-2"></i>REPORTE
        </a>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3 px-1">
        <form id="roleFilterForm" action="{{ route('usuarios.permisos.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <label class="text-muted fw-bold small mb-0">ROL:</label>
            <select class="select-round" id="roleSelector" name="rol_id">
                @forelse($roles as $rol)
                    <option value="{{ $rol['id_rol'] }}" {{ (int) ($selectedRoleId ?? 0) === (int) $rol['id_rol'] ? 'selected' : '' }}>
                        {{ strtoupper((string) ($rol['nombre'] ?? 'SIN ROL')) }}
                    </option>
                @empty
                    <option value="">SIN ROLES</option>
                @endforelse
            </select>
        </form>

        <div class="d-flex align-items-center gap-2">
            <span class="role-summary-pill">{{ strtoupper((string) (collect($roles)->firstWhere('id_rol', (int) ($selectedRoleId ?? 0))['nombre'] ?? 'SIN ROL')) }}</span>
            <span class="text-muted fw-bold small">USUARIOS: {{ count($usuariosDelRol ?? []) }}</span>
        </div>
    </div>

    <form action="{{ route('usuarios.permisos.role.store') }}" method="POST" data-unsaved-form="true" id="permissionsRoleForm">
        @csrf
        <input type="hidden" name="rol_id" value="{{ (int) ($selectedRoleId ?? 0) }}">

        <div class="d-none" id="hiddenPermissionsContainer">
            @foreach($modCatalog as $idModulo => $nombreModulo)
                @php
                    $accionesActualesModulo = array_map('strtoupper', $accionesPorModulo[$idModulo] ?? []);
                @endphp
                <input
                    class="module-toggle"
                    type="checkbox"
                    id="mod_{{ $idModulo }}"
                    name="modulos[]"
                    value="{{ $idModulo }}"
                    {{ in_array((int) $idModulo, $modsAssigned, true) ? 'checked' : '' }}
                >

                @foreach($accCatalog as $accion)
                    <input
                        class="action-toggle"
                        type="checkbox"
                        id="mod_{{ $idModulo }}_acc_{{ strtolower($accion) }}"
                        name="permisos[{{ $idModulo }}][]"
                        value="{{ $accion }}"
                        {{ in_array($accion, $accionesActualesModulo, true) ? 'checked' : '' }}
                    >
                @endforeach
            @endforeach
        </div>

        <div class="card-table shadow-sm">
            <div class="table-responsive">
                <table class="table mb-0 align-middle text-uppercase">
                    <thead>
                        <tr>
                            <th class="border-top-0" style="width:80px;">#</th>
                            <th class="border-top-0 module-title-cell">Módulo</th>
                            <th class="border-top-0 text-center" style="width:190px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($modCatalog as $idModulo => $nombreModulo)
                            <tr>
                                <td class="fw-bold text-muted">{{ (int) $idModulo }}</td>
                                <td class="fw-bold text-dark module-title-cell">{{ strtoupper((string) $nombreModulo) }}</td>
                                <td class="text-center">
                                    <button
                                        type="button"
                                        class="btn btn-action btn-edit btn-perm-edit btn-edit-perm"
                                        data-module-id="{{ (int) $idModulo }}"
                                        data-module-name="{{ strtoupper((string) $nombreModulo) }}"
                                        title="Editar"
                                        aria-label="Editar"
                                    >
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="submit" class="btn rounded-pill fw-bold text-white px-4" style="background-color:#019504; border-color:#019504;">Guardar</button>
        </div>
    </form>
</div>

<div class="modal fade" id="modalEditarPermisoModulo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Editar permisos del módulo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <span class="badge bg-primary-subtle text-primary-emphasis border" id="permModalModuleName">MODULO</span>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="permModalAccesso">
                    <label class="form-check-label fw-bold" for="permModalAccesso">Acceso al módulo</label>
                </div>

                <div class="perm-modal-grid mb-3">
                    @foreach($accCatalog as $accion)
                        <div class="form-check">
                            <input class="form-check-input perm-modal-action" type="checkbox" id="permModalAccion_{{ strtolower($accion) }}" value="{{ $accion }}">
                            <label class="form-check-label fw-semibold" for="permModalAccion_{{ strtolower($accion) }}">
                                {{ ucfirst(strtolower($accion)) }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="perm-help">
                    <strong>Orden de permisos:</strong><br>
                    <strong>Acceso</strong>: habilita entrada al módulo.<br>
                    <strong>Visualizar</strong>: permite ver listados y detalles.<br>
                    <strong>Guardar</strong>: permite crear registros.<br>
                    <strong>Actualizar</strong>: permite editar registros.<br>
                    <strong>Eliminar</strong>: permite borrar registros.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border fw-bold" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success fw-bold" id="permModalSaveBtn">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleFilterForm = document.getElementById('roleFilterForm');
        const roleSelector = document.getElementById('roleSelector');
        let lastRoleValue = roleSelector ? roleSelector.value : null;

        if (roleSelector && roleFilterForm) {
            roleSelector.addEventListener('focus', function () {
                lastRoleValue = roleSelector.value;
            });

            roleSelector.addEventListener('change', function () {
                const confirmed = window.confirm('¿Cambiar rol?');
                if (!confirmed) {
                    roleSelector.value = lastRoleValue;
                    return;
                }

                if (typeof window.allowUnsavedNavigation === 'function') {
                    window.allowUnsavedNavigation();
                }

                roleFilterForm.submit();
            });
        }

        const modalEl = document.getElementById('modalEditarPermisoModulo');
        const modalNameEl = document.getElementById('permModalModuleName');
        const modalAccessEl = document.getElementById('permModalAccesso');
        const modalActionEls = Array.from(document.querySelectorAll('.perm-modal-action'));
        const modalSaveBtn = document.getElementById('permModalSaveBtn');
        const editButtons = Array.from(document.querySelectorAll('.btn-edit-perm'));

        if (!modalEl || !modalNameEl || !modalAccessEl || !modalSaveBtn || !editButtons.length) {
            return;
        }

        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        let activeModuleId = null;
        let bypassModalClose = false;
        let modalDirty = false;

        const getHiddenModuleCheck = (moduleId) => document.getElementById(`mod_${moduleId}`);
        const getHiddenActionCheck = (moduleId, action) => document.getElementById(`mod_${moduleId}_acc_${String(action).toLowerCase()}`);

        const setModalActionsEnabled = (enabled) => {
            modalActionEls.forEach((actionEl) => {
                actionEl.disabled = !enabled;
                if (!enabled) {
                    actionEl.checked = false;
                }
            });
        };

        const openModuleModal = (moduleId, moduleName) => {
            activeModuleId = String(moduleId);
            modalNameEl.textContent = moduleName;

            const hiddenModule = getHiddenModuleCheck(activeModuleId);
            modalAccessEl.checked = !!hiddenModule?.checked;

            modalActionEls.forEach((actionEl) => {
                const hiddenAction = getHiddenActionCheck(activeModuleId, actionEl.value);
                actionEl.checked = !!hiddenAction?.checked;
            });

            setModalActionsEnabled(modalAccessEl.checked);
            modalDirty = false;
            modalInstance.show();
        };

        editButtons.forEach((buttonEl) => {
            buttonEl.addEventListener('click', () => {
                openModuleModal(buttonEl.dataset.moduleId, buttonEl.dataset.moduleName || 'MODULO');
            });
        });

        modalAccessEl.addEventListener('change', () => {
            setModalActionsEnabled(modalAccessEl.checked);
            modalDirty = true;
        });

        modalActionEls.forEach((actionEl) => {
            actionEl.addEventListener('change', () => {
                modalDirty = true;
            });
        });

        modalSaveBtn.addEventListener('click', () => {
            if (!activeModuleId) return;

            const enabled = modalAccessEl.checked;
            const hasCheckedAction = modalActionEls.some((actionEl) => actionEl.checked);

            if (enabled && !hasCheckedAction) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Selecciona al menos una acción para el módulo.',
                        confirmButtonColor: '#dc2626'
                    });
                } else {
                    alert('Selecciona al menos una acción para el módulo.');
                }
                return;
            }

            const hiddenModule = getHiddenModuleCheck(activeModuleId);
            if (hiddenModule) {
                hiddenModule.checked = enabled;
                hiddenModule.dispatchEvent(new Event('change', { bubbles: true }));
            }

            modalActionEls.forEach((actionEl) => {
                const hiddenAction = getHiddenActionCheck(activeModuleId, actionEl.value);
                if (!hiddenAction) return;

                hiddenAction.checked = enabled && actionEl.checked;
                hiddenAction.dispatchEvent(new Event('change', { bubbles: true }));
            });

            modalDirty = false;
            bypassModalClose = true;
            modalInstance.hide();
            setTimeout(() => {
                bypassModalClose = false;
            }, 0);
        });

        modalEl.addEventListener('hide.bs.modal', (event) => {
            if (bypassModalClose || !modalDirty) return;

            event.preventDefault();

            const discardChanges = () => {
                modalDirty = false;
                bypassModalClose = true;
                modalInstance.hide();
                setTimeout(() => {
                    bypassModalClose = false;
                }, 0);
            };

            if (typeof window.confirmUnsavedAction === 'function') {
                window.confirmUnsavedAction(discardChanges);
                return;
            }

            if (confirm('¡Cuidado! Los datos no se guardarán.')) {
                discardChanges();
            }
        });
    });
</script>
@endsection
