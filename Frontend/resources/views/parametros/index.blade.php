@extends('layouts.app')

@section('title', 'Parametros - Hospital Escuela')
@section('header', 'Parámetros')

@push('styles')
<style>
    .badge-state-active {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }

    .badge-state-inactive {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    .swal-cuidado-popup {
        background: #f3eee8;
        border: 2px solid #f2be7a;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    }

    .swal-cuidado-html {
        text-align: left;
        padding: 2px 2px 0;
    }

    .swal-cuidado-title {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #9a3412;
        font-weight: 900;
        font-size: 1.7rem;
        line-height: 1.2;
    }

    .swal-cuidado-title i {
        color: #9a3412;
        font-size: 0.95rem;
    }

    .swal-cuidado-text {
        color: #b45309;
        margin-top: 8px;
        margin-left: 26px;
        font-size: 1.05rem;
    }

    .swal-btn-keep {
        background: #e5e7eb !important;
        border: 1px solid #d1d5db !important;
        color: #111827 !important;
        border-radius: 6px !important;
        font-weight: 700 !important;
        padding: 8px 14px !important;
    }

    .swal-btn-leave {
        background: #facc15 !important;
        border: 1px solid #eab308 !important;
        color: #111827 !important;
        border-radius: 6px !important;
        font-weight: 700 !important;
        padding: 8px 14px !important;
    }

</style>

@endpush

@section('content')
<div class="p-4 p-md-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1f2937;">Parámetros</h4>
            <p class="text-muted mb-0">Configura los parámetros del sistema</p>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn rounded-pill fw-bold text-white px-4 shadow-sm" style="background-color: #222c5e; border-color: #222c5e;" onclick="exportarPDFParametros()">
                <i class="fas fa-print me-2"></i>Reporte
            </button>
            <button type="button" class="btn rounded-pill fw-bold text-white px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoParametro" style="background-color: #019504; border-color: #019504;">
                <i class="fas fa-plus me-2"></i>Nuevo
            </button>
        </div>
    </div>

    <div class="mb-3">
        <div class="input-group search-pill" style="max-width: 520px;">
            <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
            <input type="text" id="paramSearchInput" class="form-control border-0" placeholder="BUSCAR PARAMETRO..." style="text-transform: uppercase;">
            <button type="button" id="paramSearchClear" class="btn btn-white border-0 bg-white text-danger px-3 d-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <div class="d-flex justify-content-start align-items-center gap-3 mb-2 px-1">
        <div class="d-flex align-items-center">
            <span class="text-muted fw-bold small me-2">MOSTRAR:</span>
            <form method="GET" action="{{ route('parametros.index') }}">
                <input type="hidden" name="page" value="1">
                <select name="per_page" class="select-round" onchange="this.form.submit()">
                    <option value="5" {{ (int) request('per_page', 10) === 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ (int) request('per_page', 10) === 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ (int) request('per_page', 10) === 15 ? 'selected' : '' }}>15</option>
                </select>
            </form>
        </div>
    </div>

    <div class="card card-table">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Parametro</th>
                        <th>Valor</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parametros as $p)
                        <tr class="param-row"
                            data-id="{{ (int) ($p['id_parametro'] ?? 0) }}"
                            data-search="{{ strtolower(($p['id_parametro'] ?? '') . ' ' . ($p['nombre_parametro'] ?? '') . ' ' . ($p['valor'] ?? '')) }}">
                            <td class="text-muted fw-bold">#{{ $p['id_parametro'] ?? '' }}</td>
                            <td class="fw-bold text-dark">{{ $p['nombre_parametro'] ?? '' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $p['valor'] ?? '' }}</span></td>
                            <td class="text-center">
                                <button type="button"
                                        class="btn btn-action btn-edit btn-editar-parametro"
                                        title="Editar"
                                        aria-label="Editar"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditarParametro"
                                        data-id="{{ $p['id_parametro'] ?? '' }}"
                                        data-nombre="{{ $p['nombre_parametro'] ?? '' }}"
                                        data-valor="{{ $p['valor'] ?? '' }}">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button type="button"
                                    class="btn btn-action btn-report"
                                        title="Imprimir"
                                        aria-label="Imprimir"
                                        onclick="descargarPDFIndividualParametro({{ (int) ($p['id_parametro'] ?? 0) }})">
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr id="param-empty-row">
                            <td colspan="4" class="text-center py-4 text-muted">No hay parametros registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(is_object($parametros) && method_exists($parametros, 'currentPage') && method_exists($parametros, 'lastPage'))
        @php
            $currentPage = (int) $parametros->currentPage();
            $totalPages = (int) $parametros->lastPage();
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
        @endphp

        <div class="d-flex justify-content-end align-items-center mt-4 mb-2 px-2">
            <span class="text-muted small fw-bold me-3">
                Página {{ $currentPage }} de {{ $totalPages }}
            </span>
            <nav>
                <ul class="pagination pagination-sm mb-0 shadow-sm" style="border-radius: 8px; overflow: hidden;">
                    <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                        <a class="page-link border-0 text-dark fw-bold px-3" href="{{ route('parametros.index', array_merge(request()->query(), ['page' => $currentPage - 1])) }}">
                            <i class="fas fa-chevron-left me-1"></i> Anterior
                        </a>
                    </li>
                    @if($startPage > 1)
                        <li class="page-item">
                            <a class="page-link border-0 text-dark fw-bold px-3" href="{{ route('parametros.index', array_merge(request()->query(), ['page' => 1])) }}">1</a>
                        </li>
                        @if($startPage > 2)
                            <li class="page-item disabled"><span class="page-link border-0 text-dark fw-bold px-3">...</span></li>
                        @endif
                    @endif

                    @for($page = $startPage; $page <= $endPage; $page++)
                        <li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
                            <a class="page-link border-0 text-dark fw-bold px-3" href="{{ route('parametros.index', array_merge(request()->query(), ['page' => $page])) }}">{{ $page }}</a>
                        </li>
                    @endfor

                    @if($endPage < $totalPages)
                        @if($endPage < $totalPages - 1)
                            <li class="page-item disabled"><span class="page-link border-0 text-dark fw-bold px-3">...</span></li>
                        @endif
                        <li class="page-item">
                            <a class="page-link border-0 text-dark fw-bold px-3" href="{{ route('parametros.index', array_merge(request()->query(), ['page' => $totalPages])) }}">{{ $totalPages }}</a>
                        </li>
                    @endif

                    <li class="page-item {{ $currentPage >= $totalPages ? 'disabled' : '' }}">
                        <a class="page-link border-0 text-dark fw-bold px-3" href="{{ route('parametros.index', array_merge(request()->query(), ['page' => $currentPage + 1])) }}">
                            Siguiente <i class="fas fa-chevron-right ms-1"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    @endif
</div>

<div class="modal fade" id="modalNuevoParametro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="{{ route('parametros.store') }}" id="formNuevoParametro" class="modal-content bg-white border-0 shadow" data-unsaved-form="true">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Nuevo Parametro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nombre Parametro</label>
                        <input type="text" name="nombre_parametro" class="form-control" value="{{ old('nombre_parametro') }}" maxlength="120" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Valor</label>
                        <input type="text" name="valor" class="form-control" value="{{ old('valor') }}" maxlength="120" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill fw-bold" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-pill fw-bold">Guardar Parametro</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEditarParametro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" id="formEditarParametro" class="modal-content bg-white border-0 shadow" data-unsaved-form="true">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Editar Parametro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nombre Parametro</label>
                        <input type="text" id="edit-parametro-nombre" class="form-control" maxlength="120" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Valor</label>
                        <input type="text" name="valor" id="edit-parametro-valor" class="form-control" maxlength="120" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill fw-bold" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-pill fw-bold">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script>
    const parametrosData = @json(is_object($parametros) && method_exists($parametros, 'items') ? $parametros->items() : (array) $parametros);
    const parametrosById = new Map((parametrosData || []).map((item) => [Number(item.id_parametro || 0), item]));
    const usuarioActualPDFParametros = @json($usuario['usuario'] ?? $usuario['nombre'] ?? session('usuario_nombre') ?? 'USUARIO');
    let terminoBusquedaParametros = '';
    let logoDataUrlParametros = null;

    function formatearFechaHoraParametros(fecha) {
        const dd = String(fecha.getDate()).padStart(2, '0');
        const mm = String(fecha.getMonth() + 1).padStart(2, '0');
        const yyyy = fecha.getFullYear();
        const hh = String(fecha.getHours()).padStart(2, '0');
        const mi = String(fecha.getMinutes()).padStart(2, '0');
        const ss = String(fecha.getSeconds()).padStart(2, '0');
        return `${dd}/${mm}/${yyyy} ${hh}:${mi}:${ss}`;
    }

    function valorSeguroParametros(valor) {
        const txt = String(valor ?? '').trim();
        return txt === '' ? '-' : txt;
    }

    async function obtenerLogoDataUrlParametros() {
        if (logoDataUrlParametros !== null) return logoDataUrlParametros;
        try {
            const response = await fetch('{{ asset('login-assets/images/logo-circle.png') }}', { cache: 'force-cache' });
            if (!response.ok) throw new Error('No se pudo cargar el logo');
            const blob = await response.blob();
            logoDataUrlParametros = await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(blob);
            });
        } catch (error) {
            console.warn('No se pudo cargar el logo para PDF Parametros:', error);
            logoDataUrlParametros = '';
        }
        return logoDataUrlParametros;
    }

    function obtenerFilasParametrosVisibles() {
        const filas = Array.from(document.querySelectorAll('.param-row'));
        return filas.filter((fila) => fila.style.display !== 'none');
    }

    function obtenerParametroDesdeFila(fila) {
        const id = Number(fila.dataset.id || 0);
        const item = parametrosById.get(id) || {};
        const celdas = fila.querySelectorAll('td');
        return {
            id_parametro: id || item.id_parametro || 0,
            nombre_parametro: item.nombre_parametro || (celdas[1]?.textContent || '').trim(),
            valor: item.valor || (celdas[2]?.textContent || '').trim(),
        };
    }

    function filtrarTablaParametros() {
        const q = String(terminoBusquedaParametros || '').toLowerCase().trim();
        const filas = Array.from(document.querySelectorAll('.param-row'));
        let visibles = 0;

        filas.forEach((fila) => {
            const hay = String(fila.dataset.search || '').includes(q);
            fila.style.display = hay ? '' : 'none';
            if (hay) visibles++;
        });

        let emptyRow = document.getElementById('param-empty-row-search');
        if (!emptyRow) {
            const tbody = document.querySelector('.table tbody');
            emptyRow = document.createElement('tr');
            emptyRow.id = 'param-empty-row-search';
            emptyRow.innerHTML = '<td colspan="4" class="text-center py-4 text-muted">No hay resultados para la búsqueda.</td>';
            tbody.appendChild(emptyRow);
        }
        emptyRow.style.display = visibles === 0 ? '' : 'none';
    }

    async function exportarPDFParametros() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
        const now = new Date();
        const fechaHoraStr = formatearFechaHoraParametros(now);
        const logoDataUrl = await obtenerLogoDataUrlParametros();
        const navyBlue = [30, 58, 107];
        const accentBlue = [37, 99, 235];
        const footerGray = [240, 244, 248];
        const margin = 14;
        const headerH = 26;

        const datosExport = obtenerFilasParametrosVisibles().map((f) => obtenerParametroDesdeFila(f)).filter((x) => !!x);
        const hayFiltros = String(terminoBusquedaParametros || '').trim().length > 0;

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
            doc.text('Departamento de Sistemas — Parametros', pw / 2, 19, { align: 'center' });
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
            hayFiltros ? 'PARAMETROS — RESULTADOS FILTRADOS' : 'PARAMETROS — REPORTE GENERAL',
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
                `Busqueda: "${String(terminoBusquedaParametros).toUpperCase()}"   |   Registros mostrados: ${datosExport.length}`,
                margin + 3, startY + 4.5
            );
            startY += 10;
        }

        const columns = [
            { header: 'ID', dataKey: 'id' },
            { header: 'PARAMETRO', dataKey: 'nombre' },
            { header: 'VALOR', dataKey: 'valor' },
        ];

        const filas = datosExport.map((p) => ({
            id: valorSeguroParametros(p.id_parametro),
            nombre: valorSeguroParametros(p.nombre_parametro),
            valor: valorSeguroParametros(p.valor),
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
            doc.text(`Hospital Escuela  |  Descargado por: ${usuarioActualPDFParametros}`, margin, ph - 4.5);
            doc.setTextColor(71, 85, 105);
            doc.setFontSize(7);
            doc.text(`Página ${i} de ${totalPaginas}`, pw - margin, ph - 4.5, { align: 'right' });
        }

        const tipo = hayFiltros ? 'filtrado' : 'general';
        window.openJsPdfPreview(doc, {
            title: 'Reporte de Parámetros',
            fileName: `parametros_${tipo}_${now.toISOString().split('T')[0]}.pdf`
        });
    }

    async function descargarPDFIndividualParametro(idParametro) {
        const item = parametrosById.get(Number(idParametro));
        if (!item) return;

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        const now = new Date();
        const fechaHoraStr = formatearFechaHoraParametros(now);
        const logoDataUrl = await obtenerLogoDataUrlParametros();
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
            doc.text('Departamento de Sistemas — Ficha de Parametro', pw / 2, 19, { align: 'center' });
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
        doc.text('FICHA DE PARAMETRO', margin + 4, startY + 5);
        startY += 11;

        const campos = [
            ['ID PARAMETRO', valorSeguroParametros(item.id_parametro)],
            ['NOMBRE', valorSeguroParametros(item.nombre_parametro)],
            ['VALOR', valorSeguroParametros(item.valor)],
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
            doc.text(`Hospital Escuela  |  Descargado por: ${usuarioActualPDFParametros}`, margin, ph - 4.5);
            doc.setTextColor(71, 85, 105);
            doc.text(`Página ${i} de ${totalPaginas}`, pw - margin, ph - 4.5, { align: 'right' });
        }

        window.openJsPdfPreview(doc, {
            title: `Reporte de Parámetro #${valorSeguroParametros(item.id_parametro)}`,
            fileName: `parametro_${valorSeguroParametros(item.id_parametro)}_${now.toISOString().split('T')[0]}.pdf`
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('paramSearchInput');
        const clearBtn = document.getElementById('paramSearchClear');

        if (searchInput && clearBtn) {
            searchInput.addEventListener('input', function () {
                terminoBusquedaParametros = this.value || '';
                clearBtn.classList.toggle('d-none', terminoBusquedaParametros.length === 0);
                filtrarTablaParametros();
            });

            clearBtn.addEventListener('click', function () {
                searchInput.value = '';
                terminoBusquedaParametros = '';
                clearBtn.classList.add('d-none');
                filtrarTablaParametros();
                searchInput.focus();
            });
        }

        const showWarn = (title, text) => {
            if (window.Swal) {
                Swal.fire({
                    html: `<div class="swal-cuidado-html"><div class="swal-cuidado-title"><i class="fas fa-exclamation-triangle"></i><span>${title}</span></div><div class="swal-cuidado-text">${text}</div></div>`,
                    confirmButtonText: 'Entendido',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'swal-cuidado-popup',
                        confirmButton: 'swal-btn-leave'
                    }
                });
                return;
            }
            alert(text);
        };

        const normalize = (value) => String(value ?? '').trim();

        const validateParametroForm = (form) => {
            const nombre = normalize(form.querySelector('[name="nombre_parametro"]')?.value);
            const valor = normalize(form.querySelector('[name="valor"]')?.value);

            if (form.id === 'formNuevoParametro' && (!nombre || nombre.length < 3)) {
                showWarn('Dato invalido', 'El nombre del parametro es obligatorio y debe tener al menos 3 caracteres.');
                form.querySelector('[name="nombre_parametro"]')?.focus();
                return false;
            }

            if (!valor) {
                showWarn('Dato invalido', 'El valor es obligatorio.');
                form.querySelector('[name="valor"]')?.focus();
                return false;
            }

            return true;
        };

        const shouldOpenCreate = @json((bool) session('open_parametro_modal')) || new URLSearchParams(window.location.search).get('nuevo') === '1';
        if (shouldOpenCreate) {
            const modalElement = document.getElementById('modalNuevoParametro');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        }

        const editForm = document.getElementById('formEditarParametro');
        const editNombre = document.getElementById('edit-parametro-nombre');
        const editValor = document.getElementById('edit-parametro-valor');

        const createForm = document.getElementById('formNuevoParametro');
        if (createForm) {
            createForm.addEventListener('submit', (event) => {
                if (!validateParametroForm(createForm)) {
                    event.preventDefault();
                }
            });
        }

        if (editForm) {
            editForm.addEventListener('submit', (event) => {
                if (!validateParametroForm(editForm)) {
                    event.preventDefault();
                }
            });
        }

        document.querySelectorAll('.btn-editar-parametro').forEach(function (button) {
            button.addEventListener('click', function () {
                const id = this.dataset.id;
                editForm.action = '{{ url('parametros') }}/' + id;
                editNombre.value = this.dataset.nombre || '';
                editValor.value = this.dataset.valor || '';
            });
        });

        filtrarTablaParametros();
    });
</script>
@endpush
