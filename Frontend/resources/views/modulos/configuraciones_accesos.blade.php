@extends('layouts.app')

@section('title', 'Configuracion de accesos al sistema - Hospital Escuela')
@section('header', 'Configuracion de accesos al sistema')

@push('styles')
<style>
    .cfg-card {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        background: #fff;
        overflow: hidden;
    }

    .cfg-tabs.nav-tabs {
        border-bottom: 1px solid #e5e7eb;
        gap: 2px;
        padding-bottom: 4px;
    }

    .cfg-tabs .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        border-radius: 0;
        color: #6b7280;
        font-weight: 600;
        padding: 8px 12px;
        background: transparent;
    }

    .cfg-tabs .nav-link:hover {
        color: #374151;
        border-color: #d1d5db;
        background: transparent;
    }

    .cfg-tabs .nav-link.active {
        color: #111827;
        background: transparent;
        border-color: #111827;
    }

    .cfg-tab-pane {
        padding-top: 14px;
    }

    .cfg-table thead th {
        font-size: 0.9rem;
        font-weight: 700;
        color: #374151;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 14px;
    }

    .cfg-table tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .cfg-state-active {
        background: #dcfce7;
        color: #166534;
    }

    .cfg-state-inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .cfg-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
</style>
@endpush

@section('content')
@php
    $tipos = [
        'CONSECUENCIAS_REACCION' => 'CONSECUENCIAS DE LA REACCION',
        'DESENLACE_REACCION' => 'DESENLACE',
        'ESTADO_REACCION' => 'ESTADOS DE REACCION',
    ];
@endphp

