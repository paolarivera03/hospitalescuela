<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios - Hospital Escuela</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { width: 260px; height: 100vh; background: white; position: fixed; border-right: 1px solid #eee; }
        .main-content { margin-left: 260px; padding: 20px; }
        .nav-item-custom.active { background-color: #5eead4; color: black; font-weight: bold; border-radius: 50px; }
        .card-table { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

    <div class="sidebar p-3">
        <h5 class="text-center fw-bold mb-4">MENÚ</h5>
        <ul class="nav flex-column gap-2">
            <a href="{{ route('dashboard') }}" class="nav-link text-dark p-3">Inventario</a>
            <a href="{{ route('usuarios.index') }}" class="nav-link nav-item-custom active p-3">Mantenimiento Usuario</a>
            <a href="{{ route('bitacora') }}" class="nav-link text-dark p-3">Bitácora</a>
        </ul>
    </div>

    <div class="main-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Gestión de Usuarios</h4>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalCrear">
                <i class="fas fa-plus"></i> Nuevo
            </button>
        </div>

        <div class="card card-table">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Usuario</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $u)
                    <tr>
                        <td class="fw-bold text-primary">{{ $u['usuario'] }}</td>
                        <td>{{ $u['nombre'] }} {{ $u['apellido'] ?? '' }}</td>
                        <td>{{ $u['correo'] }}</td>
                        <td><span class="badge bg-info text-dark">{{ $u['rol'] }}</span></td>
                        <td>
                            <span class="badge {{ ($u['estado'] ?? 'Activo') == 'Activo' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $u['estado'] ?? 'Activo' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEditar{{ $u['id_usuario'] }}">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form action="{{ route('usuarios.destroy', $u['id_usuario']) }}" method="POST" onsubmit="return confirm('¿Desea cambiar el estado del usuario?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm {{ ($u['estado'] ?? 'Activo') == 'Activo' ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                        <i class="fas {{ ($u['estado'] ?? 'Activo') == 'Activo' ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalEditar{{ $u['id_usuario'] }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('usuarios.update', $u['id_usuario']) }}" method="POST" class="modal-content" data-unsaved-form="true">
                                @csrf
                                @method('PUT')
                                <div class="modal-header"><h5>Editar Usuario</h5></div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label>Usuario</label>
                                        <input type="text" name="usuario" class="form-control" value="{{ $u['usuario'] }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label>Correo</label>
                                        <input type="email" name="correo" class="form-control" value="{{ $u['correo'] }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label>Rol</label>
                                        <select name="rol" class="form-select">
                                            <option value="ADMINISTRADOR" {{ $u['rol'] == 'ADMINISTRADOR' ? 'selected' : '' }}>ADMINISTRADOR</option>
                                            <option value="MEDICO" {{ $u['rol'] == 'MEDICO' ? 'selected' : '' }}>MEDICO</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Actualizar Cambios</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalCrear" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('usuarios.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header"><h5>Nuevo Usuario</h5></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Usuario</label>
                        <input type="text" name="usuario" class="form-control" maxlength="50" required>
                    </div>
                    <div class="mb-3">
                        <label>Correo</label>
                        <input type="email" name="correo" class="form-control" maxlength="50" required>
                    </div>
                    <div class="mb-3">
                        <label>Contraseña</label>
                        <input type="password" name="contrasena" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Rol</label>
                        <select name="rol" class="form-select">
                            <option value="ADMINISTRADOR">ADMINISTRADOR</option>
                            <option value="MEDICO">MEDICO</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
