@extends('layouts.app')

@section('title', (($modo ?? 'gestion') === 'permisos' ? 'Permisos de Usuarios' : 'Gestión de Usuarios') . ' - Hospital Escuela')
@section('header', ($modo ?? 'gestion') === 'permisos' ? 'Permisos' : 'Gestión de Usuarios')

@push('styles')
<style>
    .card-table {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        overflow: hidden;
        background-color: #ffffff;
    }

    .table thead th {
        color: #374151;
        font-weight: 700;
        font-size: 0.95rem;
        padding: 15px 20px;
        border-bottom: 2px solid #edf2f7;
        background-color: #ffffff;
    }

    .table tbody td {
        padding: 15px 20px;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.9rem;
    }

    .btn-action {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
        border: 1px solid #e5e7eb;
        background: #fff;
    }

    .btn-view,
    .btn-permissions {
        color: #222c5e;
    }

    .btn-report {
        color: #15803d;
    }

    .btn-edit {
        color: #d97706;
    }

    .btn-delete {
        color: #dc2626;
    }

    .btn-view:hover,
    .btn-view:focus,
    .btn-permissions:hover,
    .btn-permissions:focus {
        background-color: rgba(10,72,152,0.12);
        border-color: rgba(10,72,152,0.25);
        color: #0a4898 !important;
    }

    .btn-report:hover,
    .btn-report:focus {
        background-color: #dcfce7;
        border-color: #86efac;
        color: #166534 !important;
    }

    .btn-edit:hover,
    .btn-edit:focus {
        background-color: #fef3c7;
        border-color: #fcd34d;
        color: #d97706 !important;
    }

    .btn-delete:hover,
    .btn-delete:focus {
        background-color: #fee2e2;
        border-color: #fca5a5;
        color: #dc2626 !important;
    }

    .select-round {
        border: 1px solid #e5e7eb;
        border-radius: 50px;
        padding: 5px 15px;
        font-weight: 700;
        color: #6b7280;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
        background-color: #fff;
    }

    .select-round:hover {
        border-color: #0a4898;
    }

    .profile-tab-btn {
        border: 1px solid #e5e7eb;
        border-radius: 999px;
        padding: 8px 14px;
        font-weight: 700;
        color: #4b5563;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s;
    }

    .profile-tab-btn.active {
        background-color: rgba(6, 182, 212, 0.16);
        border-color: rgba(6, 182, 212, 0.3);
        color: #0f172a;
    }

    .profile-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        background: #fff;
    }

    .btn-search-theme {
        background-color: #222c5e !important;
        color: #fff !important;
        border: none !important;
    }

    .btn-search-theme:hover,
    .btn-search-theme:focus {
        background-color: #0a4898 !important;
        color: #fff !important;
    }
    .page-item.active .page-link {
        background-color: #0d6efd;
        color: #ffffff;
    }
</style>
@endpush

@section('content')
@php
    $isPermisosMode = ($modo ?? 'gestion') === 'permisos';

    $actionsUsers = array_map('strtoupper', (array) (($currentUserActionsByModule[7] ?? $currentUserActionsByModule[4] ?? [])));
    $actionsPerms = array_map('strtoupper', (array) ($currentUserActionsByModule[13] ?? []));

    $canUsersGuardar = in_array('GUARDAR', $actionsUsers, true);
    $canUsersActualizar = in_array('ACTUALIZAR', $actionsUsers, true);
    $canUsersEliminar = in_array('ELIMINAR', $actionsUsers, true);
    $canPermisosVisualizar = in_array('VISUALIZAR', $actionsPerms, true);
