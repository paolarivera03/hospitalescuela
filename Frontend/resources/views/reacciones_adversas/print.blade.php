<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reacción Adversa #{{ $reaccion['id_reaccion'] ?? '' }} - Hospital Escuela</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #1f2937;
            background: #eef2f7;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 12px 24px;
            background: #eff6ff;
            border-bottom: 1px solid #bfdbfe;
        }

        .btn-back,
        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-back {
            border: 1px solid #334155;
            background: #ffffff;
            color: #1f2937;
        }

        .btn-print {
            border: 0;
            background: #1e3a6b;
            color: #ffffff;
        }

        .page {
            width: 900px;
            max-width: calc(100% - 32px);
            margin: 24px auto;
            background: #ffffff;
            box-shadow: 0 2px 16px rgba(15, 23, 42, 0.1);
        }

        .page-header {
            display: table;
            width: 100%;
            background: #1e3a6b;
            color: #ffffff;
        }

        .header-logo-cell,
        .header-center-cell,
        .header-meta-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .header-logo-cell {
            width: 90px;
            padding: 14px 0 14px 24px;
        }

        .header-logo-cell img {
            width: 62px;
            height: 62px;
            display: block;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.35);
            background: #ffffff;
            object-fit: cover;
        }

        .header-center-cell {
            padding: 14px 10px;
            text-align: center;
        }

        .header-center-cell h1 {
            margin: 0 0 4px;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 11px;
            opacity: 0.85;
        }

        .header-meta-cell {
            width: 145px;
            padding: 14px 24px 14px 10px;
            text-align: right;
            font-size: 10px;
            line-height: 1.8;
            opacity: 0.92;
        }

        .header-stripe {
            height: 5px;
            background: #2563eb;
        }

        .doc-bar {
            display: table;
            width: 100%;
            padding: 8px 24px;
            background: #f0f4f8;
            border-bottom: 1px solid #cbd5e1;
        }

        .doc-bar-title,
        .doc-bar-num {
            display: table-cell;
            vertical-align: middle;
        }

        .doc-bar-title {
            font-size: 13px;
            font-weight: 900;
            color: #1e3a6b;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .doc-bar-num {
            text-align: right;
            font-size: 11px;
            font-weight: 700;
            color: #475569;
        }

        .page-body {
            padding: 20px 24px;
        }

        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .section-title {
            margin-bottom: 8px;
            padding: 5px 8px;
            border-left: 4px solid #2563eb;
            background: #f0f4f8;
            color: #1e3a6b;
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
            margin-bottom: 4px;
        }

        .data-table th,
        .data-table td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .data-table th {
            background: #1e3a6b;
            color: #ffffff;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            border-color: #1e3a6b;
        }

        .data-table tbody tr:nth-child(odd) {
            background: #f1f5f9;
        }

        .data-table tbody tr:nth-child(even) {
            background: #ffffff;
        }

        .value-block {
            white-space: pre-wrap;
            word-break: break-word;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-registrada { background: #16a34a; }
        .badge-analisis { background: #d97706; }
        .badge-cerrada { background: #64748b; }
        .badge-default { background: #0891b2; }

        .photo-grid {
            display: table;
            width: 100%;
            table-layout: fixed;
            border-spacing: 0;
        }

        .photo-cell {
            display: table-cell;
            width: 50%;
            padding-right: 12px;
            vertical-align: top;
        }

        .photo-cell:last-child {
            padding-right: 0;
            padding-left: 12px;
        }

        .photo-card {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 10px;
            min-height: 220px;
        }

        .photo-label {
            margin-bottom: 10px;
            color: #1e3a6b;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .photo-frame {
            height: 180px;
            border: 1px dashed #cbd5e1;
            background: #ffffff;
            text-align: center;
            line-height: 178px;
            overflow: hidden;
        }

        .photo-frame img {
            max-width: 100%;
            max-height: 100%;
            vertical-align: middle;
            line-height: normal;
        }

        .photo-empty {
            display: inline-block;
            line-height: normal;
            vertical-align: middle;
            color: #64748b;
            font-weight: 700;
        }

        .signatures {
            display: table;
            width: 100%;
            margin-top: 28px;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
        }

        .sig-cell {
            display: table-cell;
            width: 33.33%;
            padding-right: 16px;
            vertical-align: top;
        }

        .sig-cell:last-child {
            padding-right: 0;
        }

        .sig-line {
            margin-top: 30px;
            padding-top: 5px;
            border-top: 1.5px solid #334155;
            color: #475569;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .page-footer {
            display: table;
            width: 100%;
            padding: 10px 24px;
            background: #f0f4f8;
            border-top: 3px solid #2563eb;
            color: #475569;
            font-size: 10px;
            line-height: 1.7;
        }

        .footer-left,
        .footer-center,
        .footer-right {
            display: table-cell;
            vertical-align: middle;
        }

        .footer-center {
            color: #94a3b8;
            font-size: 9px;
            text-align: center;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .footer-right {
            text-align: right;
        }

        .footer-left strong,
        .footer-right strong {
            color: #1e3a6b;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .toolbar {
                display: none;
            }

            .page {
                width: 100%;
                max-width: 100%;
                margin: 0;
                box-shadow: none;
            }

            .page-header,
            .header-stripe,
            .doc-bar,
            .section-title,
            .data-table th,
            .data-table tbody tr:nth-child(odd) {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                margin: 0.7cm 1cm;
            }
        }
    </style>
</head>
<body>
    @php
        $estado = strtoupper((string) ($reaccion['estado'] ?? 'SIN ESTADO'));
        $badgeCls = str_contains($estado, 'REGISTRADA')
            ? 'badge-registrada'
            : (str_contains($estado, 'ANALISIS')
                ? 'badge-analisis'
                : (str_contains($estado, 'CERR') ? 'badge-cerrada' : 'badge-default'));

        $formatDate = function ($value) {
            return !empty($value) ? \Carbon\Carbon::parse($value)->format('d/m/Y') : '—';
        };

        $formatText = function ($value) {
            $text = trim((string) ($value ?? ''));
            return $text !== '' ? $text : '—';
        };

        $fotoMedicamento = !empty($reaccion['ruta_foto_medicamento'])
            ? 'http://localhost:3000/uploads/' . ltrim($reaccion['ruta_foto_medicamento'], '/')
            : null;
        $fotoEvidencia = !empty($reaccion['ruta_foto_evidencia'])
            ? 'http://localhost:3000/uploads/' . ltrim($reaccion['ruta_foto_evidencia'], '/')
            : null;
    @endphp

    <div class="toolbar">
        <a href="{{ $backRoute ?? route('reacciones_adversas.index') }}" class="btn-back">&#8592; Volver</a>
        <button type="button" onclick="window.print()" class="btn-print">&#128438; Imprimir</button>
    </div>

    <div class="page">
        <div class="page-header">
            <div class="header-logo-cell">
                <img src="{{ asset('login-assets/images/logo-circle.png') }}" alt="Logo Hospital Escuela">
            </div>
            <div class="header-center-cell">
                <h1>Hospital Escuela</h1>
                <div class="subtitle">Unidad de Farmacovigilancia Clínica</div>
            </div>
            <div class="header-meta-cell">
                Fecha: {{ now()->format('d/m/Y') }}<br>
                Hora: {{ now()->format('H:i') }}<br>
                N.° #{{ $reaccion['id_reaccion'] ?? '' }}
            </div>
        </div>
        <div class="header-stripe"></div>

        <div class="doc-bar">
            <div class="doc-bar-title">Reacción Adversa a Medicamentos (RAM)</div>
            <div class="doc-bar-num">Registro N.° #{{ $reaccion['id_reaccion'] ?? '' }}</div>
        </div>

        <div class="page-body">
            <div class="section">
                <div class="section-title">Datos del Paciente</div>
                <table class="data-table">
                    <tbody>
                        <tr>
                            <th style="width:20%">No. Expediente</th>
                            <td style="width:30%">{{ $formatText($reaccion['numero_expediente'] ?? null) }}</td>
                            <th style="width:20%">Nombre Completo</th>
                            <td>{{ $formatText($reaccion['nombre_completo'] ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Edad</th>
                            <td>{{ $formatText($reaccion['edad'] ?? null) }}</td>
                            <th>Sexo</th>
                            <td>{{ $formatText($reaccion['sexo'] ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Sala</th>
                            <td>{{ $formatText($reaccion['sala'] ?? null) }}</td>
                            <th>No. Cama</th>
                            <td>{{ $formatText($reaccion['numero_cama'] ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Diagnóstico</th>
                            <td colspan="3"><div class="value-block">{{ $formatText($reaccion['diagnostico'] ?? null) }}</div></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Médico Responsable</div>
                <table class="data-table">
                    <tbody>
                        <tr>
                            <th style="width:20%">Médico</th>
                            <td style="width:30%">{{ $formatText($reaccion['nombre_medico'] ?? ($reaccion['nombre_completo_medico'] ?? null)) }}</td>
                            <th style="width:20%">No. Colegiación</th>
                            <td>{{ $formatText($reaccion['numero_colegiacion'] ?? null) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Datos de la Reacción Adversa</div>
                <table class="data-table">
                    <tbody>
                        <tr>
                            <th style="width:20%">Fecha Inicio</th>
                            <td style="width:30%">{{ $formatDate($reaccion['fecha_inicio_reaccion'] ?? null) }}</td>
                            <th style="width:20%">Fecha Fin</th>
                            <td>{{ $formatDate($reaccion['fecha_fin_reaccion'] ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Estado</th>
                            <td><span class="badge {{ $badgeCls }}">{{ $estado }}</span></td>
                            <th>Desenlace</th>
                            <td>{{ $formatText($reaccion['desenlace'] ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Hospitalización</th>
                            <td>{{ $formatText($reaccion['hospitalizacion'] ?? null) }}</td>
                            <th>Gravedad</th>
                            <td>{{ $formatText($reaccion['gravedad'] ?? null) }}</td>
                        </tr>
                        <tr>
                            <th>Descripción</th>
                            <td colspan="3"><div class="value-block">{{ $formatText($reaccion['descripcion_reaccion'] ?? null) }}</div></td>
                        </tr>
                        <tr>
                            <th>Observaciones</th>
                            <td colspan="3"><div class="value-block">{{ $formatText($reaccion['observaciones'] ?? null) }}</div></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Medicamentos Relacionados</div>
                @if(!empty($detalles))
                    <table class="data-table">
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
                            @foreach($detalles as $detalle)
                                <tr>
                                    <td><div class="value-block">{{ $formatText($detalle['medicamento'] ?? ($detalle['nombre_comercial'] ?? null)) }}</div></td>
                                    <td>{{ $formatText($detalle['numero_lote'] ?? null) }}</td>
                                    <td><div class="value-block">{{ $formatText($detalle['dosis_posologia'] ?? null) }}</div></td>
                                    <td>{{ $formatText($detalle['via_administracion'] ?? null) }}</td>
                                    <td>{{ $formatDate($detalle['fecha_inicio_uso'] ?? null) }}</td>
                                    <td>{{ $formatDate($detalle['fecha_fin_uso'] ?? null) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <table class="data-table">
                        <tbody>
                            <tr>
                                <td>No se registraron medicamentos.</td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="section">
                <div class="section-title">Consecuencias</div>
                @if(!empty($consecuencias))
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Descripción</th>
                                <th style="width:160px">Gravedad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($consecuencias as $consecuencia)
                                <tr>
                                    <td><div class="value-block">{{ $formatText($consecuencia['descripcion_consecuencia'] ?? null) }}</div></td>
                                    <td>{{ $formatText($consecuencia['gravedad'] ?? null) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <table class="data-table">
                        <tbody>
                            <tr>
                                <td>No se registraron consecuencias.</td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="section">
                <div class="section-title">Evidencias Fotográficas</div>
                <div class="photo-grid">
                    <div class="photo-cell">
                        <div class="photo-card">
                            <div class="photo-label">Foto del medicamento</div>
                            <div class="photo-frame">
                                @if($fotoMedicamento)
                                    <img src="{{ $fotoMedicamento }}" alt="Foto del medicamento">
                                @else
                                    <span class="photo-empty">No hay foto del medicamento.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="photo-cell">
                        <div class="photo-card">
                            <div class="photo-label">Evidencia fotográfica</div>
                            <div class="photo-frame">
                                @if($fotoEvidencia)
                                    <img src="{{ $fotoEvidencia }}" alt="Evidencia fotográfica">
                                @else
                                    <span class="photo-empty">No hay evidencia fotográfica.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="signatures">
                <div class="sig-cell">
                    <div class="sig-line">Firma del Médico Responsable</div>
                </div>
                <div class="sig-cell">
                    <div class="sig-line">Sello de Farmacología Clínica</div>
                </div>
                <div class="sig-cell">
                    <div class="sig-line">Revisado por</div>
                </div>
            </div>
        </div>

        <div class="page-footer">
            <div class="footer-left">
                <strong>Hospital Escuela</strong><br>
                Unidad de Farmacovigilancia Clínica<br>
                Sistema de Gestión de Medicamentos
            </div>
            <div class="footer-center">Documento oficial - Información clínica confidencial</div>
            <div class="footer-right">
                Generado el: {{ now()->format('d/m/Y H:i') }}<br>
                <strong>Uso exclusivo del personal autorizado</strong>
            </div>
        </div>
    </div>
</body>
</html>
