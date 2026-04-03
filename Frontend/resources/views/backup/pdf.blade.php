<!DOCTYPE html>
<html>
<head>
    <title>Backups de Base de Datos – Hospital Escuela</title>
    <style>
        @page { margin: 0cm 0cm; }

        body {
            font-family: 'Helvetica', sans-serif;
            margin: 3.9cm 1.1cm 2.6cm;
            font-size: 9px;
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
            margin-top: 0.3cm;
            margin-bottom: 0.45cm;
            font-size: 11.5px;
            font-weight: 700;
            color: #1e3a6b;
            text-transform: uppercase;
            letter-spacing: .4px;
            border-left: 4px solid #2563eb;
            padding-left: 0.25cm;
        }

        .filtros-bar {
            margin-bottom: 0.35cm;
            font-size: 8px;
            color: #374151;
            background: #f0f4f8;
            padding: 0.15cm 0.3cm;
            border-radius: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.5cm;
        }

        th {
            background-color: #1e3a6b;
            color: white;
            padding: 0.42cm 0.3cm;
            text-align: left;
            border: 1px solid #1e3a6b;
            text-transform: uppercase;
            font-weight: 700;
            font-size: 8px;
            letter-spacing: .3px;
        }

        td {
            padding: 0.35cm 0.3cm;
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
            padding: 0.25cm 1.1cm;
        }

        .footer-row   { display: table; width: 100%; }
        .footer-left  { display: table-cell; text-align: left;  vertical-align: middle; }
        .footer-center{ display: table-cell; text-align: center; vertical-align: middle; color: #94a3b8; font-size: 7px; text-transform: uppercase; letter-spacing: .4px; }
        .footer-right { display: table-cell; text-align: right; vertical-align: middle; }

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
    @php
        $fechaHoraReporte = $fechaHoraReporte ?? ($fecha ?? now()->format('d/m/Y H:i:s'));
        $logoPath = public_path('login-assets/images/logo-circle.png');
        $logoSrc = null;
        if (is_file($logoPath) && is_readable($logoPath)) {
            $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            $mime = $ext === 'png' ? 'image/png' : ($ext === 'jpg' || $ext === 'jpeg' ? 'image/jpeg' : 'image/png');
            $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }
        if (is_string($fechaHoraReporte) && !preg_match('/\d{1,2}:\d{2}/', $fechaHoraReporte)) {
            $fechaHoraReporte .= ' ' . now()->format('H:i:s');
        }
    @endphp

    @if(!empty($htmlPreview))
    <div class="no-print">
        <div class="no-print-actions">
            <a href="{{ $backUrl ?? route('backup.index') }}" class="back-btn">Volver</a>
            <button onclick="window.print()" class="print-btn">Imprimir</button>
        </div>
    </div>
    @endif

    <header>
        @if($logoSrc)
            <img class="logo" src="{{ $logoSrc }}" alt="Logo Hospital Escuela">
        @else
            <div class="logo" style="color:#1e3a6b;font-size:18px;font-weight:700;line-height:2.0cm;text-align:center;">HE</div>
        @endif
        <div class="header-text">
            <h1>Hospital Escuela</h1>
            <div class="header-sub">Departamento de Seguridad y Administración de Usuarios</div>
            <div class="datetime">Generado el: {{ $fechaHoraReporte }}</div>
        </div>
    </header>
    <div class="header-stripe"></div>

    <div class="sub-header">Backups de Base de Datos</div>

    @if(!empty($filtros))
    <div class="filtros-bar">
        <strong>Filtros aplicados:</strong>
        @if(!empty($filtros['fecha'])) &nbsp;Fecha: <strong>{{ $filtros['fecha'] }}</strong> @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width:55%">Archivo</th>
                <th style="width:30%">Fecha de Creación</th>
                <th style="width:15%">Tamaño</th>
            </tr>
        </thead>
        <tbody>
            @forelse($backups as $b)
            <tr>
                <td style="font-weight:bold;font-family:monospace;font-size:8px;text-transform:none;">{{ $b['fileName'] ?? '' }}</td>
                <td>{{ isset($b['createdAt']) ? \Carbon\Carbon::parse($b['createdAt'])->format('d/m/Y H:i:s') : '' }}</td>
                <td>{{ $b['sizeMb'] ?? '0' }} MB</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align:center;color:#9ca3af;padding:0.5cm;">
                    No hay backups para los filtros seleccionados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <footer>
        <div class="footer-row">
            <div class="footer-left">
                <strong style="color:#1e3a6b;">Hospital Escuela</strong><br>
                Departamento de Seguridad – Sistema de Gestión de Medicamentos<br>
                Descargado por: {{ $admin ?? $usuario ?? 'SISTEMA' }}
            </div>
            <div class="footer-center"></div>
            <div class="footer-right">
                @if(!empty($htmlPreview))
                    Página 1 de 1
                @else
                    
                @endif
            </div>
        </div>
    </footer>

</body>
</html>