@endphp
<div class="p-4 p-md-5">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <form action="{{ route($rutaListado ?? 'usuarios.lista') }}" method="GET" class="flex-grow-1" style="max-width: 500px;" id="searchForm">
            <input type="hidden" name="limit" value="{{ request('limit', 10) }}">
            <input type="hidden" name="sort" value="{{ request('sort', 'desc') }}">
            <div class="input-group search-pill">
                <span class="input-group-text bg-white border-0 ps-3 text-muted"><i class="fas fa-search"></i></span>
                <input type="text" name="search" id="searchInput" class="form-control border-0 px-2"
                       placeholder="BUSCAR USUARIO..."
                       value="{{ request('search') }}"
                       style="text-transform: uppercase;">
                <button type="button" id="clearSearch" class="btn btn-white border-0 bg-white text-danger px-3 {{ request('search') ? '' : 'd-none' }}">
                    <i class="fas fa-times"></i>
                </button>
                <button class="btn btn-search-theme px-4" type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>

        <div class="d-flex gap-2">
            <button type="button" class="btn rounded-pill fw-bold text-white px-4 shadow-sm" style="background-color: #222c5e; border-color: #222c5e;" onclick="exportarPDFGestion()">
                <i class="fas fa-print me-2"></i> REPORTE
            </button>
            @if(!$isPermisosMode && $canUsersGuardar)
            <a href="{{ route('usuarios.create') }}" class="btn rounded-pill fw-bold text-white px-4 shadow-sm" style="background-color: #019504; border-color: #019504;">
                <i class="fas fa-plus me-2"></i> NUEVO
            </a>
            @endif
        </div>
    </div>

    <div class="d-flex justify-content-start align-items-center gap-3 mb-2 px-1">
        <div class="d-flex align-items-center">
            <span class="text-muted fw-bold small me-2">MOSTRAR:</span>
            <form action="{{ route($rutaListado ?? 'usuarios.lista') }}" method="GET">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="sort" value="{{ request('sort', 'desc') }}">
                <select name="limit" class="select-round" onchange="this.form.submit()">
                    <option value="5" {{ (request('limit') == 5) ? 'selected' : '' }}>5</option>
                    <option value="10" {{ (request('limit', 10) == 10) ? 'selected' : '' }}>10</option>
                    <option value="15" {{ (request('limit') == 15) ? 'selected' : '' }}>15</option>
                </select>
            </form>
        </div>

        <div class="d-flex align-items-center border-start ps-3">
            <span class="text-muted fw-bold small me-2">ORDEN:</span>
            <form action="{{ route($rutaListado ?? 'usuarios.lista') }}" method="GET">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="limit" value="{{ request('limit', 10) }}">
                <select name="sort" class="select-round" onchange="this.form.submit()">
                    <option value="desc" {{ request('sort', 'desc') == 'desc' ? 'selected' : '' }}>MAS RECIENTES</option>
                    <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>MAS ANTIGUOS</option>
                </select>
            </form>
        </div>
    </div>

    <div class="card-table shadow-sm">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="border-top-0">ID</th>
                        <th class="border-top-0">Usuario</th>
                        <th class="border-top-0">Nombre Completo</th>
                        <th class="border-top-0">Correo</th>
                        <th class="text-center border-top-0">Rol</th>
                        <th class="text-center border-top-0">Estado</th>
                        <th class="text-center border-top-0">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-uppercase">
                    @forelse($usuarios as $user)
                    <tr>
                        <td class="text-muted fw-bold">#{{ $user['id_usuario'] }}</td>
                        <td class="fw-bold text-dark">{{ $user['usuario'] }}</td>
                        <td>{{ $user['nombre'] }} {{ $user['apellido'] }}</td>
                        <td class="text-lowercase text-muted">{{ $user['correo'] }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary p-2">{{ $user['rol_nombre'] ?? 'SIN ROL' }}</span>
                        </td>
                        <td class="text-center">
                            @if($user['estado'] == 'ACTIVO')
                                <span class="badge p-2" style="background-color: #d1fae5; color: #065f46;">ACTIVO</span>
                            @elseif($user['estado'] == 'INACTIVO')
                                <span class="badge p-2" style="background-color: #fee2e2; color: #991b1b;">INACTIVO</span>
                            @elseif($user['estado'] == 'NUEVO')
                                <span class="badge p-2" style="background-color: #e0f2fe; color: #075985;">NUEVO</span>
                            @else
                                <span class="badge p-2 bg-secondary">{{ $user['estado'] }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                @if($isPermisosMode)
                                    @if($canPermisosVisualizar)
                                    <a href="{{ route('usuarios.permisos', $user['id_usuario']) }}" class="btn-action btn-permissions" title="Configurar Permisos">
                                        <i class="fas fa-user-shield"></i>
                                    </a>
                                    @else
                                    <span class="badge bg-light text-dark border">SIN ACCION</span>
                                    @endif
                                @else
                                <button type="button" class="btn-action btn-view btn-ver-perfil"
                                        data-id="{{ $user['id_usuario'] }}"
                                        data-usuario="{{ $user['usuario'] }}"
                                        data-nombre="{{ $user['nombre'] }}"
                                        data-apellido="{{ $user['apellido'] }}"
                                        data-correo="{{ $user['correo'] }}"
                                        data-telefono="{{ $user['telefono'] ?? '' }}"
                                        data-estado="{{ $user['estado'] }}"
                                        data-rol="{{ $user['rol_nombre'] ?? 'SIN ROL' }}"
                                        title="Ver Detalle">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn-action btn-report" title="Ficha del Usuario" onclick="descargarPDFIndividualGestion({{ (int) $user['id_usuario'] }})">
                                    <i class="fas fa-clipboard-user"></i>
                                </button>
                                @if($canUsersActualizar)
                                <a href="{{ route('usuarios.edit', $user['id_usuario']) }}" class="btn-action btn-edit" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif

                                @if(!$canUsersEliminar)
                                    <span class="badge bg-light text-dark border">SIN ACCION</span>
                                @elseif(isset($usuario_sesion['id']) && $user['id_usuario'] == $usuario_sesion['id'])
                                    <button type="button" class="btn-action text-muted opacity-50 border" title="No puedes desactivar tu propia cuenta" disabled>
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                @else
                                    <form action="{{ route('usuarios.destroy', $user['id_usuario']) }}" method="POST" class="d-inline form-eliminar">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn-action btn-delete btn-borrar" data-usuario="{{ $user['usuario'] }}" title="Desactivar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted text-capitalize">
                            <i class="fas fa-search fs-2 mb-3 d-block text-secondary"></i>
                            No hay usuarios registrados o no se encontraron resultados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @php
        $currentPage = max(1, (int) ($pagination['currentPage'] ?? $pagination['current_page'] ?? 1));
        $totalPages = max(1, (int) ($pagination['totalPages'] ?? $pagination['total_pages'] ?? 1));
        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $currentPage + 2);
    @endphp

    @if(isset($pagination))
    <div class="d-flex justify-content-end align-items-center mt-4 mb-2 px-2">
        <span class="text-muted small fw-bold me-3">
            Pagina {{ $currentPage }} de {{ $totalPages }}
        </span>
        <nav>
            <ul class="pagination pagination-sm mb-0 shadow-sm" style="border-radius: 8px; overflow: hidden;">
                <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                    <a class="page-link border-0 text-dark fw-bold px-3"
                       href="{{ route($rutaListado ?? 'usuarios.lista', array_merge(request()->query(), ['page' => $currentPage - 1])) }}">
                        <i class="fas fa-chevron-left me-1"></i> Anterior
                    </a>
                </li>

                @if($startPage > 1)
                    <li class="page-item">
                        <a class="page-link border-0 text-dark fw-bold px-3" href="{{ route($rutaListado ?? 'usuarios.lista', array_merge(request()->query(), ['page' => 1])) }}">1</a>
                    </li>
                    @if($startPage > 2)
                        <li class="page-item disabled"><span class="page-link border-0 text-dark fw-bold px-3">...</span></li>
                    @endif
                @endif

                @for($page = $startPage; $page <= $endPage; $page++)
                    <li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
                        <a class="page-link border-0 text-dark fw-bold px-3" href="{{ route($rutaListado ?? 'usuarios.lista', array_merge(request()->query(), ['page' => $page])) }}">{{ $page }}</a>
                    </li>
                @endfor

                @if($endPage < $totalPages)
                    @if($endPage < $totalPages - 1)
                        <li class="page-item disabled"><span class="page-link border-0 text-dark fw-bold px-3">...</span></li>
                    @endif
                    <li class="page-item">
                        <a class="page-link border-0 text-dark fw-bold px-3" href="{{ route($rutaListado ?? 'usuarios.lista', array_merge(request()->query(), ['page' => $totalPages])) }}">{{ $totalPages }}</a>
                    </li>
                @endif

                <li class="page-item {{ $currentPage >= $totalPages ? 'disabled' : '' }}">
                    <a class="page-link border-0 text-dark fw-bold px-3"
                       href="{{ route($rutaListado ?? 'usuarios.lista', array_merge(request()->query(), ['page' => $currentPage + 1])) }}">
                        Siguiente <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    @endif
</div>

<div class="modal fade" id="modalVerPerfil" tabindex="-1" aria-labelledby="modalVerPerfilLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalVerPerfilLabel">Detalle de Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="d-flex flex-wrap gap-2 mb-4" id="perfil-tabs" role="tablist">
                    <button class="profile-tab-btn active" id="tab-datos-usuario" data-bs-toggle="tab" data-bs-target="#pane-datos-usuario" type="button" role="tab" aria-controls="pane-datos-usuario" aria-selected="true">
                        Datos
                    </button>
                    <button class="profile-tab-btn" id="tab-rol-usuario" data-bs-toggle="tab" data-bs-target="#pane-rol-usuario" type="button" role="tab" aria-controls="pane-rol-usuario" aria-selected="false">
                        Rol
                    </button>
                    <button class="profile-tab-btn" id="tab-estado-usuario" data-bs-toggle="tab" data-bs-target="#pane-estado-usuario" type="button" role="tab" aria-controls="pane-estado-usuario" aria-selected="false">
                        Estado
                    </button>
                </div>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="pane-datos-usuario" role="tabpanel" aria-labelledby="tab-datos-usuario">
                        <div class="profile-card">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Usuario</label>
                                    <input type="text" id="perfilUsuario" class="form-control" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Correo</label>
                                    <input type="email" id="perfilCorreo" class="form-control" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nombre</label>
                                    <input type="text" id="perfilNombre" class="form-control" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Apellido</label>
                                    <input type="text" id="perfilApellido" class="form-control" readonly>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Telefono</label>
                                    <input type="text" id="perfilTelefono" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="pane-rol-usuario" role="tabpanel" aria-labelledby="tab-rol-usuario">
                        <div class="profile-card">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Rol Asignado</label>
                                    <input type="text" id="perfilRol" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="pane-estado-usuario" role="tabpanel" aria-labelledby="tab-estado-usuario">
                        <div class="profile-card">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Estado de Cuenta</label>
                                    <input type="text" id="perfilEstado" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill fw-bold" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script>
    const usuariosPageData = @json($usuarios ?? []);
    const usuariosById = new Map((usuariosPageData || []).map((item) => [Number(item.id_usuario || 0), item]));
    const searchActualGestion = @json((string) request('search', ''));
    const usuarioActualPDFGestion = @json($usuario_sesion['usuario'] ?? $usuario_sesion['nombre'] ?? session('usuario_nombre') ?? 'USUARIO');
    let logoDataUrlGestion = null;

    function formatearFechaHoraGestion(fecha) {
        const dd = String(fecha.getDate()).padStart(2, '0');
        const mm = String(fecha.getMonth() + 1).padStart(2, '0');
        const yyyy = fecha.getFullYear();
        const hh = String(fecha.getHours()).padStart(2, '0');
        const mi = String(fecha.getMinutes()).padStart(2, '0');
        const ss = String(fecha.getSeconds()).padStart(2, '0');
        return `${dd}/${mm}/${yyyy} ${hh}:${mi}:${ss}`;
    }

    function valorSeguroGestion(valor) {
        const txt = String(valor ?? '').trim();
        return txt === '' ? '-' : txt;
    }

    async function obtenerLogoDataUrlGestion() {
        if (logoDataUrlGestion !== null) return logoDataUrlGestion;
        try {
            const response = await fetch('{{ asset('login-assets/images/logo-circle.png') }}', { cache: 'force-cache' });
            if (!response.ok) throw new Error('No se pudo cargar el logo');
            const blob = await response.blob();
            logoDataUrlGestion = await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(blob);
            });
        } catch (error) {
            console.warn('No se pudo cargar el logo para PDF Gestion:', error);
            logoDataUrlGestion = '';
        }
        return logoDataUrlGestion;
    }

    function obtenerFilasUsuariosVisibles() {
        const filas = Array.from(document.querySelectorAll('table tbody tr'));
        return filas.filter((fila) => {
            const emptyCell = fila.querySelector('td[colspan="7"]');
            return !emptyCell;
        });
    }

    function obtenerUsuarioDesdeFila(fila) {
        const celdas = fila.querySelectorAll('td');
        if (!celdas || celdas.length < 6) return null;

        const idTxt = (celdas[0].textContent || '').replace('#', '').trim();
        const id = Number(idTxt || 0);
        const usuarioBase = usuariosById.get(id) || {};

        return {
            id_usuario: id || usuarioBase.id_usuario || 0,
            usuario: usuarioBase.usuario || (celdas[1].textContent || '').trim(),
            nombre: usuarioBase.nombre || ((celdas[2].textContent || '').trim().split(' ')[0] || ''),
            apellido: usuarioBase.apellido || ((celdas[2].textContent || '').trim().split(' ').slice(1).join(' ') || ''),
            correo: usuarioBase.correo || (celdas[3].textContent || '').trim(),
            rol_nombre: usuarioBase.rol_nombre || (celdas[4].textContent || '').trim(),
            estado: usuarioBase.estado || (celdas[5].textContent || '').trim(),
            telefono: usuarioBase.telefono || '',
        };
    }

    async function exportarPDFGestion() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
        const now = new Date();
        const fechaHoraStr = formatearFechaHoraGestion(now);
        const logoDataUrl = await obtenerLogoDataUrlGestion();
        const navyBlue = [30, 58, 107];
        const accentBlue = [37, 99, 235];
        const footerGray = [240, 244, 248];
        const margin = 14;
        const headerH = 26;

        const filasVisibles = obtenerFilasUsuariosVisibles();
        const datosExport = filasVisibles
            .map((fila) => obtenerUsuarioDesdeFila(fila))
            .filter((item) => !!item);

        const termino = String(searchActualGestion || '').trim();
        const hayFiltros = termino.length > 0;

        const activos = datosExport.filter((u) => String(u.estado || '').toUpperCase().includes('ACTIVO')).length;
        const inactivos = datosExport.filter((u) => String(u.estado || '').toUpperCase().includes('INACTIVO')).length;

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
            doc.text('Departamento de Seguridad — Gestion de Usuarios', pw / 2, 19, { align: 'center' });
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
            hayFiltros ? 'GESTION DE USUARIOS — RESULTADOS FILTRADOS' : 'GESTION DE USUARIOS',
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
            { header: 'USUARIO', dataKey: 'usuario' },
            { header: 'NOMBRE COMPLETO', dataKey: 'nombreCompleto' },
            { header: 'CORREO', dataKey: 'correo' },
            { header: 'ROL', dataKey: 'rol' },
            { header: 'ESTADO', dataKey: 'estado' },
        ];

        const filas = datosExport.map((u) => ({
            id: valorSeguroGestion(u.id_usuario),
            usuario: valorSeguroGestion(u.usuario),
            nombreCompleto: valorSeguroGestion(`${u.nombre || ''} ${u.apellido || ''}`.trim()),
            correo: valorSeguroGestion(u.correo),
            rol: valorSeguroGestion(u.rol_nombre || 'SIN ROL'),
            estado: valorSeguroGestion(u.estado),
        }));

        doc.autoTable({
            startY,
            margin: { top: headerH + 4, left: margin, right: margin, bottom: 16 },
            columns,
            body: filas,
            styles: { fontSize: 7.5, cellPadding: 2.8, overflow: 'linebreak', font: 'helvetica' },
            headStyles: { fillColor: navyBlue, textColor: [255, 255, 255], fontStyle: 'bold', fontSize: 7.5 },
            bodyStyles: { fillColor: [241, 245, 249] },
            alternateRowStyles: { fillColor: [255, 255, 255] },
            columnStyles: { id: { halign: 'center' } },
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
            doc.text(`Hospital Escuela  |  Descargado por: ${usuarioActualPDFGestion}`, margin, ph - 4.5);
            doc.setTextColor(71, 85, 105);
            doc.setFontSize(7);
            doc.text(`Página ${i} de ${totalPaginas}`, pw - margin, ph - 4.5, { align: 'right' });
        }

        const tipo = hayFiltros ? 'filtrado' : 'general';
        window.openJsPdfPreview(doc, {
            title: 'Reporte de Gestión de Usuarios',
            fileName: `gestion_usuarios_${tipo}_${now.toISOString().split('T')[0]}.pdf`
        });
    }

    async function descargarPDFIndividualGestion(idUsuario) {
        const item = usuariosById.get(Number(idUsuario));
        if (!item) return;

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        const now = new Date();
        const fechaHoraStr = formatearFechaHoraGestion(now);
        const logoDataUrl = await obtenerLogoDataUrlGestion();
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
            doc.text('Departamento de Seguridad — Gestion de Usuarios', pw / 2, 19, { align: 'center' });
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
        doc.text('GESTION DE USUARIOS', margin + 4, startY + 5);
        startY += 11;

        const campos = [
            ['ID USUARIO', valorSeguroGestion(item.id_usuario)],
            ['USUARIO', valorSeguroGestion(item.usuario)],
            ['NOMBRE', valorSeguroGestion(item.nombre)],
            ['APELLIDO', valorSeguroGestion(item.apellido)],
            ['NOMBRE COMPLETO', valorSeguroGestion(`${item.nombre || ''} ${item.apellido || ''}`.trim())],
            ['CORREO', valorSeguroGestion(item.correo)],
            ['TELEFONO', valorSeguroGestion(item.telefono)],
            ['ROL', valorSeguroGestion(item.rol_nombre || 'SIN ROL')],
            ['ESTADO', valorSeguroGestion(item.estado)],
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
            doc.text(`Hospital Escuela  |  Descargado por: ${usuarioActualPDFGestion}`, margin, ph - 4.5);
            doc.setTextColor(71, 85, 105);
            doc.text(`Página ${i} de ${totalPaginas}`, pw - margin, ph - 4.5, { align: 'right' });
        }

        window.openJsPdfPreview(doc, {
            title: `Reporte de Usuario #${valorSeguroGestion(item.id_usuario)}`,
            fileName: `usuario_${valorSeguroGestion(item.id_usuario)}_${now.toISOString().split('T')[0]}.pdf`
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const clearBtn = document.getElementById('clearSearch');

        if (searchInput && clearBtn) {
            searchInput.addEventListener('input', function() {
                clearBtn.classList.toggle('d-none', this.value.length === 0);
            });

            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                this.classList.add('d-none');
                window.location.href = "{{ route($rutaListado ?? 'usuarios.lista') }}?limit={{ request('limit', 10) }}&sort={{ request('sort', 'desc') }}";
            });
        }

        document.querySelectorAll('.btn-borrar').forEach(function(boton) {
            boton.addEventListener('click', function() {
                const formulario = this.closest('.form-eliminar');
                const nombreUsuario = this.getAttribute('data-usuario');

                Swal.fire({
                    title: '¿Desactivar Usuario?',
                    html: `¿Confirma que desea desactivar permanentemente el acceso a <b>${nombreUsuario}</b>?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash-alt me-2"></i> Desactivar',
                    cancelButtonText: '<i class="fas fa-times me-2"></i> Cancelar',
                    reverseButtons: true,
                    customClass: { popup: 'rounded-4 shadow-lg' }
                }).then((result) => {
                    if (result.isConfirmed && formulario) {
                        formulario.submit();
                    }
                });
            });
        });

        const modalVerPerfilElement = document.getElementById('modalVerPerfil');
        if (modalVerPerfilElement) {
            const modalVerPerfil = new bootstrap.Modal(modalVerPerfilElement);

            document.querySelectorAll('.btn-ver-perfil').forEach(function(boton) {
                boton.addEventListener('click', function() {
                    document.getElementById('perfilUsuario').value = this.dataset.usuario || '';
                    document.getElementById('perfilNombre').value = this.dataset.nombre || '';
                    document.getElementById('perfilApellido').value = this.dataset.apellido || '';
                    document.getElementById('perfilCorreo').value = this.dataset.correo || '';
                    document.getElementById('perfilTelefono').value = this.dataset.telefono || '';
                    document.getElementById('perfilEstado').value = this.dataset.estado || '';
                    document.getElementById('perfilRol').value = this.dataset.rol || '';
                    modalVerPerfil.show();
                });
            });
        }
    });
</script>
@endpush
