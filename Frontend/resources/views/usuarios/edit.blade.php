@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="px-4 px-md-5">
    {{-- Alertas de Error de Laravel --}}
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show rounded-4">
            <ul class="mb-0">
                @foreach($errors->all() as $error) <li><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</li> @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-table shadow-sm border-0 p-4" style="border-radius: 15px;">
        <div class="mb-4">
            <h5 class="fw-bold text-dark">
                <i class="fas fa-user-edit me-2 text-warning"></i> 
                MODIFICANDO A: <span class="text-muted">{{ strtoupper($usuario['usuario']) }}</span>
            </h5>
            <hr class="text-muted opacity-25">
        </div>

        <form action="{{ route('usuarios.update', $usuario['id_usuario']) }}" method="POST" id="form-editar-usuario" data-unsaved-form="true">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-muted">Nombre de Usuario <span class="text-danger">*</span></label>
                    <input type="text" name="usuario" id="usuario" class="form-control bg-light border-0 shadow-sm" 
                              value="{{ old('usuario', $usuario['usuario']) }}" required minlength="{{ $min_usuario ?? 1 }}" maxlength="{{ $max_usuario ?? 50 }}" 
                           autocomplete="off" style="text-transform: uppercase;">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-muted">Correo Electrónico <span class="text-danger">*</span></label>
                    <input type="email" name="correo" id="correo" class="form-control bg-light border-0 shadow-sm" 
                           value="{{ old('correo', $usuario['correo']) }}" required maxlength="50" 
                           autocomplete="off" style="text-transform: lowercase;">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-muted">Estado del Usuario <span class="text-danger">*</span></label>
                    <select name="estado" class="form-select bg-light border-0 shadow-sm" required>
                        <option value="ACTIVO" {{ old('estado', $usuario['estado']) == 'ACTIVO' ? 'selected' : '' }}>ACTIVO</option>
                        <option value="INACTIVO" {{ old('estado', $usuario['estado']) == 'INACTIVO' ? 'selected' : '' }}>INACTIVO</option>
                        <option value="BLOQUEADO" {{ old('estado', $usuario['estado']) == 'BLOQUEADO' ? 'selected' : '' }}>BLOQUEADO</option>
                        <option value="NUEVO" {{ old('estado', $usuario['estado']) == 'NUEVO' ? 'selected' : '' }}>NUEVO</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-muted">Rol</label>
                    <select name="rol" class="form-select bg-light border-0 shadow-sm">
                        <option value="" {{ old('rol', $usuario['id_rol'] ?? '') == '' ? 'selected' : '' }}>SIN ROL</option>
                        @foreach($roles as $rol)
                            @if(strtoupper(trim($rol['nombre'] ?? '')) !== 'ADMINISTRADOR')
                                <option value="{{ $rol['id_rol'] }}" {{ old('rol', $usuario['id_rol'] ?? '') == $rol['id_rol'] ? 'selected' : '' }}>
                                    {{ strtoupper($rol['nombre']) }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-muted">Nombres <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" id="nombre" class="form-control bg-light border-0 shadow-sm" 
                           value="{{ old('nombre', $usuario['nombre']) }}" required style="text-transform: uppercase;">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-muted">Apellidos <span class="text-danger">*</span></label>
                    <input type="text" name="apellido" id="apellido" class="form-control bg-light border-0 shadow-sm" 
                           value="{{ old('apellido', $usuario['apellido']) }}" required style="text-transform: uppercase;">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-muted">Teléfono</label>
                    <input type="text" name="telefono" id="telefono" class="form-control bg-light border-0 shadow-sm" 
                           value="{{ old('telefono', $usuario['telefono']) }}" placeholder="0000-0000">
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4 gap-2">
                <a href="{{ route('usuarios.lista') }}" class="btn btn-light border fw-bold px-4 rounded-pill shadow-sm" data-cancel-unsaved="true">
                    <i class="fas fa-times me-2"></i> Cancelar
                </a>
                <button type="submit" class="btn text-white fw-bold px-4 rounded-pill shadow-sm btn-save" style="background-color: #f59e0b;">
                    <i class="fas fa-sync-alt me-2"></i> Actualizar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('form-editar-usuario');
        let formChanged = false;

        // El aviso de cambios sin guardar se maneja globalmente desde layouts/app.blade.php.
        form.addEventListener('input', () => formChanged = true);

        // Desactivar alerta al enviar con éxito
        form.addEventListener('submit', () => formChanged = false);

        // --- VALIDACIONES DE INPUTS EN TIEMPO REAL ---

        // Usuario: Mayúsculas y sin espacios
        document.getElementById('usuario').addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9_]/g, '');
        });

        // Correo: Minúsculas
        document.getElementById('correo').addEventListener('input', function() {
            this.value = this.value.toLowerCase().replace(/\s/g, '');
        });

        // Nombres/Apellidos: Mayúsculas y solo letras
        const sanitizeText = function() {
            this.value = this.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ\s]/g, '');
        };
        document.getElementById('nombre').addEventListener('input', sanitizeText);
        document.getElementById('apellido').addEventListener('input', sanitizeText);

        // Teléfono: Solo números
        document.getElementById('telefono').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
</script>
@endsection
