@extends('layouts.app')
@section('content')

@push('styles')
<style>
    .btn.btn-primary {
        background: #222c5e;
        border-color: #222c5e;
        color: #ffffff;
    }

    .btn.btn-primary:hover,
    .btn.btn-primary:focus {
        background: #222c5e;
        border-color: #222c5e;
        color: #ffffff;
    }

    .btn.btn-success {
        background: #059669;
        border-color: #059669;
        color: #ffffff;
    }

    .btn.btn-success:hover,
    .btn.btn-success:focus {
        background: #047857;
        border-color: #047857;
        color: #ffffff;
    }

    .btn.btn-outline-secondary,
    .btn.btn-outline-danger {
        background: #475569;
        border-color: #475569;
        color: #ffffff;
    }

    .btn.btn-outline-secondary:hover,
    .btn.btn-outline-secondary:focus,
    .btn.btn-outline-danger:hover,
    .btn.btn-outline-danger:focus {
        background: #334155;
        border-color: #334155;
        color: #ffffff;
    }

    .glass {
        border: 1px solid rgba(226,232,240,.9);
        background: #ffffff;
        border-radius: 18px;
        box-shadow: 0 18px 45px rgba(0,0,0,.08);
        color: #1f2937;
    }

    .form-control, .form-select {
        background: #ffffff !important;
        border: 1px solid rgba(226,232,240,.9) !important;
        color: #1f2937 !important;
        border-radius: 14px !important;
        font-weight: 700;
    }

    .form-control::placeholder {
        color: rgba(107,114,128,.7) !important;
    }

    .form-label {
        font-weight: 900;
        color: #374151;
    }

    .mayusculas {
        text-transform: uppercase;
    }

    .listaMedicamentos,
    .listaLotes {
        z-index: 1000;
        border-radius: 14px;
        overflow: hidden;
    }

    .listaMedicamentos .list-group-item,
    .listaLotes .list-group-item {
        background: #ffffff;
        color: #1f2937;
        border: 1px solid rgba(226,232,240,.9);
    }

    .listaMedicamentos .list-group-item:hover,
    .listaLotes .list-group-item:hover {
        background: #eef2ff;
        color: #1f2937;
    }

    .is-invalid {
        border: 1px solid rgba(248,113,113,.95) !important;
        box-shadow: 0 0 0 0.18rem rgba(248,113,113,.18) !important;
    }

    .invalid-feedback {
        color: #b91c1c !important;
        font-weight: 700;
        font-size: .84rem;
        margin-top: 6px;
        display: block;
    }

    .form-control.is-invalid,
    .form-select.is-invalid {
        background-color: rgba(248,113,113,.08) !important;
    }

    .alert-soft-danger {
        border: 1px solid rgba(248,113,113,.25);
        background: rgba(254, 226, 226, .4);
        color: #b91c1c;
        border-radius: 16px;
        padding: 12px 16px;
        font-weight: 700;
    }

    .ram-steps {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .ram-step {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #64748b;
        border-radius: 999px;
        padding: 8px 12px;
        font-weight: 800;
        font-size: .84rem;
        line-height: 1;
    }

    .ram-step-dot {
        width: 24px;
        height: 24px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid currentColor;
        font-size: .72rem;
        font-weight: 900;
    }

    .ram-step-active {
        background: #dbeafe;
        border-color: #60a5fa;
        color: #1d4ed8;
        box-shadow: 0 0 0 0.18rem rgba(59,130,246,.18);
    }

    .ram-step-complete {
        background: #dcfce7;
        border-color: #86efac;
        color: #166534;
    }

    .ram-step-sep {
        color: #94a3b8;
        font-weight: 900;
    }
</style>
@endpush

<div class="container py-2">
    @php
        $detalleInicial = $detalleInicial ?? [];
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0 fw-black" style="font-weight:900;">
                <i class="fa-solid fa-pills me-2"></i>
                Medicamentos (Detalle) — Reacción #{{ $id_reaccion }}
            </h3>
            <div class="text-muted fw-bold mt-1">Agrega uno o varios medicamentos sospechosos a la reacción adversa.</div>
        </div>

        <a href="{{ route('reacciones_adversas.index') }}"
            class="btn btn-outline-secondary fw-bold"
            style="border-radius:16px;"
            data-cancel-unsaved="true">
            <i class="fa-solid fa-ban me-2"></i> Cancelar
        </a>
    </div>

    <div class="ram-steps mb-3" aria-label="Flujo de registro de reacción adversa">
        <div class="ram-step ram-step-complete">
            <span class="ram-step-dot"><i class="fa-solid fa-check"></i></span>
            Datos Generales
        </div>
        <span class="ram-step-sep"><i class="fa-solid fa-chevron-right"></i></span>
        <div class="ram-step ram-step-active">
            <span class="ram-step-dot">2</span>
            Medicamentos
        </div>
        <span class="ram-step-sep"><i class="fa-solid fa-chevron-right"></i></span>
        <div class="ram-step">
            <span class="ram-step-dot">3</span>
            Consecuencias
        </div>
    </div>

    @if($errors->any())
        <div class="alert-soft-danger mb-3">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            Hay campos con errores. Revísalos antes de continuar.
        </div>
    @endif

    <div class="glass p-4">
        <form method="POST"
            action="{{ route('reacciones_adversas.detalles.store', $id_reaccion) }}"
            id="formDetallesRAM"
            data-unsaved-form="true">
            @csrf

            <div class="glass p-4 mb-4">
                <h5 class="fw-black mb-4" style="font-weight:900;">
                    <i class="fa-solid fa-pills me-2"></i>
                    Medicamentos Relacionados
                </h5>

                <div id="rows">
                    <div class="row g-3 align-items-end detalle-row mb-3" data-index="0">

                        <div class="col-md-4 position-relative">
                            <label class="form-label fw-bold">Medicamento <span class="text-danger">*</span></label>
                            <input type="text"
                                name="detalles[0][medicamento]"
                                class="form-control buscar-medicamento mayusculas texto-general no-repetidos"
                                placeholder="Ingresar medicamento"
                                autocomplete="off"
                                value="{{ old('detalles.0.medicamento', $detalleInicial['medicamento'] ?? ($detalleInicial['nombre_comercial'] ?? '')) }}">
                            <input type="hidden"
                                name="detalles[0][id_medicamento]"
                                class="id_medicamento"
                                value="{{ old('detalles.0.id_medicamento', $detalleInicial['id_medicamento'] ?? '') }}">
                            <div class="listaMedicamentos list-group position-absolute w-100 mt-1"></div>
                            <div class="invalid-feedback d-none error-medicamento">Debe ingresar el medicamento.</div>
                        </div>

                        <div class="col-md-3 position-relative">
                            <label class="form-label fw-bold">Lote <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control buscar-lote mayusculas texto-general no-repetidos"
                                placeholder="Buscar lote..."
                                autocomplete="off"
                                value="{{ old('detalles.0.numero_lote', $detalleInicial['numero_lote'] ?? '') }}">
                            <input type="hidden"
                                name="detalles[0][id_lote]"
                                class="id_lote"
                                value="{{ old('detalles.0.id_lote', $detalleInicial['id_lote'] ?? '') }}">
                            <div class="listaLotes list-group position-absolute w-100 mt-1"></div>
                            <div class="invalid-feedback d-none error-lote">Debe seleccionar un lote.</div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Dosis <span class="text-danger">*</span></label>
                            <input type="text"
                                name="detalles[0][dosis_posologia]"
                                class="form-control mayusculas texto-general no-repetidos dosis_posologia"
                                placeholder="Ej: 500MG CADA 8H"
                                value="{{ old('detalles.0.dosis_posologia', $detalleInicial['dosis_instrucciones'] ?? '') }}">
                            <div class="invalid-feedback d-none error-dosis">La dosis es obligatoria.</div>
                        </div>

                        <div class="col-md-2">
                            <button type="button"
                                    class="btn btn-danger w-100 fw-bold"
                                    style="border-radius:14px;"
                                    onclick="removeRow(this)">
                                Eliminar
                            </button>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Vía <span class="text-danger">*</span></label>
                            <input type="text"
                                name="detalles[0][via_administracion]"
                                class="form-control mayusculas solo-letras no-repetidos via_administracion"
                                placeholder="ORAL / IV / IM"
                                value="{{ old('detalles.0.via_administracion', $detalleInicial['via_administracion'] ?? '') }}">
                            <div class="invalid-feedback d-none error-via">La vía de administración es obligatoria.</div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Inicio uso <span class="text-danger">*</span></label>
                            <input type="date"
                                name="detalles[0][fecha_inicio_uso]"
                                class="form-control fecha_inicio_uso"
                                value="{{ old('detalles.0.fecha_inicio_uso', !empty($detalleInicial['fecha_inicio_uso']) ? \Carbon\Carbon::parse($detalleInicial['fecha_inicio_uso'])->toDateString() : (!empty($reaccion['fecha_inicio_reaccion']) ? \Carbon\Carbon::parse($reaccion['fecha_inicio_reaccion'])->toDateString() : '')) }}">
                            <div class="invalid-feedback d-none error-fecha-inicio">La fecha de inicio es obligatoria.</div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Fin uso (opcional)</label>
                            <input type="date"
                                name="detalles[0][fecha_fin_uso]"
                                class="form-control fecha_fin_uso"
                                value="{{ old('detalles.0.fecha_fin_uso', !empty($detalleInicial['fecha_fin_uso']) ? \Carbon\Carbon::parse($detalleInicial['fecha_fin_uso'])->toDateString() : (!empty($reaccion['fecha_fin_reaccion']) ? \Carbon\Carbon::parse($reaccion['fecha_fin_reaccion'])->toDateString() : (!empty($reaccion['fecha_inicio_reaccion']) ? \Carbon\Carbon::parse($reaccion['fecha_inicio_reaccion'])->toDateString() : ''))) }}">
                            <div class="invalid-feedback d-none error-fecha-fin">La fecha fin no puede ser menor que la fecha inicio.</div>
                        </div>

                    </div>
                </div>

                <button type="button"
                        class="btn btn-success mt-3 fw-bold"
                        style="border-radius:14px;"
                        onclick="addRow()">
                    <i class="fa-solid fa-plus me-2"></i>
                    Agregar
                </button>

                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('reacciones_adversas.edit', $id_reaccion) }}"
                       class="btn btn-outline-secondary fw-bold"
                       style="border-radius:14px;"
                       data-cancel-unsaved="true">
                        <i class="fa-solid fa-arrow-left me-2"></i> Regresar
                    </a>

                    <button class="btn btn-primary fw-bold" style="border-radius:14px;">
                        <i class="fa-solid fa-arrow-right me-2"></i> Siguiente
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let idx = 1;
const ajaxMedicamentosUrl = @json(route('ajax.medicamentos'));
const ajaxLotesUrl = @json(route('ajax.lotes'));
const defaultFechaInicioUso = @json(!empty($reaccion['fecha_inicio_reaccion']) ? \Carbon\Carbon::parse($reaccion['fecha_inicio_reaccion'])->toDateString() : '');
const defaultFechaFinUso = @json(!empty($reaccion['fecha_fin_reaccion']) ? \Carbon\Carbon::parse($reaccion['fecha_fin_reaccion'])->toDateString() : (!empty($reaccion['fecha_inicio_reaccion']) ? \Carbon\Carbon::parse($reaccion['fecha_inicio_reaccion'])->toDateString() : ''));

