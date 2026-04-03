@extends('layouts.app')

@section('content')

@push('styles')
<style>
    .btn.btn-primary {
        background: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }

    .btn.btn-primary:hover,
    .btn.btn-primary:focus {
        background: #1d4ed8;
        border-color: #1d4ed8;
        color: #ffffff;
    }

    .btn.btn-outline-light {
        background: #475569;
        border-color: #475569;
        color: #ffffff;
    }

    .btn.btn-outline-light:hover,
    .btn.btn-outline-light:focus {
        background: #334155;
        border-color: #334155;
        color: #ffffff;
    }

    .glass{
        border: 1px solid rgba(255,255,255,.10);
        background: rgba(255,255,255,.06);
        backdrop-filter: blur(14px);
        border-radius: 18px;
        box-shadow: 0 18px 45px rgba(0,0,0,.35);
    }

    .info-card{
        background:#ffffff;
        border:1px solid #e5e7eb;
        color:#111827;
        border-radius:16px;
        padding:16px;
        height:100%;
    }

    .info-label{
        font-size:.78rem;
        font-weight:900;
        text-transform:uppercase;
        color:#6b7280;
        margin-bottom:6px;
        letter-spacing:.04em;
    }

    .info-value{
        font-size:1rem;
        font-weight:800;
        color:#111827;
    }

    .info-subvalue{
        font-size:.92rem;
        color:#4b5563;
        font-weight:600;
        margin-top:4px;
    }

    .section-title{
        font-weight:900;
        margin-bottom:1rem;
    }

    .description-box{
        background:#ffffff;
        border:1px solid #e5e7eb;
        color:#111827;
        border-radius:16px;
        padding:16px;
        font-weight:600;
        min-height:72px;
        white-space:pre-wrap;
    }

    .table-glass{
        color: #111827;
    }

    .table-glass thead{
        background: #f8fafc;
    }

   .table-glass th{
        font-size: .82rem;
        letter-spacing: .08em;
        text-transform: uppercase;
        border: none !important;
        color: #111827 !important;
        font-weight: 900 !important;
        background: #f8fafc !important;
    }

    .table-glass td{
        border-color: #e5e7eb !important;
        vertical-align: middle;
        color: #111827 !important;
    }

    .empty-state{
        color: rgba(255,255,255,.65);
        font-weight:700;
    }

    .photo-box{
        background:#ffffff;
        border:1px solid #e5e7eb;
        border-radius:18px;
        padding:14px;
        display:flex;
        justify-content:center;
        align-items:center;
        min-height:180px;
    }

    .photo-box img{
        max-width:100%;
        max-height:360px;
        object-fit:contain;
        border-radius:14px;
    }
</style>
@endpush

