@extends('layouts.app')

@section('title', 'Inventario - Hospital Escuela')
@section('header', 'Inventario')

@push('styles')
<style>
    .inventario-page {
        font-family: 'Nunito', sans-serif;
        background-color: transparent;
    }

    .kpi-container { display: flex; gap: 20px; margin-bottom: 25px; flex-wrap: wrap; }
    .kpi-card {
        flex: 1;
        min-width: 200px;
        background: white;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        border: 1px solid #e2e8f0;
        transition: transform 0.2s;
        cursor: pointer;
    }
    .kpi-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .kpi-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 15px;
    }
    .kpi-red { border-left: 5px solid #ef4444; color: #ef4444; }
    .kpi-red .kpi-icon { background: #fee2e2; }

    .kpi-yellow { border-left: 5px solid #ca8a04; color: #ca8a04; }
    .kpi-yellow .kpi-icon { background: #fef9c3; }

    .kpi-blue { border-left: 5px solid #0284c7; color: #0284c7; }
    .kpi-blue .kpi-icon { background: #e0f2fe; }

    .kpi-purple { border-left: 5px solid #7c3aed; color: #7c3aed; }
    .kpi-purple .kpi-icon { background: #ede9fe; }

    .badge-estado-stock {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }

    .badge-estado-bajo-stock {
        background: #fef9c3;
        color: #854d0e;
        border: 1px solid #fde047;
    }

    .badge-estado-pronto {
        background: #f4e5d0;
        color: #7c4a21;
        border: 1px solid #d4a373;
    }

    .badge-estado-vencido {
        background: #111827;
        color: #ffffff;
        border: 1px solid #111827;
    }

    .badge-estado-agotado {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fca5a5;
    }

    .badge-estado-baja {
        background: #ffedd5;
        color: #c2410c;
        border: 1px solid #fdba74;
    }

    .badge-estado-cuarentena {
        background: #7c3aed;
        color: #ffffff;
        border: 1px solid #6d28d9;
    }

    .inventory-card {
        background: white;
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        overflow: visible;
    }
    .inventory-card .table-responsive {
        border-radius: 0 0 15px 15px;
        overflow-x: auto;
    }

    .table thead th {
        background-color: #f8fafc;
        color: #64748b;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        font-weight: 800;
        border-bottom: 2px solid #f1f5f9;
        padding: 15px 20px;
    }

    .badge-unit {
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
    }

    .page-link {
        border: none;
        color: #64748b;
        font-weight: 600;
        margin: 0 2px;
        border-radius: 6px !important;
    }
    .page-item.active .page-link {
        background-color: #0d6efd;
        color: #ffffff;
    }

    .modal-header { background-color: #f8fafc; border-bottom: 2px solid #f1f5f9; }
    .modal-title { font-weight: 800; color: #1f2937; }
    .form-label { font-weight: 700; color: #374151; font-size: 0.875rem; }
    .form-control:focus,
    .form-select:focus {
        border-color: #5eead4;
        box-shadow: 0 0 0 0.2rem rgba(94,234,212,0.25);
    }
    .btn-guardar {
        background-color: #111827;
        color: white;
        font-weight: 700;
        border-radius: 50px;
        padding: 10px 30px;
    }
    .btn-guardar:hover { background-color: #374151; color: white; }
    .btn-guardar:disabled { opacity: 0.65; cursor: not-allowed; }

    .filtros-wrapper { position: relative; }
    .filtros-panel {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        width: 300px;
        background: white;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 30px rgba(0,0,0,0.18);
        z-index: 1060;
        padding: 0;
        overflow: hidden;
        max-height: 480px;
        display: none;
        flex-direction: column;
    }
    .filtros-panel.show { display: flex !important; }
    .filtros-panel-body {
        overflow-y: auto;
        flex: 1;
    }
    .filtros-panel-header {
        background: #f8fafc;
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        font-weight: 800;
        font-size: 0.85rem;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .filtros-seccion {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
    }
    .filtros-seccion:last-of-type { border-bottom: none; }
    .filtros-seccion-titulo {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #94a3b8;
        margin-bottom: 8px;
    }
    .filtros-seccion .form-check { margin-bottom: 4px; }
    .filtros-seccion .form-check-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
    }
    .filtros-seccion .form-check-input:checked {
        background-color: #111827;
        border-color: #111827;
    }
    .filtros-panel-footer {
        padding: 10px 16px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }
    .btn-filtros-activo {
        background-color: #5eead4 !important;
        border-color: #5eead4 !important;
        color: #111827 !important;
    }

    .pagination-strip {
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
        backdrop-filter: none !important;
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
@php
    $canGuardar = (bool) ($canGuardarInventario ?? false);
    $canActualizar = (bool) ($canActualizarInventario ?? false);
    $canEliminar = (bool) ($canEliminarInventario ?? false);
    $esRestringido = (bool) ($esRestringido ?? false);
    $esJefe = (bool) ($esJefe ?? false);
@endphp

@if($esRestringido)
<!-- VISTA RESTRINGIDA: MÉDICOS, FARMACÉUTICOS Y ENFERMEROS -->
<div class="inventario-page p-4 p-md-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h4 class="fw-bold m-0" style="color: #1f2937;">Stock de Medicamentos</h4>
    </div>

    <div class="inventory-card">
        <div class="p-3 border-bottom">
            <div class="input-group search-pill" style="max-width: 350px;">
                <span class="input-group-text bg-white border-0 ps-3 text-muted"><i class="fas fa-search"></i></span>
                <input type="text" maxlength="50" id="medicamentoSearchRestringido" class="form-control border-0 px-2" placeholder="BUSCAR MEDICAMENTO..." style="text-transform: uppercase;">
                <button type="button" id="btnBorrarBusquedaRestringido" class="btn btn-white border-0 bg-white text-danger px-3 d-none" title="Borrar búsqueda">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                <thead>
                    <tr>
                        <th>Medicamento</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="tabla-inventario-restringido"></tbody>
            </table>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const inventarioData = @json($inventario);
    const input = document.getElementById('medicamentoSearchRestringido');

    function renderTablaRestringida(filtro = '') {
        const tbody = document.getElementById('tabla-inventario-restringido');
        tbody.innerHTML = '';
        const termino = filtro.toLowerCase();

        const estadosPermitidos = ['ACTIVO'];
        const datosEnStock = inventarioData.filter(item => {
            const estado = String(item.estado || '').toUpperCase();
            return Number(item.saldo) > 0 && estadosPermitidos.includes(estado);
        });

        const datos = termino
            ? datosEnStock.filter(item => {
                const nombre = String(item.nombre || '').toLowerCase();
                return nombre.includes(termino);
            })
            : datosEnStock;

        datos.forEach(item => {
            const estado = '<span class="badge rounded-pill badge-estado-stock">En Stock</span>';

            const fila = tbody.insertRow();
            fila.innerHTML = `
                <td class="fw-bold">${escHtml(item.nombre)}</td>
                <td>${estado}</td>
            `;
        });
    }

    renderTablaRestringida();
    input.addEventListener('keyup', () => {
        renderTablaRestringida(input.value);
        if (btnBorrarBusquedaRestringido) {
            btnBorrarBusquedaRestringido.classList.toggle('d-none', input.value.length === 0);
        }
    });

    const btnBorrarBusquedaRestringido = document.getElementById('btnBorrarBusquedaRestringido');
    if (btnBorrarBusquedaRestringido) {
        btnBorrarBusquedaRestringido.addEventListener('click', () => {
            input.value = '';
            renderTablaRestringida('');
            btnBorrarBusquedaRestringido.classList.add('d-none');
            input.focus();
        });
    }
});
</script>
@else
<!-- VISTA COMPLETA: ADMINISTRADOR, JEFE, FARMACEUTICO -->

<div class="inventario-page p-4 p-md-5">
    <div class="kpi-container">
        <div class="kpi-card kpi-blue">
            <div class="kpi-icon"><i class="fas fa-boxes-stacked"></i></div>
            <div>
                <h3 class="fw-bold mb-0" id="kpi-total">0</h3>
                <small class="fw-bold text-uppercase">Suministros totales</small>
            </div>
        </div>
        <div class="kpi-card kpi-red">
            <div class="kpi-icon"><i class="fas fa-exclamation-circle"></i></div>
            <div>
                <h3 class="fw-bold mb-0" id="kpi-criticos">0</h3>
                <small class="fw-bold text-uppercase">Bajo stock</small>
            </div>
        </div>
        <div class="kpi-card kpi-yellow">
            <div class="kpi-icon"><i class="fas fa-hourglass-half"></i></div>
            <div>
                <h3 class="fw-bold mb-0" id="kpi-vencimiento">0</h3>
                <small class="fw-bold text-uppercase">Pronto a vencer</small>
            </div>
        </div>
        <div class="kpi-card kpi-purple">
            <div class="kpi-icon"><i class="fas fa-shield-virus"></i></div>
            <div>
                <h3 class="fw-bold mb-0" id="kpi-cuarentena">0</h3>
                <small class="fw-bold text-uppercase">En cuarentena</small>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h4 class="fw-bold m-0" style="color: #1f2937;">Catálago de Medicamentos</h4>
        @if($canGuardar)
        <button class="btn rounded-pill px-4 fw-bold shadow-sm text-white" onclick="abrirModalNuevo()" style="background-color: #019504; border-color: #019504;">
            <i class="fas fa-plus me-2"></i> Agregar
        </button>
        @endif
    </div>

    <div class="inventory-card">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="input-group search-pill" style="max-width: 350px;">
                <span class="input-group-text bg-white border-0 ps-3 text-muted"><i class="fas fa-search"></i></span>
                <input type="text" maxlength="50" id="medicamentoSearch" class="form-control border-0 px-2" placeholder="BUSCAR POR NOMBRE O LOTE..." style="text-transform: uppercase;">
                <button type="button" id="btnBorrarBusquedaInventario" class="btn btn-white border-0 bg-white text-danger px-3 d-none" title="Borrar búsqueda">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="d-flex gap-2">
                <div class="filtros-wrapper">
                    <button id="btn-filtros" class="btn btn-outline-secondary rounded-pill btn-sm px-3 fw-bold" onclick="toggleFiltros()">
                        <i class="fas fa-filter me-2"></i>Filtros
                    </button>

                    <div id="panel-filtros" class="filtros-panel">
                        <div class="filtros-panel-header">
                            <i class="fas fa-sliders-h"></i> Filtrar inventario
                        </div>

                        <div class="filtros-panel-body">
                            <div class="filtros-seccion">
                                <div class="filtros-seccion-titulo">Estado</div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-estado" value="" id="filt-estado-todos" checked>
                                    <label class="form-check-label" for="filt-estado-todos">Todos</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-estado" value="ACTIVO" id="filt-activo">
                                    <label class="form-check-label" for="filt-activo">
                                        <span class="badge rounded-pill badge-estado-stock fw-bold px-2 me-1" style="font-size:0.7rem;">En Stock</span> En Stock
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-estado" value="BAJO_STOCK" id="filt-bajo-stock">
                                    <label class="form-check-label" for="filt-bajo-stock">
                                        <span class="badge rounded-pill badge-estado-bajo-stock fw-bold px-2 me-1" style="font-size:0.7rem;">Bajo Stock</span> Bajo Stock
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-estado" value="PRONTO_VENCER" id="filt-pronto-vencer">
                                    <label class="form-check-label" for="filt-pronto-vencer">
                                        <span class="badge rounded-pill badge-estado-pronto fw-bold px-2 me-1" style="font-size:0.7rem;">Pronto a vencer</span> Pronto a vencer
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-estado" value="VENCIDO" id="filt-vencido">
                                    <label class="form-check-label" for="filt-vencido">
                                        <span class="badge rounded-pill badge-estado-vencido fw-bold px-2 me-1" style="font-size:0.7rem;">Vencido</span> Vencidos
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-estado" value="AGOTADO" id="filt-agotado">
                                    <label class="form-check-label" for="filt-agotado">
                                        <span class="badge rounded-pill badge-estado-agotado fw-bold px-2 me-1" style="font-size:0.7rem;">Agotado</span> Agotados
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-estado" value="BAJA_ROTACION" id="filt-baja-rot">
                                    <label class="form-check-label" for="filt-baja-rot">
                                        <span class="badge rounded-pill badge-estado-baja fw-bold px-2 me-1" style="font-size:0.7rem;">Baja Rotación</span> Baja Rotación
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-estado" value="EN_CUARENTENA" id="filt-cuarentena">
                                    <label class="form-check-label" for="filt-cuarentena">
                                        <span class="badge rounded-pill badge-estado-cuarentena fw-bold px-2 me-1" style="font-size:0.7rem;">En Cuarentena</span> En Cuarentena
                                    </label>
                                </div>
                            </div>

                            <div class="filtros-seccion">
                                <div class="filtros-seccion-titulo">Ordenar por vencimiento</div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-venc" value="none" id="filt-venc-none" checked>
                                    <label class="form-check-label" for="filt-venc-none">Sin ordenar</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-venc" value="asc" id="filt-venc-asc">
                                    <label class="form-check-label" for="filt-venc-asc">
                                        <i class="fas fa-arrow-up me-1 text-warning" style="font-size:0.75rem;"></i>Pronto a vencerse
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-venc" value="desc" id="filt-venc-desc">
                                    <label class="form-check-label" for="filt-venc-desc">
                                        <i class="fas fa-arrow-down me-1 text-success" style="font-size:0.75rem;"></i>Mas lejos de vencer
                                    </label>
                                </div>
                            </div>

                            <div class="filtros-seccion">
                                <div class="filtros-seccion-titulo">Categoria</div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-cat" value="" id="filt-cat-todos" checked>
                                    <label class="form-check-label" for="filt-cat-todos">Todas</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-cat" value="Analgésico" id="filt-cat-1">
                                    <label class="form-check-label" for="filt-cat-1">Analgésico</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-cat" value="Antibiótico" id="filt-cat-2">
                                    <label class="form-check-label" for="filt-cat-2">Antibiótico</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-cat" value="Antihipertensivo" id="filt-cat-3">
                                    <label class="form-check-label" for="filt-cat-3">Antihipertensivo</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-cat" value="Antiinflamatorio" id="filt-cat-4">
                                    <label class="form-check-label" for="filt-cat-4">Antiinflamatorio</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-cat" value="Vitamina / Suplemento" id="filt-cat-5">
                                    <label class="form-check-label" for="filt-cat-5">Vitamina / Suplemento</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-cat" value="Solución IV" id="filt-cat-6">
                                    <label class="form-check-label" for="filt-cat-6">Solución IV</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filtro-cat" value="Otro" id="filt-cat-7">
                                    <label class="form-check-label" for="filt-cat-7">Otro</label>
                                </div>
                            </div>
                        </div>

                        <div class="filtros-panel-footer">
                            <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold" onclick="limpiarFiltros()">
                                <i class="fas fa-times me-1"></i>Limpiar
                            </button>
                            <button class="btn btn-sm rounded-pill px-3 fw-bold text-white" style="background:#111827;" onclick="aplicarFiltros()">
                                <i class="fas fa-check me-1"></i>Aplicar
                            </button>
                        </div>
                    </div>
                </div>
                @if($canGuardar)
                <button class="btn btn-outline-secondary rounded-pill btn-sm px-3 fw-bold" onclick="abrirModalCSV()">
                    <i class="fas fa-file-csv me-2"></i>Cargar CSV
                </button>
                @endif
                <button class="btn btn-outline-secondary rounded-pill btn-sm px-3 fw-bold" onclick="exportarPDF()">
                    <i class="fas fa-file-pdf me-2"></i>Reporte
                </button>
            </div>
        </div>

        <div class="d-flex justify-content-start align-items-center gap-2 mb-3 px-1 flex-wrap">
            <span class="text-muted fw-bold small">Ver</span>
            <select id="inventarioPerPageSelect" class="select-round">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="15">15</option>
            </select>
            <span class="text-muted fw-bold small">Registros</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Suministro</th>
                        <th>Categoria</th>
                        <th>Observación</th>
                        <th>Vencimiento</th>
                        <th>Saldo Actual</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-inventario"></tbody>
            </table>
        </div>

        <div class="glass pagination-strip px-4 py-3 mt-3">
            <div class="d-flex justify-content-end align-items-center flex-wrap gap-3">
                <span class="text-muted fw-bold" id="contador-registros">Página 1 de 1</span>
                <ul class="pagination pagination-sm mb-0 shadow-sm" style="border-radius: 8px; overflow: hidden;">
                    <li class="page-item disabled"><a class="page-link border-0 fw-bold px-3" href="#">Anterior</a></li>
                    <li class="page-item active"><a class="page-link border-0 fw-bold px-3" href="#">1</a></li>
                    <li class="page-item disabled"><a class="page-link border-0 fw-bold px-3" href="#">Siguiente</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endif

<!-- MODAL DE ALERTA: NUEVO INGRESO DE MEDICAMENTO -->
<div class="modal fade" id="modalNuevoIngreso" tabindex="-1" aria-labelledby="modalNuevoIngresoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
        <div class="modal-content">
            <div class="modal-header" style="background: #e0f7fa; border-bottom: 2px solid #00bcd4;">
                <h5 class="modal-title" id="modalNuevoIngresoLabel" style="color: #00695c;">
                    <i class="fas fa-box-open me-2" style="color: #00bcd4;"></i>Nuevo Medicamento Ingresado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div style="background: #f0f9ff; padding: 15px; border-radius: 8px; border-left: 4px solid #00bcd4;">
                    <p class="mb-2"><strong>Medicamento:</strong> <span id="nuevoIngresoNombre" class="text-primary"></span></p>
                    <p class="mb-2"><strong>Lote:</strong> <span id="nuevoIngresoLote" style="font-family: monospace;"></span></p>
                    <p class="mb-2"><strong>Cantidad:</strong> <span id="nuevoIngresoSaldo" class="text-success fw-bold"></span></p>
                    <p class="mb-0"><strong>Vencimiento:</strong> <span id="nuevoIngresoVenc"></span></p>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">
                    <i class="fas fa-xmark me-2"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

@if($canEliminar)
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center px-4 pb-2">
                <div class="mb-3" style="font-size: 3rem; color: #ef4444;">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Eliminar registro?</h5>
                <p class="text-muted mb-1">Esta a punto de eliminar:</p>
                <p class="fw-bold text-dark mb-3" id="eliminar-nombre-item" style="font-size: 1rem;"></p>
                <p class="text-muted small">Esta accion no se puede deshacer.</p>
            </div>
            <div class="modal-footer border-0 d-flex gap-2 justify-content-center pb-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">
                    <i class="fas fa-xmark me-2"></i>Cancelar
                </button>
                <button type="button" class="btn rounded-pill px-4 fw-bold text-white" id="btn-confirmar-eliminar"
                        style="background-color: #ef4444; border: none;">
                    <i class="fas fa-trash me-2"></i>Eliminar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@if($canGuardar || $canActualizar)
<div class="modal fade" id="modalInventario" tabindex="-1" aria-labelledby="modalInventarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalInventarioLabel">
                    <i class="fas fa-box-open me-2 text-primary"></i>Nuevo Registro
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="campo-id">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="campo-codigo" class="form-label">Codigo (N. Lote) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="campo-codigo" placeholder="Ej: MED-001" required maxlength="100" onkeydown="if(event.repeat)event.preventDefault()" oninput="this.value=this.value.toUpperCase()">
                    </div>

                    <div class="col-md-6">
                        <label for="campo-nombre" class="form-label">Nombre del Suministro <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="campo-nombre" placeholder="Ej: Acetaminofen 500 mg" required maxlength="100" onkeydown="if(event.repeat)event.preventDefault()" oninput="this.value=this.value.toUpperCase()">
                    </div>

                    <div class="col-md-6">
                        <label for="campo-categoria" class="form-label">Categoria / Tipo</label>
                        <select class="form-select" id="campo-categoria">
                            <option value="">Seleccionar</option>
                            <option value="Analgésico">Analgésico</option>
                            <option value="Antibiótico">Antibiótico</option>
                            <option value="Antihipertensivo">Antihipertensivo</option>
                            <option value="Antiinflamatorio">Antiinflamatorio</option>
                            <option value="Vitamina / Suplemento">Vitamina / Suplemento</option>
                            <option value="Solución IV">Solución IV</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="campo-unidad" class="form-label">Unidad de Emision</label>
                        <select class="form-select" id="campo-unidad">
                            <option value="">Seleccionar</option>
                            <option value="TAB">TAB - Tableta</option>
                            <option value="CAP">CAP - Capsula</option>
                            <option value="VIAL">VIAL - Vial</option>
                            <option value="AMP">AMP - Ampolla</option>
                            <option value="SOBRE">SOBRE - Sobre</option>
                            <option value="FRASCO">FRASCO - Frasco</option>
                            <option value="ML">ML - Mililitro</option>
                            <option value="G">G - Gramo</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="campo-proveedor" class="form-label">Proveedor / Laboratorio</label>
                        <input type="text" class="form-control" id="campo-proveedor" placeholder="Ej: Laboratorio XYZ" maxlength="100" onkeydown="if(event.repeat)event.preventDefault()" oninput="this.value=this.value.toUpperCase()">
                    </div>

                    <div class="col-md-6">
                        <label for="campo-saldo" class="form-label">Saldo Actual <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="campo-saldo" min="0" placeholder="Ej: 500" required>
                    </div>

                    <div class="col-md-6">
                        <label for="campo-vencimiento" class="form-label">Fecha de Vencimiento</label>
                        <input type="date" class="form-control" id="campo-vencimiento">
                    </div>

                    <div class="col-md-6">
                        <label for="campo-estado" class="form-label">Estado</label>
                        <select class="form-select" id="campo-estado">
                            <option value="ACTIVO">Activo</option>
                            <option value="BAJO_STOCK">Bajo Stock</option>
                            <option value="PRONTO_VENCER">Pronto a Vencer</option>
                            <option value="BAJA_ROTACION">Baja Rotación</option>
                            <option value="EN_CUARENTENA">En Cuarentena</option>
                            <option value="VENCIDO">Vencido</option>
                            <option value="AGOTADO">Agotado</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="campo-observacion" class="form-label">Observación</label>
                        <select class="form-select" id="campo-observacion">
                            <option value="SIN_OBSERVACION">Sin observación</option>
                            <option value="REVISAR_VENCIMIENTO">Revisar vencimiento</option>
                            <option value="PENDIENTE_REPOSICION">Pendiente reposición</option>
                            <option value="RETENER_EN_CUARENTENA">Retener en cuarentena</option>
                            <option value="BLOQUEAR_DISPENSACION">Bloquear dispensación</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="campo-descripcion" class="form-label">Descripcion / Notas</label>
                        <textarea class="form-control" id="campo-descripcion" rows="2" placeholder="Principio activo, observaciones u otras notas..." maxlength="100" onkeydown="if(event.repeat)event.preventDefault()" oninput="this.value=this.value.toUpperCase()"></textarea>
                    </div>
                </div>
                <div id="modal-error" class="alert alert-danger mt-3 d-none" role="alert"></div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-guardar" id="btn-guardar" onclick="guardarRegistro()">
                    <i class="fas fa-save me-2"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<div class="modal fade" id="modalVer" tabindex="-1" aria-labelledby="modalVerLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVerLabel">
                    <i class="fas fa-eye me-2 text-primary"></i>Detalle del Registro
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Codigo (N. Lote)</label><input type="text" class="form-control" id="ver-codigo" disabled></div>
                    <div class="col-md-6"><label class="form-label">Nombre del Suministro</label><input type="text" class="form-control" id="ver-nombre" disabled></div>
                    <div class="col-md-6"><label class="form-label">Categoria / Tipo</label><input type="text" class="form-control" id="ver-categoria" disabled></div>
                    <div class="col-md-6"><label class="form-label">Unidad de Emision</label><input type="text" class="form-control" id="ver-unidad" disabled></div>
                    <div class="col-md-6"><label class="form-label">Proveedor / Laboratorio</label><input type="text" class="form-control" id="ver-proveedor" disabled></div>
                    <div class="col-md-6"><label class="form-label">Saldo Actual</label><input type="text" class="form-control" id="ver-saldo" disabled></div>
                    <div class="col-md-6"><label class="form-label">Fecha de Vencimiento</label><input type="text" class="form-control" id="ver-vencimiento" disabled></div>
                    <div class="col-md-6"><label class="form-label">Estado</label><input type="text" class="form-control" id="ver-estado" disabled></div>
                    <div class="col-md-6"><label class="form-label">Observación</label><input type="text" class="form-control" id="ver-observacion" disabled></div>
                    <div class="col-12"><label class="form-label">Descripcion / Notas</label><textarea class="form-control" id="ver-descripcion" rows="2" disabled></textarea></div>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">
                    <i class="fas fa-xmark me-2"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCSV" tabindex="-1" aria-labelledby="modalCSVLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalCSVLabel">
                    <i class="fas fa-file-csv me-2" style="color:#5eead4;"></i>Importar Medicamentos por CSV
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" onclick="resetModalCSV()"></button>
            </div>
            <div class="modal-body px-4 pb-2 pt-3">
                <div id="csv-drop-area"
                     style="background:#f8fafc; border: 2px dashed #94a3b8; border-radius:12px; padding:40px 20px; text-align:center; cursor:pointer; transition: border-color 0.2s, background 0.2s;"
                     onclick="document.getElementById('csv-file-input').click()"
                     ondragover="csvDragOver(event)"
                     ondragleave="csvDragLeave(event)"
                     ondrop="csvDrop(event)">
                    <div style="font-size:3rem; color:#94a3b8; margin-bottom:12px;">
                        <i class="fas fa-cloud-arrow-up"></i>
                    </div>
                    <p style="color:#0ea5e9; font-weight:700; margin-bottom:4px;">
                        <span style="text-decoration:underline;">Haz clic para cargar</span> o arrastra y suelta
                    </p>
                    <p style="color:#64748b; font-size:0.82rem; margin:0;">Solo archivos <strong style="color:#334155;">.csv</strong></p>
                    <input type="file" id="csv-file-input" accept=".csv" style="display:none;" onchange="csvFileSelected(this)">
                </div>

                <div id="csv-file-info" class="mt-3 d-none">
                    <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#f0fdf4; border:1px solid #bbf7d0;">
                        <i class="fas fa-file-csv text-success fs-5"></i>
                        <div>
                            <div class="fw-bold text-success small" id="csv-file-name"></div>
                            <div class="text-muted" style="font-size:0.75rem;" id="csv-file-rows"></div>
                        </div>
                        <button type="button" class="btn-close ms-auto" style="font-size:0.6rem;" onclick="resetModalCSV()"></button>
                    </div>
                </div>

                <div id="csv-error-list" class="mt-3 d-none">
                    <div class="alert alert-warning p-2 small mb-0">
                        <strong><i class="fas fa-triangle-exclamation me-1"></i>Algunos registros no se importaron:</strong>
                        <ul class="mb-0 mt-1 ps-3" id="csv-error-items"></ul>
                    </div>
                </div>

                <div class="mt-3 text-center">
                    <button type="button" class="btn btn-link btn-sm text-decoration-none fw-bold" onclick="descargarEjemploCSV()" style="color:#0ea5e9;">
                        <i class="fas fa-download me-1"></i>Descargar ejemplo de CSV
                    </button>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal" onclick="resetModalCSV()">
                    <i class="fas fa-xmark me-2"></i>Cancelar
                </button>
                <button type="button" class="btn rounded-pill px-4 fw-bold text-white" id="btn-importar-csv"
                        style="background:#111827; border:none;" onclick="importarCSV()" disabled>
                    <span id="csv-spinner" class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                    <i class="fas fa-file-import me-2" id="csv-import-icon"></i>Importar
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
    let inventario = @json($inventario);
    const PERMS = {
        guardar: @json($canGuardar),
        actualizar: @json($canActualizar),
        eliminar: @json($canEliminar),
    };

    const ROUTES = {
        store: '{{ route("inventario.store") }}',
        bulk: '{{ route("inventario.bulk") }}',
        data: '{{ route("inventario.datos") }}',
        updateBase: '{{ route("inventario.update", ["id" => "__ID__"]) }}',
        destroyBase: '{{ route("inventario.destroy", ["id" => "__ID__"]) }}',
        update: (id) => '{{ route("inventario.update", ["id" => "__ID__"]) }}'.replace('__ID__', id),
        destroy: (id) => '{{ route("inventario.destroy", ["id" => "__ID__"]) }}'.replace('__ID__', id),
    };

    const CSRF = () => document.querySelector('meta[name="csrf-token"]').content;

    let modalBS = null;
    let modalVerBS = null;
    let modalEliminarBS = null;
    let idPendienteEliminar = null;
    let inventarioInitialState = '';
    let bypassUnsavedInventario = false;
    let csvModalBS = null;
    let csvDatos = [];
    let bypassUnsavedCsv = false;
    let csvImportInProgress = false;

    let paginaActual = 1;
    let itemsPorPagina = 10;
    let datosFiltrados = [];
    let terminoBusquedaActual = '';

    function serializarModalInventario() {
        return JSON.stringify({
            id: document.getElementById('campo-id')?.value || '',
            codigo: document.getElementById('campo-codigo')?.value || '',
            nombre: document.getElementById('campo-nombre')?.value || '',
            categoria: document.getElementById('campo-categoria')?.value || '',
            unidad: document.getElementById('campo-unidad')?.value || '',
            proveedor: document.getElementById('campo-proveedor')?.value || '',
            saldo: document.getElementById('campo-saldo')?.value || '',
            vencimiento: document.getElementById('campo-vencimiento')?.value || '',
            estado: document.getElementById('campo-estado')?.value || '',
            observacion: document.getElementById('campo-observacion')?.value || '',
            descripcion: document.getElementById('campo-descripcion')?.value || '',
        });
    }

    window.hasUnsavedChanges = function() {
        const modalInventario = document.getElementById('modalInventario');
        const hayCambiosInventario = !!modalInventario
            && modalInventario.classList.contains('show')
            && !bypassUnsavedInventario
            && (serializarModalInventario() !== inventarioInitialState);

        const modalCSV = document.getElementById('modalCSV');
        const hayCambiosCSV = !!modalCSV
            && modalCSV.classList.contains('show')
            && !bypassUnsavedCsv
            && !csvImportInProgress
            && csvDatos.length > 0;

        return hayCambiosInventario || hayCambiosCSV;
    };

    async function askDiscardInventario() {
        if (window.Swal) {
            const result = await Swal.fire({
                html: '<div class="swal-cuidado-html"><div class="swal-cuidado-title"><i class="fas fa-exclamation-triangle"></i><span>¡Cuidado!</span></div><div class="swal-cuidado-text">Los datos no se guardarán.</div></div>',
                showCancelButton: true,
                confirmButtonText: 'Salir',
                cancelButtonText: 'Quedarme',
                reverseButtons: true,
                buttonsStyling: false,
                customClass: {
                    popup: 'swal-cuidado-popup',
                    confirmButton: 'swal-btn-leave',
                    cancelButton: 'swal-btn-keep'
                }
            });
            return result.isConfirmed;
        }
        return confirm('¡Cuidado! Los datos no se guardarán.');
    }

    const modalInventarioEl = document.getElementById('modalInventario');
    if (modalInventarioEl) {
        modalInventarioEl.addEventListener('shown.bs.modal', () => {
            inventarioInitialState = serializarModalInventario();
        });

        modalInventarioEl.addEventListener('hide.bs.modal', async (event) => {
            if (bypassUnsavedInventario) return;

            event.preventDefault();
            const confirmed = await askDiscardInventario();
            if (!confirmed) return;

            bypassUnsavedInventario = true;
            bootstrap.Modal.getOrCreateInstance(modalInventarioEl).hide();
            setTimeout(() => { bypassUnsavedInventario = false; }, 0);
        });
    }

    function renderTabla() {
        const inicio = (paginaActual - 1) * itemsPorPagina;
        const fin = inicio + itemsPorPagina;
        renderTablaDatos(datosFiltrados.slice(inicio, fin));
        renderPaginacion();
    }

    function renderPaginacion() {
        const totalPaginas = Math.ceil(datosFiltrados.length / itemsPorPagina) || 1;
        const ul = document.querySelector('.pagination');
        const pageInfo = document.getElementById('contador-registros');
        if (pageInfo) {
            pageInfo.textContent = `Página ${paginaActual} de ${totalPaginas}`;
        }
        ul.innerHTML = '';

        function crearBtn(label, pagina, disabled, activo = false) {
            const li = document.createElement('li');
            li.className = `page-item${disabled ? ' disabled' : ''}${activo ? ' active' : ''}`;
            li.innerHTML = `<a class="page-link border-0 fw-bold px-3" href="#">${label}</a>`;
            if (!disabled) {
                li.addEventListener('click', e => {
                    e.preventDefault();
                    paginaActual = pagina;
                    renderTabla();
                });
            }
            ul.appendChild(li);
        }

        crearBtn('Anterior', paginaActual - 1, paginaActual === 1);

        const desde = Math.max(1, paginaActual - 2);
        const hasta = Math.min(totalPaginas, paginaActual + 2);
        for (let i = desde; i <= hasta; i++) {
            crearBtn(i, i, false, i === paginaActual);
        }

        crearBtn('Siguiente', paginaActual + 1, paginaActual === totalPaginas);
    }

    function observacionDefaultPorEstado(estado) {
        switch (String(estado || '').toUpperCase()) {
            case 'VENCIDO':
                return 'BLOQUEAR_DISPENSACION';
            case 'PRONTO_VENCER':
                return 'REVISAR_VENCIMIENTO';
            case 'BAJO_STOCK':
            case 'AGOTADO':
                return 'PENDIENTE_REPOSICION';
            case 'EN_CUARENTENA':
                return 'RETENER_EN_CUARENTENA';
            default:
                return 'SIN_OBSERVACION';
        }
    }

    function opcionesObservacion() {
        return [
            { value: 'SIN_OBSERVACION', label: 'Sin observación' },
            { value: 'REVISAR_VENCIMIENTO', label: 'Revisar vencimiento' },
            { value: 'PENDIENTE_REPOSICION', label: 'Pendiente reposición' },
            { value: 'RETENER_EN_CUARENTENA', label: 'Retener en cuarentena' },
            { value: 'BLOQUEAR_DISPENSACION', label: 'Bloquear dispensación' },
        ];
    }

    function nombreObservacion(valor) {
        const encontrada = opcionesObservacion().find(opt => opt.value === valor);
        return encontrada ? encontrada.label : 'Sin observación';
    }

    function updateObservacionInventario(idLote, valor) {
        const item = inventario.find(i => Number(i.id_lote) === Number(idLote));
        if (item) {
            item.observacion = valor;
        }
    }

    function renderObservacionSelect(item) {
        const estado = estadoLogico(item);
        const valorActual = item.observacion || observacionDefaultPorEstado(estado);
        const options = opcionesObservacion()
            .map(opt => `<option value="${opt.value}" ${opt.value === valorActual ? 'selected' : ''}>${opt.label}</option>`)
            .join('');

        return `
            <select class="form-select form-select-sm" style="min-width: 220px;" onchange="updateObservacionInventario(${Number(item.id_lote)}, this.value)">
                ${options}
            </select>
        `;
    }

    function renderTablaDatos(datos) {
        const tbody = document.getElementById('tabla-inventario');
        tbody.innerHTML = '';

        datos.forEach(item => {
            const fila = document.createElement('tr');
            fila.dataset.busqueda = (item.codigo + ' ' + item.nombre).toLowerCase();

            let acciones = `
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn-action btn-view" onclick="abrirModalVer(${item.id_lote})" title="Ver detalle" aria-label="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn-action btn-report" onclick="descargarPDFIndividual(${item.id_lote})" title="Reporte" aria-label="Reporte">
                        <i class="fas fa-file-pdf"></i>
                    </button>
            `;

            if (PERMS.actualizar) {
                acciones += `
                    <button type="button" class="btn-action btn-edit" onclick="abrirModalEditar(${item.id_lote})" title="Editar" aria-label="Editar">
                        <i class="fas fa-pen"></i>
                    </button>
                `;
            }

            if (PERMS.eliminar) {
                acciones += `
                    <button type="button" class="btn-action btn-delete" onclick="eliminarRegistro(${item.id_lote})" title="Eliminar" aria-label="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
            }

            acciones += `</div>`;

            fila.innerHTML = `
                <td class="text-muted fw-bold">${escHtml(item.codigo)}</td>
                <td>
                    <div class="fw-bold text-dark">${escHtml(item.nombre)}</div>
                    ${item.descripcion ? `<small class="text-muted">${escHtml(item.descripcion.substring(0, 50))}${item.descripcion.length > 50 ? '...' : ''}</small>` : ''}
                </td>
                <td><span class="badge-unit">${escHtml(item.categoria || '—')}</span></td>
                <td>${renderObservacionSelect(item)}</td>
                <td class="text-muted">${formatFecha(item.vencimiento)}</td>
                <td class="fw-bold fs-5 ${item.saldo <= 10 ? 'text-danger' : item.saldo <= 50 ? 'text-warning' : ''}">${Number(item.saldo).toLocaleString('es-HN')}</td>
                <td>${badgeEstado(item)}</td>
                <td class="text-center" style="white-space: nowrap;">${acciones}</td>
            `;
            tbody.appendChild(fila);
        });

        const contEl = document.getElementById('contador-registros');
        const totalPaginas = Math.ceil(datosFiltrados.length / itemsPorPagina) || 1;
        if (contEl) {
            contEl.textContent = `Página ${paginaActual} de ${totalPaginas}`;
        }

        actualizarKPIs();
    }

    function inicioDelDia(fecha) {
        const copia = new Date(fecha);
        copia.setHours(0, 0, 0, 0);
        return copia;
    }

    function parseFechaYmd(fechaYmd) {
        if (!fechaYmd) return null;
        const [y, m, d] = String(fechaYmd).split('-');
        if (!y || !m || !d) return null;
        return new Date(Number(y), Number(m) - 1, Number(d));
    }

    function parseFechaFlexible(valor) {
        if (!valor) return null;
        const fecha = new Date(valor);
        if (Number.isNaN(fecha.getTime())) return null;
        return fecha;
    }

    function calcularEstado(item) {
        const estadoActual = String(item.estado || 'ACTIVO').toUpperCase();
        const saldo = Number(item.saldo || 0);
        const hoy = inicioDelDia(new Date());

        if (estadoActual === 'EN_CUARENTENA') {
            return 'EN_CUARENTENA';
        }

        if (saldo === 0 || estadoActual === 'AGOTADO') {
            return 'AGOTADO';
        }

        const fechaVencimiento = parseFechaYmd(item.vencimiento);
        if (fechaVencimiento && inicioDelDia(fechaVencimiento) < hoy) {
            return 'VENCIDO';
        }

        const fechaUltimoMovimiento = parseFechaFlexible(item.fecha_ultimo_movimiento);
        if (fechaUltimoMovimiento) {
            const umbralBaja = new Date(hoy);
            umbralBaja.setMonth(umbralBaja.getMonth() - 3);
            if (inicioDelDia(fechaUltimoMovimiento) <= umbralBaja) {
                return 'BAJA_ROTACION';
            }
        }

        if (fechaVencimiento) {
            const en30dias = new Date(hoy);
            en30dias.setDate(en30dias.getDate() + 30);
            const venc = inicioDelDia(fechaVencimiento);
            if (venc >= hoy && venc <= en30dias) {
                return 'PRONTO_VENCER';
            }
        }

        if (saldo <= 50) {
            return 'BAJO_STOCK';
        }

        return 'ACTIVO';
    }

    function nombreEstado(estado) {
        const nombres = {
            ACTIVO: 'En Stock',
            BAJO_STOCK: 'Bajo Stock',
            PRONTO_VENCER: 'Pronto a vencer',
            VENCIDO: 'Vencido',
            AGOTADO: 'Agotado',
            BAJA_ROTACION: 'Baja Rotación',
            EN_CUARENTENA: 'En Cuarentena',
        };
        return nombres[estado] || 'En Stock';
    }

    function badgeEstado(item) {
        const estado = calcularEstado(item);

        if (estado === 'EN_CUARENTENA') {
            return '<span class="badge rounded-pill badge-estado-cuarentena fw-bold px-3">En Cuarentena</span>';
        }
        if (estado === 'BAJA_ROTACION') {
            return '<span class="badge rounded-pill badge-estado-baja fw-bold px-3">Baja Rotación</span>';
        }
        if (estado === 'PRONTO_VENCER') {
            return '<span class="badge rounded-pill badge-estado-pronto fw-bold px-3">Pronto a vencer</span>';
        }
        if (estado === 'VENCIDO') {
            return '<span class="badge rounded-pill badge-estado-vencido fw-bold px-3">Vencido</span>';
        }
        if (estado === 'AGOTADO') {
            return '<span class="badge rounded-pill badge-estado-agotado fw-bold px-3">Agotado</span>';
        }
        if (estado === 'BAJO_STOCK') {
            return '<span class="badge rounded-pill badge-estado-bajo-stock fw-bold px-3">Bajo Stock</span>';
        }

        return '<span class="badge rounded-pill badge-estado-stock fw-bold px-3">En Stock</span>';
    }

    function formatFecha(fecha) {
        if (!fecha) return '—';
        const [y, m, d] = fecha.split('-');
        return `${d}/${m}/${y}`;
    }

    function escHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function actualizarKPIs() {
        let criticos = 0;
        let porVencer = 0;
        let cuarentena = 0;

        inventario.forEach(item => {
            const estado = estadoLogico(item);

            if (estado === 'EN_CUARENTENA') {
                cuarentena++;
            }

            if (estado === 'BAJO_STOCK') criticos++;
            if (estado === 'PRONTO_VENCER') porVencer++;
        });

        document.getElementById('kpi-criticos').textContent = criticos;
        document.getElementById('kpi-vencimiento').textContent = porVencer;
        document.getElementById('kpi-total').textContent = inventario.length;
        document.getElementById('kpi-cuarentena').textContent = cuarentena;
    }

    function obtenerModal() {
        if (!modalBS) {
            modalBS = new bootstrap.Modal(document.getElementById('modalInventario'));
        }
        return modalBS;
    }

    function limpiarModal() {
        ['campo-id','campo-codigo','campo-nombre','campo-proveedor','campo-saldo','campo-vencimiento','campo-descripcion','campo-estado','campo-observacion'].forEach(id => {
            const el = document.getElementById(id);
            if (el.tagName === 'SELECT') {
                if (id === 'campo-estado') {
                    el.value = 'ACTIVO';
                } else if (id === 'campo-observacion') {
                    el.value = 'SIN_OBSERVACION';
                } else {
                    el.value = '';
                }
            } else {
                el.value = '';
            }
        });
        document.getElementById('campo-categoria').value = '';
        document.getElementById('campo-unidad').value = '';
        ocultarError();
    }

    function abrirModalNuevo() {
        if (!PERMS.guardar) return;
        limpiarModal();
        document.getElementById('modalInventarioLabel').innerHTML = '<i class="fas fa-plus-circle me-2 text-success"></i>Agregar';
        document.getElementById('campo-id').value = '';
        obtenerModal().show();
    }

    function abrirModalEditar(id_lote) {
        if (!PERMS.actualizar) return;
        const item = inventario.find(i => i.id_lote == id_lote);
        if (!item) return;

        limpiarModal();
        document.getElementById('modalInventarioLabel').innerHTML = '<i class="fas fa-pen me-2 text-warning"></i>Editar Registro';
        document.getElementById('campo-id').value = item.id_lote;
        document.getElementById('campo-codigo').value = item.codigo;
        document.getElementById('campo-nombre').value = item.nombre;
        document.getElementById('campo-categoria').value = item.categoria || '';
        document.getElementById('campo-unidad').value = item.unidad || '';
        document.getElementById('campo-proveedor').value = item.proveedor || '';
        document.getElementById('campo-saldo').value = item.saldo;
        document.getElementById('campo-vencimiento').value = item.vencimiento || '';
        document.getElementById('campo-estado').value = item.estado || 'ACTIVO';
        document.getElementById('campo-observacion').value = item.observacion || observacionDefaultPorEstado(estadoLogico(item));
        document.getElementById('campo-descripcion').value = item.descripcion || '';

        obtenerModal().show();
    }

    function abrirModalVer(id_lote) {
        const item = inventario.find(i => i.id_lote == id_lote);
        if (!item) return;

        document.getElementById('ver-codigo').value = item.codigo;
        document.getElementById('ver-nombre').value = item.nombre;
        document.getElementById('ver-categoria').value = item.categoria || '—';
        document.getElementById('ver-unidad').value = item.unidad || '—';
        document.getElementById('ver-proveedor').value = item.proveedor || '—';
        document.getElementById('ver-saldo').value = Number(item.saldo).toLocaleString('es-HN');
        document.getElementById('ver-vencimiento').value = formatFecha(item.vencimiento);
        document.getElementById('ver-estado').value = nombreEstado(estadoLogico(item));
        document.getElementById('ver-observacion').value = nombreObservacion(item.observacion || observacionDefaultPorEstado(estadoLogico(item)));
        document.getElementById('ver-descripcion').value = item.descripcion || '';

        if (!modalVerBS) modalVerBS = new bootstrap.Modal(document.getElementById('modalVer'));
        modalVerBS.show();
    }

    function mostrarError(msg) {
        const el = document.getElementById('modal-error');
        el.textContent = msg;
        el.classList.remove('d-none');
    }

    function ocultarError() {
        document.getElementById('modal-error').classList.add('d-none');
    }

    function setBtnGuardar(cargando) {
        const btn = document.getElementById('btn-guardar');
        btn.disabled = cargando;
        btn.innerHTML = cargando
            ? '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...'
            : '<i class="fas fa-save me-2"></i>Guardar';
    }

    function tieneCaracteresNoPermitidos(valor) {
        return /[$&#!]/.test(String(valor || ''));
    }

    async function guardarRegistro() {
        ocultarError();

        const codigo = document.getElementById('campo-codigo').value.trim();
        const nombre = document.getElementById('campo-nombre').value.trim();
        const categoria = document.getElementById('campo-categoria').value || '';
        const unidad = document.getElementById('campo-unidad').value || '';
        const proveedor = document.getElementById('campo-proveedor').value.trim();
        const descripcion = document.getElementById('campo-descripcion').value.trim();
        const observacion = document.getElementById('campo-observacion').value || 'SIN_OBSERVACION';
        const saldoStr = document.getElementById('campo-saldo').value;

        if (!codigo) { mostrarError('El campo Codigo es obligatorio.'); return; }
        if (!nombre) { mostrarError('El campo Nombre del Suministro es obligatorio.'); return; }
        if (tieneCaracteresNoPermitidos(codigo)) { mostrarError('No se permiten los caracteres $ & # ! en Codigo.'); return; }
        if (tieneCaracteresNoPermitidos(nombre)) { mostrarError('No se permiten los caracteres $ & # ! en Nombre del Suministro.'); return; }
        if (tieneCaracteresNoPermitidos(categoria)) { mostrarError('No se permiten los caracteres $ & # ! en Categoria / Tipo.'); return; }
        if (tieneCaracteresNoPermitidos(unidad)) { mostrarError('No se permiten los caracteres $ & # ! en Unidad de Emision.'); return; }
        if (tieneCaracteresNoPermitidos(proveedor)) { mostrarError('No se permiten los caracteres $ & # ! en Proveedor / Laboratorio.'); return; }
        if (tieneCaracteresNoPermitidos(descripcion)) { mostrarError('No se permiten los caracteres $ & # ! en Descripcion / Notas.'); return; }
        if (saldoStr === '' || isNaN(saldoStr) || Number(saldoStr) < 0) {
            mostrarError('El Saldo Actual debe ser un numero mayor o igual a 0.');
            return;
        }

        const datos = {
            codigo,
            nombre,
            categoria: categoria || null,
            unidad: unidad || null,
            proveedor: proveedor || null,
            saldo: parseInt(saldoStr, 10),
            vencimiento: document.getElementById('campo-vencimiento').value || null,
            estado: document.getElementById('campo-estado').value || 'ACTIVO',
            observacion,
            descripcion: descripcion || null,
        };

        const idExistente = document.getElementById('campo-id').value;
        if (!idExistente && !PERMS.guardar) {
            mostrarError('No tienes permiso para guardar registros.');
            return;
        }
        if (idExistente && !PERMS.actualizar) {
            mostrarError('No tienes permiso para actualizar registros.');
            return;
        }

        const method = idExistente ? 'PUT' : 'POST';
        const url = idExistente ? ROUTES.update(idExistente) : ROUTES.store;

        setBtnGuardar(true);

        try {
            const resp = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify(datos),
            });

            const result = await resp.json();

            if (!resp.ok) {
                mostrarError(result.message || 'Ocurrio un error al guardar.');
                return;
            }

            // Recargar datos del servidor para asegurar consistencia
            try {
                const respDatos = await fetch(ROUTES.data, {
                    headers: {
                        'X-CSRF-TOKEN': CSRF(),
                        'Accept': 'application/json',
                    },
                });

                if (respDatos.ok) {
                    const datosActualizados = await respDatos.json();
                    inventario = datosActualizados;
                    datosFiltrados = inventario.slice();
                    paginaActual = 1;
                } else {
                    // Si falla la recarga, usar los datos locales
                    if (idExistente) {
                        const idx = inventario.findIndex(i => i.id_lote == idExistente);
                        if (idx !== -1) {
                            inventario[idx] = { ...inventario[idx], ...datos, id_lote: parseInt(idExistente, 10) };
                        }
                    } else {
                        inventario.push({ ...datos, id_lote: result.id_lote });
                    }
                    datosFiltrados = inventario.slice();
                    paginaActual = 1;
                }
            } catch (errDatos) {
                // Si hay error en la recarga, usar los datos locales
                if (idExistente) {
                    const idx = inventario.findIndex(i => i.id_lote == idExistente);
                    if (idx !== -1) {
                        inventario[idx] = { ...inventario[idx], ...datos, id_lote: parseInt(idExistente, 10) };
                    }
                } else {
                    inventario.push({ ...datos, id_lote: result.id_lote });
                }
                datosFiltrados = inventario.slice();
                paginaActual = 1;
            }

            bypassUnsavedInventario = true;
            obtenerModal().hide();
            setTimeout(() => { bypassUnsavedInventario = false; }, 0);
            renderTabla();
        } catch (err) {
            mostrarError('Error de conexion. Verifica que el servidor este en linea.');
        } finally {
            setBtnGuardar(false);
        }
    }

    function eliminarRegistro(id_lote) {
        if (!PERMS.eliminar) return;
        const item = inventario.find(i => i.id_lote == id_lote);
        if (!item) return;

        idPendienteEliminar = id_lote;
        document.getElementById('eliminar-nombre-item').textContent = item.nombre;

        if (!modalEliminarBS) modalEliminarBS = new bootstrap.Modal(document.getElementById('modalEliminar'));
        modalEliminarBS.show();
    }

    const btnConfirmarEliminar = document.getElementById('btn-confirmar-eliminar');
    if (btnConfirmarEliminar) btnConfirmarEliminar.addEventListener('click', async function () {
        if (idPendienteEliminar === null) return;

        const btnEl = document.getElementById('btn-confirmar-eliminar');
        btnEl.disabled = true;
        btnEl.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Eliminando...';

        try {
            const resp = await fetch(ROUTES.destroy(idPendienteEliminar), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF(),
                    'Accept': 'application/json',
                },
            });

            if (resp.ok) {
                idPendienteEliminar = null;
                
                // Recargar datos del servidor para asegurar consistencia
                try {
                    const respDatos = await fetch(ROUTES.data, {
                        headers: {
                            'X-CSRF-TOKEN': CSRF(),
                            'Accept': 'application/json',
                        },
                    });

                    if (respDatos.ok) {
                        const datosActualizados = await respDatos.json();
                        inventario = datosActualizados;
                        datosFiltrados = inventario.slice();
                        paginaActual = 1;
                    } else {
                        // Fallback: remover localmente
                        inventario = inventario.filter(i => i.id_lote != idPendienteEliminar);
                        datosFiltrados = inventario.slice();
                        paginaActual = 1;
                    }
                } catch (errDatos) {
                    // Fallback: remover localmente
                    inventario = inventario.filter(i => i.id_lote != idPendienteEliminar);
                    datosFiltrados = inventario.slice();
                    paginaActual = 1;
                }

                modalEliminarBS.hide();
                renderTabla();
            } else {
                const result = await resp.json();
                alert('Error al eliminar: ' + (result.message || 'Intenta de nuevo.'));
            }
        } catch (err) {
            alert('Error de conexion al intentar eliminar.');
        } finally {
            btnEl.disabled = false;
            btnEl.innerHTML = '<i class="fas fa-trash me-2"></i>Eliminar';
        }
    });

    function toggleFiltros() {
        document.getElementById('panel-filtros').classList.toggle('show');
    }

    function estadoLogico(item) {
        return calcularEstado(item);
    }

    function aplicarFiltros() {
        const estadoSel = document.querySelector('input[name="filtro-estado"]:checked')?.value || '';
        const ordenVenc = document.querySelector('input[name="filtro-venc"]:checked')?.value || 'none';
        const catSel = document.querySelector('input[name="filtro-cat"]:checked')?.value || '';
        terminoBusquedaActual = '';
        document.getElementById('medicamentoSearch').value = '';

        let resultado = inventario.slice();

        if (estadoSel !== '') {
            resultado = resultado.filter(item => estadoLogico(item) === estadoSel);
        }

        if (catSel !== '') {
            resultado = resultado.filter(item => item.categoria === catSel);
        }

        if (ordenVenc !== 'none') {
            resultado.sort((a, b) => {
                if (!a.vencimiento && !b.vencimiento) return 0;
                if (!a.vencimiento) return 1;
                if (!b.vencimiento) return -1;
                const diff = new Date(a.vencimiento) - new Date(b.vencimiento);
                return ordenVenc === 'asc' ? diff : -diff;
            });
        }

        const hayFiltros = estadoSel !== '' || catSel !== '' || ordenVenc !== 'none';
        const btnFiltros = document.getElementById('btn-filtros');
        btnFiltros.classList.toggle('btn-filtros-activo', hayFiltros);
        btnFiltros.classList.toggle('btn-outline-secondary', !hayFiltros);

        datosFiltrados = resultado;
        paginaActual = 1;
        renderTabla();
        document.getElementById('panel-filtros').classList.remove('show');
    }

    function limpiarFiltros() {
        document.getElementById('filt-estado-todos').checked = true;
        document.getElementById('filt-venc-none').checked = true;
        document.getElementById('filt-cat-todos').checked = true;
        terminoBusquedaActual = '';
        document.getElementById('medicamentoSearch').value = '';

        const btnFiltros = document.getElementById('btn-filtros');
        btnFiltros.classList.remove('btn-filtros-activo');
        btnFiltros.classList.add('btn-outline-secondary');

        datosFiltrados = inventario.slice();
        paginaActual = 1;
        renderTabla();
        document.getElementById('panel-filtros').classList.remove('show');
    }

    function filtrarTabla(valor) {
        const termino = (valor || '').trim().toLowerCase();
        terminoBusquedaActual = termino;
        datosFiltrados = termino
            ? inventario.filter(item => {
                const nombre = String(item.nombre || '').toLowerCase();
                const lote = String(item.lote || item.codigo || '').toLowerCase();
                return nombre.includes(termino) || lote.includes(termino);
            })
            : inventario.slice();
        paginaActual = 1;
        renderTabla();
    }

    const LOGO_LOGIN_URL = @json(asset('login-assets/images/logo-circle.png'));
    let logoDataUrlCache = null;
    const inventarioPerPageSelect = document.getElementById('inventarioPerPageSelect');

    if (inventarioPerPageSelect) {
        inventarioPerPageSelect.value = String(itemsPorPagina);
        inventarioPerPageSelect.addEventListener('change', function() {
            itemsPorPagina = parseInt(this.value, 10) || 10;
            paginaActual = 1;
            renderTabla();
        });
    }

    function formatearFechaHora(fecha) {
        return fecha.toLocaleString('es-HN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        });
    }

    async function obtenerLogoDataUrl() {
        if (logoDataUrlCache !== null) return logoDataUrlCache;

        try {
            const response = await fetch(LOGO_LOGIN_URL, { cache: 'force-cache' });
            if (!response.ok) {
                logoDataUrlCache = '';
                return logoDataUrlCache;
            }

            const blob = await response.blob();
            logoDataUrlCache = await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onloadend = () => resolve(reader.result || '');
                reader.onerror = reject;
                reader.readAsDataURL(blob);
            });
        } catch (error) {
            console.warn('No se pudo cargar el logo para PDF:', error);
            logoDataUrlCache = '';
        }

        return logoDataUrlCache;
    }

    async function exportarPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
        const hoy = new Date();
        const fechaHoraStr = formatearFechaHora(hoy);
        const logoDataUrl = await obtenerLogoDataUrl();
        const usuarioActual = @json($usuario['usuario'] ?? 'USUARIO');
        const navyBlue   = [30, 58, 107];
        const accentBlue = [37, 99, 235];
        const footerGray = [240, 244, 248];
        const margin  = 14;
        const headerH = 26;
        const pageW   = doc.internal.pageSize.width;

        // ── Detectar filtros activos ──────────────────────────────────────
        const terminoBusqueda = terminoBusquedaActual || '';
        const estadoFiltro = document.querySelector('input[name="filtro-estado"]:checked')?.value || '';
        const catFiltro    = document.querySelector('input[name="filtro-cat"]:checked')?.value    || '';
        const hayFiltros   = !!(terminoBusqueda || estadoFiltro || catFiltro);

        let contextLabel = '';
        if (terminoBusqueda) {
            contextLabel = `Búsqueda: "${terminoBusqueda.toUpperCase()}"`;
        } else if (estadoFiltro || catFiltro) {
            const parts = [];
            if (estadoFiltro) parts.push('Estado: ' + nombreEstado(estadoFiltro));
            if (catFiltro)    parts.push('Categoría: ' + catFiltro);
            contextLabel = 'Filtro: ' + parts.join(' | ');
        }

        const datosExport = (datosFiltrados && datosFiltrados.length > 0) ? datosFiltrados : inventario;
        let criticos  = 0;
        let porVencer = 0;
        datosExport.forEach(item => {
            const est = estadoLogico(item);
            if (est === 'BAJO_STOCK')    criticos++;
            if (est === 'PRONTO_VENCER') porVencer++;
        });

        // ── Encabezado de página (se redibuja en cada hoja) ──────────────
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
            doc.text('Departamento de Farmacia — Inventario de Medicamentos', pw / 2, 19, { align: 'center' });
            doc.setFontSize(7);
            doc.text(`Generado: ${fechaHoraStr}`, pw - margin, 23, { align: 'right' });
            doc.setFillColor(...accentBlue);
            doc.rect(0, headerH, pw, 1.2, 'F');
        };

        drawPageHeader();
        let startY = headerH + 6;

        // ── Título del reporte ────────────────────────────────────────────
        doc.setDrawColor(...accentBlue);
        doc.setLineWidth(1.5);
        doc.line(margin, startY, margin, startY + 7);
        doc.setLineWidth(0.1);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(10.5);
        doc.setTextColor(...navyBlue);
        doc.text(
            hayFiltros ? 'CATÁLOGO DE MEDICAMENTOS — RESULTADOS FILTRADOS' : 'CATÁLOGO DE MEDICAMENTOS',
            margin + 4, startY + 5
        );
        startY += 11;

        // ── Barra de contexto (búsqueda / filtro activo) ──────────────────
        if (contextLabel) {
            doc.setFillColor(240, 244, 248);
            doc.roundedRect(margin, startY, pageW - margin * 2, 7, 1.5, 1.5, 'F');
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(7.5);
            doc.setTextColor(55, 65, 81);
            doc.text(
                `${contextLabel}   |   Total mostrado: ${datosExport.length} de ${inventario.length} registros`,
                margin + 3, startY + 4.5
            );
            startY += 10;
        }

        // ── Tabla ─────────────────────────────────────────────────────────
        const columns = [
            { header: 'CÓDIGO',      dataKey: 'codigo'      },
            { header: 'SUMINISTRO',  dataKey: 'nombre'      },
            { header: 'CATEGORÍA',   dataKey: 'categoria'   },
            { header: 'OBSERVACIÓN', dataKey: 'observacion' },
            { header: 'VENCIMIENTO', dataKey: 'vencimiento' },
            { header: 'SALDO',       dataKey: 'saldo'       },
            { header: 'ESTADO',      dataKey: 'estado'      },
        ];

        const filas = datosExport.map(item => ({
            codigo:      item.codigo || '—',
            nombre:      item.nombre + (item.descripcion ? '\n' + item.descripcion.substring(0, 60) : ''),
            categoria:   item.categoria || '—',
            observacion: nombreObservacion(item.observacion || observacionDefaultPorEstado(estadoLogico(item))),
            vencimiento: formatFecha(item.vencimiento),
            saldo:       Number(item.saldo).toLocaleString('es-HN'),
            estado:      nombreEstado(estadoLogico(item)),
        }));

        doc.autoTable({
            startY,
            margin: { top: headerH + 4, left: margin, right: margin, bottom: 16 },
            columns,
            body: filas,
            styles:               { fontSize: 7.5, cellPadding: 2.8, overflow: 'linebreak', font: 'helvetica' },
            headStyles:           { fillColor: navyBlue, textColor: [255, 255, 255], fontStyle: 'bold', fontSize: 7.5 },
            bodyStyles:           { fillColor: [241, 245, 249] },
            alternateRowStyles:   { fillColor: [255, 255, 255] },
            columnStyles:         { saldo: { halign: 'right', fontStyle: 'bold' } },
            didDrawPage: (data) => { if (data.pageNumber > 1) drawPageHeader(); },
        });

        // ── Pie de página en cada hoja ────────────────────────────────────
        const totalPaginas = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPaginas; i++) {
            doc.setPage(i);
            const ph  = doc.internal.pageSize.height;
            const pw2 = doc.internal.pageSize.width;
            doc.setFillColor(...footerGray);
            doc.rect(0, ph - 12, pw2, 12, 'F');
            doc.setDrawColor(...accentBlue);
            doc.setLineWidth(0.5);
            doc.line(0, ph - 12, pw2, ph - 12);
            doc.setLineWidth(0.1);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(7);
            doc.setTextColor(71, 85, 105);
            doc.text(`Hospital Escuela  |  Descargado por: ${usuarioActual}`, margin, ph - 4.5);
            doc.setTextColor(71, 85, 105);
            doc.setFontSize(7);
            doc.text(`Página ${i} de ${totalPaginas}`, pw2 - margin, ph - 4.5, { align: 'right' });
        }

        const tipo = hayFiltros ? 'filtrado' : 'general';
        window.openJsPdfPreview(doc, {
            title: 'Reporte de Inventario',
            fileName: `inventario_${tipo}_${hoy.toISOString().split('T')[0]}.pdf`
        });
    }

    async function descargarPDFIndividual(id_lote) {
        const item = inventario.find(i => i.id_lote == id_lote);
        if (!item) return;

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        const hoy = new Date();
        const fechaHoraStr = formatearFechaHora(hoy);
        const logoDataUrl  = await obtenerLogoDataUrl();
        const usuarioActual = @json($usuario['usuario'] ?? 'USUARIO');
        const navyBlue   = [30, 58, 107];
        const accentBlue = [37, 99, 235];
        const footerGray = [240, 244, 248];
        const margin  = 14;
        const headerH = 26;

        // ── Encabezado de página ──────────────────────────────────────────
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
            doc.text('Departamento de Farmacia — Ficha de Medicamento', pw / 2, 19, { align: 'center' });
            doc.setFontSize(7);
            doc.text(`Generado: ${fechaHoraStr}`, pw - margin, 23, { align: 'right' });
            doc.setFillColor(...accentBlue);
            doc.rect(0, headerH, pw, 1.2, 'F');
        };

        drawPageHeader();
        let startY = headerH + 6;

        // ── Título ────────────────────────────────────────────────────────
        doc.setDrawColor(...accentBlue);
        doc.setLineWidth(1.5);
        doc.line(margin, startY, margin, startY + 7);
        doc.setLineWidth(0.1);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(10.5);
        doc.setTextColor(...navyBlue);
        doc.text('FICHA DE MEDICAMENTO', margin + 4, startY + 5);
        startY += 11;

        // ── Tabla CAMPO / VALOR ───────────────────────────────────────────
        const campos = [
            ['CÓDIGO (N. LOTE)',          item.codigo    || '—'],
            ['NOMBRE DEL SUMINISTRO',     item.nombre    || '—'],
            ['CATEGORÍA / TIPO',          item.categoria || '—'],
            ['UNIDAD DE EMISIÓN',         item.unidad    || '—'],
            ['PROVEEDOR / LABORATORIO',   item.proveedor || '—'],
            ['SALDO ACTUAL',              Number(item.saldo).toLocaleString('es-HN')],
            ['FECHA DE VENCIMIENTO',      formatFecha(item.vencimiento)],
            ['ESTADO',                    nombreEstado(estadoLogico(item))],
            ['OBSERVACIÓN',               nombreObservacion(item.observacion || observacionDefaultPorEstado(estadoLogico(item)))],
            ['DESCRIPCIÓN / NOTAS',       item.descripcion || '—'],
        ];

        doc.autoTable({
            startY,
            margin: { top: headerH + 4, left: margin, right: margin, bottom: 16 },
            head:   [['CAMPO', 'VALOR']],
            body:   campos,
            styles:             { fontSize: 9.5, cellPadding: 3.5, overflow: 'linebreak', font: 'helvetica' },
            headStyles:         { fillColor: navyBlue, textColor: [255, 255, 255], fontStyle: 'bold', fontSize: 9 },
            bodyStyles:         { fillColor: [241, 245, 249] },
            alternateRowStyles: { fillColor: [255, 255, 255] },
            columnStyles: {
                0: { cellWidth: 70, fontStyle: 'bold', textColor: navyBlue },
            },
            didDrawPage: (data) => { if (data.pageNumber > 1) drawPageHeader(); },
        });

        // ── Pie de página en cada hoja ────────────────────────────────────
        const totalPaginas = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPaginas; i++) {
            doc.setPage(i);
            const ph  = doc.internal.pageSize.height;
            const pw2 = doc.internal.pageSize.width;
            doc.setFillColor(...footerGray);
            doc.rect(0, ph - 12, pw2, 12, 'F');
            doc.setDrawColor(...accentBlue);
            doc.setLineWidth(0.5);
            doc.line(0, ph - 12, pw2, ph - 12);
            doc.setLineWidth(0.1);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(7);
            doc.setTextColor(71, 85, 105);
            doc.text(`Hospital Escuela  |  Descargado por: ${usuarioActual}`, margin, ph - 4.5);
            doc.setTextColor(71, 85, 105);
            doc.text(`Página ${i} de ${totalPaginas}`, pw2 - margin, ph - 4.5, { align: 'right' });
        }

        const codigoSafe = (item.codigo || 'medicamento').replace(/[^a-zA-Z0-9_-]/g, '_');
        window.openJsPdfPreview(doc, {
            title: `Reporte de Inventario ${codigoSafe}`,
            fileName: `medicamento_${codigoSafe}_${hoy.toISOString().split('T')[0]}.pdf`
        });
    }

    function abrirModalCSV() {
        if (!PERMS.guardar) return;
        resetModalCSV();
        if (!csvModalBS) csvModalBS = new bootstrap.Modal(document.getElementById('modalCSV'));
        csvModalBS.show();
    }

    function resetModalCSV() {
        bypassUnsavedCsv = false;
        csvDatos = [];
        document.getElementById('csv-file-input').value = '';
        document.getElementById('csv-file-info').classList.add('d-none');
        document.getElementById('csv-error-list').classList.add('d-none');
        document.getElementById('csv-error-items').innerHTML = '';
        document.getElementById('btn-importar-csv').disabled = true;
        const areaReset = document.getElementById('csv-drop-area');
        areaReset.style.borderColor = '#94a3b8';
        areaReset.style.background = '#f8fafc';
    }

    function csvDragOver(e) {
        e.preventDefault();
        const area = document.getElementById('csv-drop-area');
        area.style.borderColor = '#5eead4';
        area.style.background = '#f0fdf9';
    }

    function csvDragLeave() {
        const area = document.getElementById('csv-drop-area');
        area.style.borderColor = '#94a3b8';
        area.style.background = '#f8fafc';
    }

    function csvDrop(e) {
        e.preventDefault();
        csvDragLeave();
        const file = e.dataTransfer.files[0];
        if (file) procesarArchivoCSV(file);
    }

    function csvFileSelected(input) {
        if (input.files[0]) procesarArchivoCSV(input.files[0]);
    }

    function procesarArchivoCSV(file) {
        if (!file.name.toLowerCase().endsWith('.csv')) {
            alert('Por favor selecciona un archivo con extension .csv');
            return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
            const texto = e.target.result;
            csvDatos = parsearCSV(texto);

            document.getElementById('csv-file-name').textContent = file.name;
            document.getElementById('csv-file-rows').textContent = csvDatos.length + ' registro(s) detectado(s)';
            document.getElementById('csv-file-info').classList.remove('d-none');
            document.getElementById('btn-importar-csv').disabled = csvDatos.length === 0;
            document.getElementById('csv-error-list').classList.add('d-none');
        };
        reader.readAsText(file, 'UTF-8');
    }

    function parsearCSV(texto) {
        const lineas = texto.split(/\r?\n/).filter(l => l.trim() !== '');
        if (lineas.length < 2) return [];

        const sep = lineas[0].includes(';') ? ';' : ',';

        const mapeo = {
            'código': 'codigo', 'codigo': 'codigo',
            'suministro': 'nombre', 'nombre': 'nombre',
            'categoría': 'categoria', 'categoria': 'categoria',
            'u. de emisión': 'unidad', 'u.de emisión': 'unidad', 'unidad': 'unidad', 'unidad de emisión': 'unidad',
            'proveedor': 'proveedor',
            'vencimiento': 'vencimiento', 'fecha de vencimiento': 'vencimiento',
            'saldo actual': 'saldo', 'saldo': 'saldo',
            'estado': 'estado'
        };

        const headers = lineas[0].split(sep).map(h => h.trim().toLowerCase().replace(/"/g, ''));
        const camposAPI = headers.map(h => mapeo[h] || null);

        const registros = [];
        for (let i = 1; i < lineas.length; i++) {
            const cols = lineas[i].split(sep).map(c => c.trim().replace(/^"|"$/g, ''));
            const obj = {};
            camposAPI.forEach((campo, idx) => {
                if (campo) obj[campo] = cols[idx] !== undefined ? cols[idx] : '';
            });
            if (obj.codigo || obj.nombre) registros.push(obj);
        }

        return registros;
    }

    async function importarCSV() {
        if (!PERMS.guardar) return;
        if (csvDatos.length === 0) return;

        const btn = document.getElementById('btn-importar-csv');
        const spinner = document.getElementById('csv-spinner');
        const icon = document.getElementById('csv-import-icon');

        csvImportInProgress = true;
        btn.disabled = true;
        spinner.classList.remove('d-none');
        icon.classList.add('d-none');

        try {
            const resp = await fetch(ROUTES.bulk, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify(csvDatos),
            });

            const data = await resp.json();

            if (!resp.ok) {
                alert(data.message || 'Error al importar CSV.');
                return;
            }

            if (data.errores && data.errores.length > 0) {
                const lista = document.getElementById('csv-error-items');
                lista.innerHTML = data.errores.map(e => `<li>Fila ${e.fila} (${e.codigo}): ${e.error}</li>`).join('');
                document.getElementById('csv-error-list').classList.remove('d-none');
            }

            const totalProcesados = Number(data.insertados || 0) + Number(data.actualizados || 0);

            if (totalProcesados > 0) {
                // Recargar datos del servidor
                try {
                    const respDatos = await fetch(ROUTES.data, {
                        headers: {
                            'X-CSRF-TOKEN': CSRF(),
                            'Accept': 'application/json',
                        },
                    });

                    if (respDatos.ok) {
                        const datosActualizados = await respDatos.json();
                        inventario = datosActualizados;
                        datosFiltrados = inventario.slice();
                        paginaActual = 1;
                        renderTabla();
                    }
                } catch (errDatos) {
                    // Si falla, hacer reload completo como fallback
                    window.location.reload();
                }

                if (csvModalBS) {
                    bypassUnsavedCsv = true;
                    csvModalBS.hide();
                }
                resetModalCSV();
            }

            if (totalProcesados === 0 && (!data.errores || data.errores.length === 0)) {
                alert('No se importaron registros. Verifica el formato del archivo.');
            }
        } catch (err) {
            alert('Error de conexion al importar. Intenta de nuevo.');
        } finally {
            csvImportInProgress = false;
            btn.disabled = false;
            spinner.classList.add('d-none');
            icon.classList.remove('d-none');
        }
    }

    function descargarEjemploCSV() {
        const bom = '\uFEFF';
        const contenido = bom +
            'Código;Suministro;U. de Emisión;Saldo\n' +
            'J05AR0200;Abacavir 120 mg + lamivudina 60 mg;TAB;100\n' +
            'N02BE0102;Acetaminofén 500 mg;TAB;200\n';

        const blob = new Blob([contenido], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'ejemplo_medicamentos.csv';
        a.click();
        URL.revokeObjectURL(url);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const modalCSVEl = document.getElementById('modalCSV');
        if (modalCSVEl) {
            modalCSVEl.addEventListener('hide.bs.modal', async (event) => {
                if (bypassUnsavedCsv || csvImportInProgress) return;

                event.preventDefault();
                const confirmed = await askDiscardInventario();
                if (!confirmed) return;

                bypassUnsavedCsv = true;
                bootstrap.Modal.getOrCreateInstance(modalCSVEl).hide();
                setTimeout(() => {
                    resetModalCSV();
                    bypassUnsavedCsv = false;
                }, 0);
            });
        }

        document.getElementById('medicamentoSearch').addEventListener('keydown', function(event) {
            const texto = this.value;
            const tecla = event.key;
            if (tecla.length === 1 && texto.endsWith(tecla + tecla)) {
                event.preventDefault();
            }
        });

        const medicamentoSearch = document.getElementById('medicamentoSearch');
        const btnBorrarBusquedaInventario = document.getElementById('btnBorrarBusquedaInventario');

        medicamentoSearch.addEventListener('keyup', function () {
            filtrarTabla(this.value.toLowerCase());
            if (btnBorrarBusquedaInventario) {
                btnBorrarBusquedaInventario.classList.toggle('d-none', this.value.length === 0);
            }
        });

        if (btnBorrarBusquedaInventario) {
            btnBorrarBusquedaInventario.addEventListener('click', function () {
                medicamentoSearch.value = '';
                filtrarTabla('');
                btnBorrarBusquedaInventario.classList.add('d-none');
                medicamentoSearch.focus();
            });
        }

        document.addEventListener('click', function (e) {
            const panel = document.getElementById('panel-filtros');
            const btn = document.getElementById('btn-filtros');
            if (panel.classList.contains('show') && !panel.contains(e.target) && !btn.contains(e.target)) {
                panel.classList.remove('show');
            }
        });

        datosFiltrados = inventario.slice();
        renderTabla();

        // ============================================================
        // Detectar nuevo ingreso de medicamento y mostrar alerta
        // ============================================================
        const storageKey = 'inventario_ids_cache';
        const cacheInventario = JSON.parse(localStorage.getItem(storageKey)) || [];
        const idsActuales = inventario.map(i => i.id_lote);
        const nuevosIngresos = idsActuales.filter(id => !cacheInventario.includes(id));

        if (nuevosIngresos.length > 0 && cacheInventario.length > 0) {
            // Si hay nuevos ingresos (y no es la primera carga), mostrar alert
            nuevosIngresos.forEach(id => {
                const item = inventario.find(i => i.id_lote == id);
                if (item) {
                    setTimeout(() => {
                        document.getElementById('nuevoIngresoNombre').textContent = item.nombre || '—';
                        document.getElementById('nuevoIngresoLote').textContent = item.codigo || '—';
                        document.getElementById('nuevoIngresoSaldo').textContent = Number(item.saldo).toLocaleString('es-HN');
                        document.getElementById('nuevoIngresoVenc').textContent = item.vencimiento 
                            ? item.vencimiento.split('-').reverse().join('/')
                            : '—';
                        const modalIngreso = new bootstrap.Modal(document.getElementById('modalNuevoIngreso'));
                        modalIngreso.show();
                    }, Math.random() * 500); // Pequeño delay para efecto cascada
                }
            });
        }

        // Guardar los IDs actuales para próxima comparación
        localStorage.setItem(storageKey, JSON.stringify(idsActuales));
    });
</script>
@endpush