function quitarRepetidosExcesivos(valor) {
    return valor
        .replace(/([A-ZÁÉÍÓÚÑ0-9.,\-\/])\1{2,}/g, '$1$1')
        .replace(/\b(\w+)(\s+\1\b){2,}/gi, '$1 $1');
}

function limpiarTextoGeneral(valor) {
    valor = valor.toUpperCase();

    valor = valor
        .replace(/[^A-ZÁÉÍÓÚÑ0-9\s.,\-\/]/g, '')
        .replace(/\s{2,}/g, ' ')
        .trimStart();

    valor = quitarRepetidosExcesivos(valor);

    return valor;
}

function limpiarSoloLetras(valor) {
    valor = valor.toUpperCase();

    valor = valor
        .replace(/[^A-ZÁÉÍÓÚÑ\s\/]/g, '')
        .replace(/\s{2,}/g, ' ')
        .trimStart();

    valor = valor
        .replace(/([A-ZÁÉÍÓÚÑ])\1{2,}/g, '$1$1')
        .replace(/\b(\w+)(\s+\1\b){2,}/gi, '$1 $1');

    return valor;
}

function setInvalidField(element, errorElement = null, message = null) {
    if (!element) return;
    element.classList.add('is-invalid');

    if (errorElement) {
        if (message) errorElement.textContent = message;
        errorElement.classList.remove('d-none');
    }
}

