@extends('layouts.app')

@section('title', 'Preguntas - Hospital Escuela')
@section('header', 'Preguntas')

@push('styles')
<style>
    .card-preguntas {
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
    .page-item.active .page-link {
        background-color: #0d6efd;
        color: #ffffff;
    }
</style>
@endpush

@section('content')
<div class="p-4 p-md-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1f2937;">Catalogo de Preguntas</h4>
            <p class="text-muted mb-0">Administra las preguntas que quieres que responda el usuario para configurar</p>
        </div>

        <button type="button" class="btn rounded-pill fw-bold text-white px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaPregunta" style="background-color: #019504; border-color: #019504;">
            <i class="fas fa-plus me-2"></i>Nuevo
        </button>
    </div>

    <div class="d-flex justify-content-start align-items-center gap-3 mb-2 px-1">
        <div class="d-flex align-items-center">
            <span class="text-muted fw-bold small me-2">MOSTRAR:</span>
            <form method="GET" action="{{ route('preguntas.index') }}">
                <input type="hidden" name="page" value="1">
                <select name="per_page" class="select-round" onchange="this.form.submit()">
                    <option value="5" {{ (int) request('per_page', 10) === 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ (int) request('per_page', 10) === 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ (int) request('per_page', 10) === 15 ? 'selected' : '' }}>15</option>
                </select>
            </form>
        </div>
    </div>

    <div class="card card-preguntas">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pregunta</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($preguntas as $pregunta)
                        <tr>
                            <td class="text-muted fw-bold">#{{ $pregunta['id_pregunta'] ?? '' }}</td>
                            <td class="text-dark fw-semibold">{{ $pregunta['pregunta'] ?? '' }}</td>
                            <td>
                                @if(($pregunta['estado'] ?? '') === 'ACTIVO')
                                    <span class="badge rounded-pill badge-state-active">ACTIVO</span>
                                @else
                                    <span class="badge rounded-pill badge-state-inactive">INACTIVO</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button"
                                        class="btn btn-action btn-edit btn-editar-pregunta"
                                        title="Editar"
                                        aria-label="Editar"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditarPregunta"
                                        data-id="{{ $pregunta['id_pregunta'] ?? '' }}"
                                        data-pregunta="{{ $pregunta['pregunta'] ?? '' }}"
                                        data-estado="{{ $pregunta['estado'] ?? 'ACTIVO' }}">
                                    <i class="fas fa-pen"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No hay preguntas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(is_object($preguntas) && method_exists($preguntas, 'currentPage') && method_exists($preguntas, 'lastPage'))
        @php
            $currentPage = (int) $preguntas->currentPage();
            $totalPages = (int) $preguntas->lastPage();
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
        @endphp

        @if($totalPages > 1)
            <div class="d-flex justify-content-end align-items-center mt-4 mb-2 px-2">
                <span class="text-muted small fw-bold me-3">
                    Página {{ $currentPage }} de {{ $totalPages }}
                </span>
                <nav>
                    <ul class="pagination pagination-sm mb-0 shadow-sm" style="border-radius: 8px; overflow: hidden;">
                        <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                            <a class="page-link border-0 text-dark fw-bold px-3" href="{{ route('preguntas.index', array_merge(request()->query(), ['page' => $currentPage - 1])) }}">
                                <i class="fas fa-chevron-left me-1"></i> Anterior
                            </a>
                        </li>
                        @if($startPage > 1)
                            <li class="page-item">
                                <a class="page-link border-0 text-dark fw-bold px-3" href="{{ route('preguntas.index', array_merge(request()->query(), ['page' => 1])) }}">1</a>
                            </li>
                            @if($startPage > 2)
                                <li class="page-item disabled"><span class="page-link border-0 text-dark fw-bold px-3">...</span></li>
                            @endif
                        @endif

                        @for($page = $startPage; $page <= $endPage; $page++)
                            <li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
                                <a class="page-link border-0 text-dark fw-bold px-3" href="{{ route('preguntas.index', array_merge(request()->query(), ['page' => $page])) }}">{{ $page }}</a>
                            </li>
                        @endfor

                        @if($endPage < $totalPages)
                            @if($endPage < $totalPages - 1)
                                <li class="page-item disabled"><span class="page-link border-0 text-dark fw-bold px-3">...</span></li>
                            @endif
                            <li class="page-item">
                                <a class="page-link border-0 text-dark fw-bold px-3" href="{{ route('preguntas.index', array_merge(request()->query(), ['page' => $totalPages])) }}">{{ $totalPages }}</a>
                            </li>
                        @endif

                        <li class="page-item {{ $currentPage >= $totalPages ? 'disabled' : '' }}">
                            <a class="page-link border-0 text-dark fw-bold px-3" href="{{ route('preguntas.index', array_merge(request()->query(), ['page' => $currentPage + 1])) }}">
                                Siguiente <i class="fas fa-chevron-right ms-1"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        @endif
    @endif
</div>

<div class="modal fade" id="modalNuevaPregunta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="{{ route('preguntas.store') }}" id="formNuevaPregunta" class="modal-content bg-white border-0 shadow">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Nueva Pregunta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold">Pregunta</label>
                        <textarea name="pregunta" rows="3" class="form-control" maxlength="255" required>{{ old('pregunta') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Estado</label>
                        <select name="estado" class="form-select" required>
                            <option value="ACTIVO" {{ old('estado', 'ACTIVO') === 'ACTIVO' ? 'selected' : '' }}>ACTIVO</option>
                            <option value="INACTIVO" {{ old('estado') === 'INACTIVO' ? 'selected' : '' }}>INACTIVO</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill fw-bold" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-pill fw-bold">Guardar Pregunta</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEditarPregunta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" id="formEditarPregunta" class="modal-content bg-white border-0 shadow" data-unsaved-form="true">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Editar Pregunta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold">Pregunta</label>
                        <textarea name="pregunta" id="edit-pregunta-texto" rows="3" class="form-control" maxlength="255" required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Estado</label>
                        <select name="estado" id="edit-pregunta-estado" class="form-select" required>
                            <option value="ACTIVO">ACTIVO</option>
                            <option value="INACTIVO">INACTIVO</option>
                        </select>
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
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

        const askDiscard = async () => {
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
        };

        const normalize = (value) => String(value ?? '').trim();

        const validatePreguntaForm = (form) => {
            const texto = normalize(form.querySelector('[name="pregunta"]')?.value);
            if (!texto || texto.length < 8) {
                showWarn('Dato invalido', 'La pregunta es obligatoria y debe tener al menos 8 caracteres.');
                form.querySelector('[name="pregunta"]')?.focus();
                return false;
            }

            if (texto.length > 255) {
                showWarn('Dato invalido', 'La pregunta no debe exceder 255 caracteres.');
                form.querySelector('[name="pregunta"]')?.focus();
                return false;
            }

            return true;
        };

        const attachUnsavedGuard = (modalId, formId) => {
            const modalEl = document.getElementById(modalId);
            const formEl = document.getElementById(formId);
            if (!modalEl || !formEl) return;

            let initialState = '';
            let bypassGuard = false;
            let hasSubmitted = false;

            const serialize = () => JSON.stringify(Object.fromEntries(new FormData(formEl).entries()));

            modalEl.addEventListener('shown.bs.modal', () => {
                initialState = serialize();
                hasSubmitted = false;
            });

            formEl.addEventListener('submit', (event) => {
                if (!validatePreguntaForm(formEl)) {
                    event.preventDefault();
                    return;
                }
                hasSubmitted = true;
            });

            modalEl.addEventListener('hide.bs.modal', async (event) => {
                if (bypassGuard || hasSubmitted) return;
                if (serialize() === initialState) return;

                event.preventDefault();
                const confirmed = await askDiscard();
                if (!confirmed) return;

                bypassGuard = true;
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                setTimeout(() => { bypassGuard = false; }, 0);
            });
        };

        const shouldOpenCreate = @json((bool) session('open_pregunta_modal')) || new URLSearchParams(window.location.search).get('nuevo') === '1';
        if (shouldOpenCreate) {
            const modalElement = document.getElementById('modalNuevaPregunta');
            if (modalElement) {
                new bootstrap.Modal(modalElement).show();
            }
        }

        const editForm = document.getElementById('formEditarPregunta');
        const editText = document.getElementById('edit-pregunta-texto');
        const editEstado = document.getElementById('edit-pregunta-estado');

        attachUnsavedGuard('modalNuevaPregunta', 'formNuevaPregunta');
        attachUnsavedGuard('modalEditarPregunta', 'formEditarPregunta');

        document.querySelectorAll('.btn-editar-pregunta').forEach(function (button) {
            button.addEventListener('click', function () {
                const id = this.dataset.id;
                editForm.action = '{{ url('preguntas-admin') }}/' + id;
                editText.value = this.dataset.pregunta || '';
                editEstado.value = this.dataset.estado || 'ACTIVO';
            });
        });
    });
</script>
@endpush
