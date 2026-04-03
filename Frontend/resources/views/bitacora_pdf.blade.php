<!DOCTYPE html>
<html>
<head>
    <title>Bitácora de Auditoría – Hospital Escuela</title>
    <style>
        @page { margin: 0cm 0cm; }

        body {
            font-family: 'Helvetica', sans-serif;
            margin: 3.9cm 1.1cm 2.6cm;
            font-size: 8.5px;
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
            padding: 0.38cm 0.25cm;
            text-align: left;
            border: 1px solid #1e3a6b;
            text-transform: uppercase;
            font-weight: 700;
            font-size: 7.5px;
            letter-spacing: .3px;
        }

        td {
            padding: 0.3cm 0.25cm;
            border: 1px solid #e2e8f0;
            font-size: 8px;
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

        .footer-row    { display: table; width: 100%; }
        .footer-left   { display: table-cell; text-align: left;  vertical-align: middle; }
        .footer-center { display: table-cell; text-align: center; vertical-align: middle; color: #94a3b8; font-size: 7px; text-transform: uppercase; letter-spacing: .4px; }
        .footer-right  { display: table-cell; text-align: right; vertical-align: middle; }

        .badge-accion {
            display: inline-block;
            background: #1e3a6b;
            color: #fff;
            border-radius: 3px;
            padding: 1px 4px;
            font-size: 7px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <header>
        <img class="logo" src="{{ public_path('login-assets/images/logo-circle.png') }}" alt="Logo Hospital Escuela">
        <div class="header-text">
            <h1>Hospital Escuela</h1>
            <div class="header-sub">Bitácora de Auditoría del Sistema</div>
            <div class="datetime">Generado el: {{ now()->format('d/m/Y H:i:s') }} &nbsp;|&nbsp; Por: {{ strtoupper($admin) }}</div>
        </div>
    </header>
    <div class="header-stripe"></div>

    <div class="sub-header">Registro de Actividades – {{ $fecha }}</div>

    @if(!empty($filtros))
    <div class="filtros-bar">
        <strong>Filtros aplicados:</strong>
        @if(!empty($filtros['usuario'])) &nbsp;Usuario: <strong>{{ $filtros['usuario'] }}</strong> @endif
        @if(!empty($filtros['accion'])) &nbsp;| Acción: <strong>{{ $filtros['accion'] }}</strong> @endif
        @if(!empty($filtros['fecha_desde'])) &nbsp;| Desde: <strong>{{ $filtros['fecha_desde'] }}</strong> @endif
        @if(!empty($filtros['fecha_hasta'])) &nbsp;| Hasta: <strong>{{ $filtros['fecha_hasta'] }}</strong> @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width:4%">#ID</th>
                <th style="width:14%">Fecha / Hora</th>
                <th style="width:10%">Usuario</th>
                <th style="width:18%">Acción</th>
                <th style="width:30%">Descripción</th>
                <th style="width:16%">Ruta</th>
                <th style="width:8%">IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($registros as $reg)
            <tr>
                <td style="color:#6b7280;">#{{ $reg['id_bitacora'] ?? '' }}</td>
                <td style="font-weight:bold;">{{ $reg['fecha'] ?? '' }}</td>
                <td>{{ strtoupper($reg['nombre_usuario'] ?? '') }}</td>
                <td><span class="badge-accion">{{ $reg['tipo_accion'] ?? '' }}</span></td>
                <td style="color:#374151;">{{ $reg['descripcion'] ?? '' }}</td>
                <td style="font-family:monospace;font-size:7px;color:#6b7280;">{{ $reg['ruta'] ?? '' }}</td>
                <td style="font-family:monospace;font-size:7px;color:#6b7280;">{{ $reg['direccion_ip'] ?? '' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;color:#9ca3af;padding:0.5cm;">
                    No hay registros de auditoría para los filtros seleccionados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <footer>
        <div class="footer-row">
            <div class="footer-left">
                <strong>Total de registros:</strong> {{ count($registros) }}<br>
                Generado por: {{ strtoupper($admin) }}
            </div>
            <div class="footer-center">
                Hospital Escuela &bull; Sistema de Farmacovigilancia &bull; Documento Confidencial
            </div>
            <div class="footer-right">
                Fecha: {{ now()->format('d/m/Y') }}<br>
                Hora: {{ now()->format('H:i:s') }}
            </div>
        </div>
    </footer>

</body>
</html>