function clearInvalidField(element, errorElement = null) {
    if (!element) return;
    element.classList.remove('is-invalid');

    if (errorElement) {
        errorElement.classList.add('d-none');
    }
}

function aplicarValidacionEnFila(row) {
    row.querySelectorAll('.texto-general').forEach(input => {
        input.addEventListener('input', function () {
            this.value = limpiarTextoGeneral(this.value);
        });
    });

    row.querySelectorAll('.solo-letras').forEach(input => {
        input.addEventListener('input', function () {
            this.value = limpiarSoloLetras(this.value);
        });
    });
}

function activarAutocompleteEnFila(row) {
    const inputMedicamento = row.querySelector('.buscar-medicamento');
    const hiddenMedicamento = row.querySelector('.id_medicamento');
    const listaMedicamentos = row.querySelector('.listaMedicamentos');
    const errorMedicamento = row.querySelector('.error-medicamento');

    const inputLote = row.querySelector('.buscar-lote');
    const hiddenLote = row.querySelector('.id_lote');
    const listaLotes = row.querySelector('.listaLotes');
    const errorLote = row.querySelector('.error-lote');

    async function cargarLotes(idMedicamento, q = '') {
        if (!idMedicamento) {
            listaLotes.innerHTML = '';
            return;
        }

        try {
            const res = await fetch(`${ajaxLotesUrl}?q=${encodeURIComponent(q)}&id_medicamento=${encodeURIComponent(idMedicamento)}`);
            const data = await res.json();

            listaLotes.innerHTML = '';

            if (!Array.isArray(data) || data.length === 0) {
                listaLotes.innerHTML = `<div class="list-group-item">No se encontraron lotes para este medicamento</div>`;
                return;
            }

            data.forEach(item => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action';

                const fechaExpiracion = item.fecha_expiracion
                    ? new Date(item.fecha_expiracion).toLocaleDateString('es-HN')
                    : 'SIN FECHA';

                btn.innerHTML = `
                    <strong>${item.numero_lote}</strong>
                    <br>
                    <small>Vence: ${fechaExpiracion}</small>
                `;

                btn.onclick = () => {
                    inputLote.value = item.numero_lote;
                    hiddenLote.value = item.id_lote;
                    listaLotes.innerHTML = '';
                    clearInvalidField(inputLote, errorLote);
                };

                listaLotes.appendChild(btn);
            });
        } catch (error) {
            console.error(error);
            listaLotes.innerHTML = '';
        }
    }

    inputMedicamento.addEventListener('input', async function() {
        this.value = limpiarTextoGeneral(this.value);
        const q = this.value.trim();

        hiddenMedicamento.value = '';
        inputLote.value = '';
        hiddenLote.value = '';
        clearInvalidField(inputMedicamento, errorMedicamento);
        clearInvalidField(inputLote, errorLote);

        if (q.length < 2) {
            listaMedicamentos.innerHTML = '';
            return;
        }

        try {
            const res = await fetch(`${ajaxMedicamentosUrl}?q=${encodeURIComponent(q)}`);
            const data = await res.json();

            listaMedicamentos.innerHTML = '';

            if (!Array.isArray(data) || data.length === 0) {
                listaMedicamentos.innerHTML = `<div class="list-group-item">No se encontraron resultados</div>`;
                return;
            }

            data.forEach(item => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action';

                btn.innerHTML = `
                    <strong>${item.nombre_comercial}</strong>
                    <br>
                    <small>${item.principio_activo ?? 'SIN PRINCIPIO ACTIVO'}${item.laboratorio_fabricante ? ` | ${item.laboratorio_fabricante}` : ''}</small>
                `;

                btn.onclick = async () => {
                    inputMedicamento.value = item.nombre_comercial;
                    hiddenMedicamento.value = item.id_medicamento;
                    inputLote.value = '';
                    hiddenLote.value = '';
                    listaMedicamentos.innerHTML = '';
                    clearInvalidField(inputMedicamento, errorMedicamento);

                    await cargarLotes(item.id_medicamento);
                };

                listaMedicamentos.appendChild(btn);
            });
        } catch (error) {
            console.error(error);
            listaMedicamentos.innerHTML = '';
        }
    });

    inputLote.addEventListener('input', async function() {
        this.value = limpiarTextoGeneral(this.value);
        const q = this.value.trim();
        const idMedicamento = hiddenMedicamento.value;

        hiddenLote.value = '';
        clearInvalidField(inputLote, errorLote);

        if (!idMedicamento) {
            listaLotes.innerHTML = '';
            return;
        }

        if (q.length < 1) {
            listaLotes.innerHTML = '';
            return;
        }

        await cargarLotes(idMedicamento, q);
    });

    inputLote.addEventListener('focus', async function() {
        const idMedicamento = hiddenMedicamento.value;

        if (!idMedicamento || listaLotes.innerHTML.trim() !== '') {
            return;
        }

        await cargarLotes(idMedicamento, this.value.trim());
    });

    document.addEventListener('click', function(e) {
        if (!inputMedicamento.contains(e.target) && !listaMedicamentos.contains(e.target)) {
            listaMedicamentos.innerHTML = '';
        }
        if (!inputLote.contains(e.target) && !listaLotes.contains(e.target)) {
            listaLotes.innerHTML = '';
        }
    });
}

