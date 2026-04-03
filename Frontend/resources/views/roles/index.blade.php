@extends('layouts.app')

@section('title', 'Roles - Hospital Escuela')
@section('header', 'Roles')

@push('styles')
<style>
    .roles-card {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        background: #fff;
        overflow: hidden;
    }

    .roles-table thead th {
        font-size: 0.9rem;
        font-weight: 700;
        color: #374151;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 14px;
    }

    .roles-table tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .state-badge-active {
        background: #dcfce7;
        color: #166534;
    }

    .state-badge-inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .limit-pill {
        border: 1px solid #d1d5db;
        border-radius: 999px;
        padding: 6px 14px;
        font-weight: 700;
        color: #374151;
        background: #fff;
    }

    .role-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-activate {
        color: #16a34a;
    }

    .btn-activate:hover,
    .btn-activate:focus {
        background-color: #dcfce7;
        border-color: #86efac;
        color: #15803d;
    }
</style>
@endpush

@section('content')
@php
    $rolePerms = $rolePermissions ?? [];
    $canGuardarRoles = (bool) ($rolePerms['guardar'] ?? true);
    $canActualizarRoles = (bool) ($rolePerms['actualizar'] ?? true);
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

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <form action="{{ route('roles.index') }}" method="GET" class="flex-grow-1" style="max-width: 500px;">
            <input type="hidden" name="limit" value="{{ request('limit', 10) }}">
            <div class="input-group search-pill">
                <span class="input-group-text bg-white border-0 ps-3 text-muted"><i class="fas fa-search"></i></span>
                <input type="text" id="rolesSearchInput" class="form-control border-0 px-2" name="search" value="{{ request('search') }}" placeholder="BUSCAR ROL..." style="text-transform: uppercase;">
                <button type="button" id="clearRolesSearch" class="btn btn-white border-0 bg-white text-danger px-3 {{ request('search') ? '' : 'd-none' }}"><i class="fas fa-times"></i></button>
                <button class="btn btn-primary px-4" type="submit"><i class="fas fa-search"></i></button>
            </div>
        </form>

        <div class="d-flex gap-2">
            <button type="button" class="btn rounded-pill fw-bold text-white px-4 shadow-sm" style="background-color: #222c5e; border-color: #222c5e;" onclick="exportarPDFRoles()">
                <i class="fas fa-print me-2"></i>REPORTE
            </button>
            @if($canGuardarRoles)
                <button type="button" class="btn text-white fw-bold rounded-pill px-4" style="background-color:#019504;" data-bs-toggle="modal" data-bs-target="#modalNuevoRol">
                    <i class="fas fa-plus me-2"></i>NUEVO
                </button>
            @endif
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 mb-2">
        <span class="small fw-bold text-muted">MOSTRAR:</span>
        <form action="{{ route('roles.index') }}" method="GET">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <select name="limit" class="limit-pill" onchange="this.form.submit()">
                <option value="5" {{ (int) request('limit') === 5 ? 'selected' : '' }}>5</option>
                <option value="10" {{ (int) request('limit', 10) === 10 ? 'selected' : '' }}>10</option>
                <option value="15" {{ (int) request('limit') === 15 ? 'selected' : '' }}>15</option>
            </select>
        </form>
    </div>

    <div class="roles-card shadow-sm">
        <div class="table-responsive">
            <table class="table roles-table mb-0 text-uppercase">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Rol</th>
                        <th>Descripcion</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $rol)
                    <tr>
                        <td class="fw-bold text-muted">{{ $rol['id_rol'] ?? '-' }}</td>
                        <td class="fw-bold">{{ $rol['nombre'] ?? '-' }}</td>
                        <td>{{ !empty($rol['descripcion']) ? $rol['descripcion'] : 'SIN DESCRIPCION' }}</td>
                        <td class="text-center">
                            @if(($rol['estado'] ?? '') === 'ACTIVO')
                                <span class="badge state-badge-active">ACTIVO</span>
                            @else
                                <span class="badge state-badge-inactive">INACTIVO</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $isAdminRole = (int) ($rol['id_rol'] ?? 0) === 1 || strtoupper((string) ($rol['nombre'] ?? '')) === 'ADMINISTRADOR';
                                $estadoActual = strtoupper((string) ($rol['estado'] ?? 'INACTIVO'));
                                $nuevoEstado = $estadoActual === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
                            @endphp
                            @if($isAdminRole)
                                <span class="badge bg-secondary">BLOQUEADO</span>
                            @elseif(!$canActualizarRoles)
                                <span class="badge bg-light text-dark border">SIN ACCION</span>
                            @else
                                <div class="role-actions">
                                    <button
                                        type="button"
                                        class="btn btn-action btn-report"
                                        title="Imprimir"
                                        aria-label="Imprimir"
                                        onclick="descargarPDFIndividualRol({{ (int) ($rol['id_rol'] ?? 0) }})"
                                    >
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-action btn-edit btn-edit-role"
                                        title="Editar"
                                        aria-label="Editar"
                                        data-id="{{ $rol['id_rol'] }}"
                                        data-nombre="{{ $rol['nombre'] ?? '' }}"
                                        data-descripcion="{{ $rol['descripcion'] ?? '' }}"
                                        data-estado="{{ strtoupper((string) ($rol['estado'] ?? 'ACTIVO')) }}"
                                    >
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <form action="{{ route('roles.toggle', ['id' => $rol['id_rol']]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="estado" value="{{ $nuevoEstado }}">
                                        @if($estadoActual === 'ACTIVO')
                                            <button type="submit" class="btn btn-action btn-delete" title="Desactivar" aria-label="Desactivar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-action btn-activate" title="Activar" aria-label="Activar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No hay roles registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($roles->hasPages())
        <div class="d-flex justify-content-end mt-3">
            {{ $roles->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

@if($canGuardarRoles)
    <div class="modal fade" id="modalNuevoRol" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('roles.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Nuevo Rol</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Rol</label>
                            <input type="text" name="nombre" class="form-control text-uppercase" maxlength="100" value="{{ old('nombre') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripcion</label>
                            <textarea name="descripcion" rows="3" class="form-control" maxlength="100">{{ old('descripcion') }}</textarea>
                        </div>
                        <div>
                            <label class="form-label fw-bold">Estado del Rol</label>
                            <select name="estado" class="form-select" required>
                                <option value="ACTIVO" {{ old('estado', 'ACTIVO') === 'ACTIVO' ? 'selected' : '' }}>ACTIVO</option>
                                <option value="INACTIVO" {{ old('estado') === 'INACTIVO' ? 'selected' : '' }}>INACTIVO</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success fw-bold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if($canActualizarRoles)
    <div class="modal fade" id="modalEditarRol" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form id="editRoleForm" action="" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Editar Rol</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Rol</label>
                            <input type="text" name="nombre" id="editRoleNombre" class="form-control text-uppercase" maxlength="100" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripcion</label>
                            <textarea name="descripcion" id="editRoleDescripcion" rows="3" class="form-control" maxlength="100"></textarea>
                        </div>
                        <div>
                            <label class="form-label fw-bold">Estado del Rol</label>
                            <select name="estado" id="editRoleEstado" class="form-select" required>
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary fw-bold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<script>
    const rolesPageData = @json($roles->items() ?? []);
    const rolesById = new Map((rolesPageData || []).map((item) => [Number(item.id_rol || 0), item]));
    const searchActualRoles = @json((string) request('search', ''));
    const usuarioActualPDFRoles = @json($usuario_sesion['usuario'] ?? $usuario_sesion['nombre'] ?? session('usuario_nombre') ?? 'USUARIO');
    let logoDataUrlRoles = null;

    function formatearFechaHoraRoles(fecha) {
        const dd = String(fecha.getDate()).padStart(2, '0');
        const mm = String(fecha.getMonth() + 1).padStart(2, '0');
        const yyyy = fecha.getFullYear();
        const hh = String(fecha.getHours()).padStart(2, '0');
        const mi = String(fecha.getMinutes()).padStart(2, '0');
        const ss = String(fecha.getSeconds()).padStart(2, '0');
        return `${dd}/${mm}/${yyyy} ${hh}:${mi}:${ss}`;
    }

    function valorSeguroRoles(valor) {
        const txt = String(valor ?? '').trim();
        return txt === '' ? '-' : txt;
    }

    async function obtenerLogoDataUrlRoles() {
        if (logoDataUrlRoles !== null) return logoDataUrlRoles;
        try {
            const response = await fetch('{{ asset('login-assets/images/logo-circle.png') }}', { cache: 'force-cache' });
            if (!response.ok) throw new Error('No se pudo cargar el logo');
            const blob = await response.blob();
            logoDataUrlRoles = await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(blob);
            });
        } catch (error) {
            console.warn('No se pudo cargar el logo para PDF Roles:', error);
            logoDataUrlRoles = '';
        }
        return logoDataUrlRoles;
    }

    function obtenerFilasRolesVisibles() {
        const filas = Array.from(document.querySelectorAll('.roles-table tbody tr'));
        return filas.filter((fila) => !fila.querySelector('td[colspan="5"]'));
    }

    function obtenerRolDesdeFila(fila) {
        const celdas = fila.querySelectorAll('td');
        if (!celdas || celdas.length < 4) return null;
        const id = Number((celdas[0].textContent || '').trim() || 0);
        const item = rolesById.get(id) || {};
        return {
            id_rol: id || item.id_rol || 0,
            nombre: item.nombre || (celdas[1].textContent || '').trim(),
            descripcion: item.descripcion || (celdas[2].textContent || '').trim(),
            estado: item.estado || (celdas[3].textContent || '').trim(),
        };
    }

    async function exportarPDFRoles() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
        const now = new Date();
        const fechaHoraStr = formatearFechaHoraRoles(now);
        const logoDataUrl = await obtenerLogoDataUrlRoles();
        const navyBlue = [30, 58, 107];
        const accentBlue = [37, 99, 235];
        const footerGray = [240, 244, 248];
        const margin = 14;
        const headerH = 26;

        const datosExport = obtenerFilasRolesVisibles().map((f) => obtenerRolDesdeFila(f)).filter((x) => !!x);
        const termino = String(searchActualRoles || '').trim();
        const hayFiltros = termino.length > 0;

        const activos = datosExport.filter((r) => String(r.estado || '').toUpperCase().includes('ACTIVO')).length;
        const inactivos = datosExport.filter((r) => String(r.estado || '').toUpperCase().includes('INACTIVO')).length;

        const drawPageHeader = () => {
            const pw = doc.internal.pageSize.width;
            doc.setFillColor(...navyBlue);
            doc.rect(0, 0, pw, headerH, 'F');
            if (logoDataUrl) doc.addImage(logoDataUrl, 'PNG', 6, 6, 13, 13);
            doc.setTextColor(255, 255, 255);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(15);
            doc.text('HOSPITAL ESCUELA', pw / 2, 12, { align: 'center' });
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8.5);
            doc.text('Departamento de Seguridad — Gestion de Roles', pw / 2, 19, { align: 'center' });
            doc.setFontSize(7);
            doc.text(`Generado: ${fechaHoraStr}`, pw - margin, 23, { align: 'right' });
            doc.setFillColor(...accentBlue);
            doc.rect(0, headerH, pw, 1.2, 'F');
        };

        drawPageHeader();
        let startY = headerH + 6;

        doc.setDrawColor(...accentBlue);
        doc.setLineWidth(1.5);
        doc.line(margin, startY, margin, startY + 7);
        doc.setLineWidth(0.1);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(10.5);
        doc.setTextColor(...navyBlue);
        doc.text(
            hayFiltros ? 'GESTION DE ROLES — RESULTADOS FILTRADOS' : 'GESTION DE ROLES',
            margin + 4, startY + 5
        );
        startY += 11;

        if (hayFiltros) {
            doc.setFillColor(240, 244, 248);
            doc.roundedRect(margin, startY, doc.internal.pageSize.width - margin * 2, 7, 1.5, 1.5, 'F');
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(7.5);
            doc.setTextColor(55, 65, 81);
            doc.text(
                `Busqueda: "${termino.toUpperCase()}"   |   Registros mostrados: ${datosExport.length}`,
                margin + 3, startY + 4.5
            );
            startY += 10;
        }

        const columns = [
            { header: 'ID', dataKey: 'id' },
            { header: 'ROL', dataKey: 'nombre' },
            { header: 'DESCRIPCION', dataKey: 'descripcion' },
            { header: 'ESTADO', dataKey: 'estado' },
        ];

        const filas = datosExport.map((r) => ({
            id: valorSeguroRoles(r.id_rol),
            nombre: valorSeguroRoles(r.nombre),
            descripcion: valorSeguroRoles(r.descripcion),
            estado: valorSeguroRoles(r.estado),
        }));

        doc.autoTable({
            startY,
            margin: { top: headerH + 4, left: margin, right: margin, bottom: 16 },
            columns,
            body: filas,
            styles: { fontSize: 8, cellPadding: 3, overflow: 'linebreak', font: 'helvetica' },
            headStyles: { fillColor: navyBlue, textColor: [255, 255, 255], fontStyle: 'bold', fontSize: 8 },
            bodyStyles: { fillColor: [241, 245, 249] },
            alternateRowStyles: { fillColor: [255, 255, 255] },
            columnStyles: { id: { halign: 'center', cellWidth: 18 } },
            didDrawPage: (data) => { if (data.pageNumber > 1) drawPageHeader(); },
        });

        const totalPaginas = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPaginas; i++) {
            doc.setPage(i);
            const ph = doc.internal.pageSize.height;
            const pw = doc.internal.pageSize.width;
            doc.setFillColor(...footerGray);
            doc.rect(0, ph - 12, pw, 12, 'F');
            doc.setDrawColor(...accentBlue);
            doc.setLineWidth(0.5);
            doc.line(0, ph - 12, pw, ph - 12);
            doc.setLineWidth(0.1);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(7);
            doc.setTextColor(71, 85, 105);
            doc.text(`Hospital Escuela  |  Descargado por: ${usuarioActualPDFRoles}`, margin, ph - 4.5);
            doc.setTextColor(71, 85, 105);
            doc.setFontSize(7);
            doc.text(`Página ${i} de ${totalPaginas}`, pw - margin, ph - 4.5, { align: 'right' });
        }

        const tipo = hayFiltros ? 'filtrado' : 'general';
        window.openJsPdfPreview(doc, {
            title: 'Reporte de Gestión de Roles',
            fileName: `gestion_roles_${tipo}_${now.toISOString().split('T')[0]}.pdf`
        });
    }

    async function descargarPDFIndividualRol(idRol) {
        const item = rolesById.get(Number(idRol));
        if (!item) return;

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        const now = new Date();
        const fechaHoraStr = formatearFechaHoraRoles(now);
        const logoDataUrl = await obtenerLogoDataUrlRoles();
        const navyBlue = [30, 58, 107];
        const accentBlue = [37, 99, 235];
        const footerGray = [240, 244, 248];
        const margin = 14;
        const headerH = 26;

        const drawPageHeader = () => {
            const pw = doc.internal.pageSize.width;
            doc.setFillColor(...navyBlue);
            doc.rect(0, 0, pw, headerH, 'F');
            if (logoDataUrl) doc.addImage(logoDataUrl, 'PNG', 6, 6, 13, 13);
            doc.setTextColor(255, 255, 255);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(15);
            doc.text('HOSPITAL ESCUELA', pw / 2, 12, { align: 'center' });
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8.5);
            doc.text('Departamento de Seguridad — Gestion de Roles', pw / 2, 19, { align: 'center' });
            doc.setFontSize(7);
            doc.text(`Generado: ${fechaHoraStr}`, pw - margin, 23, { align: 'right' });
            doc.setFillColor(...accentBlue);
            doc.rect(0, headerH, pw, 1.2, 'F');
        };

        drawPageHeader();
        let startY = headerH + 6;

        doc.setDrawColor(...accentBlue);
        doc.setLineWidth(1.5);
        doc.line(margin, startY, margin, startY + 7);
        doc.setLineWidth(0.1);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(10.5);
        doc.setTextColor(...navyBlue);
        doc.text('GESTION DE ROLES', margin + 4, startY + 5);
        startY += 11;

        const campos = [
            ['ID ROL', valorSeguroRoles(item.id_rol)],
            ['NOMBRE DEL ROL', valorSeguroRoles(item.nombre)],
            ['DESCRIPCION', valorSeguroRoles(item.descripcion)],
            ['ESTADO', valorSeguroRoles(item.estado)],
        ];

        doc.autoTable({
            startY,
            margin: { top: headerH + 4, left: margin, right: margin, bottom: 16 },
            head: [['CAMPO', 'VALOR']],
            body: campos,
            styles: { fontSize: 9.5, cellPadding: 3.5, overflow: 'linebreak', font: 'helvetica' },
            headStyles: { fillColor: navyBlue, textColor: [255, 255, 255], fontStyle: 'bold', fontSize: 9 },
            bodyStyles: { fillColor: [241, 245, 249] },
            alternateRowStyles: { fillColor: [255, 255, 255] },
            columnStyles: {
                0: { cellWidth: 70, fontStyle: 'bold', textColor: navyBlue },
            },
            didDrawPage: (data) => { if (data.pageNumber > 1) drawPageHeader(); },
        });

        const totalPaginas = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPaginas; i++) {
            doc.setPage(i);
            const ph = doc.internal.pageSize.height;
            const pw = doc.internal.pageSize.width;
            doc.setFillColor(...footerGray);
            doc.rect(0, ph - 12, pw, 12, 'F');
            doc.setDrawColor(...accentBlue);
            doc.setLineWidth(0.5);
            doc.line(0, ph - 12, pw, ph - 12);
            doc.setLineWidth(0.1);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(7);
            doc.setTextColor(71, 85, 105);
            doc.text(`Hospital Escuela  |  Descargado por: ${usuarioActualPDFRoles}`, margin, ph - 4.5);
            doc.setTextColor(71, 85, 105);
            doc.text(`Página ${i} de ${totalPaginas}`, pw - margin, ph - 4.5, { align: 'right' });
        }

        window.openJsPdfPreview(doc, {
            title: `Reporte de Rol #${valorSeguroRoles(item.id_rol)}`,
            fileName: `rol_${valorSeguroRoles(item.id_rol)}_${now.toISOString().split('T')[0]}.pdf`
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const editModalElement = document.getElementById('modalEditarRol');
        const editForm = document.getElementById('editRoleForm');
        const editNombre = document.getElementById('editRoleNombre');
        const editDescripcion = document.getElementById('editRoleDescripcion');
        const editEstado = document.getElementById('editRoleEstado');

        if (editModalElement && editForm && editNombre && editDescripcion && editEstado) {
            const editModal = new bootstrap.Modal(editModalElement);

            document.querySelectorAll('.btn-edit-role').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id = this.dataset.id;
                    editForm.action = "{{ url('/roles') }}/" + id;
                    editNombre.value = this.dataset.nombre || '';
                    editDescripcion.value = this.dataset.descripcion || '';
                    editEstado.value = (this.dataset.estado || 'ACTIVO').toUpperCase();
                    editModal.show();
                });
            });

            @if(session('open_edit_role_modal') && session('editing_role_id'))
                editForm.action = "{{ url('/roles') }}/{{ (int) session('editing_role_id') }}";
                editNombre.value = @json(old('nombre', ''));
                editDescripcion.value = @json(old('descripcion', ''));
                editEstado.value = @json(strtoupper((string) old('estado', 'ACTIVO')));
                editModal.show();
            @endif
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rolesSearchInput = document.getElementById('rolesSearchInput');
        const clearRolesSearch = document.getElementById('clearRolesSearch');
        if (rolesSearchInput && clearRolesSearch) {
            rolesSearchInput.addEventListener('input', function () {
                clearRolesSearch.classList.toggle('d-none', this.value.length === 0);
            });
            clearRolesSearch.addEventListener('click', function () {
                rolesSearchInput.value = '';
                this.classList.add('d-none');
                window.location.href = "{{ route('roles.index') }}?limit={{ request('limit', 10) }}";
            });
        }
    });
</script>

@if(session('open_role_modal'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(!$canGuardarRoles)
        return;
        @endif

        const modalElement = document.getElementById('modalNuevoRol');
        if (!modalElement) {
            return;
        }
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    });
</script>
@endif
@endsection
