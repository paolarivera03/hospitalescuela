@extends('layouts.app')

@section('title', 'Backups - Hospital Escuela')
@section('header', 'Backup BD')

@push('styles')
<style>
    .card-table { border-radius: 12px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.02); overflow: hidden; background-color: #ffffff; }
    .table thead th { color: #374151; font-weight: 700; font-size: 0.95rem; padding: 15px 20px; border-bottom: 2px solid #edf2f7; background-color: #ffffff; }
    .table tbody td { padding: 15px 20px; vertical-align: middle; border-bottom: 1px solid #f3f4f6; font-size: 0.9rem; }
    .page-item.active .page-link { background-color: #0d6efd; color: #ffffff; }
    .btn-backup { border-radius: 8px; font-weight: 600; font-size: 0.85rem; padding: 8px 16px; transition: all .15s; }
    .filter-panel { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; margin-bottom: 20px; }
    .filter-panel .form-label { font-size: 0.8rem; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .4px; }
    .select-round { border-radius: 8px; border: 1px solid #d1d5db; padding: 5px 10px; font-size: 0.85rem; background-color: #fff; }
</style>
@endpush

@section('content')
<div class="p-4 p-md-5">

    {{-- ── CABECERA ────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1f2937;">Backups de la base de datos</h4>
            <p class="text-muted mb-0">Genera respaldos SQL y descárgalos desde el sistema.</p>
        </div>

        <div class="d-flex flex-wrap gap-2 align-items-center">

            {{-- Reporte PDF --}}
            <a href="{{ route('backup.reporte', request()->query()) }}"
               class="btn btn-success btn-backup shadow-sm"
               title="Generar reporte de backups"
               target="_blank"
               rel="noopener">
                <i class="fas fa-file-pdf me-2"></i>Reporte
            </a>

            {{-- Filtros --}}
            <button type="button"
                    class="btn btn-outline-secondary btn-backup shadow-sm"
                    data-bs-toggle="collapse"
                    data-bs-target="#filtrosPanel"
                    aria-expanded="{{ !empty($filtros) ? 'true' : 'false' }}"
                    title="Mostrar / ocultar filtros">
                <i class="fas fa-filter me-2"></i>Filtros
                @if(!empty($filtros))
                    <span class="badge bg-primary ms-1">{{ count($filtros) }}</span>
                @endif
            </button>

            {{-- Generar Backup --}}
            <form action="{{ route('backup.create') }}" method="POST" class="m-0">
                @csrf
                <button type="submit"
                        class="btn btn-primary btn-backup shadow-sm"
                        onclick="return confirm('¿Desea generar un nuevo backup de la base de datos?')">
                    <i class="fas fa-database me-2"></i>Generar Backup
                </button>
            </form>

        </div>
    </div>

    {{-- ── ALERTAS ─────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── PANEL DE FILTROS ───────────────────────────────────────── --}}
    <div class="collapse {{ !empty($filtros) ? 'show' : '' }}" id="filtrosPanel">
        <div class="filter-panel">
            <form method="GET" action="{{ route('backup.index') }}" id="formFiltros">
                <input type="hidden" name="page" value="1">
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha" class="form-control form-control-sm"
                               value="{{ request('fecha') }}">
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                    <a href="{{ route('backup.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                        <i class="fas fa-times me-1"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── CONTROLES SHOW ─────────────────────────────────────────── --}}
    <div class="d-flex justify-content-start align-items-center gap-3 mb-2 px-1">
        <div class="d-flex align-items-center">
            <span class="text-muted fw-bold small me-2">MOSTRAR:</span>
            <form method="GET" action="{{ route('backup.index') }}">
                <input type="hidden" name="page" value="1">
                @foreach(request()->except(['per_page','page']) as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <select name="per_page" class="select-round" onchange="this.form.submit()">
                    <option value="5"  {{ (int) request('per_page', 10) === 5  ? 'selected' : '' }}>5</option>
                    <option value="10" {{ (int) request('per_page', 10) === 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ (int) request('per_page', 10) === 15 ? 'selected' : '' }}>15</option>
                </select>
            </form>
        </div>
        @if(!empty($filtros))
            <span class="text-muted small">
                Mostrando resultados filtrados
                <a href="{{ route('backup.index') }}" class="text-danger ms-1 small fw-bold">
                    <i class="fas fa-times-circle"></i> Quitar filtros
                </a>
            </span>
        @endif
    </div>

    {{-- ── TABLA ───────────────────────────────────────────────────── --}}
    <div class="card card-table">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Archivo</th>
                        <th>Fecha</th>
                        <th>Tamaño</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $backup)
                        <tr>
                            <td class="fw-bold text-dark">{{ $backup['fileName'] ?? '' }}</td>
                            <td class="text-muted">
                                {{ isset($backup['createdAt']) ? \Carbon\Carbon::parse($backup['createdAt'])->format('d/m/Y H:i:s') : '' }}
                            </td>
                            <td class="text-muted">{{ $backup['sizeMb'] ?? '0' }} MB</td>
                            <td class="text-center">
                                @if(!empty($backup['fileName']))
                                    <form action="{{ route('backup.restore', ['fileName' => $backup['fileName']]) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('¿Desea restaurar este backup? Esta acción reemplazará la base de datos actual.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill fw-bold px-3">
                                            <i class="fas fa-undo me-1"></i> Restaurar
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                No hay backups generados todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── PAGINACIÓN ─────────────────────────────────────────────── --}}
    @if(is_object($backups) && method_exists($backups, 'currentPage') && method_exists($backups, 'lastPage'))
        @php
            $currentPage = (int) $backups->currentPage();
            $totalPages  = (int) $backups->lastPage();
            $startPage   = max(1, $currentPage - 2);
            $endPage     = min($totalPages, $currentPage + 2);
        @endphp

        @if($totalPages > 1)
            <div class="d-flex justify-content-end align-items-center mt-4 mb-2 px-2">
                <span class="text-muted small fw-bold me-3">Página {{ $currentPage }} de {{ $totalPages }}</span>
                <nav>
                    <ul class="pagination pagination-sm mb-0 shadow-sm" style="border-radius:8px;overflow:hidden;">
                        <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                            <a class="page-link border-0 text-dark fw-bold px-3"
                               href="{{ route('backup.index', array_merge(request()->query(), ['page' => $currentPage - 1])) }}">
                                <i class="fas fa-chevron-left me-1"></i> Anterior
                            </a>
                        </li>
                        @if($startPage > 1)
                            <li class="page-item">
                                <a class="page-link border-0 text-dark fw-bold px-3"
                                   href="{{ route('backup.index', array_merge(request()->query(), ['page' => 1])) }}">1</a>
                            </li>
                            @if($startPage > 2)
                                <li class="page-item disabled"><span class="page-link border-0 text-dark fw-bold px-3">...</span></li>
                            @endif
                        @endif
                        @for($page = $startPage; $page <= $endPage; $page++)
                            <li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
                                <a class="page-link border-0 text-dark fw-bold px-3"
                                   href="{{ route('backup.index', array_merge(request()->query(), ['page' => $page])) }}">{{ $page }}</a>
                            </li>
                        @endfor
                        @if($endPage < $totalPages)
                            @if($endPage < $totalPages - 1)
                                <li class="page-item disabled"><span class="page-link border-0 text-dark fw-bold px-3">...</span></li>
                            @endif
                            <li class="page-item">
                                <a class="page-link border-0 text-dark fw-bold px-3"
                                   href="{{ route('backup.index', array_merge(request()->query(), ['page' => $totalPages])) }}">{{ $totalPages }}</a>
                            </li>
                        @endif
                        <li class="page-item {{ $currentPage >= $totalPages ? 'disabled' : '' }}">
                            <a class="page-link border-0 text-dark fw-bold px-3"
                               href="{{ route('backup.index', array_merge(request()->query(), ['page' => $currentPage + 1])) }}">
                                Siguiente <i class="fas fa-chevron-right ms-1"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        @endif
    @endif

</div>
@endsection

@push('styles')
<style>
    .backup-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        overflow: hidden;
        background-color: #ffffff;
    }

    .backup-title {
        font-weight: 800;
        color: #111827;
        letter-spacing: 0.2px;
    }

    .backup-subtitle {
        color: #6b7280;
        font-weight: 700;
        font-size: 0.9rem;
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
</style>
@endpush

@section('content')
<div class="p-4 p-md-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="backup-title mb-1">Backups de la base de datos</h4>
            <p class="backup-subtitle mb-0">Genera respaldos SQL y descárgalos desde el sistema.</p>
        </div>

        <form action="{{ route('backup.create') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-primary rounded-pill fw-bold px-4 py-2 shadow-sm">
                <i class="fas fa-database me-2"></i> Generar Backup
            </button>
        </form>
    </div>

    <div class="card backup-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Archivo</th>
                        <th>Fecha</th>
                        <th>Tamaño</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $backup)
                        <tr>
                            <td class="fw-bold text-dark">{{ $backup['fileName'] ?? '' }}</td>
                            <td class="text-muted">{{ isset($backup['createdAt']) ? \Carbon\Carbon::parse($backup['createdAt'])->format('d/m/Y H:i:s') : '' }}</td>
                            <td class="text-muted">{{ $backup['sizeMb'] ?? '0' }} MB</td>
                            <td class="text-center">
                                @if(!empty($backup['fileName']))
                                    <a href="{{ route('backup.download', ['fileName' => $backup['fileName']]) }}"
                                       class="btn btn-sm btn-outline-primary rounded-pill fw-bold px-3">
                                        <i class="fas fa-download me-1"></i> Descargar
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                No hay backups generados todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