<div class="container py-2">

    @php
        $returnRoute = $backRoute ?? route('reacciones_adversas.index');
        $returnLabel = $backLabel ?? 'Volver';
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-black mb-0" style="font-weight:900;">
                <i class="fa-solid fa-eye me-2"></i>
                Detalle Reacción Adversa #{{ $reaccion['id_reaccion'] ?? '' }}
            </h3>
            <div class="text-dark fw-bold mt-1">
                Información completa del registro de farmacovigilancia.
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ $returnRoute }}"
               class="btn btn-outline-light fw-bold"
               style="border-radius:16px;">
                <i class="fa-solid fa-arrow-left me-2"></i> {{ $returnLabel }}
            </a>

            <a href="{{ route('reacciones_adversas.print', ['id' => ($reaccion['id_reaccion'] ?? ''), 'return_url' => $returnRoute]) }}"
               class="btn btn-primary fw-bold"
               style="border-radius:16px;"
               target="_blank"
               rel="noopener">
                <i class="fa-solid fa-file-pdf me-2"></i> Reporte
            </a>
        </div>
    </div>

    {{-- DATOS DEL PACIENTE --}}
    <div class="glass p-4 mb-4">
        <h5 class="section-title">
            <i class="fa-solid fa-id-card-clip me-2"></i>
            Datos del Paciente
        </h5>

        <div class="table-wrap">
            <div class="table-responsive">
                <table class="table table-glass align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. Expediente</th>
                            <th>Paciente</th>
                            <th>Edad</th>
                            <th>Sexo</th>
                            <th>Sala</th>
                            <th>Cama</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $reaccion['numero_expediente'] ?? '-' }}</td>
                            <td class="fw-bold">{{ $reaccion['nombre_completo'] ?? '-' }}</td>
                            <td>{{ $reaccion['edad'] ?? '-' }}</td>
                            <td>{{ $reaccion['sexo'] ?? '-' }}</td>
                            <td>{{ $reaccion['sala'] ?? '-' }}</td>
                            <td>{{ $reaccion['numero_cama'] ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-wrap mt-3">
            <div class="table-responsive">
                <table class="table table-glass align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Diagnóstico</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold">
                                {{ $reaccion['diagnostico'] ?? '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- DATOS DEL MÉDICO --}}
    <div class="glass p-4 mb-4">
        <h5 class="section-title">
            <i class="fa-solid fa-user-doctor me-2"></i>
            Datos del Médico
        </h5>

        <div class="table-wrap">
            <div class="table-responsive">
                <table class="table table-glass align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Médico Responsable</th>
                            <th>Especialidad</th>
                            <th>No. Colegiación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold">{{ $reaccion['nombre_medico'] ?? $reaccion['nombre_completo_medico'] ?? '-' }}</td>
                            <td>{{ $reaccion['especialidad'] ?? '-' }}</td>
                            <td>{{ $reaccion['numero_colegiacion'] ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- DATOS DE LA REACCIÓN --}}
    <div class="glass p-4 mb-4">
        <h5 class="section-title">
            <i class="fa-solid fa-file-medical me-2"></i>
            Datos de la Reacción Adversa
        </h5>

        <div class="table-wrap mb-3">
            <div class="table-responsive">
                <table class="table table-glass align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Estado</th>
                            <th>Desenlace</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                {{ !empty($reaccion['fecha_inicio_reaccion'])
                                    ? \Carbon\Carbon::parse($reaccion['fecha_inicio_reaccion'])->format('d/m/Y')
                                    : '-' }}
                            </td>
                            <td>
                                {{ !empty($reaccion['fecha_fin_reaccion'])
                                    ? \Carbon\Carbon::parse($reaccion['fecha_fin_reaccion'])->format('d/m/Y')
                                    : '-' }}
                            </td>
                            <td>
                                @php
                                    $estado = strtoupper($reaccion['estado'] ?? '-');
                                    $bg = str_contains($estado, 'CERR') ? 'secondary'
                                        : (str_contains($estado, 'REGISTRADA') ? 'success'
                                        : (str_contains($estado, 'ANALISIS') ? 'warning'
                                        : 'info'));
                                @endphp

                                <span class="badge bg-{{ $bg }} rounded-pill px-3 py-2 fw-bold">
                                    {{ $estado }}
                                </span>
                            </td>
                            <td>{{ $reaccion['desenlace'] ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-wrap mb-3">
            <div class="table-responsive">
                <table class="table table-glass align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Descripción de la reacción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold">
                                {{ $reaccion['descripcion_reaccion'] ?? '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>


        <div class="table-wrap">
            <div class="table-responsive">
                <table class="table table-glass align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold">
                                {{ $reaccion['observaciones'] ?? '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- CONSECUENCIAS --}}
        <h5 class="section-title mt-4">
            <i class="fa-solid fa-exclamation-triangle me-2"></i> Consecuencias
        </h5>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="info-card">
                    <div class="info-label">Descripción de Consecuencia</div>
                    <div class="description-box">
                        {{ $reaccion['descripcion_consecuencia'] ?? '-' }}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-card">
                    <div class="info-label">Gravedad</div>
                    <div class="info-value">
                        @php
                            $gravedad = strtoupper($reaccion['gravedad'] ?? '-');
                            $gravedadBg = match($gravedad) {
                                'LEVE' => 'info',
                                'MODERADA' => 'warning',
                                'GRAVE' => 'danger',
                                'MUY_GRAVE' => 'danger',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge bg-{{ $gravedadBg }} rounded-pill px-3 py-2 fw-bold">
                            {{ $gravedad }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DETALLES / MEDICAMENTOS --}}
    <div class="glass p-4 mb-4">
        <h5 class="section-title">
            <i class="fa-solid fa-pills me-2"></i> Medicamentos Relacionados
        </h5>

        @if(!empty($detalles))
            <div class="table-responsive">
                <table class="table table-glass align-middle mb-0">
                    <thead style="background: rgba(255,255,255,.10);">
                        <tr>
                            <th>Medicamento</th>
                            <th>Lote</th>
                            <th>Dosis / Posología</th>
                            <th>Vía</th>
                            <th>Inicio uso</th>
                            <th>Fin uso</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detalles as $d)
                            <tr>
                                <td class="fw-bold">{{ $d['medicamento'] ?? ($d['nombre_comercial'] ?? '-') }}</td>
                                <td class="fw-bold">{{ $d['numero_lote'] ?? '-' }}</td>
                                <td>{{ $d['dosis_posologia'] ?? '-' }}</td>
                                <td>{{ $d['via_administracion'] ?? '-' }}</td>
                                <td>
                                    {{ !empty($d['fecha_inicio_uso'])
                                        ? \Carbon\Carbon::parse($d['fecha_inicio_uso'])->format('d/m/Y')
                                        : '-' }}
                                </td>
                                <td>
                                    {{ !empty($d['fecha_fin_uso'])
                                        ? \Carbon\Carbon::parse($d['fecha_fin_uso'])->format('d/m/Y')
                                        : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">No hay medicamentos registrados.</div>
        @endif
    </div>

    <div class="glass p-4 mb-4">
        <h5 class="section-title">
            <i class="fa-solid fa-images me-2"></i> Fotografías Registradas
        </h5>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="info-card">
                    <div class="info-label">Foto del medicamento</div>
                    <div class="photo-box">
                        @if(!empty($reaccion['ruta_foto_medicamento']))
                            <img src="http://localhost:3000/uploads/{{ ltrim($reaccion['ruta_foto_medicamento'], '/') }}" alt="Foto del medicamento">
                        @else
                            <span class="text-muted fw-bold">No hay foto del medicamento.</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="info-card">
                    <div class="info-label">Evidencia fotográfica</div>
                    <div class="photo-box">
                        @if(!empty($reaccion['ruta_foto_evidencia']))
                            <img src="http://localhost:3000/uploads/{{ ltrim($reaccion['ruta_foto_evidencia'], '/') }}" alt="Evidencia fotográfica">
                        @else
                            <span class="text-muted fw-bold">No hay evidencia fotográfica.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CONSECUENCIAS --}}
    <div class="glass p-4">
        <h5 class="section-title">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> Consecuencias Registradas
        </h5>

        @if(!empty($consecuencias))
            <div class="table-responsive">
                <table class="table table-glass align-middle mb-0">
                    <thead style="background: rgba(255, 255, 255, 0.1);">
                        <tr>
                            <th>Descripción de consecuencia</th>
                            <th style="width:180px;">Gravedad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($consecuencias as $c)
                            @php
                                $g = strtoupper($c['gravedad'] ?? 'LEVE');
                                $bg = $g === 'GRAVE' ? 'danger' : ($g === 'MODERADA' ? 'warning' : 'success');
                            @endphp
                            <tr>
                                <td class="fw-bold">{{ $c['descripcion_consecuencia'] ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $bg }} rounded-pill px-3 py-2 fw-bold">
                                        {{ $g }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">No hay consecuencias registradas.</div>
        @endif
    </div>

</div>

@endsection
