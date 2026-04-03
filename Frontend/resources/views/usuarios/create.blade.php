@extends('layouts.app')

@section('title', 'Nuevo Usuario')

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
        <form action="{{ route('usuarios.store') }}" method="POST" id="form-usuario" data-unsaved-form="true">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-muted">Nombre de Usuario <span class="text-danger">*</span></label>
                    <input type="text" name="usuario" id="usuario" class="form-control bg-light border-0 shadow-sm" 
                              value="{{ old('usuario') }}" required minlength="{{ $min_usuario ?? 1 }}" maxlength="{{ $max_usuario ?? 50 }}" autocomplete="off" 
                           placeholder="EJ. AMARTINEZ" style="text-transform: uppercase;">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-muted">Correo Electrónico <span class="text-danger">*</span></label>
                    <input type="email" name="correo" id="correo" class="form-control bg-light border-0 shadow-sm" 
                           value="{{ old('correo') }}" required maxlength="50" autocomplete="off" 
                           placeholder="ejemplo@hospital.com" style="text-transform: lowercase;">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-muted">Rol del Sistema</label>
                    <select name="rol" class="form-select bg-light border-0 shadow-sm">
                        <option value="" {{ old('rol') ? '' : 'selected' }}>SIN ROL</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol['id_rol'] }}" {{ old('rol') == $rol['id_rol'] ? 'selected' : '' }}>
                                {{ strtoupper($rol['nombre']) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-muted">Nombres <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" id="nombre" class="form-control bg-light border-0 shadow-sm" 
                           value="{{ old('nombre') }}" required style="text-transform: uppercase;">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-muted">Apellidos <span class="text-danger">*</span></label>
                    <input type="text" name="apellido" id="apellido" class="form-control bg-light border-0 shadow-sm" 
                           value="{{ old('apellido') }}" required style="text-transform: uppercase;">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-muted">Teléfono</label>
                    <input type="text" name="telefono" id="telefono" class="form-control bg-light border-0 shadow-sm" 
                           value="{{ old('telefono') }}" placeholder="0000-0000">
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4 gap-2">
                <a href="{{ route('usuarios.lista') }}" class="btn btn-light border fw-bold px-4 rounded-pill shadow-sm btn-cancelar">
                    <i class="fas fa-times me-2"></i> Cancelar
                </a>
                <button type="submit" class="btn text-white fw-bold px-4 rounded-pill shadow-sm btn-save" style="background-color: #10b981;">
                    <i class="fas fa-save me-2"></i> Guardar
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
        const form = document.getElementById('form-usuario');
        let formChanged = false;

        // El aviso de cambios sin guardar se maneja globalmente desde layouts/app.blade.php.
        form.addEventListener('input', () => formChanged = true);

        // Resetear bandera al enviar
        form.addEventListener('submit', () => formChanged = false);

        // --- VALIDACIONES DE INPUTS ---
        
        // Usuario: Mayúsculas y sin caracteres raros
        document.getElementById('usuario').addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9_]/g, '');
        });

        // Correo: Minúsculas y sin espacios
        document.getElementById('correo').addEventListener('input', function() {
            this.value = this.value.toLowerCase().replace(/\s/g, '');
        });

        // Nombres/Apellidos: Solo letras y mayúsculas
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
