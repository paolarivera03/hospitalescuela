<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reacción Adversa #{{ $reaccion['id_reaccion'] ?? '' }} – Hospital Escuela</title>
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
            padding-bottom: 0.3cm;
            margin-bottom: 0.5cm;
            font-size: 12px;
            font-weight: 700;
            color: #1e3a6b;
            text-transform: uppercase;
            letter-spacing: .4px;
            border-left: 4px solid #2563eb;
            padding-left: 0.25cm;
        }

        .section {
            margin-top: 0.3cm;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 9.5px;
            font-weight: 700;
            margin-bottom: 0.2cm;
            color: #1e3a6b;
            text-transform: uppercase;
            letter-spacing: .3px;
            border-left: 3px solid #2563eb;
            padding-left: 0.2cm;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.35cm;
            font-size: 8.5px;
        }

        .table th,
        .table td {
            padding: 0.28cm;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .table tr { page-break-inside: avoid; }

        .table th {
            background-color: #1e3a6b;
            color: white;
            text-transform: uppercase;
            font-weight: 700;
            font-size: 8.5px;
            letter-spacing: .3px;
        }

        .table tbody tr:nth-child(odd)  { background-color: #f1f5f9; }
        .table tbody tr:nth-child(even) { background-color: #ffffff; }

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

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 700;
            color: #fff;
        }

        .badge-registrada { background: #16a34a; }
        .badge-analisis   { background: #d97706; }
        .badge-cerrada    { background: #64748b; }
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
            <div class="header-sub">Unidad de Farmacovigilancia Clínica</div>
            <div class="datetime">Generado el: {{ $fechaHoraReporte }}</div>
        </div>
    </header>
    <div class="header-stripe"></div>

    <div class="sub-header">Reacción Adversa a Medicamentos (RAM) – N.° #{{ $reaccion['id_reaccion'] ?? '' }}</div>

    <main>
        <div class="section">
            <div class="section-title">Datos del Paciente</div>
            <table class="table">
                <tr>
                    <th>No. Expediente</th>
                    <td>{{ $reaccion['numero_expediente'] ?? '—' }}</td>
                    <th>Paciente</th>
                    <td>{{ $reaccion['nombre_completo'] ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Edad</th>
                    <td>{{ $reaccion['edad'] ?? '—' }}</td>
                    <th>Sexo</th>
                    <td>{{ $reaccion['sexo'] ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Sala</th>
                    <td>{{ $reaccion['sala'] ?? '—' }}</td>
                    <th>Cama</th>
                    <td>{{ $reaccion['numero_cama'] ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Diagnóstico</th>
                    <td colspan="3">{{ $reaccion['diagnostico'] ?? '—' }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Datos del Médico</div>
            <table class="table">
                <tr>
                    <th>Médico</th>
                    <td>{{ $reaccion['nombre_medico'] ?? $reaccion['nombre_completo_medico'] ?? '—' }}</td>
                    <th>No. Colegiación</th>
                    <td>{{ $reaccion['numero_colegiacion'] ?? '—' }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Reacción Adversa</div>
            <table class="table">
                @php
                    $estado = strtoupper($reaccion['estado'] ?? '');
                    $clase = 'badge-cerrada';
                    if ($estado === 'REGISTRADA') $clase = 'badge-registrada';
                    elseif (str_contains($estado, 'ANALISIS')) $clase = 'badge-analisis';
                    elseif (str_contains($estado, 'CERR')) $clase = 'badge-cerrada';
                @endphp
                <tr>
                    <th style="width:22%">Inicio</th>
                    <td>{{ !empty($reaccion['fecha_inicio_reaccion']) ? \Carbon\Carbon::parse($reaccion['fecha_inicio_reaccion'])->format('d/m/Y') : '—' }}</td>
                    <th style="width:22%">Fin</th>
                    <td>{{ !empty($reaccion['fecha_fin_reaccion']) ? \Carbon\Carbon::parse($reaccion['fecha_fin_reaccion'])->format('d/m/Y') : '—' }}</td>
                </tr>
                <tr>
                    <th>Estado</th>
                    <td><span class="badge {{ $clase }}">{{ $estado ?: 'SIN ESTADO' }}</span></td>
                    <th>Desenlace</th>
                    <td>{{ $reaccion['desenlace'] ?? '—' }}</td>
                </tr>
                @if(!empty($reaccion['hospitalizacion']))
                <tr>
                    <th>Hospitalización</th>
                    <td colspan="3">{{ $reaccion['hospitalizacion'] }}</td>
                </tr>
                @endif
                @if(!empty($reaccion['gravedad']))
                <tr>
                    <th>Gravedad</th>
                    <td colspan="3">{{ $reaccion['gravedad'] }}</td>
                </tr>
                @endif
                <tr>
                    <th>Descripción</th>
                    <td colspan="3">{{ $reaccion['descripcion_reaccion'] ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Observaciones</th>
                    <td colspan="3">{{ $reaccion['observaciones'] ?? '—' }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Medicamentos Relacionados</div>
            @if(!empty($detalles))
                <table class="table">
                    <thead>
                        <tr>
                            <th>Medicamento</th>
                            <th>Lote</th>
                            <th>Dosis / Posología</th>
                            <th>Vía</th>
                            <th>Inicio Uso</th>
                            <th>Fin Uso</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detalles as $d)
                        <tr>
                            <td>{{ $d['medicamento'] ?? ($d['nombre_comercial'] ?? '—') }}</td>
                            <td>{{ $d['numero_lote'] ?? '—' }}</td>
                            <td>{{ $d['dosis_posologia'] ?? '—' }}</td>
                            <td>{{ $d['via_administracion'] ?? '—' }}</td>
                            <td>{{ !empty($d['fecha_inicio_uso']) ? \Carbon\Carbon::parse($d['fecha_inicio_uso'])->format('d/m/Y') : '—' }}</td>
                            <td>{{ !empty($d['fecha_fin_uso'])   ? \Carbon\Carbon::parse($d['fecha_fin_uso'])->format('d/m/Y')   : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="font-style:italic; color:#64748b; font-size:8.5px;">No se registraron medicamentos.</p>
            @endif
        </div>

        <div class="section">
            <div class="section-title">Consecuencias</div>
            @if(!empty($consecuencias))
                <table class="table">
                    <thead>
                        <tr>
                            <th>Descripción</th>
                            <th style="width:120px">Gravedad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($consecuencias as $c)
                        <tr>
                            <td>{{ $c['descripcion_consecuencia'] ?? '—' }}</td>
                            <td>{{ $c['gravedad'] ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="font-style:italic; color:#64748b; font-size:8.5px;">No se registraron consecuencias.</p>
            @endif
        </div>
    </main>

    <footer>
        <div class="footer-row">
            <div class="footer-left">
                <strong style="color:#1e3a6b;">Hospital Escuela</strong><br>
                Unidad de Farmacovigilancia – Sistema de Gestión de Medicamentos<br>
                Descargado por: {{ $usuario ?? $admin ?? 'SISTEMA' }}
            </div>
            <div class="footer-center">Documento oficial – Información clínica confidencial</div>
            <div class="footer-right">
                <script type="text/php">echo "P\xc3\xa1gina $PAGE_NUM de $PAGE_COUNT";</script>
            </div>
        </div>
    </footer>

</body>
</html>