<div class="container-fluid py-3">
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

    <div class="mb-4 d-flex justify-content-end align-items-center flex-wrap gap-2">
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success rounded-pill fw-bold px-4 shadow-sm" onclick="abrirModalNuevoConfiguracion()">
                <i class="fas fa-plus me-2"></i>NUEVO
            </button>
            <button type="button" class="btn rounded-pill fw-bold text-white px-4 shadow-sm" style="background-color: #222c5e; border-color: #222c5e;" onclick="exportarPDFConfiguraciones()">
                <i class="fas fa-print me-2"></i>REPORTE
            </button>
        </div>
    </div>

    <ul class="nav nav-tabs cfg-tabs" id="cfgTab" role="tablist">
        @foreach($tipos as $tipo => $titulo)
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link {{ $loop->first ? 'active' : '' }}"
                    id="cfg-tab-{{ $tipo }}"
                    data-bs-toggle="tab"
                    data-bs-target="#cfg-pane-{{ $tipo }}"
                    type="button"
                    role="tab"
                    aria-controls="cfg-pane-{{ $tipo }}"
                    aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                >
                    {{ $titulo }}
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content" id="cfgTabContent">
        @foreach($tipos as $tipo => $titulo)
            @php
                $items = is_array($catalogo[$tipo] ?? null) ? $catalogo[$tipo] : [];
            @endphp

            <div
                class="tab-pane fade cfg-tab-pane {{ $loop->first ? 'show active' : '' }}"
                id="cfg-pane-{{ $tipo }}"
                role="tabpanel"
                aria-labelledby="cfg-tab-{{ $tipo }}"
                tabindex="0"
            >
                <div class="cfg-card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table cfg-table mb-0 text-uppercase">
                                <thead>
                                    <tr>
                                        <th style="width: 70px;">#</th>
                                        <th>Valor</th>
                                        <th class="text-center" style="width: 140px;">Estado</th>
                                        <th class="text-center" style="width: 220px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $idx => $item)
                                        <tr>
                                            <td class="fw-bold text-muted">{{ $idx + 1 }}</td>
                                            <td class="fw-bold">{{ $item['valor_objeto'] ?? '-' }}</td>
                                            <td class="text-center">
                                                @if(strtoupper((string) ($item['estado'] ?? '')) === 'ACTIVO')
                                                    <span class="badge cfg-state-active">ACTIVO</span>
                                                @else
                                                    <span class="badge cfg-state-inactive">INACTIVO</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="cfg-actions">
                                                    <button type="button" class="btn btn-action btn-edit" title="Editar" aria-label="Editar" data-bs-toggle="modal" data-bs-target="#cfg-edit-{{ $tipo }}-{{ $item['id_objeto'] }}">
                                                        <i class="fas fa-pen"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('configuraciones.accesos.destroy', ['tipo' => $tipo, 'id' => $item['id_objeto']]) }}" class="d-inline" onsubmit="return confirm('Se eliminara la opcion. Continuar?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-action btn-delete" title="Eliminar" aria-label="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="cfg-edit-{{ $tipo }}-{{ $item['id_objeto'] }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content border-0 shadow">
                                                    <form method="POST" action="{{ route('configuraciones.accesos.update', ['tipo' => $tipo, 'id' => $item['id_objeto']]) }}" data-unsaved-form="true">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Editar opcion</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Valor</label>
                                                                <input type="text" name="valor_objeto" class="form-control" value="{{ $item['valor_objeto'] }}" maxlength="255" required>
                                                            </div>
                                                            <div>
                                                                <label class="form-label fw-bold">Estado</label>
                                                                <select name="estado" class="form-select" required>
                                                                    <option value="ACTIVO" {{ strtoupper((string) ($item['estado'] ?? '')) === 'ACTIVO' ? 'selected' : '' }}>ACTIVO</option>
                                                                    <option value="INACTIVO" {{ strtoupper((string) ($item['estado'] ?? '')) === 'INACTIVO' ? 'selected' : '' }}>INACTIVO</option>
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
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">No hay opciones configuradas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="cfg-create-{{ $tipo }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content border-0 shadow">
                        <form method="POST" action="{{ route('configuraciones.accesos.store', ['tipo' => $tipo]) }}" data-unsaved-form="true">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">Nueva opcion</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Valor</label>
                                    <input type="text" name="valor_objeto" class="form-control" maxlength="255" required>
                                </div>
                                <div>
                                    <label class="form-label fw-bold">Estado</label>
                                    <select name="estado" class="form-select" required>
                                        <option value="ACTIVO">ACTIVO</option>
                                        <option value="INACTIVO">INACTIVO</option>
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
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script>
    const catalogoConfiguraciones = @json($catalogo ?? []);
    const usuarioActualPDFConfiguraciones = @json($usuario['usuario'] ?? $usuario['nombre'] ?? session('usuario_nombre') ?? 'USUARIO');
    let logoDataUrlConfiguraciones = null;

    (() => {
        const tabContainer = document.getElementById('cfgTab');
        if (!tabContainer) return;

        const getButtonByHash = (hash) =>
            tabContainer.querySelector(`button[data-bs-target="${hash}"]`);

        const activateTabFromHash = () => {
            const hash = window.location.hash;
            if (!hash || !hash.startsWith('#cfg-pane-')) return;

            const button = getButtonByHash(hash);
            if (!button) return;

            bootstrap.Tab.getOrCreateInstance(button).show();
        };

        tabContainer.querySelectorAll('button[data-bs-toggle="tab"]').forEach((button) => {
            button.addEventListener('shown.bs.tab', (event) => {
                const target = event.target.getAttribute('data-bs-target');
                if (!target) return;
                history.replaceState(null, '', target);
            });
        });

        window.addEventListener('hashchange', activateTabFromHash);
        activateTabFromHash();
    })();

    function abrirModalNuevoConfiguracion() {
        const activePane = document.querySelector('#cfgTabContent .tab-pane.active');
        if (!activePane || !activePane.id || !activePane.id.startsWith('cfg-pane-')) {
            return;
        }

        const tipo = activePane.id.replace('cfg-pane-', '');
        const modalEl = document.getElementById(`cfg-create-${tipo}`);
        if (!modalEl) return;

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function formatearFechaHoraConfiguraciones(fecha) {
        const dd = String(fecha.getDate()).padStart(2, '0');
        const mm = String(fecha.getMonth() + 1).padStart(2, '0');
        const yyyy = fecha.getFullYear();
        const hh = String(fecha.getHours()).padStart(2, '0');
        const mi = String(fecha.getMinutes()).padStart(2, '0');
        const ss = String(fecha.getSeconds()).padStart(2, '0');
        return `${dd}/${mm}/${yyyy} ${hh}:${mi}:${ss}`;
    }

    function valorSeguroConfiguraciones(valor) {
        const txt = String(valor ?? '').trim();
        return txt === '' ? '-' : txt;
    }

    async function obtenerLogoDataUrlConfiguraciones() {
        if (logoDataUrlConfiguraciones !== null) return logoDataUrlConfiguraciones;
        try {
            const response = await fetch('{{ asset('login-assets/images/logo-circle.png') }}', { cache: 'force-cache' });
            if (!response.ok) throw new Error('No se pudo cargar el logo');
            const blob = await response.blob();
            logoDataUrlConfiguraciones = await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(blob);
            });
        } catch (error) {
            console.warn('No se pudo cargar el logo para PDF Configuraciones:', error);
            logoDataUrlConfiguraciones = '';
        }
        return logoDataUrlConfiguraciones;
    }

    function getRowsConfiguraciones() {
        const titulos = {
            CONSECUENCIAS_REACCION: 'CONSECUENCIAS DE LA REACCION',
            DESENLACE_REACCION: 'DESENLACE',
            ESTADO_REACCION: 'ESTADOS DE REACCION',
        };

        const rows = [];
        Object.keys(titulos).forEach((tipo) => {
            const lista = Array.isArray(catalogoConfiguraciones[tipo]) ? catalogoConfiguraciones[tipo] : [];
            lista.forEach((item) => {
                rows.push({
                    tipo: titulos[tipo],
                    valor: valorSeguroConfiguraciones(item?.valor_objeto),
                    estado: valorSeguroConfiguraciones(item?.estado),
                });
            });
        });
        return rows;
    }

    async function exportarPDFConfiguraciones() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
        const now = new Date();
        const fechaHoraStr = formatearFechaHoraConfiguraciones(now);
        const logoDataUrl = await obtenerLogoDataUrlConfiguraciones();
        const navyBlue = [30, 58, 107];
        const accentBlue = [37, 99, 235];
        const footerGray = [240, 244, 248];
        const margin = 14;
        const headerH = 26;

        const datosExport = getRowsConfiguraciones();

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
            doc.text('Departamento de Seguridad — Configuracion de accesos al sistema', pw / 2, 19, { align: 'center' });
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
        doc.text('CONFIGURACION DE ACCESOS AL SISTEMA', margin + 4, startY + 5);
        startY += 11;

        const columns = [
            { header: 'CATEGORIA', dataKey: 'tipo' },
            { header: 'VALOR', dataKey: 'valor' },
            { header: 'ESTADO', dataKey: 'estado' },
        ];

        doc.autoTable({
            startY,
            margin: { top: headerH + 4, left: margin, right: margin, bottom: 16 },
            columns,
            body: datosExport,
            styles: { fontSize: 8, cellPadding: 3, overflow: 'linebreak', font: 'helvetica' },
            headStyles: { fillColor: navyBlue, textColor: [255, 255, 255], fontStyle: 'bold', fontSize: 8 },
            bodyStyles: { fillColor: [241, 245, 249] },
            alternateRowStyles: { fillColor: [255, 255, 255] },
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
            doc.text(`Hospital Escuela  |  Descargado por: ${usuarioActualPDFConfiguraciones}`, margin, ph - 4.5);
            doc.text(`Página ${i} de ${totalPaginas}`, pw - margin, ph - 4.5, { align: 'right' });
        }

        window.openJsPdfPreview(doc, {
            title: 'Reporte de Configuración de Accesos',
            fileName: `configuracion_accesos_${now.toISOString().split('T')[0]}.pdf`
        });
    }
</script>
@endpush