function actualizarIndices() {
    document.querySelectorAll('.detalle-row').forEach((row, index) => {
        row.setAttribute('data-index', index);

        const idMedicamento = row.querySelector('.id_medicamento');
        const idLote = row.querySelector('.id_lote');
        const dosis = row.querySelector('.dosis_posologia');
        const via = row.querySelector('.via_administracion');
        const fechaInicio = row.querySelector('.fecha_inicio_uso');
        const fechaFin = row.querySelector('.fecha_fin_uso');

        if (idMedicamento) idMedicamento.name = `detalles[${index}][id_medicamento]`;
        const medicamento = row.querySelector('.buscar-medicamento');
        if (idLote) idLote.name = `detalles[${index}][id_lote]`;
        if (medicamento) medicamento.name = `detalles[${index}][medicamento]`;
        if (dosis) dosis.name = `detalles[${index}][dosis_posologia]`;
        if (via) via.name = `detalles[${index}][via_administracion]`;
        if (fechaInicio) fechaInicio.name = `detalles[${index}][fecha_inicio_uso]`;
        if (fechaFin) fechaFin.name = `detalles[${index}][fecha_fin_uso]`;
    });
}

function limpiarFila(row) {
    row.querySelectorAll('input').forEach(input => {
        input.value = '';
        input.classList.remove('is-invalid');
    });

    row.querySelectorAll('.invalid-feedback').forEach(error => {
        error.classList.add('d-none');
    });

    row.querySelectorAll('.listaMedicamentos, .listaLotes').forEach(lista => {
        lista.innerHTML = '';
    });

    const fechaInicio = row.querySelector('.fecha_inicio_uso');
    const fechaFin = row.querySelector('.fecha_fin_uso');
    if (fechaInicio && defaultFechaInicioUso) fechaInicio.value = defaultFechaInicioUso;
    if (fechaFin && defaultFechaFinUso) fechaFin.value = defaultFechaFinUso;
}

