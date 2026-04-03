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

    .btn.btn-outline-secondary {
        background: #475569;
        border-color: #475569;
        color: #ffffff;
    }

    .btn.btn-outline-secondary:hover,
    .btn.btn-outline-secondary:focus {
        background: #334155;
        border-color: #334155;
        color: #ffffff;
    }

    .form-card {
        border: 1px solid rgba(226,232,240,.9);
        background: #ffffff;
        border-radius: 18px;
        box-shadow: 0 18px 45px rgba(0,0,0,.08);
        color: #1f2937;
    }

    .form-control,
    .form-select {
        background: #ffffff !important;
        border: 1px solid rgba(226,232,240,.9) !important;
        color: #1f2937 !important;
        border-radius: 14px !important;
        font-weight: 700;
    }

    .form-control::placeholder {
        color: rgba(107,114,128,.7) !important;
    }

    .form-control[readonly] {
        background: #f8fafc !important;
        color: #6b7280 !important; 
        cursor: default;
    }

    .form-label {
        font-weight: 900;
        color: #374151;
    }

    .help {
        color: #6b7280;
        font-weight: 700;
        font-size: .86rem;
    }

    .upload-box {
        border: 2px dashed rgba(148,163,184,.6);
        background: #f8fafc;
        cursor: pointer;
        transition: .25s ease;
        border-radius: 18px;
    }

    .upload-box:hover {
        background: #eef2ff;
        border-color: rgba(99,102,241,.45);
        transform: translateY(-1px);
    }

    .upload-icon {
        width: 54px;
        height: 54px;
        border-radius: 18px;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, rgba(99,102,241,.2), rgba(94,234,212,.2));
        color: #1f2937;
        box-shadow: 0 14px 35px rgba(0,0,0,.1);
        margin: 0 auto 12px;
    }


    .form-select option {
        color: #000;
    }

    .mayusculas {
        text-transform: uppercase;
    }

    #listaMedicamentos .list-group-item,
    #listaLotes .list-group-item,
    #listaMedicos .list-group-item {
        background: #ffffff;
        color: #1f2937;
        border: 1px solid rgba(226,232,240,.9);
    }

    #listaMedicamentos .list-group-item:hover,
    #listaLotes .list-group-item:hover,
    #listaMedicos .list-group-item:hover {
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
        $pendiente = $pendienteSeleccionado ?? null;
        $isPendienteLocked = !empty($pendiente);
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0 fw-black" style="font-weight:900;">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                Nueva Reacción Adversa
            </h3>
            <div class="text-muted fw-bold mt-1">
                Registro unificado de paciente, medicamento y reacción adversa.
            </div>
        </div>

        <a href="{{ route('reacciones_adversas.index') }}"
           class="btn btn-outline-secondary fw-bold"
              style="border-radius:16px;">
            <i class="fa-solid fa-arrow-left me-2"></i> Volver
        </a>
    </div>

        <div class="ram-steps mb-3" aria-label="Flujo de registro de reacción adversa">
            <div class="ram-step ram-step-active" data-step="1">
                <span class="ram-step-dot">1</span>
                Paciente & Ubicación
            </div>
            <span class="ram-step-sep">/</span>
            <div class="ram-step" data-step="2">
                <span class="ram-step-dot">2</span>
                Medicamento
            </div>
            <span class="ram-step-sep">/</span>
            <div class="ram-step" data-step="3">
                <span class="ram-step-dot">3</span>
                Reacción
            </div>
            <span class="ram-step-sep">/</span>
            <div class="ram-step" data-step="4">
                <span class="ram-step-dot">4</span>
                Consecuencias
            </div>
        </div>

    @if($isPendienteLocked)
        <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:18px; background:#ecfdf5; color:#166534;">
            <div class="fw-bold mb-1">
                <i class="fa-solid fa-bell me-2"></i>Registro nuevo recibido desde PX
            </div>
            <div class="fw-semibold">
                Este formulario está enlazado al paciente {{ $pendiente['nombre_paciente'] ?? 'seleccionado' }}. Al terminar el flujo, ese pendiente saldrá de la bandeja y quedará la reacción adversa registrada.
            </div>
        </div>
    @endif

    @if($errors->any() && $errors->hasAny([
        'id_paciente','paciente_nombre','paciente_edad','paciente_sexo','notificador','id_medico','diagnostico_ingreso',
        'id_medicamento','id_lote','dosis_posologia','via_administracion','fecha_inicio_uso','fecha_fin_uso',
        'descripcion_reaccion','fecha_inicio_reaccion','fecha_fin_reaccion','desenlace','estado','sala','numero_cama',
        'foto','foto_medicamento','id_pendiente_bandeja','descripcion_consecuencia','gravedad'
    ]))
        <div class="alert-soft-danger mb-3">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            Hay campos con errores. Revísalos antes de continuar.
        </div>
    @endif

    <div class="form-card p-4">
        <form id="formCrearRAM"
              method="POST"
              action="{{ route('reacciones_adversas.store') }}"
              enctype="multipart/form-data"
              data-unsaved-form="true">
            @csrf
                        <input type="hidden" name="id_paciente" id="id_paciente" value="{{ old('id_paciente', $pendiente['id_paciente'] ?? $pacienteSeleccionado['id_paciente'] ?? '') }}">
                        <input type="hidden" name="id_pendiente_bandeja" id="id_pendiente_bandeja" value="{{ old('id_pendiente_bandeja', $pendiente['id_bandeja'] ?? '') }}">
                                                <input type="hidden" name="generate_report" id="generate_report" value="0">

            <!-- PASO 1: DATOS DEL PACIENTE, NOTIFICADOR Y UBICACIÓN -->
            <div class="step active" data-step="1">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-id-card-clip me-2" style="color:#60a5fa;"></i>
                                <h5 class="mb-0 fw-black" style="font-weight:900;">DATOS DEL PACIENTE</h5>
                            </div>
                            <span class="badge rounded-pill px-3 py-2" style="background: #222c5e; color:#ffffff; border:1px solid #222c5e;">
                                Expediente Clínico
                            </span>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">No. Expediente</label>
                                <input type="text" id="p_numero_expediente" class="form-control" value="GENERADO AUTOMATICAMENTE" readonly>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3">
                            <i class="fa-solid fa-user me-2" style="color:#60a5fa;"></i>
                            <h6 class="mb-0 fw-bold" style="color:#1e3a8a;">Datos Personales</h6>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" id="p_nombre" name="paciente_nombre" class="form-control mayusculas texto-general no-repetidos @error('paciente_nombre') is-invalid @enderror" value="{{ old('paciente_nombre') }}">
                                @error('paciente_nombre')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Edad <span class="text-danger">*</span></label>
                                <input type="number" id="p_edad" name="paciente_edad" min="1" max="99" class="form-control @error('paciente_edad') is-invalid @enderror" value="{{ old('paciente_edad') }}">
                                @error('paciente_edad')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Sexo <span class="text-danger">*</span></label>
                                <select id="p_sexo" name="paciente_sexo" class="form-select @error('paciente_sexo') is-invalid @enderror">
                                    <option value="">Seleccione</option>
                                    <option value="M" {{ old('paciente_sexo') === 'M' ? 'selected' : '' }}>MASCULINO</option>
                                    <option value="F" {{ old('paciente_sexo') === 'F' ? 'selected' : '' }}>FEMENINO</option>
                                    <option value="Otro" {{ old('paciente_sexo') === 'Otro' ? 'selected' : '' }}>OTRO</option>
                                </select>
                                @error('paciente_sexo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Diagnóstico de ingreso <span class="text-danger">*</span></label>
                                <textarea id="p_diagnostico" name="diagnostico_ingreso" rows="2" class="form-control mayusculas texto-general no-repetidos @error('diagnostico_ingreso') is-invalid @enderror">{{ old('diagnostico_ingreso') }}</textarea>
                                @error('diagnostico_ingreso')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3">
                            <i class="fa-solid fa-user-doctor me-2" style="color:#60a5fa;"></i>
                            <h6 class="mb-0 fw-bold" style="color:#1e3a8a;">Datos del Notificador</h6>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Notificador <span class="text-danger">*</span></label>
                                <select id="notificador" name="notificador" class="form-select @error('notificador') is-invalid @enderror" required>
                                    <option value="">Seleccione</option>
                                    <option value="MEDICO" {{ old('notificador') === 'MEDICO' ? 'selected' : '' }}>MEDICO</option>
                                    <option value="ENFERMERO" {{ old('notificador') === 'ENFERMERO' ? 'selected' : '' }}>ENFERMERO</option>
                                    <option value="FARMACEUTICO" {{ old('notificador') === 'FARMACEUTICO' ? 'selected' : '' }}>FARMACEUTICO</option>
                                </select>
                                @error('notificador')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-8 position-relative">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text"
                                       id="buscarMedico"
                                       class="form-control mayusculas texto-general no-repetidos"
                                       placeholder="Buscar nombre por rol seleccionado"
                                       autocomplete="off"
                                       value="{{ old('buscarMedico') }}"
                                       required>
                                <input type="hidden" id="id_medico" name="id_medico" value="{{ old('id_medico', $pendiente['id_medico'] ?? '') }}">
                                <div id="listaMedicos" class="list-group position-absolute w-100 mt-1" style="z-index:1000; border-radius:14px; overflow:hidden;"></div>
                                @error('id_medico')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="invalid-feedback d-none" id="error_id_medico">Debe seleccionar un nombre válido.</div>
                            </div>
                        </div>

                        <div class="my-4" style="border-top:1px solid rgba(255,255,255,.10);"></div>

                        <div class="d-flex align-items-center mb-3">
                            <i class="fa-solid fa-hospital-user me-2" style="color:#f87171;"></i>
                            <h5 class="mb-0 fw-black" style="font-weight:900;">UBICACIÓN</h5>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Sala <span class="text-danger">*</span></label>
                                <input type="text" id="p_sala" name="sala" class="form-control mayusculas texto-general no-repetidos @error('sala') is-invalid @enderror" value="{{ old('sala') }}" required>
                                @error('sala')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Cama <span class="text-danger">*</span></label>
                                <input type="text" id="p_numero_cama" name="numero_cama" class="form-control mayusculas texto-general no-repetidos @error('numero_cama') is-invalid @enderror" value="{{ old('numero_cama') }}" required>
                                @error('numero_cama')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 2: MEDICAMENTO USADO -->
            <div class="step" data-step="2" style="display: none;">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fa-solid fa-pills me-2" style="color:#60a5fa;"></i>
                            <h5 class="mb-0 fw-black" style="font-weight:900;">MEDICAMENTO USADO</h5>
                        </div>

                        <div class="col-md-5 position-relative">
                            <label class="form-label">Medicamento <span class="text-danger">*</span></label>
                            <input type="text" id="buscarMedicamento" name="medicamento_nombre" class="form-control mayusculas texto-general no-repetidos" placeholder="Buscar medicamento..." autocomplete="off" value="{{ old('medicamento_nombre') }}" required>
                            <input type="hidden" name="id_medicamento" id="id_medicamento" value="{{ old('id_medicamento') }}">
                            <div id="listaMedicamentos" class="list-group position-absolute w-100 mt-1" style="z-index:1000; border-radius:14px; overflow:hidden;"></div>
                        </div>

                        <div class="col-md-3 position-relative">
                            <label class="form-label">Lote <span class="text-danger">*</span></label>
                            <input type="text" id="buscarLote" class="form-control mayusculas texto-general no-repetidos" placeholder="Buscar lote..." autocomplete="off" value="{{ old('buscarLote') }}" required>
                            <input type="hidden" name="id_lote" id="id_lote" value="{{ old('id_lote') }}">
                            <div id="listaLotes" class="list-group position-absolute w-100 mt-1" style="z-index:1000; border-radius:14px; overflow:hidden;"></div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Dosis <span class="text-danger">*</span></label>
                            <input type="text" name="dosis_posologia" id="dosis_posologia" class="form-control mayusculas texto-general no-repetidos" value="{{ old('dosis_posologia') }}" placeholder="Ej: 500MG" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Vía <span class="text-danger">*</span></label>
                            <input type="text" name="via_administracion" id="via_administracion" class="form-control mayusculas texto-general no-repetidos" value="{{ old('via_administracion') }}" placeholder="ORAL / IV" required>
                        </div>

                        <div class="my-3" style="border-top:1px solid rgba(255,255,255,.10);"></div>

                        <div class="col-md-3">
                            <label class="form-label">Inicio uso <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_inicio_uso" id="fecha_inicio_uso" class="form-control" value="{{ old('fecha_inicio_uso') }}" max="{{ now()->toDateString() }}" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Fin uso (opcional)</label>
                            <input type="date" name="fecha_fin_uso" id="fecha_fin_uso" class="form-control" value="{{ old('fecha_fin_uso') }}" min="{{ old('fecha_inicio_uso') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                <i class="fa-solid fa-image me-2"></i>Foto del medicamento (opcional)
                            </label>

                            <input type="file" name="foto_medicamento" id="fotoMedicamento" accept="image/*" class="d-none">

                            <div class="upload-box p-4 text-center" id="uploadBoxMedicamento">
                                <div id="uploadPlaceholderMedicamento">
                                    <div class="upload-icon">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                    </div>
                                    <div class="fw-black" style="font-weight:900;">Haz clic para cargar la foto</div>
                                    <div class="help mt-1">Formatos: JPG / PNG / WEBP — Máx. 15MB</div>
                                </div>

                                <img id="previewImageMedicamento"
                                     class="img-fluid rounded-4 mt-3 d-none"
                                     style="max-height: 260px;"
                                     src="">

                                <div id="fileNameMedicamento" class="help mt-3 d-none"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 3: DATOS REACCIÓN ADVERSA -->
            <div class="step" data-step="3" style="display: none;">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fa-solid fa-file-medical me-2" style="color:#60a5fa;"></i>
                            <h5 class="mb-0 fw-black" style="font-weight:900;">DATOS REACCIÓN ADVERSA</h5>
                        </div>

                        <input type="hidden" name="descripcion_reaccion" id="descripcion_reaccion" value="{{ old('descripcion_reaccion') }}">

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fa-solid fa-calendar-day me-2"></i>Fecha inicio <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                name="fecha_inicio_reaccion"
                                id="fecha_inicio_reaccion"
                                class="form-control @error('fecha_inicio_reaccion') is-invalid @enderror"
                                value="{{ old('fecha_inicio_reaccion', !empty($pendiente['fecha_envio']) ? \Carbon\Carbon::parse($pendiente['fecha_envio'])->toDateString() : '') }}"
                                max="{{ now()->toDateString() }}"
                                required>
                            @error('fecha_inicio_reaccion')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fa-solid fa-calendar-check me-2"></i>Fecha fin (opcional)
                            </label>
                            <input type="date"
                                name="fecha_fin_reaccion"
                                id="fecha_fin_reaccion"
                                class="form-control @error('fecha_fin_reaccion') is-invalid @enderror"
                                value="{{ old('fecha_fin_reaccion', !empty($pendiente['fecha_envio']) ? \Carbon\Carbon::parse($pendiente['fecha_envio'])->toDateString() : '') }}"
                                min="{{ old('fecha_inicio_reaccion', !empty($pendiente['fecha_envio']) ? \Carbon\Carbon::parse($pendiente['fecha_envio'])->toDateString() : '') }}">
                            @error('fecha_fin_reaccion')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fa-solid fa-stethoscope me-2"></i>Desenlace (opcional)
                            </label>
                            <select
                                name="desenlace"
                                id="desenlace"
                                class="form-select @error('desenlace') is-invalid @enderror">
                                <option value="">Seleccione una opción</option>
                                @foreach(($desenlaceOptions ?? []) as $option)
                                    <option value="{{ $option['clave'] }}" {{ old('desenlace') == ($option['clave'] ?? '') ? 'selected' : '' }}>
                                        {{ $option['valor'] ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('desenlace')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fa-solid fa-flag me-2"></i>Estado <span class="text-danger">*</span>
                            </label>
                            <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
                                <option value="">Seleccione una opción</option>
                                @foreach(($estadoReaccionOptions ?? []) as $option)
                                    <option value="{{ $option['clave'] }}" {{ old('estado') == ($option['clave'] ?? '') ? 'selected' : '' }}>
                                        {{ $option['valor'] ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('estado')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                <i class="fa-solid fa-comment-dots me-2"></i>Observaciones (opcional)
                            </label>
                            <textarea name="observaciones"
                                    id="observaciones"
                                    class="form-control mayusculas texto-general no-repetidos @error('observaciones') is-invalid @enderror"
                                    rows="3"
                                    placeholder="Notas adicionales...">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fa-solid fa-camera me-2"></i>Evidencia Fotográfica (opcional)
                                </label>

                                <input type="file" name="foto" id="fotoEvidencia" accept="image/*" class="d-none">

                                <div class="upload-box p-4 text-center" id="uploadBoxEvidencia">
                                    <div id="uploadPlaceholderEvidencia">
                                        <div class="upload-icon">
                                            <i class="fa-solid fa-cloud-arrow-up"></i>
                                        </div>
                                        <div class="fw-black" style="font-weight:900;">Haz clic para cargar la evidencia</div>
                                        <div class="help mt-1">Formatos: JPG / PNG / WEBP — Máx. 15MB</div>
                                    </div>

                                    <img id="previewImageEvidencia"
                                         class="img-fluid rounded-4 mt-3 d-none"
                                         style="max-height: 260px;"
                                         src="">

                                    <div id="fileNameEvidencia" class="help mt-3 d-none">
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>

            <!-- PASO 4: CONSECUENCIAS -->
            <div class="step" data-step="4" style="display: none;">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fa-solid fa-triangle-exclamation me-2" style="color:#f87171;"></i>
                            <h5 class="mb-0 fw-black" style="font-weight:900;">CONSECUENCIAS</h5>
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                <i class="fa-solid fa-comment-dots me-2"></i>Descripción de consecuencia (opcional)
                            </label>
                            <select name="descripcion_consecuencia"
                                    id="descripcion_consecuencia"
                                    class="form-select">
                                <option value="">Seleccione una opción</option>
                                @foreach(($consecuenciaOptions ?? []) as $option)
                                    <option value="{{ $option['clave'] }}" {{ old('descripcion_consecuencia') == ($option['clave'] ?? '') ? 'selected' : '' }}>
                                        {{ $option['valor'] ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="my-3" style="border-top:1px solid rgba(255,255,255,.10);"></div>

                        <div class="col-md-12">
                            <label class="form-label">
                                <i class="fa-solid fa-exclamation-circle me-2"></i>Gravedad <span class="text-danger">*</span>
                            </label>
                            <select name="gravedad" id="gravedad" class="form-select" required>
                                <option value="">Seleccione la gravedad</option>
                                @foreach(($gravedadOptions ?? ['LEVE', 'MODERADA', 'GRAVE']) as $opcionGravedad)
                                    <option value="{{ $opcionGravedad }}" {{ old('gravedad') === $opcionGravedad ? 'selected' : '' }}>
                                        {{ str_replace('_', ' ', $opcionGravedad) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12">

            <div class="mt-4 d-flex gap-2 justify-content-between">
                <div id="navegacion-pasos" style="display: flex; gap: 8px;">
                    <button id="btn-anterior" type="button" class="btn btn-outline-secondary fw-bold" style="border-radius:16px; display:none;">
                        <i class="fa-solid fa-arrow-left me-2"></i> Anterior
                    </button>
                    <button id="btn-siguiente" type="button" class="btn btn-primary fw-bold px-4" style="border-radius:16px;">
                        <i class="fa-solid fa-arrow-right me-2"></i> Siguiente
                    </button>
                    <button id="btn-enviar" type="submit" class="btn btn-success fw-bold px-4" style="border-radius:16px; display:none;">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Finalizar
                    </button>
                </div>
            </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const pendingTrayData = @json($pendienteSeleccionado ?? null);
    const selectedPacienteData = @json($pacienteSeleccionado ?? null);
    const personalClinicoData = @json($personalClinico ?? []);

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
            .replace(/[^A-ZÁÉÍÓÚÑ\s]/g, '')
            .replace(/\s{2,}/g, ' ')
            .trimStart();

        valor = valor
            .replace(/([A-ZÁÉÍÓÚÑ])\1{2,}/g, '$1$1')
            .replace(/\b(\w+)(\s+\1\b){2,}/gi, '$1 $1');

        return valor;
    }

    function limpiarSoloNumeros(valor) {
        valor = valor
            .replace(/[^0-9]/g, '')
            .replace(/(\d)\1{2,}/g, '$1$1');

        return valor;
    }

    function aplicarValidacion(input) {
        if (input.classList.contains('solo-letras')) {
            input.value = limpiarSoloLetras(input.value);
        } else if (input.classList.contains('solo-numeros')) {
            input.value = limpiarSoloNumeros(input.value);
        } else if (input.classList.contains('texto-general') || input.classList.contains('no-repetidos')) {
            input.value = limpiarTextoGeneral(input.value);
        } else if (input.classList.contains('mayusculas')) {
            input.value = input.value.toUpperCase();
        }
    }

    document.querySelectorAll('.mayusculas, .solo-letras, .solo-numeros, .texto-general, .no-repetidos').forEach(input => {
        input.addEventListener('input', function () {
            aplicarValidacion(this);
        });

        input.addEventListener('blur', function () {
            aplicarValidacion(this);
        });
    });

    function llenarDatosPaciente(item) {
        document.getElementById('p_numero_expediente').value = item.numero_expediente ?? '';
        document.getElementById('p_nombre').value = item.nombre_completo ?? item.nombre_paciente ?? '';
        document.getElementById('p_edad').value = item.edad ?? '';
        document.getElementById('p_sexo').value = item.sexo ?? '';
        document.getElementById('p_diagnostico').value = item.diagnostico ?? '';

        const fechaBase = item.fecha_creacion || item.fecha_envio || '';
        if (fechaBase) {
            const fecha = String(fechaBase).slice(0, 10);
            const fechaInicio = document.getElementById('fecha_inicio_reaccion');
            const fechaFin = document.getElementById('fecha_fin_reaccion');

            if (fechaInicio && !fechaInicio.value) {
                fechaInicio.value = fecha;
            }

            if (fechaFin && !fechaFin.value) {
                fechaFin.value = fecha;
            }
        }

        buscarResponsable(item);
    }

    function buscarResponsable(item) {
        const idMedicoInput = document.getElementById('id_medico');
        const buscarMedicoInput = document.getElementById('buscarMedico');
        const notificadorInput = document.getElementById('notificador');
        if (!idMedicoInput) return;
        const idMedico = item.id_medico ?? '';
        idMedicoInput.value = idMedico;

        if (buscarMedicoInput && idMedico) {
            const medico = personalClinicoData.find((m) => String(m.id_medico) === String(idMedico));
            if (medico) {
                if (notificadorInput && medico.rol_nombre) {
                    notificadorInput.value = String(medico.rol_nombre).toUpperCase();
                }
                buscarMedicoInput.value = `${medico.nombre_completo ?? 'MEDICO'}${medico.numero_colegiacion ? ' - ' + medico.numero_colegiacion : ''}`;
            }
        }

        clearInvalidField(idMedicoInput, 'error_id_medico');
    }

    (function activarBusquedaMedicos() {
        const input = document.getElementById('buscarMedico');
        const hidden = document.getElementById('id_medico');
        const notificadorInput = document.getElementById('notificador');
        const lista = document.getElementById('listaMedicos');
        if (!input || !hidden || !lista) return;

        const obtenerRolSeleccionado = () => String(notificadorInput?.value || '').trim().toUpperCase();

        const obtenerPersonalFiltrado = (texto = '') => {
            const rol = obtenerRolSeleccionado();
            if (!rol) return [];

            const termino = String(texto || '').trim().toUpperCase();
            return personalClinicoData
                .filter((m) => String(m.rol_nombre || '').toUpperCase() === rol)
                .filter((m) => {
                    if (!termino) return true;
                    const nombre = String(m.nombre_completo || '').toUpperCase();
                    const coleg = String(m.numero_colegiacion || '').toUpperCase();
                    return nombre.includes(termino) || coleg.includes(termino);
                })
                .slice(0, 10);
        };

        const render = (items) => {
            lista.innerHTML = '';
            if (!obtenerRolSeleccionado()) {
                lista.innerHTML = '<div class="list-group-item">Seleccione primero el notificador</div>';
                return;
            }

            if (!Array.isArray(items) || items.length === 0) {
                lista.innerHTML = '<div class="list-group-item">No se encontraron resultados para ese rol</div>';
                return;
            }

            items.forEach((item) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action';
                btn.innerHTML = `<strong>${item.nombre_completo ?? 'MEDICO'}</strong><br><small>${item.numero_colegiacion ?? ''}</small>`;
                btn.addEventListener('click', () => {
                    input.value = `${item.nombre_completo ?? 'MEDICO'}${item.numero_colegiacion ? ' - ' + item.numero_colegiacion : ''}`;
                    hidden.value = item.id_medico ?? '';
                    lista.innerHTML = '';
                    clearInvalidField(input);
                    clearInvalidField(hidden, 'error_id_medico');
                });
                lista.appendChild(btn);
            });
        };

        input.addEventListener('input', function() {
            this.value = limpiarTextoGeneral(this.value);
            hidden.value = '';
            const q = this.value.trim();
            if (q.length < 1 && !obtenerRolSeleccionado()) {
                lista.innerHTML = '';
                return;
            }

            const filtered = obtenerPersonalFiltrado(q);

            render(filtered);
        });

        input.addEventListener('focus', function() {
            render(obtenerPersonalFiltrado(this.value.trim()));
        });

        if (notificadorInput) {
            notificadorInput.addEventListener('change', function() {
                input.value = '';
                hidden.value = '';
                clearInvalidField(input, 'error_id_medico');
                render(obtenerPersonalFiltrado(''));
            });
        }

        document.addEventListener('click', function (e) {
            if (!input.contains(e.target) && !lista.contains(e.target)) {
                lista.innerHTML = '';
            }
        });
    })();

    if (pendingTrayData) {
        llenarDatosPaciente(pendingTrayData);
    }

    function setInvalidField(element, message = null, errorId = null) {
        if (!element) return;
        element.classList.add('is-invalid');

        if (errorId) {
            const errorBox = document.getElementById(errorId);
            if (errorBox && message) {
                errorBox.textContent = message;
                errorBox.classList.remove('d-none');
                errorBox.classList.add('d-block');
            }
        }
    }

    function clearInvalidField(element, errorId = null) {
        if (!element) return;
        element.classList.remove('is-invalid');

        if (errorId) {
            const errorBox = document.getElementById(errorId);
            if (errorBox) {
                errorBox.classList.add('d-none');
                errorBox.classList.remove('d-block');
            }
        }
    }

    function validarCamposObligatorios() {
        let valido = true;

        const idPaciente = document.getElementById('id_paciente');
        const nombrePaciente = document.getElementById('p_nombre');
        const edadPaciente = document.getElementById('p_edad');
        const sexoPaciente = document.getElementById('p_sexo');
        const notificador = document.getElementById('notificador');
        const diagnosticoIngreso = document.getElementById('p_diagnostico');

        const idMedico = document.getElementById('id_medico');
        const buscarMedico = document.getElementById('buscarMedico');
        const medicamento = document.getElementById('buscarMedicamento');
        const idMedicamento = document.getElementById('id_medicamento');
        const lote = document.getElementById('buscarLote');
        const idLote = document.getElementById('id_lote');
        const dosis = document.getElementById('dosis_posologia');
        const via = document.getElementById('via_administracion');
        const inicioUso = document.getElementById('fecha_inicio_uso');
        const finUso = document.getElementById('fecha_fin_uso');

        const descripcion = document.getElementById('descripcion_reaccion');
        const desenlace = document.getElementById('desenlace');
        const observaciones = document.getElementById('observaciones');
        const fechaInicio = document.getElementById('fecha_inicio_reaccion');
        const fechaFin = document.getElementById('fecha_fin_reaccion');
        const estado = document.getElementById('estado');
        const sala = document.getElementById('p_sala');
        const cama = document.getElementById('p_numero_cama');

        const usaPacienteExistente = !!(idPaciente && idPaciente.value.trim());

        if (!usaPacienteExistente) {
            if (!nombrePaciente.value.trim()) {
                setInvalidField(nombrePaciente);
                valido = false;
            } else {
                clearInvalidField(nombrePaciente);
            }

            const edad = Number(edadPaciente.value || 0);
            if (!edad || edad < 1 || edad > 99) {
                setInvalidField(edadPaciente);
                valido = false;
            } else {
                clearInvalidField(edadPaciente);
            }

            if (!sexoPaciente.value.trim()) {
                setInvalidField(sexoPaciente);
                valido = false;
            } else {
                clearInvalidField(sexoPaciente);
            }

            if (!diagnosticoIngreso.value.trim()) {
                setInvalidField(diagnosticoIngreso);
                valido = false;
            } else {
                clearInvalidField(diagnosticoIngreso);
            }
        }

        if (!idMedico.value.trim()) {
            setInvalidField(buscarMedico, 'Debe seleccionar un nombre válido.', 'error_id_medico');
            valido = false;
        } else {
            clearInvalidField(buscarMedico, 'error_id_medico');
        }

        if (!notificador.value.trim()) {
            setInvalidField(notificador);
            valido = false;
        } else {
            clearInvalidField(notificador);
        }

        if (!medicamento.value.trim() || !idMedicamento.value.trim()) {
            setInvalidField(medicamento);
            valido = false;
        } else {
            clearInvalidField(medicamento);
        }

        if (!lote.value.trim() || !idLote.value.trim()) {
            setInvalidField(lote);
            valido = false;
        } else {
            clearInvalidField(lote);
        }

        if (!dosis.value.trim()) {
            setInvalidField(dosis);
            valido = false;
        } else {
            clearInvalidField(dosis);
        }

        if (!via.value.trim()) {
            setInvalidField(via);
            valido = false;
        } else {
            clearInvalidField(via);
        }

        const hoy = new Date().toISOString().slice(0, 10);

        if (!inicioUso.value.trim() || inicioUso.value > hoy) {
            setInvalidField(inicioUso);
            valido = false;
        } else {
            clearInvalidField(inicioUso);
        }

        if (finUso.value && (inicioUso.value && finUso.value < inicioUso.value)) {
            setInvalidField(finUso);
            valido = false;
        } else {
            clearInvalidField(finUso);
        }

        if (inicioUso && finUso) {
            finUso.min = inicioUso.value || '';
        }

        if (!descripcion.value.trim()) {
            const baseDesc = observaciones?.value?.trim() || desenlace?.value?.trim() || 'SIN DESCRIPCION';
            descripcion.value = limpiarTextoGeneral(baseDesc);
            clearInvalidField(descripcion);
        }

        if (!fechaInicio.value.trim()) {
            setInvalidField(fechaInicio);
            valido = false;
        } else {
            clearInvalidField(fechaInicio);
        }

        if (!sala.value.trim()) {
            setInvalidField(sala);
            valido = false;
        } else {
            clearInvalidField(sala);
        }

        if (!cama.value.trim()) {
            setInvalidField(cama);
            valido = false;
        } else {
            clearInvalidField(cama);
        }

        if (fechaInicio.value && fechaFin.value && fechaFin.value < fechaInicio.value) {
            setInvalidField(fechaFin);
            valido = false;
        } else {
            clearInvalidField(fechaFin);
        }

        if (!estado.value.trim()) {
            setInvalidField(estado);
            valido = false;
        } else {
            clearInvalidField(estado);
        }

        return valido;
    }

    document.getElementById('fecha_inicio_reaccion').addEventListener('change', function () {
        clearInvalidField(this);
    });

    document.getElementById('fecha_fin_reaccion').addEventListener('change', function () {
        clearInvalidField(this);
    });

    (function sincronizarMinFechaFinReaccion() {
        const inicioReaccion = document.getElementById('fecha_inicio_reaccion');
        const finReaccion = document.getElementById('fecha_fin_reaccion');
        if (!inicioReaccion || !finReaccion) return;

        const aplicarMin = () => {
            finReaccion.min = inicioReaccion.value || '';
            if (finReaccion.value && inicioReaccion.value && finReaccion.value < inicioReaccion.value) {
                finReaccion.value = inicioReaccion.value;
            }
        };

        inicioReaccion.addEventListener('change', aplicarMin);
        aplicarMin();
    })();

    document.getElementById('p_sala').addEventListener('input', function () {
        clearInvalidField(this);
    });

    document.getElementById('p_numero_cama').addEventListener('input', function () {
        clearInvalidField(this);
    });

    document.getElementById('estado').addEventListener('change', function () {
        clearInvalidField(this);
    });

    ['p_nombre','p_edad','p_sexo','notificador','p_diagnostico','buscarMedico','buscarMedicamento','buscarLote','dosis_posologia','via_administracion','fecha_inicio_uso','fecha_fin_uso']
        .forEach((id) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', function(){ clearInvalidField(this); });
            el.addEventListener('change', function(){ clearInvalidField(this); });
        });

    (function sincronizarMinFechaFinUso() {
        const inicioUso = document.getElementById('fecha_inicio_uso');
        const finUso = document.getElementById('fecha_fin_uso');
        if (!inicioUso || !finUso) return;

        const aplicarMin = () => {
            finUso.min = inicioUso.value || '';
            if (finUso.value && inicioUso.value && finUso.value < inicioUso.value) {
                finUso.value = inicioUso.value;
            }
        };

        inicioUso.addEventListener('change', aplicarMin);
        aplicarMin();
    })();

    document.getElementById('p_edad').addEventListener('input', function() {
        this.value = String(this.value || '').replace(/[^0-9]/g, '');
        const n = Number(this.value || 0);
        if (n > 99) this.value = '99';
        if (n > 0 && n < 1) this.value = '1';
    });

    async function buscarMedicamentos(q) {
        const res = await fetch(`{{ route('ajax.medicamentos') }}?q=${encodeURIComponent(q)}`);
        return await res.json();
    }

    async function buscarLotes(q, idMedicamento) {
        const res = await fetch(`{{ route('ajax.lotes') }}?q=${encodeURIComponent(q)}&id_medicamento=${encodeURIComponent(idMedicamento || '')}`);
        return await res.json();
    }

    const inputMedicamento = document.getElementById('buscarMedicamento');
    const inputLote = document.getElementById('buscarLote');
    const idMedicamento = document.getElementById('id_medicamento');
    const idLote = document.getElementById('id_lote');
    const listaMedicamentos = document.getElementById('listaMedicamentos');
    const listaLotes = document.getElementById('listaLotes');

    if (inputMedicamento) {
        inputMedicamento.addEventListener('input', async function() {
            const q = this.value.trim();
            idMedicamento.value = '';
            idLote.value = '';
            inputLote.value = '';
            listaLotes.innerHTML = '';

            if (q.length < 2) {
                listaMedicamentos.innerHTML = '';
                return;
            }

            const data = await buscarMedicamentos(q);
            listaMedicamentos.innerHTML = '';

            if (!Array.isArray(data) || data.length === 0) {
                listaMedicamentos.innerHTML = '<div class="list-group-item">No se encontraron resultados</div>';
                return;
            }

            data.forEach(item => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action';
                btn.innerHTML = `<strong>${item.nombre_comercial ?? ''}</strong><br><small>${item.principio_activo ?? ''}</small>`;
                btn.onclick = async () => {
                    inputMedicamento.value = item.nombre_comercial ?? '';
                    idMedicamento.value = item.id_medicamento ?? '';
                    idLote.value = '';
                    inputLote.value = '';
                    listaMedicamentos.innerHTML = '';
                    
                    // Cargar lotes automáticamente para el medicamento seleccionado
                    const lotes = await buscarLotes('', item.id_medicamento ?? '');
                    
                    if (Array.isArray(lotes) && lotes.length === 1) {
                        // Si hay solo un lote, llenarlo automáticamente
                        inputLote.value = lotes[0].numero_lote ?? '';
                        idLote.value = lotes[0].id_lote ?? '';
                        listaLotes.innerHTML = '';
                    } else if (Array.isArray(lotes) && lotes.length > 1) {
                        // Si hay múltiples lotes, mostrar la lista
                        listaLotes.innerHTML = '';
                        lotes.forEach(lote => {
                            const loteBtn = document.createElement('button');
                            loteBtn.type = 'button';
                            loteBtn.className = 'list-group-item list-group-item-action';
                            loteBtn.innerHTML = `<strong>${lote.numero_lote ?? ''}</strong>`;
                            loteBtn.onclick = () => {
                                inputLote.value = lote.numero_lote ?? '';
                                idLote.value = lote.id_lote ?? '';
                                listaLotes.innerHTML = '';
                            };
                            listaLotes.appendChild(loteBtn);
                        });
                    } else {
                        listaLotes.innerHTML = '<div class="list-group-item">No hay lotes disponibles</div>';
                    }
                };
                listaMedicamentos.appendChild(btn);
            });
        });
    }

    if (inputLote) {
        inputLote.addEventListener('input', async function() {
            const q = this.value.trim();
            idLote.value = '';

            if (!idMedicamento.value) {
                listaLotes.innerHTML = '<div class="list-group-item">Seleccione primero un medicamento</div>';
                return;
            }

            const data = await buscarLotes(q, idMedicamento.value);
            listaLotes.innerHTML = '';

            if (!Array.isArray(data) || data.length === 0) {
                listaLotes.innerHTML = '<div class="list-group-item">No se encontraron lotes</div>';
                return;
            }

            data.forEach(item => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action';
                btn.innerHTML = `<strong>${item.numero_lote ?? ''}</strong>`;
                btn.onclick = () => {
                    inputLote.value = item.numero_lote ?? '';
                    idLote.value = item.id_lote ?? '';
                    listaLotes.innerHTML = '';
                };
                listaLotes.appendChild(btn);
            });
        });
    }

    const formCrear = document.getElementById('formCrearRAM');
    const draftKey = 'ram_create_draft_v1';

    function saveCreateDraft() {
        if (!formCrear) return;

        const payload = {
            id_paciente: document.getElementById('id_paciente')?.value || '',
            paciente_nombre: document.getElementById('p_nombre')?.value || '',
            paciente_edad: document.getElementById('p_edad')?.value || '',
            paciente_sexo: document.getElementById('p_sexo')?.value || '',
            buscarMedico: document.getElementById('buscarMedico')?.value || '',
            id_medico: document.getElementById('id_medico')?.value || '',
            diagnostico_ingreso: document.getElementById('p_diagnostico')?.value || '',
            medicamento_nombre: document.getElementById('buscarMedicamento')?.value || '',
            id_medicamento: document.getElementById('id_medicamento')?.value || '',
            buscarLote: document.getElementById('buscarLote')?.value || '',
            id_lote: document.getElementById('id_lote')?.value || '',
            dosis_posologia: document.getElementById('dosis_posologia')?.value || '',
            via_administracion: document.getElementById('via_administracion')?.value || '',
            fecha_inicio_uso: document.getElementById('fecha_inicio_uso')?.value || '',
            fecha_fin_uso: document.getElementById('fecha_fin_uso')?.value || '',
            sala: document.getElementById('p_sala')?.value || '',
            numero_cama: document.getElementById('p_numero_cama')?.value || '',
            fecha_inicio_reaccion: document.getElementById('fecha_inicio_reaccion')?.value || '',
            fecha_fin_reaccion: document.getElementById('fecha_fin_reaccion')?.value || '',
            desenlace: document.getElementById('desenlace')?.value || '',
            estado: document.getElementById('estado')?.value || '',
            observaciones: document.getElementById('observaciones')?.value || '',
        };

        localStorage.setItem(draftKey, JSON.stringify(payload));
    }

    function restoreCreateDraft() {
        if (!formCrear) return;

        const raw = localStorage.getItem(draftKey);
        if (!raw) return;

        try {
            const data = JSON.parse(raw);
            if (!data || typeof data !== 'object') return;

            if (!document.getElementById('id_paciente')?.value && data.id_paciente) {
                document.getElementById('id_paciente').value = data.id_paciente;
            }

            if (!document.getElementById('id_medico')?.value && data.id_medico) {
                document.getElementById('id_medico').value = data.id_medico;
            }

            if (!document.getElementById('buscarMedico')?.value && data.buscarMedico) {
                document.getElementById('buscarMedico').value = data.buscarMedico;
            }

            if (!document.getElementById('p_nombre')?.value && data.paciente_nombre) {
                document.getElementById('p_nombre').value = data.paciente_nombre;
            }

            if (!document.getElementById('p_edad')?.value && data.paciente_edad) {
                document.getElementById('p_edad').value = data.paciente_edad;
            }

            if (!document.getElementById('p_sexo')?.value && data.paciente_sexo) {
                document.getElementById('p_sexo').value = data.paciente_sexo;
            }

            if (!document.getElementById('p_diagnostico')?.value && data.diagnostico_ingreso) {
                document.getElementById('p_diagnostico').value = data.diagnostico_ingreso;
            }

            if (!document.getElementById('buscarMedicamento')?.value && data.medicamento_nombre) {
                document.getElementById('buscarMedicamento').value = data.medicamento_nombre;
            }

            if (!document.getElementById('id_medicamento')?.value && data.id_medicamento) {
                document.getElementById('id_medicamento').value = data.id_medicamento;
            }

            if (!document.getElementById('buscarLote')?.value && data.buscarLote) {
                document.getElementById('buscarLote').value = data.buscarLote;
            }

            if (!document.getElementById('id_lote')?.value && data.id_lote) {
                document.getElementById('id_lote').value = data.id_lote;
            }

            if (!document.getElementById('dosis_posologia')?.value && data.dosis_posologia) {
                document.getElementById('dosis_posologia').value = data.dosis_posologia;
            }

            if (!document.getElementById('via_administracion')?.value && data.via_administracion) {
                document.getElementById('via_administracion').value = data.via_administracion;
            }

            if (!document.getElementById('fecha_inicio_uso')?.value && data.fecha_inicio_uso) {
                document.getElementById('fecha_inicio_uso').value = data.fecha_inicio_uso;
            }

            if (!document.getElementById('fecha_fin_uso')?.value && data.fecha_fin_uso) {
                document.getElementById('fecha_fin_uso').value = data.fecha_fin_uso;
            }

            if (!document.getElementById('p_sala')?.value && data.sala) {
                document.getElementById('p_sala').value = data.sala;
            }

            if (!document.getElementById('p_numero_cama')?.value && data.numero_cama) {
                document.getElementById('p_numero_cama').value = data.numero_cama;
            }

            if (!document.getElementById('fecha_inicio_reaccion')?.value && data.fecha_inicio_reaccion) {
                document.getElementById('fecha_inicio_reaccion').value = data.fecha_inicio_reaccion;
            }

            if (!document.getElementById('fecha_fin_reaccion')?.value && data.fecha_fin_reaccion) {
                document.getElementById('fecha_fin_reaccion').value = data.fecha_fin_reaccion;
            }

            if (!document.getElementById('desenlace')?.value && data.desenlace) {
                document.getElementById('desenlace').value = data.desenlace;
            }

            if (!document.getElementById('estado')?.value && data.estado) {
                document.getElementById('estado').value = data.estado;
            }

            if (!document.getElementById('observaciones')?.value && data.observaciones) {
                document.getElementById('observaciones').value = data.observaciones;
            }
        } catch (e) {
        }
    }

    // Regla funcional: no conservar borradores al salir/cancelar.
    localStorage.removeItem(draftKey);

    const prevDiscardHandlerCreate = window.onDiscardUnsavedChanges;
    window.onDiscardUnsavedChanges = function() {
        if (typeof prevDiscardHandlerCreate === 'function') {
            prevDiscardHandlerCreate();
        }
        localStorage.removeItem(draftKey);
    };

    document.getElementById('formCrearRAM').addEventListener('submit', function (e) {
        const descripcion = document.getElementById('descripcion_reaccion');
        const desenlace = document.getElementById('desenlace');
        const observaciones = document.getElementById('observaciones');

        if (descripcion && !descripcion.value.trim()) {
            const baseDesc = observaciones?.value?.trim() || desenlace?.value?.trim() || 'SIN DESCRIPCION';
            descripcion.value = baseDesc;
        }

        [descripcion, observaciones].forEach(campo => {
            if (campo) campo.value = limpiarTextoGeneral(campo.value);
        });

        const repetido = /(.)\1{3,}/;
        const palabraRepetida = /\b(\w+)(\s+\1\b){2,}/i;
        const camposTexto = [descripcion, observaciones];

        for (let campo of camposTexto) {
            if (!campo || !campo.value) continue;

            if (repetido.test(campo.value) || palabraRepetida.test(campo.value)) {
                e.preventDefault();
                setInvalidField(campo);
                campo.focus();
                return;
            }
        }

        if (!validarCamposObligatorios()) {
            e.preventDefault();
            return;
        }

        localStorage.removeItem(draftKey);
    });

    // ============= NAVEGACIÓN ENTRE PASOS =============
    let pasoActual = 1;
    const totalPasos = 4;

    const mapaCamposPorPaso = {
        1: ['p_nombre', 'p_edad', 'p_sexo', 'p_diagnostico', 'notificador', 'id_medico', 'buscarMedico', 'p_sala', 'p_numero_cama'],
        2: ['buscarMedicamento', 'id_medicamento', 'buscarLote', 'id_lote', 'dosis_posologia', 'via_administracion', 'fecha_inicio_uso'],
        3: ['fecha_inicio_reaccion', 'estado'],
        4: ['gravedad']
    };

    function mostrarPaso(numero) {
        if (numero < 1 || numero > totalPasos) return;

        // Ocultar todos los pasos
        document.querySelectorAll('.step').forEach(step => {
            step.style.display = 'none';
            step.classList.remove('active');
        });

        // Mostrar el paso actual
        const stepActual = document.querySelector(`.step[data-step="${numero}"]`);
        if (stepActual) {
            stepActual.style.display = 'block';
            stepActual.classList.add('active');
        }

        // Actualizar indicadores visuales
        document.querySelectorAll('.ram-step[data-step]').forEach(step => {
            const stepNum = parseInt(step.getAttribute('data-step'));
            step.classList.remove('ram-step-active', 'ram-step-complete');

            if (stepNum < numero) {
                step.classList.add('ram-step-complete');
            } else if (stepNum === numero) {
                step.classList.add('ram-step-active');
            }
        });

        // Actualizar botones
        const btnAnterior = document.getElementById('btn-anterior');
        const btnSiguiente = document.getElementById('btn-siguiente');
        const btnEnviar = document.getElementById('btn-enviar');

        if (numero === 1) {
            btnAnterior.style.display = 'none';
            btnSiguiente.style.display = 'inline-block';
            btnEnviar.style.display = 'none';
        } else if (numero === totalPasos) {
            btnAnterior.style.display = 'inline-block';
            btnSiguiente.style.display = 'none';
            btnEnviar.style.display = 'inline-block';
        } else {
            btnAnterior.style.display = 'inline-block';
            btnSiguiente.style.display = 'inline-block';
            btnEnviar.style.display = 'none';
        }

        pasoActual = numero;
        window.scrollTo(0, 0);
    }

    function validarPasoActual() {
        const campos = mapaCamposPorPaso[pasoActual] || [];
        let valido = true;

        campos.forEach(idCampo => {
            const campo = document.getElementById(idCampo);
            if (!campo) return;

            const valor = campo.value?.trim();
            const esObligatorio = campo.hasAttribute('required') || campo.classList.contains('form-control') && campo.previousElementSibling?.textContent?.includes('*');

            // Validación básica
            if (pasoActual === 1) {
                if (idCampo === 'p_nombre' && !valor) {
                    setInvalidField(campo);
                    valido = false;
                } else if (idCampo === 'p_edad' && (!valor || Number(valor) < 1 || Number(valor) > 99)) {
                    setInvalidField(campo);
                    valido = false;
                } else if (idCampo === 'p_sexo' && !valor) {
                    setInvalidField(campo);
                    valido = false;
                } else if (idCampo === 'p_diagnostico' && !valor) {
                    setInvalidField(campo);
                    valido = false;
                } else if (idCampo === 'notificador' && !valor) {
                    setInvalidField(campo);
                    valido = false;
                } else if (idCampo === 'id_medico' && !valor) {
                    setInvalidField(document.getElementById('buscarMedico'), 'Debe seleccionar un nombre válido.', 'error_id_medico');
                    valido = false;
                } else if (idCampo === 'p_sala' && !valor) {
                    setInvalidField(campo);
                    valido = false;
                } else if (idCampo === 'p_numero_cama' && !valor) {
                    setInvalidField(campo);
                    valido = false;
                } else {
                    clearInvalidField(campo);
                }
            } else if (pasoActual === 2) {
                if (idCampo === 'buscarMedicamento' && !valor) {
                    setInvalidField(campo);
                    valido = false;
                } else if (idCampo === 'id_medicamento' && !document.getElementById('id_medicamento')?.value) {
                    setInvalidField(document.getElementById('buscarMedicamento'));
                    valido = false;
                } else if (idCampo === 'buscarLote' && !valor) {
                    setInvalidField(campo);
                    valido = false;
                } else if (idCampo === 'id_lote' && !document.getElementById('id_lote')?.value) {
                    setInvalidField(document.getElementById('buscarLote'));
                    valido = false;
                } else if (idCampo === 'dosis_posologia' && !valor) {
                    setInvalidField(campo);
                    valido = false;
                } else if (idCampo === 'via_administracion' && !valor) {
                    setInvalidField(campo);
                    valido = false;
                } else if (idCampo === 'fecha_inicio_uso' && !valor) {
                    setInvalidField(campo);
                    valido = false;
                } else if (idCampo === 'fecha_inicio_uso' && !validarFechaNoFutura(campo, 'La fecha de inicio de uso')) {
                    setInvalidField(campo);
                    valido = false;
                } else {
                    clearInvalidField(campo);
                }
            } else if (pasoActual === 3) {
                if (idCampo === 'fecha_inicio_reaccion' && !valor) {
                    setInvalidField(campo);
                    valido = false;
                } else if (idCampo === 'fecha_inicio_reaccion' && !validarFechaNoFutura(campo, 'La fecha de inicio de reacción')) {
                    setInvalidField(campo);
                    valido = false;
                } else if (idCampo === 'estado' && !valor) {
                    setInvalidField(campo);
                    valido = false;
                } else {
                    clearInvalidField(campo);
                }
            } else if (pasoActual === 4) {
                if (idCampo === 'gravedad' && !valor) {
                    setInvalidField(campo);
                    valido = false;
                } else {
                    clearInvalidField(campo);
                }
            }
        });

        return valido;
    }

    // Event listeners para botones de navegación
    document.getElementById('btn-siguiente').addEventListener('click', (e) => {
        e.preventDefault();
        if (validarPasoActual()) {
            mostrarPaso(pasoActual + 1);
        }
    });

    document.getElementById('btn-anterior').addEventListener('click', (e) => {
        e.preventDefault();
        mostrarPaso(pasoActual - 1);
    });

    // Inicializar el primer paso
    mostrarPaso(1);

    const MAX_IMG_SIZE = 15 * 1024 * 1024; // 15MB

    function mostrarAlertaError(mensaje) {
        if (window.Swal) {
            Swal.fire({
                icon: 'warning',
                title: 'Validación',
                text: mensaje,
                confirmButtonText: 'Entendido'
            });
            return;
        }
        alert(mensaje);
    }

    function configurarUploadImagen({ boxId, inputId, previewId, placeholderId, fileNameId, label }) {
        const box = document.getElementById(boxId);
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);
        const fileName = document.getElementById(fileNameId);

        if (!box || !input || !preview || !placeholder || !fileName) return;

        box.addEventListener('click', () => input.click());

        input.addEventListener('change', (e) => {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            if (file.size > MAX_IMG_SIZE) {
                e.target.value = '';
                mostrarAlertaError(`La ${label} no puede exceder 15MB.`);
                return;
            }

            const reader = new FileReader();
            reader.onload = (event) => {
                preview.src = event.target.result;
                preview.classList.remove('d-none');
                placeholder.classList.add('d-none');
                fileName.textContent = `Archivo: ${file.name}`;
                fileName.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    }

    configurarUploadImagen({
        boxId: 'uploadBoxMedicamento',
        inputId: 'fotoMedicamento',
        previewId: 'previewImageMedicamento',
        placeholderId: 'uploadPlaceholderMedicamento',
        fileNameId: 'fileNameMedicamento',
        label: 'foto del medicamento'
    });

    configurarUploadImagen({
        boxId: 'uploadBoxEvidencia',
        inputId: 'fotoEvidencia',
        previewId: 'previewImageEvidencia',
        placeholderId: 'uploadPlaceholderEvidencia',
        fileNameId: 'fileNameEvidencia',
        label: 'evidencia fotográfica'
    });

    function validarFechaNoFutura(input, nombreCampo) {
        if (!input || !input.value) return true;

        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        const fecha = new Date(`${input.value}T00:00:00`);

        if (fecha > hoy) {
            input.classList.add('is-invalid');
            input.setCustomValidity('No se permiten fechas futuras.');
            mostrarAlertaError(`${nombreCampo} no puede ser una fecha futura.`);
            return false;
        }

        input.classList.remove('is-invalid');
        input.setCustomValidity('');
        return true;
    }

    [
        { id: 'fecha_inicio_uso', label: 'La fecha de inicio de uso' },
        { id: 'fecha_inicio_reaccion', label: 'La fecha de inicio de reacción' }
    ].forEach(({ id, label }) => {
        const campo = document.getElementById(id);
        if (!campo) return;
        campo.addEventListener('change', () => validarFechaNoFutura(campo, label));
    });
</script>
@endpush

@endsection
