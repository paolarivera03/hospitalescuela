<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reacciones Adversas – Hospital Escuela</title>
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
            margin-bottom: 0.2cm;
            font-size: 11.5px;
            font-weight: 700;
            color: #1e3a6b;
            text-transform: uppercase;
            letter-spacing: .4px;
            border-left: 4px solid #2563eb;
            padding-left: 0.25cm;
        }

        .meta {
            margin-top: 0;
            margin-bottom: 0.4cm;
            font-size: 8.5px;
            color: #475569;
            padding-left: 0.35cm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.5cm;
        }

        thead { display: table-header-group; }
        tr    { page-break-inside: avoid; }

        th {
            background-color: #1e3a6b;
            color: white;
            padding: 0.35cm 0.2cm;
            text-align: left;
            border: 1px solid #1e3a6b;
            text-transform: uppercase;
            font-weight: 700;
            font-size: 8px;
            letter-spacing: .3px;
        }

        td {
            padding: 0.3cm 0.2cm;
            border: 1px solid #e2e8f0;
            font-size: 8px;
            vertical-align: top;
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

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: 700;
            color: #fff;
        }

        .registrada { background: #16a34a; }
        .analisis   { background: #d97706; }
        .cerrada    { background: #64748b; }

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
            background: #222c5e;
            color: #fff;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
        }

        .print-btn:hover { background: #1a2248; }

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

        .back-btn:hover {
            background: #f8fafc;
            color: #0f172a;
            text-decoration: none;
        }

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    @php
        $fechaHoraReporte = $fechaHoraReporte ?? ($fechaGeneracion ?? now()->format('d/m/Y H:i:s'));
        $isHtmlPreview = !empty($htmlPreview);
        $logoSrc = $isHtmlPreview
            ? asset('login-assets/images/logo-circle.png')
            : public_path('login-assets/images/logo-circle.png');
        if (is_string($fechaHoraReporte) && !preg_match('/\d{1,2}:\d{2}/', $fechaHoraReporte)) {
            $fechaHoraReporte .= ' ' . now()->format('H:i:s');
        }
    @endphp

    @if($isHtmlPreview)
    <div class="no-print">
        <div class="no-print-actions">
            <a href="{{ route('reacciones_adversas.index') }}" class="back-btn">Volver</a>
            <button onclick="window.print()" class="print-btn">Imprimir</button>
        </div>
    </div>
    @endif

    <header>
        <img class="logo" src="{{ $logoSrc }}" alt="Logo Hospital Escuela">
        <div class="header-text">
            <h1>Hospital Escuela</h1>
            <div class="header-sub">Unidad de Farmacovigilancia Clínica</div>
            <div class="datetime">Generado el: {{ $fechaHoraReporte }}</div>
        </div>
    </header>
    <div class="header-stripe"></div>

    <div class="sub-header">Reacciones Adversas a Medicamentos (RAM)</div>
    <div class="meta">Total de registros: {{ $totalRegistros }}</div>

    <footer>
        <div class="footer-row">
            <div class="footer-left">
                <strong style="color:#1e3a6b;">Hospital Escuela</strong><br>
                Unidad de Farmacovigilancia – Sistema de Gestión de Medicamentos<br>
                Descargado por: {{ $usuario ?? $admin ?? 'SISTEMA' }}
            </div>
            <div class="footer-center">Documento oficial – Información clínica confidencial</div>
            <div class="footer-right">
                @if($isHtmlPreview)
                    Página 1
                @else
                    Página <script type="text/php">echo $PAGE_NUM;</script> de <script type="text/php">echo $PAGE_COUNT;</script>
                @endif
            </div>
        </div>
    </footer>

    <main>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">N°</th>
                    <th style="width: 18%;">Paciente</th>
                    <th style="width: 12%;">Expediente</th>
                    <th style="width: 18%;">Médico</th>
                    <th style="width: 22%;">Descripción de la reacción</th>
                    <th style="width: 10%;">Fecha inicio</th>
                    <th style="width: 10%;">Fecha fin</th>
                    <th style="width: 10%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reacciones as $index => $r)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $r->paciente }}</td>
                        <td>{{ $r->numero_expediente ?? '—' }}</td>
                        <td>{{ $r->medico }}</td>
                        <td>
                            {{ $r->descripcion_reaccion ?? $r->desenlace ?? 'Sin descripción' }}
                        </td>
                        <td>
                            {{ $r->fecha_inicio_reaccion ? \Carbon\Carbon::parse($r->fecha_inicio_reaccion)->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            {{ $r->fecha_fin_reaccion ? \Carbon\Carbon::parse($r->fecha_fin_reaccion)->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            @php
                                $estado = strtoupper($r->estado ?? '');
                                $clase = 'cerrada';

                                if ($estado === 'REGISTRADA') $clase = 'registrada';
                                elseif ($estado === 'EN_ANALISIS' || $estado === 'EN ANÁLISIS') $clase = 'analisis';
                                elseif ($estado === 'CERRADA') $clase = 'cerrada';
                            @endphp

                            <span class="badge {{ $clase }}">
                                {{ str_replace('_', ' ', $estado ?: 'SIN ESTADO') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding:20px;">
                            No hay registros disponibles.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </main>

    <script type="text/php">
        if ( isset($pdf) ) {
            $pdf->page_script('
                $font = $fontMetrics->get_font("helvetica", "bold");
                $pdf->text(720, 555, "Página $PAGE_NUM de $PAGE_COUNT", $font, 10, array(0,0,0));
            ');
        }
    </script>

</body>
</html>