function getDetallesPayload() {
    return Array.from(document.querySelectorAll('.detalle-row')).map((row) => ({
        buscar_medicamento: row.querySelector('.buscar-medicamento')?.value || '',
        medicamento: row.querySelector('.buscar-medicamento')?.value || '',
        id_medicamento: row.querySelector('.id_medicamento')?.value || '',
        buscar_lote: row.querySelector('.buscar-lote')?.value || '',
        id_lote: row.querySelector('.id_lote')?.value || '',
        dosis_posologia: row.querySelector('.dosis_posologia')?.value || '',
        via_administracion: row.querySelector('.via_administracion')?.value || '',
        fecha_inicio_uso: row.querySelector('.fecha_inicio_uso')?.value || '',
        fecha_fin_uso: row.querySelector('.fecha_fin_uso')?.value || '',
    }));
}

function saveDetallesDraft() {
    const payload = {
        id_reaccion: @json($id_reaccion),
        rows: getDetallesPayload(),
    };
    localStorage.setItem(`ram_detalles_draft_${payload.id_reaccion}`, JSON.stringify(payload));
}

function createEmptyRow() {
    const rows = document.getElementById('rows');
    const template = rows.firstElementChild.cloneNode(true);
    limpiarFila(template);
    rows.appendChild(template);
    actualizarIndices();
    aplicarValidacionEnFila(template);
    activarAutocompleteEnFila(template);
    idx++;
    return template;
}

