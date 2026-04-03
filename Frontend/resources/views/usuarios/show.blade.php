@extends('layouts.app')

@section('title', 'Detalle de Usuario')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="fw-bold mb-0 text-dark">
            <i class="fas fa-eye me-2 text-cyan"></i> Ficha de Usuario
        </h4>
        <div class="d-flex gap-2">
            <a href="{{ route('usuarios.lista') }}" class="btn btn-light border rounded-pill px-4 shadow-sm fw-bold">
                <i class="fas fa-arrow-left me-2"></i> Volver a la Lista
            </a>
            
        </div>
    </div>

    <div class="card border-0 shadow-sm p-4" style="border-radius: 15px; background-color: white;">
        
        <h6 class="fw-bold text-primary mb-3"><i class="fas fa-id-card me-2"></i>Identificación del Sistema</h6>
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold text-muted small">ID Usuario</label>
                <input type="text" class="form-control bg-light fw-bold text-success border-0 px-3 py-2" value="#{{ $usuario['id_usuario'] }}" disabled readonly>
            </div>
            <div class="col-md-5 mb-3">
                <label class="form-label fw-bold text-muted small">Nombre de Usuario (Login)</label>
                <input type="text" class="form-control bg-light fw-bold text-dark border-0 px-3 py-2" value="{{ $usuario['usuario'] }}" disabled readonly>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold text-muted small">Estado de Cuenta</label>
                <div class="pt-1">
                    @if($usuario['estado'] == 'ACTIVO')
                        <span class="badge p-2 px-3 rounded-pill" style="background-color: #d1fae5; color: #065f46; font-size: 0.8rem;">ACTIVO</span>
                    @elseif($usuario['estado'] == 'INACTIVO')
                        <span class="badge p-2 px-3 rounded-pill" style="background-color: #fee2e2; color: #991b1b; font-size: 0.8rem;">INACTIVO</span>
                    @elseif($usuario['estado'] == 'NUEVO')
                        <span class="badge p-2 px-3 rounded-pill" style="background-color: #e0f2fe; color: #075985; font-size: 0.8rem;">NUEVO</span>
                    @else
                        <span class="badge p-2 px-3 rounded-pill bg-secondary" font-size: 0.8rem;">{{ $usuario['estado'] }}</span>
                    @endif
                </div>
            </div>
        </div>

        <hr class="text-muted my-4 opacity-25">

        <h6 class="fw-bold text-danger mb-3"><i class="fas fa-user-tag me-2"></i>Datos Personales y Rol</h6>
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold text-muted small">Nombre Completo</label>
                <input type="text" class="form-control bg-light border-0 px-3 py-2 text-uppercase" value="{{ $usuario['nombre'] }} {{ $usuario['apellido'] }}" disabled readonly>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold text-muted small">Correo Electrónico</label>
                <input type="email" class="form-control bg-light border-0 px-3 py-2 text-lowercase" value="{{ $usuario['correo'] }}" disabled readonly>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold text-muted small">Rol Asignado</label>
                <input type="text" class="form-control bg-light fw-bold text-indigo border-0 px-3 py-2" value="{{ $usuario['rol_nombre'] ?? 'SIN ROL ASIGNADO' }}" disabled readonly style="color: #4f46e5;">
            </div>
            @if(isset($usuario['fecha_creacion']))
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold text-muted small">Miembro Desde</label>
                <input type="text" class="form-control bg-light border-0 px-3 py-2" value="{{ \Carbon\Carbon::parse($usuario['fecha_creacion'])->format('d/m/Y h:i A') }}" disabled readonly>
            </div>
            @endif
        </div>

        </div>
@endsection
