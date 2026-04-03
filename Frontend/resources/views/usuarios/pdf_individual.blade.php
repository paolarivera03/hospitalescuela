<!DOCTYPE html>
<html>
<head>
    <title>Usuario – Hospital Escuela</title>
    <style>
        @page { margin: 0cm 0cm; }

        body {
            font-family: 'Helvetica', sans-serif;
            margin: 3.9cm 1.6cm 2.6cm;
            font-size: 11px;
            color: #1f2937;
        }

        header {
            position: fixed;
            top: 0cm; left: 0cm; right: 0cm;
            height: 3.4cm;
            background-color: #1e3a6b;
            color: white;
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
            border: 2px solid rgba(255,255,255,.35);
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
            background-color: #2563eb;
        }

        .sub-header {
            margin-top: 0.4cm;
            margin-bottom: 0.5cm;
            font-size: 12px;
            font-weight: 700;
            color: #1e3a6b;
            text-transform: uppercase;
            letter-spacing: .4px;
            border-left: 4px solid #2563eb;
            padding-left: 0.25cm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #1e3a6b;
            color: white;
            padding: 0.45cm 0.4cm;
            text-align: left;
            border: 1px solid #1e3a6b;
            width: 35%;
            text-transform: uppercase;
            font-weight: 700;
            font-size: 9px;
            letter-spacing: .3px;
        }

        td {
            padding: 0.45cm 0.4cm;
            border: 1px solid #e2e8f0;
            text-transform: uppercase;
            font-size: 9px;
        }

        tbody tr:nth-child(odd)  { background-color: #f1f5f9; }
        tbody tr:nth-child(even) { background-color: #ffffff; }

        footer {
            position: fixed;
            bottom: 0cm; left: 0cm; right: 0cm;
            height: 2.2cm;
            background-color: #f0f4f8;
            border-top: 3px solid #2563eb;
            font-size: 7.5px;
            color: #475569;
            padding: 0.25cm 1.6cm;
        }

        .footer-row   { display: table; width: 100%; }
        .footer-left  { display: table-cell; text-align: left;  vertical-align: middle; }
        .footer-center{ display: table-cell; text-align: center; vertical-align: middle; color: #94a3b8; font-size: 7px; text-transform: uppercase; letter-spacing: .4px; }
        .footer-right { display: table-cell; text-align: right; vertical-align: middle; }
    </style>
</head>
<body>
    @php
        $fechaHoraReporte = $fechaHoraReporte ?? ($fecha ?? now()->format('d/m/Y H:i:s'));
        if (is_string($fechaHoraReporte) && !preg_match('/\d{1,2}:\d{2}/', $fechaHoraReporte)) {
            $fechaHoraReporte .= ' ' . now()->format('H:i:s');
        }
    @endphp

    <header>
        <img class="logo" src="{{ public_path('login-assets/images/logo-circle.png') }}" alt="Logo Hospital Escuela">
        <div class="header-text">
            <h1>Hospital Escuela</h1>
            <div class="header-sub">Departamento de Seguridad y Administración de Usuarios</div>
            <div class="datetime">Generado el: {{ $fechaHoraReporte }}</div>
        </div>
    </header>
    <div class="header-stripe"></div>

    <div class="sub-header">Usuario del Sistema</div>

    <table>
        <tr>
            <th>ID USUARIO</th>
            <td>{{ $u['id_usuario'] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>NOMBRE DE ACCESO</th>
            <td>{{ $u['usuario'] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>NOMBRE COMPLETO</th>
            <td>{{ ($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? '') }}</td>
        </tr>
        <tr>
            <th>CORREO ELECTRÓNICO</th>
            <td style="text-transform: lowercase;">{{ $u['correo'] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>ROL ASIGNADO</th>
            {{-- SOLUCIÓN AL ERROR: Verificamos ambas llaves posibles --}}
            <td>{{ $u['rol'] ?? $u['rol_nombre'] ?? 'SIN ROL ASIGNADO' }}</td>
        </tr>
        <tr>
            <th>TELÉFONO</th>
            <td>{{ $u['telefono'] ?? 'NO REGISTRADO' }}</td>
        </tr>
        <tr>
            <th>ESTADO ACTUAL</th>
            <td>{{ $u['estado'] ?? 'INACTIVO' }}</td>
        </tr>
    </table>

    <footer>
        <div class="footer-row">
            <div class="footer-left">
                <strong style="color:#1e3a6b;">Hospital Escuela</strong><br>
                Departamento de Seguridad – Sistema de Gestión de Medicamentos<br>
                Descargado por: {{ $admin ?? $usuario ?? 'SISTEMA' }}
            </div>
            <div class="footer-center">Documento oficial – Información confidencial</div>
            <div class="footer-right">
                <script type="text/php">echo "P\xc3\xa1gina $PAGE_NUM de $PAGE_COUNT";</script>
            </div>
        </div>
    </footer>

    <script type="text/php">
        if ( isset($pdf) ) {
            $pdf->page_script('
                $font = $fontMetrics->get_font("helvetica", "bold");
                // Coordenadas para vertical: 500 es derecha, 770 es abajo
                $pdf->text(500, 775, "Página $PAGE_NUM de $PAGE_COUNT", $font, 10, array(0,0,0));
            ');
        }
    </script>
</body>
</html>