function restoreDetallesDraft() {
    const key = `ram_detalles_draft_${@json($id_reaccion)}`;
    const raw = localStorage.getItem(key);
    if (!raw) return;

    try {
        const data = JSON.parse(raw);
        if (!Array.isArray(data.rows) || data.rows.length === 0) return;

        const rows = document.querySelectorAll('.detalle-row');
        rows.forEach((row, i) => {
            if (i > 0) row.remove();
        });
        actualizarIndices();

        data.rows.forEach((item, index) => {
            const row = (index === 0)
                ? document.querySelector('.detalle-row')
                : createEmptyRow();

            if (!row) return;
            row.querySelector('.buscar-medicamento').value = item.medicamento || item.buscar_medicamento || '';
            row.querySelector('.id_medicamento').value = item.id_medicamento || '';
            row.querySelector('.buscar-lote').value = item.buscar_lote || '';
            row.querySelector('.id_lote').value = item.id_lote || '';
            row.querySelector('.dosis_posologia').value = item.dosis_posologia || '';
            row.querySelector('.via_administracion').value = item.via_administracion || '';
            row.querySelector('.fecha_inicio_uso').value = item.fecha_inicio_uso || defaultFechaInicioUso || '';
            row.querySelector('.fecha_fin_uso').value = item.fecha_fin_uso || defaultFechaFinUso || '';
        });
    } catch (e) {
    }
}

function addRow() {
    const rows = document.getElementById('rows');
    const template = rows.firstElementChild.cloneNode(true);

    limpiarFila(template);
    rows.appendChild(template);
    actualizarIndices();
    aplicarValidacionEnFila(template);
    activarAutocompleteEnFila(template);
    idx++;
}

function removeRow(btn) {
    const row = btn.closest('.detalle-row');

    if (document.querySelectorAll('.detalle-row').length === 1) {
        return;
    }

    row.remove();
    actualizarIndices();
}

