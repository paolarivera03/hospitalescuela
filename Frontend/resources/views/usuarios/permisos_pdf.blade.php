<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Permisos – {{ $rolNombre }}</title>
    <style>
        @page { margin: 0cm 0cm; }

        body {
            font-family: 'Helvetica', sans-serif;
            margin: 3.9cm 1.1cm 2.6cm;
            font-size: 9px;
            color: #1e3a6b;
        }

        /* ── HEADER ── */
        header {
            position: fixed;
            top: 0cm; left: 0cm; right: 0cm;
            height: 3.4cm;
            background-color: #1e3a6b;
            color: #ffffff;
            padding-top: 0.3cm;
        }

        header .logo {
            position: absolute;
            left: 0.6cm;
            top: 0.45cm;
            width: 2.0cm;
            height: 2.0cm;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ffffff;
            background: #ffffff;
            padding: 2px;
        }

        header .header-text { text-align: center; }

        header h1 {
            margin: 0.1cm 0 0.04cm;
            font-size: 21px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }

        header .header-sub { font-size: 10px; opacity: .85; margin-bottom: 0.12cm; }
        header .datetime   { font-size: 9px; opacity: .9; }

        .header-stripe {
            position: fixed;
            top: 3.4cm; left: 0; right: 0;
            height: 0.15cm;
            background-color: #1e3a6b;
        }

        /* ── FOOTER ── */
        footer {
            position: fixed;
            bottom: 0cm; left: 0cm; right: 0cm;
            height: 2.2cm;
            background-color: #eef4fa;
            border-top: 3px solid #1e3a6b;
            font-size: 7.5px;
            color: #1e3a6b;
            padding: 0.25cm 1.1cm;
        }

        .footer-row    { display: table; width: 100%; }
        .footer-left   { display: table-cell; text-align: left;  vertical-align: middle; }
        .footer-center { display: table-cell; text-align: center; vertical-align: middle; color: #1e3a6b; font-size: 7px; text-transform: uppercase; letter-spacing: .4px; }
        .footer-right  { display: table-cell; text-align: right; vertical-align: middle; }

        /* ── SECTION TITLE ── */
        .sub-header {
            margin-top: 0.3cm;
            margin-bottom: 0.45cm;
            font-size: 11.5px;
            font-weight: 700;
            color: #1e3a6b;
            text-transform: uppercase;
            letter-spacing: .4px;
            border-left: 4px solid #1e3a6b;
            padding-left: 0.25cm;
        }

        /* ── ROLE SUMMARY BOX ── */
        .role-box {
            background: #eef4fa;
            border: 1px solid #1e3a6b;
            border-radius: 4px;
            padding: 0.3cm 0.5cm;
            margin-bottom: 0.5cm;
            display: table;
            width: 100%;
        }

        .role-box-left  { display: table-cell; vertical-align: middle; }
        .role-box-right { display: table-cell; vertical-align: middle; text-align: right; font-size: 8px; color: #1e3a6b; }

        .role-name {
            font-size: 16px;
            font-weight: 800;
            color: #1e3a6b;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .role-label {
            font-size: 8px;
            color: #1e3a6b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .3px;
            margin-bottom: 2px;
        }

        /* ── PERMISSIONS TABLE ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.5cm;
        }

        thead th {
            background-color: #1e3a6b;
            color: #ffffff;
            padding: 0.38cm 0.3cm;
            text-align: center;
            border: 1px solid #1e3a6b;
            text-transform: uppercase;
            font-weight: 700;
            font-size: 8px;
            letter-spacing: .3px;
        }

        thead th.col-module {
            text-align: left;
            width: 42%;
        }

        tbody td {
            padding: 0.30cm 0.3cm;
            border: 1px solid #1e3a6b;
            font-size: 8.5px;
            text-align: center;
            vertical-align: middle;
        }

        tbody td.col-module {
            text-align: left;
            font-weight: 700;
            color: #1e3a6b;
        }

        tbody td.col-module.unassigned {
            color: #1e3a6b;
            font-weight: 400;
            font-style: italic;
        }

        tbody tr:nth-child(odd)  { background-color: #eef4fa; }
        tbody tr:nth-child(even) { background-color: #ffffff; }

        tbody tr.row-assigned td { background-color: #eef4fa; }
        tbody tr.row-assigned td.col-module { color: #1e3a6b; }

        .badge-yes {
            display: inline-block;
            background-color: #16a34a;
            color: #ffffff;
            border-radius: 3px;
            padding: 1px 6px;
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-no {
            display: inline-block;
            background-color: #ffffff;
            color: #1e3a6b;
            border: 1px solid #1e3a6b;
            border-radius: 3px;
            padding: 1px 6px;
            font-size: 7.5px;
        }

        /* ── USERS LIST ── */
        .users-section { margin-top: 0.45cm; }

        .users-title {
            font-size: 11px;
            font-weight: 700;
            color: #1e3a6b;
            text-transform: uppercase;
            letter-spacing: .4px;
            border-left: 4px solid #1e3a6b;
            padding-left: 0.25cm;
            margin-bottom: 0.3cm;
        }

        .users-table thead th {
            background-color: #1e3a6b;
            font-size: 7.5px;
            padding: 0.28cm 0.3cm;
        }

        .users-table tbody td {
            font-size: 8px;
            padding: 0.22cm 0.3cm;
        }

        .no-users {
            color: #1e3a6b;
            font-style: italic;
            font-size: 8px;
            padding: 0.2cm 0;
        }

        .no-print {
            background: #eff6ff;
            border-bottom: 1px solid #bfdbfe;
            padding: 10px 30px;
            text-align: right;
        }

        .no-print-actions {
            display: inline-flex;
            gap: 10px;
            align-items: center;
        }

        .print-btn {
            padding: 9px 22px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: #fff;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 9px 18px;
            border: 1px solid #334155;
            border-radius: 8px;
            background: #ffffff;
            color: #1f2937;
            font-weight: 700;
            font-size: 13px;
            text-decoration: none;
        }

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    @if(!empty($htmlPreview))
    <div class="no-print">
        <div class="no-print-actions">
            <a href="{{ $backUrl ?? route('usuarios.permisos.index', ['rol_id' => (int) ($selectedRoleId ?? 0)]) }}" class="back-btn">Volver</a>
            <button onclick="window.print()" class="print-btn">Imprimir</button>
        </div>
    </div>
    @endif

    <header>
        <img class="logo" src="{{ public_path('login-assets/images/logo-circle.png') }}" alt="Logo Hospital Escuela">
        <div class="header-text">
            <h1>Hospital Escuela</h1>
            <div class="header-sub">Departamento de Seguridad – Reporte de Permisos por Rol</div>
            <div class="datetime">Generado el: {{ now()->format('d/m/Y H:i:s') }} &nbsp;|&nbsp; Por: {{ strtoupper($admin) }}</div>
        </div>
    </header>
    <div class="header-stripe"></div>

    <footer>
        <div class="footer-row">
            <div class="footer-left">
                <strong style="color:#1e3a6b;">Hospital Escuela</strong><br>
                Departamento de Seguridad – Sistema de Gestión de Medicamentos<br>
                Descargado por: {{ strtoupper($admin) }}
            </div>
            <div class="footer-center"></div>
            <div class="footer-right">
                @if(!empty($htmlPreview))
                    Página 1 de 1
                @else
                    Página <script type="text/php">echo $PAGE_NUM;</script> de <script type="text/php">echo $PAGE_COUNT;</script>
                @endif
            </div>
        </div>
    </footer>

    {{-- ── SECTION TITLE ── --}}
    <div class="sub-header">Configuración de Permisos – {{ $fecha }}</div>

    {{-- ── ROLE SUMMARY BOX ── --}}
    @php
        $modsAsignados  = array_map('intval', $modulosAsignados ?? []);
        $accionesPorMod = $accionesAsignadasPorModulo ?? [];
        $modCatalog     = $moduloCatalogo ?? [];
        $accCatalog     = $accionCatalogo ?? ['VISUALIZAR', 'GUARDAR', 'ACTUALIZAR', 'ELIMINAR'];
        $totalModulos   = count($modsAsignados);
        $totalUsuarios  = count($usuariosDelRol ?? []);
    @endphp

    <div class="role-box">
        <div class="role-box-left">
            <div class="role-label">Rol</div>
            <div class="role-name">{{ $rolNombre }}</div>
        </div>
        <div class="role-box-right">
            <strong>{{ $totalModulos }}</strong> módulo(s) asignado(s)<br>
            <strong>{{ $totalUsuarios }}</strong> usuario(s) con este rol
        </div>
    </div>

    {{-- ── PERMISSIONS MATRIX TABLE ── --}}
    <table>
        <thead>
            <tr>
                <th class="col-module">Módulo</th>
                @foreach($accCatalog as $acc)
                    <th>{{ ucfirst(strtolower($acc)) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($modCatalog as $idModulo => $nombreModulo)
                @php
                    $isAssigned   = in_array((int)$idModulo, $modsAsignados, true);
                    $accionesModulo = array_map('strtoupper', $accionesPorMod[$idModulo] ?? []);
                    $rowClass     = $isAssigned ? 'row-assigned' : '';
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="col-module {{ !$isAssigned ? 'unassigned' : '' }}">
                        @if($isAssigned)
                            &#9679;&nbsp;{{ $nombreModulo }}
                        @else
                            {{ $nombreModulo }}
                        @endif
                    </td>
                    @foreach($accCatalog as $acc)
                        @php
                            $tieneAccion = $isAssigned && in_array(strtoupper($acc), $accionesModulo, true);
                        @endphp
                        <td>
                            @if($tieneAccion)
                                <span class="badge-yes">&#10003; Sí</span>
                            @else
                                <span class="badge-no">—</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── USERS LIST ── --}}
    @if(!empty($usuariosDelRol))
    <div class="users-section">
        <div class="users-title">Usuarios con el Rol {{ $rolNombre }}</div>
        <table class="users-table">
            <thead>
                <tr>
                    <th style="text-align:left; width:8%">#</th>
                    <th style="text-align:left; width:30%">Usuario</th>
                    <th style="text-align:left; width:40%">Correo</th>
                    <th style="text-align:center; width:22%">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usuariosDelRol as $i => $usu)
                <tr>
                    <td style="color:#1e3a6b; text-align:left;">{{ $i + 1 }}</td>
                    <td style="font-weight:700; text-align:left;">{{ strtoupper($usu['usuario'] ?? '') }}</td>
                    <td style="text-align:left; color:#1e3a6b;">{{ $usu['correo'] ?? '' }}</td>
                    <td style="text-align:center;">
                        @if((int)($usu['estado'] ?? 1) === 1)
                            <span class="badge-yes">Activo</span>
                        @else
                            <span class="badge-no">Inactivo</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="no-users">No hay usuarios registrados con el rol <strong>{{ $rolNombre }}</strong>.</div>
    @endif

</body>
</html>
