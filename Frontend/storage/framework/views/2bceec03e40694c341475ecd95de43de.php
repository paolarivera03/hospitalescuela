<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" type="image/png" href="<?php echo e(asset('login-assets/images/logo-circle.png')); ?>"/>

    <title><?php echo $__env->yieldContent('title', 'Hospital Escuela'); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Nunito:wght@400;600;700;800&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-primary: #222c5e;
            --brand-primary-dark: #0a4898;
            --brand-secondary: #222c5e;
            --brand-secondary-dark: #0a4898;
            --brand-accent: #222c5e;
            --brand-danger: #ef4444;
            --btn-radius: 999px;
            --btn-font-weight: 800;
            --text-muted: #4b5563;
            --bg-surface: #ffffff;
            --border-100: #e5e7eb;
            --shadow-sm: 0 2px 10px rgba(0,0,0,0.02);
            --card-radius: 12px;
            --font-title: 'Montserrat', sans-serif;
            --font-subtitle: 'Poppins', sans-serif;
            --font-body: 'Nunito', sans-serif;
        }

        body {
            font-family: var(--font-body);
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        /* Jerarquia tipografica global */
        p,
        span,
        small,
        li,
        a,
        label,
        input,
        textarea,
        select,
        button,
        .form-control,
        .form-select,
        .table tbody td,
        .modal-body,
        .card-text {
            font-family: var(--font-body);
        }

        h1,
        h2,
        .modal-title,
        .top-header-ribbon h5,
        .unsaved-toast-title,
        .farewell-title,
        .profile-menu-user .fw-bold {
            font-family: var(--font-title);
            letter-spacing: 0.01em;
        }

        h3,
        h4,
        h5,
        h6,
        .table thead th,
        .form-label,
        .card-header,
        .nav-item-custom,
        .nav-subitem,
        .btn,
        .profile-tab-btn {
            font-family: var(--font-subtitle);
        }

        .sidebar {
            width: 260px;
            height: 100vh;
            background-color: #ffffff;
            position: fixed;
            top: 0;
            left: 0;
            border-right: 1px solid #e9ecef;
            z-index: 1040;
            transition: all 0.3s ease-in-out;
        }

        .sidebar.collapsed { left: -260px; }

        .nav-item-custom {
            color: #222c5e;
            border-radius: 50px;
            font-weight: 700;
            padding: 12px 20px;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
        }

        .nav-item-custom:hover { background-color: #0a4898; color: #ffffff; }
        .nav-item-custom.active { background-color: #222c5e; color: #ffffff; }

        .nav-submenu {
            margin-left: 14px;
            padding-left: 12px;
            border-left: 2px dashed #d1d5db;
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-top: 6px;
        }

        .nav-subitem {
            color: #222c5e;
            border-radius: 12px;
            font-weight: 700;
            padding: 8px 12px;
            text-decoration: none;
            font-size: 0.88rem;
            transition: all 0.2s;
        }

        .nav-subitem:hover {
            background-color: #0a4898;
            color: #ffffff;
        }

        .nav-subitem.active {
            background-color: #222c5e;
            color: #ffffff;
        }

        .icon-sidebar {
            width: 25px;
            text-align: center;
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            width: calc(100% - 260px);
            transition: all 0.3s ease-in-out;
        }

        .main-content.expanded { margin-left: 0; width: 100%; }

        .top-header-ribbon {
            background-color: var(--brand-primary);
            color: #ffffff;
            margin-top: 12px;
        }

        .top-header-ribbon #btn-toggle,
        .top-header-ribbon h5,
        .top-header-ribbon .user-name {
            color: #ffffff !important;
        }

        .profile-initials {
            width: 38px;
            height: 38px;
            border: 2px solid #ffffff;
            background-color: transparent;
            color: #ffffff;
            font-size: 0.9rem;
        }

        .content-shell {
            padding: 20px 24px 24px;
        }

        #sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            background-color: rgba(0,0,0,0.5);
            z-index: 1030;
        }

        @media (max-width: 768px) {
            .sidebar { left: -260px; }
            .sidebar.show { left: 0; }
            .main-content { margin-left: 0; width: 100%; }
            #sidebar-overlay.show { display: block; }
        }

        /* ------------------------------------ */
        /* Componentes compartidos de diseño */
        /* ------------------------------------ */

        .card-table {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            overflow: hidden;
            background-color: #ffffff;
        }

        .glass {
            background-color: #ffffff;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: var(--card-radius);
            box-shadow: var(--shadow-sm);
            color: #1f2937;
        }

        .glass a,
        .glass span,
        .glass p,
        .glass h3,
        .glass h4,
        .glass h5 {
            color: inherit;
        }

        .table thead th {
            color: #ffffff;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 15px 20px;
            border-bottom: 1px solid #1a234b;
            background-color: var(--brand-primary);
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .table tbody td {
            padding: 15px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.9rem;
            color: #1f2937;
            background-color: #ffffff;
        }

        .table tbody tr:hover td {
            background-color: #f8fbff;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
            border: 1px solid #d7ddea;
            background: #fff;
        }

        .btn-action i {
            font-size: 0.85rem;
            line-height: 1;
        }

        /* Estandar global de botones */
        .btn {
            border-radius: var(--btn-radius) !important;
            font-weight: var(--btn-font-weight) !important;
            letter-spacing: 0.01em;
            min-height: 40px;
            padding: 0.5rem 1.1rem;
            border-width: 1px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
            transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease, border-color 0.15s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.14);
        }

        .btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.12);
        }

        .btn:focus-visible {
            box-shadow: 0 0 0 0.22rem rgba(34, 44, 94, 0.22);
        }

        .btn:disabled,
        .btn.disabled {
            opacity: 0.72;
            transform: none;
            box-shadow: none;
        }

        .btn-sm {
            min-height: 34px;
            padding: 0.32rem 0.85rem;
        }

        .btn-lg {
            min-height: 46px;
            padding: 0.65rem 1.35rem;
        }

        .btn-action,
        .btn-action.btn,
        .btn-action.btn-sm {
            min-height: 36px !important;
            padding: 0 !important;
            border-radius: 8px !important;
            box-shadow: none !important;
            transform: none !important;
        }

        .btn-action:disabled,
        .btn-action.disabled {
            background-color: #f3f4f6 !important;
            border-color: #e5e7eb !important;
            color: #9ca3af !important;
            opacity: 1;
        }

        .btn-edit { color: #ea8a00; }
        .btn-edit:hover { background-color: #fff4db; color: #d97706 !important; border-color: #f6c66c; }
        .btn-delete:hover { background-color: #fee2e2; color: #dc2626 !important; border-color: #fca5a5; }
        .btn-view { color: #38bdf8; }
        .btn-view:hover { background-color: #e0f2fe; border-color: #7dd3fc; color: #0284c7; }

        .btn-primary {
            background-color: var(--brand-primary);
            border-color: var(--brand-primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--brand-primary-dark);
            border-color: var(--brand-primary-dark);
        }

        .btn-secondary {
            background-color: var(--brand-secondary);
            border-color: var(--brand-secondary);
            color: white;
        }

        .btn-secondary:hover {
            background-color: var(--brand-secondary-dark);
            border-color: var(--brand-secondary-dark);
        }

        .btn-dark {
            background-color: var(--brand-primary);
            border-color: var(--brand-primary);
            color: white;
        }

        .btn-dark:hover {
            background-color: var(--brand-primary-dark);
            border-color: var(--brand-primary-dark);
            color: white;
        }

        .btn-outline-primary {
            color: var(--brand-primary);
            border-color: var(--brand-primary);
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus,
        .btn-outline-primary:active {
            background-color: var(--brand-primary-dark);
            border-color: var(--brand-primary-dark);
            color: white;
        }

        .btn-danger {
            background-color: var(--brand-danger);
            border-color: var(--brand-danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: rgba(239, 68, 68, 0.9);
            border-color: rgba(239, 68, 68, 0.9);
        }

        .btn-report { color: var(--brand-primary); }
        .btn-report:hover { background-color: rgba(34, 44, 94, 0.12); border-color: rgba(34, 44, 94, 0.32); color: #1c2652 !important; }

        .btn-permissions { color: var(--brand-primary); }
        .btn-permissions:hover { background-color: rgba(10, 72, 152, 0.12); border-color: rgba(10, 72, 152, 0.25); color: var(--brand-primary-dark) !important; }

        .select-round {
            border: 1px solid #e5e7eb;
            border-radius: 50px;
            padding: 5px 15px;
            font-weight: 700;
            color: #6b7280;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            background-color: white;
        }

        .select-round:hover { border-color: #0a4898; }

        .btn-search-theme {
            background-color: #222c5e !important;
            color: #fff !important;
            border: none !important;
        }

        .btn-search-theme:hover,
        .btn-search-theme:focus {
            background-color: #0a4898 !important;
            color: #fff !important;
        }

        .profile-trigger {
            border: none;
            background: transparent;
            padding: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile-trigger .user-name {
            color: #111827;
        }

        .profile-trigger:hover .user-name {
            color: var(--brand-primary-dark);
        }

        .profile-tab-btn {
            border: 1px solid #e5e7eb;
            border-radius: 999px;
            padding: 8px 14px;
            font-weight: 700;
            color: #4b5563;
            background: #fff;
        }

        .profile-tab-btn.active {
            background-color: rgba(6, 182, 212, 0.16);
            border-color: rgba(6, 182, 212, 0.3);
            color: #0f172a;
        }

        .profile-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            background: #fff;
        }

        .profile-action-btn {
            width: 100%;
            border-radius: 999px;
            font-weight: 800;
            padding: 12px 16px;
        }

        .farewell-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.28);
            backdrop-filter: blur(1.5px);
            align-items: center;
            justify-content: center;
        }

        .farewell-overlay.show {
            display: flex;
        }

        .farewell-card {
            width: min(360px, calc(100vw - 32px));
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 22px 24px;
            text-align: center;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.16);
        }

        .farewell-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        .farewell-subtitle {
            margin: 6px 0 0;
            font-size: 13px;
            color: #475569;
        }

        .unsaved-toast {
            position: fixed;
            top: 50%;
            left: 50%;
            z-index: 2000;
            width: min(460px, calc(100vw - 24px));
            background: #fff7ed;
            border: 1px solid #fdba74;
            border-radius: 16px;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.16);
            padding: 14px 16px;
            color: #9a3412;
            transform: translate(-50%, calc(-50% + 20px));
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .unsaved-toast.show {
            opacity: 1;
            transform: translate(-50%, -50%);
            pointer-events: auto;
        }

        .unsaved-toast-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.18);
            z-index: 1999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .unsaved-toast-backdrop.show {
            opacity: 1;
            pointer-events: auto;
        }

        .unsaved-toast-title {
            font-weight: 900;
            margin-bottom: 4px;
        }

        .unsaved-toast-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 12px;
        }

        @media (max-width: 576px) {
            .content-shell {
                padding: 16px 12px 20px;
            }

            .unsaved-toast {
                width: calc(100vw - 24px);
            }
        }

        .profile-menu-dropdown {
            position: fixed;
            z-index: 1080;
            width: min(320px, calc(100vw - 24px));
            max-width: min(320px, calc(100vw - 24px));
            background: #ffffff;
            border: 1px solid var(--border-100);
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.2);
            padding: 14px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(-8px);
            transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease;
        }

        .profile-menu-dropdown.show {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0);
        }

        .profile-menu-user {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 8px;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-100);
        }

        .profile-menu-user .profile-initials {
            color: #222c5e;
            border-color: #dbe3f0;
        }

        @media (max-width: 576px) {
            .profile-menu-dropdown {
                width: calc(100vw - 24px);
                max-width: calc(100vw - 24px);
            }
        }

        /* Buscador estandar global */
        .search-pill {
            border-radius: 999px;
            border: 1px solid #d1d5db;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }
        .search-pill .form-control:focus { box-shadow: none; }
        .search-pill .input-group-text { color: #6b7280; }
    </style>

    <?php echo $__env->yieldPushContent('styles'); ?>

    <style>
        /*
         * Estandar visual global de tablas (se declara al final para sobreescribir
         * estilos locales de cada vista y mantener consistencia en todos los modulos).
         */
        :root {
            --table-unified-header-bg: var(--brand-primary);
            --table-unified-header-fg: #ffffff;
            --table-unified-body-bg: #ffffff;
            --table-unified-border: #e5e7eb;
            --table-unified-radius: 12px;
        }

        .main-content .table-responsive {
            border: 1px solid var(--table-unified-border);
            border-radius: var(--table-unified-radius);
            background: var(--table-unified-body-bg);
            overflow: hidden;
        }

        .main-content .card-table,
        .main-content .card:has(.table) {
            border-radius: var(--table-unified-radius);
            overflow: hidden;
            background: var(--table-unified-body-bg);
        }

        .main-content table.table,
        .main-content table[class*="table"] {
            margin-bottom: 0;
            background: var(--table-unified-body-bg);
            border-collapse: separate;
            border-spacing: 0;
        }

        .main-content table.table thead th,
        .main-content table[class*="table"] thead th {
            background: var(--table-unified-header-bg) !important;
            color: var(--table-unified-header-fg) !important;
            border-bottom: 1px solid #1a234b !important;
            font-weight: 700 !important;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .main-content table.table tbody td,
        .main-content table[class*="table"] tbody td {
            background: var(--table-unified-body-bg) !important;
            color: #1f2937 !important;
            border-bottom: 1px solid #eef2f7 !important;
        }

        .main-content table.table tbody tr:nth-child(odd) td,
        .main-content table.table tbody tr:nth-child(even) td,
        .main-content table[class*="table"] tbody tr:nth-child(odd) td,
        .main-content table[class*="table"] tbody tr:nth-child(even) td {
            background: var(--table-unified-body-bg) !important;
        }

        .main-content table.table tbody tr:hover td,
        .main-content table[class*="table"] tbody tr:hover td {
            background: #f8fbff !important;
        }

        /*
         * Estandar de boton Ver en Acciones.
         * Se define al final para sobreescribir estilos locales por modulo.
         */
        .main-content .btn-action.btn-view,
        .main-content .btn-action[class*="btn-ver"] {
            color: #2563eb !important;
            border-color: #93c5fd !important;
            background-color: #eff6ff !important;
        }

        .main-content .btn-action.btn-view:hover,
        .main-content .btn-action.btn-view:focus,
        .main-content .btn-action[class*="btn-ver"]:hover,
        .main-content .btn-action[class*="btn-ver"]:focus {
            color: #1d4ed8 !important;
            border-color: #60a5fa !important;
            background-color: #dbeafe !important;
        }
    </style>
</head>
<body>

<div id="sidebar-overlay"></div>

<?php
    $currentUser = $usuario_sesion ?? $usuario ?? [];
    $fullName = trim(($currentUser['nombre'] ?? '') . ' ' . ($currentUser['apellido'] ?? ''));
    $rawDisplayName = $fullName !== '' ? $fullName : ($currentUser['usuario'] ?? 'USUARIO');
    // Role prefix — dinámico por capacidades
    $caps            = $currentUserCapacidades ?? [];
    $rolNombrePrefix = strtoupper($currentUser['rol_nombre'] ?? '');
    if (in_array('prefijo_farm', $caps, true)) {
        $rolePrefix = 'Farm.';
    } elseif (in_array('prefijo_med', $caps, true)) {
        $rolePrefix = 'Dr.';
    } elseif (in_array('prefijo_enf', $caps, true)) {
        $rolePrefix = 'Enf.';
    } else {
        $rolePrefix = '';
    }
    $displayName = $rolePrefix !== '' ? $rolePrefix . ' ' . $rawDisplayName : $rawDisplayName;
    $initialSource = $rawDisplayName !== '' ? $rawDisplayName : 'US';
    $parts = array_values(array_filter(explode(' ', $initialSource)));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1, 'UTF-8'), 'UTF-8');
    }
    $initials = $initials ?: 'US';
?>

<div class="d-flex">

    <?php
        $mods = array_map('intval', $currentUserModules ?? []);
        $canInv = in_array(1, $mods, true);
        $canRea = in_array(3, $mods, true);
        $canUsu = in_array(7, $mods, true) || in_array(4, $mods, true);
        $canRoles = in_array(8, $mods, true);
        $canBackup = in_array(10, $mods, true);
        $canParam = in_array(11, $mods, true);
        $canConfigAccesos = $canParam;
        $canPreg = in_array(12, $mods, true);
        $canPermisos = in_array(13, $mods, true);
        $canBit = in_array(5, $mods, true);
        $showSecurity = $canUsu || $canRoles || $canBackup || $canParam || $canConfigAccesos || $canPreg || $canPermisos || $canBit;
        $rolNombre        = strtoupper($currentUser['rol_nombre'] ?? '');
        $isAdminRole      = in_array('acceso_total', $currentUserCapacidades ?? [], true);
        $isFarmaceutico   = in_array('prefijo_farm', $currentUserCapacidades ?? [], true);

        if (! $isAdminRole) {
            $canUsu = false;
            $canRoles = false;
            $canBackup = false;
            $canParam = false;
            $canConfigAccesos = false;
            $canPreg = false;
            $canPermisos = false;
            $canBit = false;
        }

        $isUsersManagementRoute = request()->routeIs('usuarios.lista') || request()->routeIs('usuarios.index') || request()->routeIs('usuarios.create') || request()->routeIs('usuarios.store') || request()->routeIs('usuarios.show') || request()->routeIs('usuarios.edit') || request()->routeIs('usuarios.update') || request()->routeIs('usuarios.destroy') || request()->routeIs('usuarios.reporte') || request()->routeIs('usuarios.reporte_general');
        $isUsersPermissionsRoute = request()->routeIs('usuarios.permisos.index') || request()->routeIs('usuarios.permisos') || request()->routeIs('usuarios.permisos.store');
        $isRolesRoute = request()->routeIs('roles.*');
        $isSecuritySection = $isUsersManagementRoute || $isUsersPermissionsRoute || $isRolesRoute || Request::is('bitacora') || Request::is('backup*') || request()->routeIs('parametros.*') || request()->routeIs('configuraciones.accesos.*') || request()->routeIs('preguntas.*');

        $headerTitle = 'Dashboard';
        if (Request::is('inventario*')) {
            $headerTitle = 'Inventario';
        } elseif (Request::is('reacciones-adversas*')) {
            $headerTitle = 'Reacciones Adversas';
        } elseif ($isUsersPermissionsRoute) {
            $headerTitle = 'Permisos';
        } elseif ($isUsersManagementRoute) {
            $headerTitle = 'Gestión de Usuarios';
        } elseif ($isRolesRoute) {
            $headerTitle = 'Roles';
        } elseif (Request::is('bitacora')) {
            $headerTitle = 'Bitácora';
        } elseif (Request::is('backup*')) {
            $headerTitle = 'Backup BD';
        } elseif (request()->routeIs('parametros.*')) {
            $headerTitle = 'Parámetros';
        } elseif (request()->routeIs('configuraciones.accesos.*')) {
            $headerTitle = 'Configuracion de accesos al sistema';
        } elseif (request()->routeIs('preguntas.*')) {
            $headerTitle = 'Preguntas';
        }

        $customHeader = trim($__env->yieldContent('header'));
    ?>

    <div class="sidebar d-flex flex-column p-3" id="sidebar">
        <div class="text-center mt-3 mb-4">
            <a href="<?php echo e(route('dashboard')); ?>" title="Ir al dashboard">
                <img src="<?php echo e(asset('login-assets/images/logo-circle.png')); ?>"
                     alt="Hospital Escuela"
                     style="width:72px;height:72px;border-radius:50%;object-fit:cover;box-shadow:0 2px 10px rgba(0,0,0,.15);">
            </a>
        </div>

        <ul class="nav flex-column mb-auto gap-2 px-2">
            <li>
                <a href="<?php echo e(route('dashboard')); ?>" class="nav-link nav-item-custom <?php echo e(Request::is('dashboard') ? 'active' : ''); ?>">
                    <i class="fas fa-house icon-sidebar"></i> Inicio
                </a>
            </li>

            <?php if($canInv): ?>
            <li>
                <a href="<?php echo e(route('inventario')); ?>"
                   class="nav-link nav-item-custom <?php echo e(Request::is('inventario*') ? 'active' : ''); ?>">
                    <i class="fas fa-folder icon-sidebar"></i> Inventario
                </a>
            </li>
            <?php endif; ?>

            <?php if($canRea): ?>
            <li>
                <a href="<?php echo e(route('reacciones_adversas.index')); ?>"
                class="nav-link nav-item-custom <?php echo e(Request::is('reacciones-adversas*') ? 'active' : ''); ?>">
                    <i class="fas fa-exclamation-triangle icon-sidebar"></i> Reacciones
                </a>
            </li>
            <?php endif; ?>

            <?php if($showSecurity): ?>
            <li>
                <a class="nav-link nav-item-custom d-flex justify-content-between align-items-center <?php echo e($isSecuritySection ? 'active' : ''); ?>"
                   data-bs-toggle="collapse"
                   href="#menu-seguridad"
                   role="button"
                   aria-expanded="<?php echo e($isSecuritySection ? 'true' : 'false'); ?>"
                   aria-controls="menu-seguridad">
                    <span><i class="fas fa-shield-halved icon-sidebar"></i> Seguridad</span>
                    <i class="fas fa-chevron-down small"></i>
                </a>

                <div class="collapse <?php echo e($isSecuritySection ? 'show' : ''); ?>" id="menu-seguridad">
                    <div class="nav-submenu">
                        <?php if($canUsu): ?>
                        <a href="<?php echo e(route('usuarios.lista')); ?>" class="nav-subitem <?php echo e($isUsersManagementRoute ? 'active' : ''); ?>">
                            <i class="far fa-user me-2"></i>Gestión
                        </a>
                        <?php endif; ?>

                        <?php if($canPermisos): ?>
                        <a href="<?php echo e(route('usuarios.permisos.index')); ?>" class="nav-subitem <?php echo e($isUsersPermissionsRoute ? 'active' : ''); ?>">
                            <i class="fas fa-user-shield me-2"></i>Permisos
                        </a>
                        <?php endif; ?>

                        <?php if($canRoles): ?>
                        <a href="<?php echo e(route('roles.index')); ?>" class="nav-subitem <?php echo e($isRolesRoute ? 'active' : ''); ?>">
                            <i class="fas fa-users-gear me-2"></i>Roles
                        </a>
                        <?php endif; ?>

                        <?php if($canBackup): ?>
                        <a href="<?php echo e(route('backup.index')); ?>" class="nav-subitem <?php echo e(Request::is('backup*') ? 'active' : ''); ?>">
                            <i class="fas fa-database me-2"></i>Backup BD
                        </a>
                        <?php endif; ?>

                        <?php if($canParam): ?>
                        <a href="<?php echo e(route('parametros.index')); ?>" class="nav-subitem <?php echo e(request()->routeIs('parametros.*') ? 'active' : ''); ?>">
                            <i class="fas fa-sliders me-2"></i>Parámetros
                        </a>
                        <?php endif; ?>

                        <?php if($canConfigAccesos): ?>
                        <a href="<?php echo e(route('configuraciones.accesos.index')); ?>" class="nav-subitem <?php echo e(request()->routeIs('configuraciones.accesos.*') ? 'active' : ''); ?>">
                            <i class="fas fa-sliders-h me-2"></i>Configuraciones
                        </a>
                        <?php endif; ?>

                        <?php if($canBit): ?>
                        <a href="<?php echo e(route('bitacora')); ?>" class="nav-subitem <?php echo e(Request::is('bitacora') ? 'active' : ''); ?>">
                            <i class="fas fa-clipboard-list me-2"></i>Bitácora
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </li>
            <?php endif; ?>

            <?php if(!$canInv && !$canRea && !$canUsu && !$canBit): ?>
            <li>
                <div class="nav-link text-muted" style="font-size: 0.9rem; opacity: .85; cursor: default;">
                    <i class="fas fa-circle-info me-2"></i>Este usuario no tiene módulos asignados.
                </div>
            </li>
            <?php endif; ?>
        </ul>

    </div>

    <div class="main-content" id="main-content">
        <div class="top-header-ribbon d-flex justify-content-between align-items-center px-4 py-3 shadow-sm">
            <div class="d-flex align-items-center">
                <i class="fas fa-bars fs-5 me-3" id="btn-toggle" style="cursor: pointer;"></i>
                <h5 class="fw-bold mb-0"><?php echo e($customHeader !== '' ? $customHeader : $headerTitle); ?></h5>
            </div>

            <button type="button" class="profile-trigger" aria-expanded="false" aria-controls="profile-menu-dropdown">
                <div class="profile-initials rounded-circle d-flex justify-content-center align-items-center fw-bold shadow-sm">
                    <?php echo e($initials); ?>

                </div>
                <span class="fw-bold d-none d-sm-block user-name">
                    <?php echo e($displayName); ?>

                </span>
            </button>
        </div>

        <div class="content-shell">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>
</div>

<div id="unsaved-toast-backdrop" class="unsaved-toast-backdrop"></div>

<div id="unsaved-toast" class="unsaved-toast" aria-live="polite" aria-atomic="true">
    <div class="d-flex align-items-start gap-2">
        <i class="fas fa-triangle-exclamation mt-1"></i>
        <div class="flex-grow-1">
            <div class="unsaved-toast-title">¡Cuidado!</div>
            <div>Los datos no se guardarán.</div>
        </div>
    </div>
    <div class="unsaved-toast-actions">
        <button type="button" class="btn btn-sm btn-light border fw-bold" id="unsaved-stay">Quedarme</button>
        <button type="button" class="btn btn-sm btn-warning text-dark fw-bold" id="unsaved-leave">Salir</button>
    </div>
</div>

<div id="profile-menu-dropdown" class="profile-menu-dropdown" aria-hidden="true">
    <div class="profile-menu-user">
        <div class="profile-initials rounded-circle d-flex justify-content-center align-items-center fw-bold shadow-sm">
            <?php echo e($initials); ?>

        </div>
        <div class="fw-bold text-dark"><?php echo e($displayName); ?></div>
    </div>

    <div class="d-grid gap-2">
        <button type="button"
                class="btn btn-primary profile-action-btn"
                data-bs-toggle="modal"
                data-bs-target="#modalPerfilDetalle">
            <i class="fas fa-user-cog me-2"></i>Perfil
        </button>

        <form action="/logout" method="POST" class="m-0" id="logout-form">
            <?php echo csrf_field(); ?>
            <button type="button" class="btn btn-danger profile-action-btn" id="btn-salir">
                <i class="fas fa-sign-out-alt me-2"></i>Salir
            </button>
        </form>
    </div>
</div>


<div id="farewell-toast" class="farewell-overlay" aria-live="polite" aria-atomic="true">
    <div class="farewell-card">
        <p class="farewell-title">Hasta pronto, <?php echo e($rawDisplayName); ?></p>
        <p class="farewell-subtitle">Cerrando sesion...</p>
    </div>
</div>

<script>
document.getElementById('btn-salir').addEventListener('click', function () {
    const toast = document.getElementById('farewell-toast');
    toast.classList.add('show');
    setTimeout(function () {
        document.getElementById('logout-form').submit();
    }, 1800);
});
</script>

<div class="modal fade" id="modalPerfilDetalle" tabindex="-1" aria-labelledby="modalPerfilDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalPerfilDetalleLabel">
                    Configuracion de Perfil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="d-flex flex-wrap gap-2 mb-4" id="perfil-tabs" role="tablist">
                    <button class="profile-tab-btn active" id="tab-datos" data-bs-toggle="tab" data-bs-target="#pane-datos" type="button" role="tab" aria-controls="pane-datos" aria-selected="true">
                        Datos
                    </button>
                    <button class="profile-tab-btn" id="tab-password" data-bs-toggle="tab" data-bs-target="#pane-password" type="button" role="tab" aria-controls="pane-password" aria-selected="false">
                        Contrasena
                    </button>
                </div>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="pane-datos" role="tabpanel" aria-labelledby="tab-datos">
                        <div class="profile-card">
                            <form action="<?php echo e(route('perfil.actualizar')); ?>" method="POST" class="row g-3" data-unsaved-form="true">
                                <?php echo csrf_field(); ?>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nombre</label>
                                    <input type="text" name="nombre" class="form-control" value="<?php echo e(old('nombre', $currentUser['nombre'] ?? '')); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Apellido</label>
                                    <input type="text" name="apellido" class="form-control" value="<?php echo e(old('apellido', $currentUser['apellido'] ?? '')); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Correo</label>
                                    <input type="email" name="correo" class="form-control" value="<?php echo e(old('correo', $currentUser['correo'] ?? '')); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Telefono</label>
                                    <input type="text" name="telefono" class="form-control" value="<?php echo e(old('telefono', $currentUser['telefono'] ?? '')); ?>">
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Guardar cambios</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="pane-password" role="tabpanel" aria-labelledby="tab-password">
                        <div class="profile-card">
                            <form action="<?php echo e(route('perfil.cambiar-password')); ?>" method="POST" class="row g-3" data-unsaved-form="true">
                                <?php echo csrf_field(); ?>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Contrasena actual</label>
                                    <input type="password" name="contrasena_actual" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nueva contrasena</label>
                                    <input type="password" name="contrasena_nueva" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Confirmar nueva contrasena</label>
                                    <input type="password" name="contrasena_nueva_confirmation" class="form-control" required>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-secondary rounded-pill px-4 fw-bold">Actualizar contrasena</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-outline-secondary rounded-pill fw-bold"
                        id="profile-detail-back"
                        data-bs-dismiss="modal">
                    Volver
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const btnToggle = document.getElementById("btn-toggle");
        const sidebar = document.getElementById("sidebar");
        const mainContent = document.getElementById("main-content");
        const overlay = document.getElementById("sidebar-overlay");

        function toggleMenu() {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle("show");
                overlay.classList.toggle("show");
            } else {
                sidebar.classList.toggle("collapsed");
                mainContent.classList.toggle("expanded");
            }
        }

        if (btnToggle) btnToggle.addEventListener("click", toggleMenu);
        if (overlay) overlay.addEventListener("click", toggleMenu);

        const profileTrigger = document.querySelector('.profile-trigger');
        const profileMenuDropdown = document.getElementById('profile-menu-dropdown');
        const profileDetailBack = document.getElementById('profile-detail-back');

        function positionProfileDropdown() {
            if (!profileTrigger || !profileMenuDropdown) return;

            const rect = profileTrigger.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const desiredWidth = Math.min(320, viewportWidth - 24);

            let left = rect.right - desiredWidth;
            left = Math.max(12, Math.min(left, viewportWidth - desiredWidth - 12));
            const top = rect.bottom + 8;

            profileMenuDropdown.style.left = `${left}px`;
            profileMenuDropdown.style.top = `${top}px`;
        }

        function closeProfileDropdown() {
            if (!profileMenuDropdown || !profileTrigger) return;
            profileMenuDropdown.classList.remove('show');
            profileMenuDropdown.setAttribute('aria-hidden', 'true');
            profileTrigger.setAttribute('aria-expanded', 'false');
        }

        function openProfileDropdown() {
            if (!profileMenuDropdown || !profileTrigger) return;
            positionProfileDropdown();
            profileMenuDropdown.classList.add('show');
            profileMenuDropdown.setAttribute('aria-hidden', 'false');
            profileTrigger.setAttribute('aria-expanded', 'true');
        }

        if (profileTrigger && profileMenuDropdown) {
            profileTrigger.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();

                if (profileMenuDropdown.classList.contains('show')) {
                    closeProfileDropdown();
                } else {
                    openProfileDropdown();
                }
            });

            document.addEventListener('click', function(event) {
                if (!profileMenuDropdown.classList.contains('show')) return;
                if (profileMenuDropdown.contains(event.target) || profileTrigger.contains(event.target)) return;
                closeProfileDropdown();
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeProfileDropdown();
                }
            });

            window.addEventListener('resize', function() {
                if (profileMenuDropdown.classList.contains('show')) {
                    positionProfileDropdown();
                }
            });
        }

        const profileDetailModalElement = document.getElementById('modalPerfilDetalle');
        if (profileDetailModalElement) {
            profileDetailModalElement.addEventListener('show.bs.modal', function() {
                closeProfileDropdown();
            });
        }

        if (profileDetailBack) {
            profileDetailBack.addEventListener('click', function() {
                setTimeout(openProfileDropdown, 100);
            });
        }

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('perfil') === '1') {
            openProfileDropdown();
        }

        <?php if(session('open_profile_modal')): ?>
            const profileModalElement = document.getElementById('modalPerfilDetalle');
            if (profileModalElement) {
                const profileModal = new bootstrap.Modal(profileModalElement);
                profileModal.show();
            }
        <?php endif; ?>

        // Confirmación global para botones de eliminación
        document.querySelectorAll('.btn-borrar').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const form = this.closest('form');
                const name = this.dataset.paciente || this.dataset.usuario || '';
                const itemLabel = name ? `\n\n"${name}"` : '';

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `Esta acción no se puede deshacer.${itemLabel}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                }).then((result) => {
                    if (result.isConfirmed && form) {
                        form.submit();
                    }
                });
            });
        });

        <?php if(session('success') && !session('report_prompt')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Listo',
                text: <?php echo json_encode(session('success'), 15, 512) ?>,
                confirmButtonColor: '#06b6d4'
            });
        <?php endif; ?>

        <?php if(session('warning')): ?>
            Swal.fire({
                icon: 'warning',
                title: 'Atencion',
                text: <?php echo json_encode(session('warning'), 15, 512) ?>,
                confirmButtonColor: '#d97706'
            });
        <?php endif; ?>

        <?php if(session('report_prompt')): ?>
            (() => {
                const reportPrompt = <?php echo json_encode(session('report_prompt'), 15, 512) ?>;
                const yesUrl = reportPrompt?.yes_url || null;
                const noUrl = reportPrompt?.no_url || <?php echo json_encode(route('dashboard'), 15, 512) ?>;

                Swal.fire({
                    title: reportPrompt?.title || '¿Desea generar reporte?',
                    text: reportPrompt?.text || '',
                    icon: 'question',
                    showCancelButton: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonColor: '#222c5e',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: reportPrompt?.confirm_text || 'Sí, generar reporte',
                    cancelButtonText: reportPrompt?.cancel_text || 'No, ir al inicio',
                }).then((result) => {
                    if (result.isConfirmed && yesUrl) {
                        window.open(yesUrl, '_blank', 'noopener');
                        window.location.href = noUrl;
                        return;
                    }

                    window.location.href = noUrl;
                });
            })();
        <?php endif; ?>

        window.openPdfPreviewWindow = function(blob, options = {}) {
            if (!(blob instanceof Blob)) return;

            const previewTitle = String(options.title || 'Reporte');
            const blobUrl = URL.createObjectURL(blob);
            const previewWindow = window.open('', '_blank');

            if (!previewWindow) {
                const fallbackLink = document.createElement('a');
                fallbackLink.href = blobUrl;
                fallbackLink.target = '_blank';
                fallbackLink.rel = 'noopener';
                fallbackLink.click();
                setTimeout(() => URL.revokeObjectURL(blobUrl), 10 * 60 * 1000);
                return;
            }

            try {
                previewWindow.document.title = previewTitle;
            } catch (error) {
                console.warn('No se pudo establecer el titulo del reporte.', error);
            }

            previewWindow.location.replace(blobUrl);
            previewWindow.focus();

            setTimeout(() => URL.revokeObjectURL(blobUrl), 10 * 60 * 1000);
        };

        window.openJsPdfPreview = function(doc, options = {}) {
            if (!doc || typeof doc.output !== 'function') return;
            const blob = doc.output('blob');
            window.openPdfPreviewWindow(blob, options);
        };

        <?php if($errors->any()): ?>
            Swal.fire({
                icon: 'error',
                title: 'Atencion',
                text: <?php echo json_encode($errors->first(), 15, 512) ?>,
                confirmButtonColor: '#dc2626'
            });
        <?php endif; ?>

        const collectUnsavedForms = () =>
            Array.from(document.querySelectorAll('form')).filter((form) => {
                const methodAttr = form.getAttribute('method');
                const method = methodAttr ? methodAttr.toUpperCase() : '';
                if (method === 'GET') return false;
                if (form.id === 'logout-form') return false;
                if (form.hasAttribute('data-unsaved-ignore')) return false;
                return true;
            });

        {
            const unsavedToast = document.getElementById('unsaved-toast');
            const unsavedBackdrop = document.getElementById('unsaved-toast-backdrop');
            const stayButton = document.getElementById('unsaved-stay');
            const leaveButton = document.getElementById('unsaved-leave');
            let isDirty = false;
            let allowNavigation = false;
            let pendingAction = null;
            let suppressPopGuard = false;
            let unsavedForms = [];

            const syncUnsavedForms = () => {
                unsavedForms = collectUnsavedForms();
                unsavedForms.forEach((form) => {
                    if (!form.dataset.unsavedDirty) {
                        form.dataset.unsavedDirty = '0';
                    }
                });
                return unsavedForms;
            };

            const hasCustomUnsavedChanges = () => {
                if (typeof window.hasUnsavedChanges !== 'function') return false;
                try {
                    return !!window.hasUnsavedChanges();
                } catch (error) {
                    console.warn('No se pudo evaluar hasUnsavedChanges()', error);
                    return false;
                }
            };

            const hasDirtyForms = () => unsavedForms.some((form) => form.dataset.unsavedDirty === '1');
            const hasPendingChanges = () => {
                syncUnsavedForms();
                return hasDirtyForms() || hasCustomUnsavedChanges();
            };

            const markDirty = (event) => {
                syncUnsavedForms();
                const targetForm = event.target.closest('form');
                if (targetForm && unsavedForms.includes(targetForm)) {
                    targetForm.dataset.unsavedDirty = '1';
                    isDirty = true;
                }
            };

            const hideUnsavedToast = () => {
                if (unsavedToast) {
                    unsavedToast.classList.remove('show');
                }
                if (unsavedBackdrop) {
                    unsavedBackdrop.classList.remove('show');
                }
                pendingAction = null;
            };

            const runDiscardUnsavedHook = () => {
                if (typeof window.onDiscardUnsavedChanges !== 'function') return;
                try {
                    window.onDiscardUnsavedChanges();
                } catch (error) {
                    console.warn('Error al descartar cambios locales.', error);
                }
            };

            const showUnsavedToast = (action) => {
                pendingAction = action;
                if (unsavedToast) {
                    unsavedToast.classList.add('show');
                }
                if (unsavedBackdrop) {
                    unsavedBackdrop.classList.add('show');
                }
            };

            const handleExitAttempt = (action) => {
                if (!hasPendingChanges() || allowNavigation) {
                    action();
                    return;
                }

                showUnsavedToast(action);
            };

            // Permite reutilizar la misma alerta de cambios sin guardar desde otras vistas.
            window.confirmUnsavedAction = (action) => handleExitAttempt(action);
            window.hasPendingUnsavedChanges = () => hasPendingChanges();
            window.allowUnsavedNavigation = () => {
                allowNavigation = true;
                hideUnsavedToast();
            };

            syncUnsavedForms();

            document.addEventListener('input', markDirty, true);
            document.addEventListener('change', markDirty, true);
            document.addEventListener('submit', (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) return;
                syncUnsavedForms();
                if (!unsavedForms.includes(form)) return;

                allowNavigation = true;
                form.dataset.unsavedDirty = '0';
                isDirty = hasDirtyForms();
                hideUnsavedToast();
            }, true);

            Array.from(document.querySelectorAll('.modal')).forEach((modalEl) => {
                if (modalEl.id === 'modalPerfilDetalle') return;
                let bypassModalGuard = false;

                modalEl.addEventListener('hide.bs.modal', function(event) {
                    if (bypassModalGuard || allowNavigation) return;

                    syncUnsavedForms();
                    const modalForms = unsavedForms.filter((form) => modalEl.contains(form));
                    if (!modalForms.length) return;

                    const modalHasDirtyChanges = modalForms.some((form) => form.dataset.unsavedDirty === '1');
                    if (!modalHasDirtyChanges && !hasCustomUnsavedChanges()) return;

                    event.preventDefault();

                    showUnsavedToast(() => {
                        bypassModalGuard = true;
                        modalForms.forEach((form) => {
                            form.dataset.unsavedDirty = '0';
                        });
                        isDirty = hasDirtyForms();

                        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modalInstance.hide();

                        setTimeout(() => {
                            bypassModalGuard = false;
                        }, 0);
                    });
                });
            });

            document.addEventListener('click', function(event) {
                const link = event.target.closest('a[href]');

                if (!link) return;
                if (link.target === '_blank') return;
                if (link.getAttribute('href') === '#') return;
                if (link.closest('#unsaved-toast')) return;

                if (hasPendingChanges() && !allowNavigation) {
                    event.preventDefault();
                    handleExitAttempt(() => {
                        allowNavigation = true;
                        window.location.href = link.href;
                    });
                }
            }, true);

            window.addEventListener('beforeunload', function(event) {
                if (!hasPendingChanges() || allowNavigation) return;
                event.preventDefault();
                event.returnValue = '';
            });

            history.pushState({ unsavedGuard: true }, '', window.location.href);

            window.addEventListener('popstate', function() {
                if (suppressPopGuard) {
                    suppressPopGuard = false;
                    return;
                }

                if (!hasPendingChanges() || allowNavigation) {
                    return;
                }

                history.pushState({ unsavedGuard: true }, '', window.location.href);
                handleExitAttempt(() => {
                    allowNavigation = true;
                    suppressPopGuard = true;
                    history.back();
                });
            });

            if (stayButton) {
                stayButton.addEventListener('click', hideUnsavedToast);
            }

            if (unsavedBackdrop) {
                unsavedBackdrop.addEventListener('click', hideUnsavedToast);
            }

            if (leaveButton) {
                leaveButton.addEventListener('click', function() {
                    const action = pendingAction;
                    hideUnsavedToast();
                    if (action) {
                        runDiscardUnsavedHook();
                        action();
                    }
                });
            }

            const profileModalElement = document.getElementById('modalPerfilDetalle');
            if (profileModalElement) {
                let allowProfileModalClose = false;
                const profileUnsavedForms = Array.from(profileModalElement.querySelectorAll('form[data-unsaved-form="true"]'));

                const profileFields = profileUnsavedForms.flatMap((form) =>
                    Array.from(form.querySelectorAll('input, textarea, select'))
                );

                const captureProfileInitialState = () => {
                    profileFields.forEach((field) => {
                        if (field.type === 'checkbox' || field.type === 'radio') {
                            field.dataset.initialChecked = field.checked ? '1' : '0';
                            return;
                        }
                        field.dataset.initialValue = field.value;
                    });
                };

                const restoreProfileInitialState = () => {
                    profileFields.forEach((field) => {
                        if (field.type === 'checkbox' || field.type === 'radio') {
                            field.checked = field.dataset.initialChecked === '1';
                            return;
                        }
                        field.value = field.dataset.initialValue ?? '';
                    });
                };

                captureProfileInitialState();

                const resetProfileForms = () => {
                    profileUnsavedForms.forEach((form) => {
                        try {
                            form.reset();
                        } catch (_) {}
                    });

                    // Asegura volver exactamente al estado original del modal
                    restoreProfileInitialState();
                };

                const previousDiscardHook = typeof window.onDiscardUnsavedChanges === 'function'
                    ? window.onDiscardUnsavedChanges
                    : null;

                window.onDiscardUnsavedChanges = function() {
                    if (typeof previousDiscardHook === 'function') {
                        try {
                            previousDiscardHook();
                        } catch (_) {}
                    }
                    resetProfileForms();
                };

                profileModalElement.addEventListener('hide.bs.modal', function(event) {
                    if (allowProfileModalClose || allowNavigation) {
                        return;
                    }

                    event.preventDefault();

                    handleExitAttempt(() => {
                        allowProfileModalClose = true;
                        const instance = bootstrap.Modal.getInstance(profileModalElement) || new bootstrap.Modal(profileModalElement);
                        instance.hide();
                        setTimeout(() => {
                            isDirty = false;
                            allowProfileModalClose = false;
                        }, 0);
                    });
                });

                profileModalElement.addEventListener('hidden.bs.modal', function() {
                    isDirty = false;
                });

                // Cuando se guarda y se reabre en la misma vista, refrescar baseline actual
                profileModalElement.addEventListener('shown.bs.modal', function() {
                    if (!hasPendingChanges()) {
                        captureProfileInitialState();
                    }
                });
            }
        }

        // Estandariza iconos de acciones en toda la app (ver, PDF, editar, eliminar).
        const collectSpacingClasses = (iconEl) => {
            const spacing = [];
            iconEl.classList.forEach((cls) => {
                if (/^(m|mx|my|mt|mb|ms|me)-\d$/.test(cls)) {
                    spacing.push(cls);
                }
            });
            return spacing;
        };

        const applyIconToButtons = (selector, iconClasses) => {
            document.querySelectorAll(selector).forEach((button) => {
                const iconEl = button.querySelector('i');
                if (!iconEl) return;

                const spacing = collectSpacingClasses(iconEl);
                iconEl.className = `${iconClasses} ${spacing.join(' ')}`.trim();
            });
        };

        const estandarizarIconosAcciones = () => {
            applyIconToButtons('.btn-view, [class*="btn-ver"]', 'fa-solid fa-eye');
            applyIconToButtons('.btn-report, .btn-param-report, [class*="btn-reporte"], [onclick*="PDF"], [onclick*="pdf"]', 'fa-solid fa-file-pdf');
            applyIconToButtons('.btn-edit, [class*="btn-editar"]', 'fa-solid fa-pen');
            applyIconToButtons('.btn-delete, [class*="btn-eliminar"], [class*="btn-borrar"]', 'fa-solid fa-trash');
        };

        estandarizarIconosAcciones();

        let iconRefreshScheduled = false;
        const scheduleIconRefresh = () => {
            if (iconRefreshScheduled) return;
            iconRefreshScheduled = true;
            requestAnimationFrame(() => {
                estandarizarIconosAcciones();
                iconRefreshScheduled = false;
            });
        };

        const iconObserver = new MutationObserver(scheduleIconRefresh);
        iconObserver.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class'],
        });

        <?php if($canRea && $isFarmaceutico): ?>
            const bandejaEndpoint = <?php echo json_encode(route('ajax.bandeja_pacientes'), 15, 512) ?>;
            let lastNewPatientsCount = null;

            const emitBandejaUpdate = (payload) => {
                window.dispatchEvent(new CustomEvent('bandeja-pacientes-actualizada', {
                    detail: payload,
                }));
            };

            const notifyNewPatients = (count) => {
                const title = count === 1
                    ? 'Hay 1 nuevo registro de paciente'
                    : `Hay ${count} nuevos registros de paciente`;

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title,
                    text: 'Revisa Reacciones Adversas para procesarlos.',
                    showConfirmButton: false,
                    timer: 4500,
                    timerProgressBar: true,
                });
            };

            const pollBandejaPacientes = async () => {
                try {
                    const response = await fetch(bandejaEndpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    const newCount = Number(payload.total_nuevos || 0);

                    if (lastNewPatientsCount === null) {
                        if (newCount > 0) {
                            notifyNewPatients(newCount);
                        }
                    } else if (newCount > lastNewPatientsCount) {
                        notifyNewPatients(newCount - lastNewPatientsCount);
                    }

                    lastNewPatientsCount = newCount;
                    emitBandejaUpdate(payload);
                } catch (error) {
                    console.error('No se pudo consultar la bandeja de pacientes.', error);
                }
            };

            pollBandejaPacientes();
            window.setInterval(pollBandejaPacientes, 15000);
        <?php endif; ?>
    });
</script>

<?php echo $__env->yieldContent('scripts'); ?>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>


<?php /**PATH C:\Users\mrner\Escritorio\PROYECTO\Sistema HE\ProyectoHospitalEscuela\Frontend\resources\views/layouts/app.blade.php ENDPATH**/ ?>