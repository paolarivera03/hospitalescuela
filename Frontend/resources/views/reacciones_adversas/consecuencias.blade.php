@extends('layouts.app')
@section('content')

@push('styles')
<style>
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

    .btn.btn-outline-light,
    .btn.btn-outline-secondary {
        background: #475569;
        border-color: #475569;
        color: #ffffff;
    }

    .btn.btn-outline-light:hover,
    .btn.btn-outline-light:focus,
    .btn.btn-outline-secondary:hover,
    .btn.btn-outline-secondary:focus {
        background: #334155;
        border-color: #334155;
        color: #ffffff;
    }

    .form-control,
    .form-select {
        background: #ffffff !important;
        border: 1px solid rgba(226,232,240,.9) !important;
        color: #1f2937 !important;
        border-radius: 12px !important;
        font-weight: 700;
    }

    .form-control::placeholder {
        color: rgba(107,114,128,.7) !important;
    }

    .form-control:focus,
    .form-select:focus {
        background: #ffffff !important;
        border-color: #3b82f6 !important;
        color: #1f2937 !important;
        box-shadow: none !important;
    }

    .form-select option {
        color: #000;
    }

    .mayusculas {
        text-transform: uppercase;
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0 fw-black" style="font-weight:900;">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                Consecuencias — Reacción #{{ $id_reaccion }}
            </h3>
            <div class="text-muted fw-bold mt-1">Agrega una o varias consecuencias y su gravedad.</div>
        </div>

        <a href="{{ route('reacciones_adversas.index') }}"
            class="btn btn-outline-light fw-bold"
            style="border-radius:16px;"
            data-cancel-unsaved="true">
            <i class="fa-solid fa-ban me-2"></i> Cancelar
        </a>
    </div>

    <div class="ram-steps mb-3" aria-label="Flujo de registro de reacción adversa">
        <div class="ram-step ram-step-complete">
            <span class="ram-step-dot"><i class="fa-solid fa-check"></i></span>
            Paciente y Reacción
        </div>
        <span class="ram-step-sep"><i class="fa-solid fa-chevron-right"></i></span>
        <div class="ram-step ram-step-active">
            <span class="ram-step-dot">2</span>
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
            action="{{ route('reacciones_adversas.consecuencias.store', $id_reaccion) }}"
            id="formConsecuenciasRAM"
            data-unsaved-form="true">
            @csrf

            <div id="rowsC">
                <div class="row g-3 align-items-end conse-row mb-3" data-index="0">

                    <div class="col-md-7">
                        <label class="form-label fw-bold">Descripción de consecuencia <span class="text-danger">*</span></label>
                        <input type="text"
                            name="consecuencias[0][descripcion_consecuencia]"
                            class="form-control mayusculas texto-general no-repetidos descripcion_consecuencia @error('consecuencias.0.descripcion_consecuencia') is-invalid @enderror"
                            placeholder="Ej: HOSPITALIZACIÓN">

                        @error('consecuencias.0.descripcion_consecuencia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="invalid-feedback d-none error-descripcion">La descripción de la consecuencia es obligatoria.</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Gravedad <span class="text-danger">*</span></label>
                        <select name="consecuencias[0][gravedad]" class="form-select gravedad @error('consecuencias.0.gravedad') is-invalid @enderror">
                            <option value="">Seleccione</option>
                            <option value="LEVE">LEVE</option>
                            <option value="MODERADA">MODERADA</option>
                            <option value="GRAVE">GRAVE</option>
                        </select>

                        @error('consecuencias.0.gravedad')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="invalid-feedback d-none error-gravedad">Debe seleccionar la gravedad.</div>
                    </div>

                    <div class="col-md-2">
                        <button type="button"
                                class="btn btn-danger fw-bold w-100"
                                style="border-radius:14px;"
                                onclick="removeRowC(this)">
                            Eliminar
                        </button>
                    </div>

                </div>
            </div>

            <div class="d-flex gap-2 mt-2">
                <button type="button"
                        class="btn btn-outline-light fw-bold"
                        style="border-radius:14px;"
                        onclick="addRowC()">
                    <i class="fa-solid fa-plus me-2"></i> Agregar
                </button>

                     <a href="{{ route('reacciones_adversas.edit', $id_reaccion) }}"
                   class="btn btn-outline-secondary fw-bold"
                   style="border-radius:14px;"
                   data-cancel-unsaved="true">
                    <i class="fa-solid fa-arrow-left me-2"></i> Regresar
                </a>

                <button class="btn btn-success fw-bold" style="border-radius:14px;">
                    <i class="fa-solid fa-arrow-right me-2"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let idxC = 1;

function quitarRepetidosExcesivos(valor) {
    return valor
        .replace(/([A-ZÁÉÍÓÚÑ0-9.,\-\/])\1{2,}/g, '$1$1')
        .replace(/\b(\w+)(\s+\1\b){2,}/gi, '$1 $1');
}

function limpiarTextoGeneral(valor) {
    valor = valor.toUpperCase();

    valor = valor
        .replace(/[^A-ZÁÉÍÓÚÑ0-9\s\.,\-\/]/g, '')
        .replace(/\s{2,}/g, ' ')
        .trimStart();

    valor = quitarRepetidosExcesivos(valor);

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

function aplicarValidacionEnFilaConsecuencia(row) {
    row.querySelectorAll('.texto-general').forEach(input => {
        input.addEventListener('input', function () {
            this.value = limpiarTextoGeneral(this.value);
        });
    });
}

function actualizarIndicesC() {
    document.querySelectorAll('.conse-row').forEach((row, index) => {
        row.setAttribute('data-index', index);

        const descripcion = row.querySelector('.descripcion_consecuencia');
        const gravedad = row.querySelector('.gravedad');

        if (descripcion) {
            descripcion.name = `consecuencias[${index}][descripcion_consecuencia]`;
        }

        if (gravedad) {
            gravedad.name = `consecuencias[${index}][gravedad]`;
        }
    });
}

function limpiarFilaC(row) {
    row.querySelectorAll('input').forEach(input => {
        input.value = '';
        input.classList.remove('is-invalid');
    });

    row.querySelectorAll('select').forEach(select => {
        select.value = '';
        select.classList.remove('is-invalid');
    });

    row.querySelectorAll('.invalid-feedback').forEach(error => {
        if (error.classList.contains('error-descripcion') || error.classList.contains('error-gravedad')) {
            error.classList.add('d-none');
        }
    });
}

function addRowC() {
    const rows = document.getElementById('rowsC');
    const template = rows.firstElementChild.cloneNode(true);

    limpiarFilaC(template);
    rows.appendChild(template);
    actualizarIndicesC();
    aplicarValidacionEnFilaConsecuencia(template);
    idxC++;
}

function removeRowC(btn) {
    const row = btn.closest('.conse-row');
    const all = document.querySelectorAll('.conse-row');

    if (all.length <= 1) return;

    row.remove();
    actualizarIndicesC();
}

function validarFilaC(row) {
    let valido = true;

    const descripcion = row.querySelector('.descripcion_consecuencia');
    const gravedad = row.querySelector('.gravedad');

    const errorDescripcion = row.querySelector('.error-descripcion');
    const errorGravedad = row.querySelector('.error-gravedad');

    if (!descripcion.value.trim()) {
        setInvalidField(descripcion, errorDescripcion, 'La descripción de la consecuencia es obligatoria.');
        valido = false;
    } else {
        clearInvalidField(descripcion, errorDescripcion);
    }

    if (!gravedad.value.trim()) {
        setInvalidField(gravedad, errorGravedad, 'Debe seleccionar la gravedad.');
        valido = false;
    } else {
        clearInvalidField(gravedad, errorGravedad);
    }

    const repetido = /(.)\1{3,}/;
    const palabraRepetida = /\b(\w+)(\s+\1\b){2,}/i;

    if (descripcion.value && (repetido.test(descripcion.value) || palabraRepetida.test(descripcion.value))) {
        setInvalidField(descripcion, errorDescripcion, 'La descripción contiene caracteres o palabras repetidas en exceso.');
        valido = false;
    }

    return valido;
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.conse-row').forEach(row => {
        aplicarValidacionEnFilaConsecuencia(row);

        const descripcion = row.querySelector('.descripcion_consecuencia');
        const gravedad = row.querySelector('.gravedad');
        const errorDescripcion = row.querySelector('.error-descripcion');
        const errorGravedad = row.querySelector('.error-gravedad');

        if (descripcion) {
            descripcion.addEventListener('input', function () {
                this.value = limpiarTextoGeneral(this.value);
                clearInvalidField(this, errorDescripcion);
            });
        }

        if (gravedad) {
            gravedad.addEventListener('change', function () {
                clearInvalidField(this, errorGravedad);
            });
        }
    });

    document.getElementById('formConsecuenciasRAM').addEventListener('submit', function(e) {
        let valido = true;

        document.querySelectorAll('.conse-row').forEach(row => {
            const filaValida = validarFilaC(row);
            if (!filaValida) valido = false;
        });

        if (!valido) {
            e.preventDefault();
        }
    });
});
</script>
@endpush

@endsection