function validarFila(row) {
    let valido = true;

    const inputMedicamento = row.querySelector('.buscar-medicamento');
    const hiddenMedicamento = row.querySelector('.id_medicamento');
    const errorMedicamento = row.querySelector('.error-medicamento');

    const inputLote = row.querySelector('.buscar-lote');
    const hiddenLote = row.querySelector('.id_lote');
    const errorLote = row.querySelector('.error-lote');

    const dosis = row.querySelector('.dosis_posologia');
    const errorDosis = row.querySelector('.error-dosis');

    const via = row.querySelector('.via_administracion');
    const errorVia = row.querySelector('.error-via');

    const fechaInicio = row.querySelector('.fecha_inicio_uso');
    const errorFechaInicio = row.querySelector('.error-fecha-inicio');

    const fechaFin = row.querySelector('.fecha_fin_uso');
    const errorFechaFin = row.querySelector('.error-fecha-fin');

    if (!inputMedicamento.value.trim()) {
        setInvalidField(inputMedicamento, errorMedicamento, 'Debe ingresar el medicamento.');
        valido = false;
    } else {
        clearInvalidField(inputMedicamento, errorMedicamento);
    }

    if (!hiddenLote.value.trim()) {
        setInvalidField(inputLote, errorLote, 'Debe seleccionar un lote.');
        valido = false;
    } else {
        clearInvalidField(inputLote, errorLote);
    }

    if (!dosis.value.trim()) {
        setInvalidField(dosis, errorDosis, 'La dosis es obligatoria.');
        valido = false;
    } else {
        clearInvalidField(dosis, errorDosis);
    }

    if (!via.value.trim()) {
        setInvalidField(via, errorVia, 'La vía de administración es obligatoria.');
        valido = false;
    } else {
        clearInvalidField(via, errorVia);
    }

    if (!fechaInicio.value.trim()) {
        setInvalidField(fechaInicio, errorFechaInicio, 'La fecha de inicio es obligatoria.');
        valido = false;
    } else {
        clearInvalidField(fechaInicio, errorFechaInicio);
    }

    if (fechaInicio.value && fechaFin.value && fechaFin.value < fechaInicio.value) {
        setInvalidField(fechaFin, errorFechaFin, 'La fecha fin no puede ser menor que la fecha inicio.');
        valido = false;
    } else {
        clearInvalidField(fechaFin, errorFechaFin);
    }

    const repetido = /(.)\1{3,}/;
    const palabraRepetida = /\b(\w+)(\s+\1\b){2,}/i;

    [dosis, via].forEach(campo => {
        if (!campo.value) return;
        if (repetido.test(campo.value) || palabraRepetida.test(campo.value)) {
            setInvalidField(campo);
            valido = false;
        }
    });

    return valido;
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.detalle-row').forEach(row => {
        aplicarValidacionEnFila(row);
        activarAutocompleteEnFila(row);

        const campos = [
            row.querySelector('.buscar-medicamento'),
            row.querySelector('.buscar-lote'),
            row.querySelector('.dosis_posologia'),
            row.querySelector('.via_administracion'),
            row.querySelector('.fecha_inicio_uso'),
            row.querySelector('.fecha_fin_uso')
        ];

        campos.forEach(campo => {
            if (!campo) return;
            campo.addEventListener('input', () => {
                campo.classList.remove('is-invalid');
            });
            campo.addEventListener('change', () => {
                campo.classList.remove('is-invalid');
            });
        });
    });

    const detallesDraftKey = `ram_detalles_draft_${@json($id_reaccion)}`;
    localStorage.removeItem(detallesDraftKey);
    const prevDiscardHandlerDetalles = window.onDiscardUnsavedChanges;
    window.onDiscardUnsavedChanges = function() {
        if (typeof prevDiscardHandlerDetalles === 'function') {
            prevDiscardHandlerDetalles();
        }
        localStorage.removeItem(detallesDraftKey);
    };

    const formDetalles = document.getElementById('formDetallesRAM');

    document.getElementById('formDetallesRAM').addEventListener('submit', function(e) {
        let valido = true;

        document.querySelectorAll('.detalle-row').forEach(row => {
            const filaValida = validarFila(row);
            if (!filaValida) valido = false;
        });

        if (!valido) {
            e.preventDefault();
            return;
        }

        localStorage.removeItem(detallesDraftKey);
    });
});
</script>
@endpush

@endsection
