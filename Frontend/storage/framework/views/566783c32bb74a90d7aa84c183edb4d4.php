

<?php $__env->startSection('title', 'Reacciones Adversas - Hospital Escuela'); ?>
<?php $__env->startSection('header', 'Reacciones Adversas'); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .btn.btn-primary {
        background: #222c5e;
        border-color: #222c5e;
        color: #ffffff;
    }

    .btn.btn-primary:hover,
    .btn.btn-primary:focus {
        background: #0a4898;
        border-color: #0a4898;
        color: #ffffff;
    }

    .btn.btn-secondary,
    .btn.btn-outline-secondary {
        background: #ffffff;
        border-color: #d1d5db;
        color: #374151;
    }

    .btn.btn-secondary:hover,
    .btn.btn-secondary:focus,
    .btn.btn-outline-secondary:hover,
    .btn.btn-outline-secondary:focus {
        background: #f3f4f6;
        border-color: #9ca3af;
        color: #111827;
    }

    #searchInput::placeholder {
        color: rgba(107, 114, 128, 0.8);
    }

    #perPageSelect {
        padding-right: 28px;
    }

    #perPageSelect option {
        color: #000 !important;
        background: #fff;
    }

    .filter-btn {
        background: #ffffff;
        border: 1px solid #d1d5db;
        color: #374151;
    }

    .filter-btn:hover,
    .filter-btn:focus {
        background: #f3f4f6;
        border-color: #9ca3af;
        color: #111827;
    }

    .filter-btn-active {
        background: #222c5e !important;
        border-color: #222c5e !important;
        color: #ffffff !important;
    }

    .filter-btn-active:hover,
    .filter-btn-active:focus {
        background: #0a4898 !important;
        border-color: #0a4898 !important;
        color: #ffffff !important;
    }

    .enviados-scroll {
        max-height: 260px;
        overflow-y: auto;
        overflow-x: auto;
    }

    .section-separator {
        position: relative;
        height: 20px;
        margin: 10px 2px 18px;
    }

    .section-separator::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        border-top: 1px dashed #cbd5e1;
    }

    .section-separator span {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #f8fafc;
        color: #64748b;
        font-size: .75rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        padding: 0 10px;
        border-radius: 999px;
    }

    .pagination-strip {
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
        backdrop-filter: none !important;
    }
    .page-item.active .page-link {
        background-color: #0d6efd;
        color: #ffffff;
    }
</style>
<?php $__env->stopPush(); ?>

<?php
    $permRAM = $reaccionPermisos ?? [];
    $canGuardarRAM = (bool) ($permRAM['guardar'] ?? true);
    $canActualizarRAM = (bool) ($permRAM['actualizar'] ?? true);
    $canEliminarRAM = (bool) ($permRAM['eliminar'] ?? true);

    $total = is_array($reacciones) ? count($reacciones) : 0;
    $hoy = 0;
    $semana = 0;
    $analisis = 0;
    $registradas = 0;

    foreach (($reacciones ?? []) as $r) {
        $estado = strtoupper($r['estado'] ?? '');
        if ($estado === 'EN ANALISIS' || $estado === 'ANALISIS' || $estado === 'EN_ANALISIS') $analisis++;
        if ($estado === 'REGISTRADA') $registradas++;

        $fi = $r['fecha_inicio_reaccion'] ?? null;
        if ($fi) {
            $dt = \Carbon\Carbon::parse($fi);
            if ($dt->isToday()) $hoy++;
            if ($dt->greaterThanOrEqualTo(\Carbon\Carbon::now()->startOfWeek())) $semana++;
        }
    }

    function estadoBadge($estado)
    {
        $e = strtoupper(str_replace('_', ' ', $estado ?? 'SIN ESTADO'));

        return match (true) {
            str_contains($e, 'REGISTRADA') => ['bg' => 'success', 'icon' => 'fa-circle-check', 'text' => $e],
            str_contains($e, 'ANALISIS') => ['bg' => 'warning', 'icon' => 'fa-eye', 'text' => $e],
            str_contains($e, 'CERR') => ['bg' => 'secondary', 'icon' => 'fa-lock', 'text' => $e],
            default => ['bg' => 'info', 'icon' => 'fa-circle-info', 'text' => $e],
        };
    }
?>

<?php if(!empty($errorMessage)): ?>
    <div class="alert alert-warning">
        <strong>Atención:</strong> <?php echo e($errorMessage); ?>

    </div>
<?php endif; ?>

<div class="p-4 p-md-5">

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1f2937;">Reacciones Adversas</h4>
            <p class="text-muted mb-0">
                Listado general de Reacciones Adversas registradas en el sistema.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge rounded-pill text-bg-light fw-bold px-3 py-2">
                <i class="fa-solid fa-list me-2"></i> Total: <?php echo e($total); ?>

            </span>

                <button type="button"
                        class="btn rounded-pill fw-bold text-white px-4 shadow-sm"
                        style="background-color: #222c5e; border-color: #222c5e;"
                        onclick="exportarPDFReacciones()">
                    <i class="fa-solid fa-chart-column me-2"></i> Reporte
                </button>

                <?php if($canGuardarRAM): ?>
                    <a href="<?php echo e(route('reacciones_adversas.create')); ?>"
                       class="btn rounded-pill fw-bold text-white px-4 shadow-sm"
                       style="background-color: #019504; border-color: #019504;">
                        <i class="fa-solid fa-plus me-2"></i> Nuevo
                    </a>
                <?php endif; ?>
        </div>
</div>

<div class="glass p-3 mb-3">
    
    <div class="row g-2 align-items-center mb-2">
        <div class="col-md-5">
            <div class="input-group search-pill">
                <span class="input-group-text bg-white border-0 ps-3 text-muted">
                    <i class="fas fa-search"></i>
                </span>
                <input id="searchInput"
                       class="form-control border-0 px-2"
                       placeholder="BUSCAR POR PACIENTE, MÉDICO, ESTADO..."
                       style="text-transform:uppercase;">
                <button type="button" id="clearRASearch" class="btn btn-white border-0 bg-white text-danger px-3 d-none"><i class="fas fa-times"></i></button>
            </div>
        </div>

        <div class="col-md-7">
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-sm fw-bold px-3 filter-btn filter-btn-active" data-filter="ALL" style="border-radius:999px;">
                    <i class="fa-solid fa-globe me-1"></i> Todas
                </button>
                <button class="btn btn-sm fw-bold px-3 filter-btn" data-filter="REGISTRADA" style="border-radius:999px;">
                    <i class="fa-solid fa-circle-check me-1"></i> Registrada
                </button>
                <button class="btn btn-sm fw-bold px-3 filter-btn" data-filter="ANALISIS" style="border-radius:999px;">
                    <i class="fa-solid fa-eye me-1"></i> Analisis
                </button>
                <button class="btn btn-sm fw-bold px-3 filter-btn" data-filter="CERR" style="border-radius:999px;">
                    <i class="fa-solid fa-lock me-1"></i> Cerrada
                </button>
            </div>
        </div>
    </div>

    
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="text-muted fw-bold">Ver</span>
        <select id="perPageSelect" class="form-select form-select-sm fw-bold" style="width:80px; border-radius:12px;">
            <option value="5"  style="color:#000;background:#fff;">5</option>
            <option value="10" style="color:#000;background:#fff;">10</option>
            <option value="15" style="color:#000;background:#fff;">15</option>
            <option value="20" style="color:#000;background:#fff;">20</option>
        </select>
        <span class="text-muted fw-bold">Registros</span>
        <select id="sortSelect" class="form-select form-select-sm fw-bold" style="width:175px; border-radius:12px;">
            <option value="new">Más recientes</option>
            <option value="old">Más antiguas</option>
            <option value="id_desc">ID (desc)</option>
            <option value="id_asc">ID (asc)</option>
        </select>
    </div>
</div>

<div class="glass p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0" id="tablaRAM">
            <thead class="table-light">
                <tr class="text-uppercase" style="font-size:.78rem; letter-spacing:.08em;">
                    <th class="ps-4">No</th>
                    <th>Nombre del Paciente</th>
                    <th>Nombre del Médico</th>
                    <th>Descripción de la Reacción</th>
                    <th>Meds. por Paciente</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Estado</th>
                    <th class="text-end pe-4">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $reacciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $id = $r['id_reaccion'] ?? '';
                        $pac = $r['nombre_completo'] ?? '-';
                        $med = $r['nombre_medico'] ?? '-';
                        $desc = $r['descripcion_reaccion'] ?? '-';
                        $totalMedsPaciente = (int) ($r['total_medicamentos_paciente'] ?? 0);
                        $ini = $r['fecha_inicio_reaccion'] ?? null;
                        $fin = $r['fecha_fin_reaccion'] ?? null;
                        $est = $r['estado'] ?? 'SIN ESTADO';

                        $badge = estadoBadge($est);
                        $iniFmt = $ini ? \Carbon\Carbon::parse($ini)->format('d/m/Y') : '-';
                        $finFmt = $fin ? \Carbon\Carbon::parse($fin)->format('d/m/Y') : '-';
                        $iniSortable = $ini ? \Carbon\Carbon::parse($ini)->timestamp : 0;
                    ?>

                    <tr class="ram-row"
                        data-search="<?php echo e(strtolower($id . ' ' . $pac . ' ' . $med . ' ' . $desc . ' ' . $est)); ?>"
                        data-estado="<?php echo e(strtoupper(str_replace('_', ' ', $est))); ?>"
                        data-id="<?php echo e((int) $id); ?>"
                        data-ini="<?php echo e($iniSortable); ?>">
                        <td class="ps-4 fw-black" style="font-weight:900;"><?php echo e($loop->iteration); ?></td>
                        <td class="fw-bold"><?php echo e($pac); ?></td>
                        <td class="fw-bold"><?php echo e($med); ?></td>
                        <td class="fw-bold text-dark"><?php echo e($desc); ?></td>
                        <td class="text-center">
                            <span class="badge bg-danger rounded-pill px-3 py-2 fw-bold fs-6"><?php echo e($totalMedsPaciente); ?></span>
                        </td>
                        <td><?php echo e($iniFmt); ?></td>
                        <td><?php echo e($finFmt); ?></td>
                        <td>
                            <span class="badge bg-<?php echo e($badge['bg']); ?> rounded-pill px-3 py-2 fw-bold">
                                <i class="fa-solid <?php echo e($badge['icon']); ?> me-2"></i><?php echo e($badge['text']); ?>

                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?php echo e(route('reacciones_adversas.show', $r['id_reaccion'])); ?>"
                                   class="btn btn-action btn-view"
                                   title="Ver detalle"
                                   aria-label="Ver detalle">
                                    <i class="fa-regular fa-eye"></i>
                                </a>

                                <?php if($canActualizarRAM): ?>
                                    <a href="<?php echo e(route('reacciones_adversas.edit', $r['id_reaccion'])); ?>"
                                       class="btn btn-action btn-edit"
                                       title="Editar"
                                       aria-label="Editar">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                <?php endif; ?>

                                    <button type="button"
                                        class="btn btn-action btn-report"
                                        title="Reporte"
                                        aria-label="Reporte"
                                        onclick="descargarPDFIndividualRAM(<?php echo e((int) $r['id_reaccion']); ?>)">
                                    <i class="fa-solid fa-file-pdf"></i>
                                    </button>

                                <?php if($canEliminarRAM): ?>
                                    <form action="<?php echo e(route('reacciones_adversas.destroy', $r['id_reaccion'])); ?>"
                                          method="POST"
                                          style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>

                                        <button type="submit"
                                                class="btn btn-action btn-delete btn-borrar"
                                                title="Eliminar"
                                                aria-label="Eliminar"
                                                data-paciente="<?php echo e($pac); ?>">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php if(empty($reacciones)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="fa-regular fa-folder-open me-2"></i> No hay registros
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="glass pagination-strip px-4 py-3 mt-3">
    <div class="d-flex justify-content-end align-items-center flex-wrap gap-3">
        <span id="pageInfo" class="text-muted fw-bold">Página 1 de 1</span>

        <div class="d-flex align-items-center gap-2">
            <button id="prevPage" class="btn btn-sm btn-outline-secondary fw-bold" style="border-radius:12px;">
                <i class="fa-solid fa-chevron-left me-1"></i> Anterior
            </button>

            <div id="pageNumbers" class="d-flex align-items-center gap-1"></div>

            <button id="nextPage" class="btn btn-sm btn-outline-secondary fw-bold" style="border-radius:12px;">
                Siguiente <i class="fa-solid fa-chevron-right ms-1"></i>
            </button>
        </div>
    </div>
</div>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script>
    const reaccionesData = <?php echo json_encode($reacciones ?? [], 15, 512) ?>;
    const ramById = new Map((reaccionesData || []).map((item) => [Number(item.id_reaccion || 0), item]));
    const usuarioActualPDF = <?php echo json_encode($usuario['usuario'] ?? $usuario['nombre'] ?? session('usuario_nombre') ?? 'USUARIO', 15, 512) ?>;
    let logoDataUrlRAM = null;

    function formatearFechaHoraRAM(fecha) {
        const dd = String(fecha.getDate()).padStart(2, '0');
        const mm = String(fecha.getMonth() + 1).padStart(2, '0');
        const yyyy = fecha.getFullYear();
        const hh = String(fecha.getHours()).padStart(2, '0');
        const mi = String(fecha.getMinutes()).padStart(2, '0');
        const ss = String(fecha.getSeconds()).padStart(2, '0');
        return `${dd}/${mm}/${yyyy} ${hh}:${mi}:${ss}`;
    }

    async function obtenerLogoDataUrlRAM() {
        if (logoDataUrlRAM !== null) return logoDataUrlRAM;
        try {
            const response = await fetch('<?php echo e(asset('login-assets/images/logo-circle.png')); ?>', { cache: 'force-cache' });
            if (!response.ok) throw new Error('No se pudo cargar el logo');
            const blob = await response.blob();
            logoDataUrlRAM = await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(blob);
            });
        } catch (error) {
            console.warn('No se pudo cargar el logo para PDF RAM:', error);
            logoDataUrlRAM = '';
        }
        return logoDataUrlRAM;
    }

    function estadoNormalizadoRAM(estado) {
        const e = String(estado || '').toUpperCase().replace(/_/g, ' ').trim();
        if (!e) return 'SIN ESTADO';
        return e;
    }

    function valorSeguroRAM(valor) {
        const txt = String(valor ?? '').trim();
        return txt === '' ? '-' : txt;
    }

    async function exportarPDFReacciones() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
        const now = new Date();
        const fechaHoraStr = formatearFechaHoraRAM(now);
        const logoDataUrl = await obtenerLogoDataUrlRAM();
        const navyBlue = [30, 58, 107];
        const accentBlue = [37, 99, 235];
        const footerGray = [240, 244, 248];
        const margin = 14;
        const headerH = 26;

        const q = (searchInput.value || '').trim();
        const hayFiltroEstado = activeFilter && activeFilter !== 'ALL';
        const hayFiltros = q.length > 0 || hayFiltroEstado;

        const idsFiltrados = getSortedRows(getFilteredRows()).map((row) => Number(row.dataset.id || 0));
        const datosExport = idsFiltrados
            .map((id) => ramById.get(id))
            .filter((item) => !!item);

        const totalAnalisis = datosExport.filter((r) => estadoNormalizadoRAM(r.estado).includes('ANALISIS')).length;
        const totalRegistradas = datosExport.filter((r) => estadoNormalizadoRAM(r.estado).includes('REGISTRADA')).length;

        const drawPageHeader = () => {
            const pw = doc.internal.pageSize.width;
            doc.setFillColor(...navyBlue);
            doc.rect(0, 0, pw, headerH, 'F');
            if (logoDataUrl) doc.addImage(logoDataUrl, 'PNG', 6, 6, 13, 13);
            doc.setTextColor(255, 255, 255);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(15);
            doc.text('HOSPITAL ESCUELA', pw / 2, 12, { align: 'center' });
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8.5);
            doc.text('Departamento de Farmacovigilancia — Reacciones Adversas', pw / 2, 19, { align: 'center' });
            doc.setFontSize(7);
            doc.text(`Generado: ${fechaHoraStr}`, pw - margin, 23, { align: 'right' });
            doc.setFillColor(...accentBlue);
            doc.rect(0, headerH, pw, 1.2, 'F');
        };

        drawPageHeader();
        let startY = headerH + 6;

        doc.setDrawColor(...accentBlue);
        doc.setLineWidth(1.5);
        doc.line(margin, startY, margin, startY + 7);
        doc.setLineWidth(0.1);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(10.5);
        doc.setTextColor(...navyBlue);
        doc.text(
            hayFiltros ? 'REACCIONES ADVERSAS — RESULTADOS FILTRADOS' : 'REACCIONES ADVERSAS',
            margin + 4, startY + 5
        );
        startY += 11;

        if (hayFiltros) {
            const filtrosTxt = [];
            if (q) filtrosTxt.push(`Busqueda: "${q.toUpperCase()}"`);
            if (hayFiltroEstado) filtrosTxt.push(`Estado: ${activeFilter}`);
            const contextLabel = filtrosTxt.join(' | ');

            doc.setFillColor(240, 244, 248);
            doc.roundedRect(margin, startY, doc.internal.pageSize.width - margin * 2, 7, 1.5, 1.5, 'F');
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(7.5);
            doc.setTextColor(55, 65, 81);
            doc.text(
                `${contextLabel}   |   Total mostrado: ${datosExport.length} de ${allRows.length} registros`,
                margin + 3, startY + 4.5
            );
            startY += 10;
        }

        const columns = [
            { header: 'ID', dataKey: 'id' },
            { header: 'PACIENTE', dataKey: 'paciente' },
            { header: 'MEDICO', dataKey: 'medico' },
            { header: 'REACCION', dataKey: 'descripcion' },
            { header: 'MEDS', dataKey: 'meds' },
            { header: 'INICIO', dataKey: 'inicio' },
            { header: 'FIN', dataKey: 'fin' },
            { header: 'ESTADO', dataKey: 'estado' },
        ];

        const filas = datosExport.map((r) => ({
            id: r.id_reaccion || '-',
            paciente: valorSeguroRAM(r.nombre_completo),
            medico: valorSeguroRAM(r.nombre_medico),
            descripcion: valorSeguroRAM(r.descripcion_reaccion),
            meds: Number(r.total_medicamentos_paciente || 0).toLocaleString('es-HN'),
            inicio: valorSeguroRAM(r.fecha_inicio_reaccion ? new Date(r.fecha_inicio_reaccion).toLocaleDateString('es-HN') : '-'),
            fin: valorSeguroRAM(r.fecha_fin_reaccion ? new Date(r.fecha_fin_reaccion).toLocaleDateString('es-HN') : '-'),
            estado: estadoNormalizadoRAM(r.estado),
        }));

        doc.autoTable({
            startY,
            margin: { top: headerH + 4, left: margin, right: margin, bottom: 16 },
            columns,
            body: filas,
            styles: { fontSize: 7.5, cellPadding: 2.8, overflow: 'linebreak', font: 'helvetica' },
            headStyles: { fillColor: navyBlue, textColor: [255, 255, 255], fontStyle: 'bold', fontSize: 7.5 },
            bodyStyles: { fillColor: [241, 245, 249] },
            alternateRowStyles: { fillColor: [255, 255, 255] },
            columnStyles: {
                id: { halign: 'center' },
                meds: { halign: 'right', fontStyle: 'bold' },
            },
            didDrawPage: (data) => { if (data.pageNumber > 1) drawPageHeader(); },
        });

        const totalPaginas = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPaginas; i++) {
            doc.setPage(i);
            const ph = doc.internal.pageSize.height;
            const pw = doc.internal.pageSize.width;
            doc.setFillColor(...footerGray);
            doc.rect(0, ph - 12, pw, 12, 'F');
            doc.setDrawColor(...accentBlue);
            doc.setLineWidth(0.5);
            doc.line(0, ph - 12, pw, ph - 12);
            doc.setLineWidth(0.1);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(7);
            doc.setTextColor(71, 85, 105);
            doc.text(`Hospital Escuela  |  Descargado por: ${usuarioActualPDF}`, margin, ph - 4.5);
            doc.setTextColor(71, 85, 105);
            doc.setFontSize(7);
            doc.text(`Página ${i} de ${totalPaginas}`, pw - margin, ph - 4.5, { align: 'right' });
        }

        const tipo = hayFiltros ? 'filtrado' : 'general';
        window.openJsPdfPreview(doc, {
            title: 'Reporte de Reacciones Adversas',
            fileName: `reacciones_adversas_${tipo}_${now.toISOString().split('T')[0]}.pdf`
        });
    }

    async function descargarPDFIndividualRAM(idReaccion) {
        const item = ramById.get(Number(idReaccion));
        if (!item) return;

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        const now = new Date();
        const fechaHoraStr = formatearFechaHoraRAM(now);
        const logoDataUrl = await obtenerLogoDataUrlRAM();
        const navyBlue = [30, 58, 107];
        const accentBlue = [37, 99, 235];
        const footerGray = [240, 244, 248];
        const margin = 14;
        const headerH = 26;

        const drawPageHeader = () => {
            const pw = doc.internal.pageSize.width;
            doc.setFillColor(...navyBlue);
            doc.rect(0, 0, pw, headerH, 'F');
            if (logoDataUrl) doc.addImage(logoDataUrl, 'PNG', 6, 6, 13, 13);
            doc.setTextColor(255, 255, 255);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(15);
            doc.text('HOSPITAL ESCUELA', pw / 2, 12, { align: 'center' });
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8.5);
            doc.text('Departamento de Farmacovigilancia — Reacciones Adversas', pw / 2, 19, { align: 'center' });
            doc.setFontSize(7);
            doc.text(`Generado: ${fechaHoraStr}`, pw - margin, 23, { align: 'right' });
            doc.setFillColor(...accentBlue);
            doc.rect(0, headerH, pw, 1.2, 'F');
        };

        drawPageHeader();
        let startY = headerH + 6;

        doc.setDrawColor(...accentBlue);
        doc.setLineWidth(1.5);
        doc.line(margin, startY, margin, startY + 7);
        doc.setLineWidth(0.1);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(10.5);
        doc.setTextColor(...navyBlue);
        doc.text('REACCIONES ADVERSAS', margin + 4, startY + 5);
        startY += 11;

        const campos = [
            ['ID REACCION', valorSeguroRAM(item.id_reaccion)],
            ['PACIENTE', valorSeguroRAM(item.nombre_completo)],
            ['MEDICO', valorSeguroRAM(item.nombre_medico)],
            ['DESCRIPCION DE LA REACCION', valorSeguroRAM(item.descripcion_reaccion)],
            ['MEDICAMENTOS DEL PACIENTE', Number(item.total_medicamentos_paciente || 0).toLocaleString('es-HN')],
            ['FECHA INICIO', valorSeguroRAM(item.fecha_inicio_reaccion ? new Date(item.fecha_inicio_reaccion).toLocaleDateString('es-HN') : '-')],
            ['FECHA FIN', valorSeguroRAM(item.fecha_fin_reaccion ? new Date(item.fecha_fin_reaccion).toLocaleDateString('es-HN') : '-')],
            ['ESTADO', estadoNormalizadoRAM(item.estado)],
            ['SEVERIDAD', valorSeguroRAM(item.severidad)],
            ['OBSERVACIONES', valorSeguroRAM(item.observaciones || item.notas || '-')],
        ];

        doc.autoTable({
            startY,
            margin: { top: headerH + 4, left: margin, right: margin, bottom: 16 },
            head: [['CAMPO', 'VALOR']],
            body: campos,
            styles: { fontSize: 9.5, cellPadding: 3.5, overflow: 'linebreak', font: 'helvetica' },
            headStyles: { fillColor: navyBlue, textColor: [255, 255, 255], fontStyle: 'bold', fontSize: 9 },
            bodyStyles: { fillColor: [241, 245, 249] },
            alternateRowStyles: { fillColor: [255, 255, 255] },
            columnStyles: {
                0: { cellWidth: 70, fontStyle: 'bold', textColor: navyBlue },
            },
            didDrawPage: (data) => { if (data.pageNumber > 1) drawPageHeader(); },
        });

        const totalPaginas = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPaginas; i++) {
            doc.setPage(i);
            const ph = doc.internal.pageSize.height;
            const pw = doc.internal.pageSize.width;
            doc.setFillColor(...footerGray);
            doc.rect(0, ph - 12, pw, 12, 'F');
            doc.setDrawColor(...accentBlue);
            doc.setLineWidth(0.5);
            doc.line(0, ph - 12, pw, ph - 12);
            doc.setLineWidth(0.1);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(7);
            doc.setTextColor(71, 85, 105);
            doc.text(`Hospital Escuela  |  Descargado por: ${usuarioActualPDF}`, margin, ph - 4.5);
            doc.setTextColor(71, 85, 105);
            doc.text(`Página ${i} de ${totalPaginas}`, pw - margin, ph - 4.5, { align: 'right' });
        }

        window.openJsPdfPreview(doc, {
            title: `Reporte de Reacción Adversa #${valorSeguroRAM(item.id_reaccion)}`,
            fileName: `reaccion_adversa_${valorSeguroRAM(item.id_reaccion)}_${now.toISOString().split('T')[0]}.pdf`
        });
    }

    const globalSearch = document.getElementById('globalSearch');
    const localSearch = document.getElementById('searchInput');

    if (globalSearch && localSearch) {
        globalSearch.value = '';
        globalSearch.addEventListener('input', () => {
            localSearch.value = globalSearch.value;
            localSearch.dispatchEvent(new Event('input'));
        });
    }

    const tbody = document.querySelector('#tablaRAM tbody');
    const allRows = Array.from(document.querySelectorAll('.ram-row'));
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');
    const perPageSelect = document.getElementById('perPageSelect');
    const prevPageBtn = document.getElementById('prevPage');
    const nextPageBtn = document.getElementById('nextPage');
    const pageNumbers = document.getElementById('pageNumbers');
    const pageInfo = document.getElementById('pageInfo');

    let activeFilter = 'ALL';
    let currentPage = 1;
    let perPage = parseInt(perPageSelect.value, 10);

    function getFilteredRows() {
        const q = (searchInput.value || '').toLowerCase().trim();

        return allRows.filter((r) => {
            const hay = (r.dataset.search || '').includes(q);
            const est = r.dataset.estado || '';
            const matchEstado = (activeFilter === 'ALL') ? true : est.includes(activeFilter);
            return hay && matchEstado;
        });
    }

    function getSortedRows(rows) {
        const mode = sortSelect.value;

        return [...rows].sort((a, b) => {
            if (mode === 'new') return parseInt(b.dataset.ini || '0', 10) - parseInt(a.dataset.ini || '0', 10);
            if (mode === 'old') return parseInt(a.dataset.ini || '0', 10) - parseInt(b.dataset.ini || '0', 10);
            if (mode === 'id_desc') return parseInt(b.dataset.id || '0', 10) - parseInt(a.dataset.id || '0', 10);
            if (mode === 'id_asc') return parseInt(a.dataset.id || '0', 10) - parseInt(b.dataset.id || '0', 10);
            return 0;
        });
    }

    function renderTable() {
        const filtered = getFilteredRows();
        const sorted = getSortedRows(filtered);

        const totalRows = sorted.length;
        const totalPages = Math.max(1, Math.ceil(totalRows / perPage));

        if (currentPage > totalPages) currentPage = totalPages;

        const start = (currentPage - 1) * perPage;
        const end = start + perPage;
        const paginatedRows = sorted.slice(start, end);

        tbody.innerHTML = '';

        if (totalRows === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="fa-regular fa-folder-open me-2"></i> No hay registros
                    </td>
                </tr>
            `;
        } else {
            paginatedRows.forEach((row, index) => {
                const firstCell = row.querySelector('td:first-child');
                if (firstCell) firstCell.textContent = start + index + 1;
                tbody.appendChild(row);
            });
        }

        pageInfo.textContent = `Página ${currentPage} de ${totalPages}`;

        prevPageBtn.disabled = currentPage === 1;
        nextPageBtn.disabled = currentPage === totalPages;

        prevPageBtn.style.opacity = currentPage === 1 ? '0.5' : '1';
        nextPageBtn.style.opacity = currentPage === totalPages ? '0.5' : '1';

        prevPageBtn.style.cursor = currentPage === 1 ? 'not-allowed' : 'pointer';
        nextPageBtn.style.cursor = currentPage === totalPages ? 'not-allowed' : 'pointer';

        if (pageNumbers) {
            pageNumbers.innerHTML = '';

            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);

            const createPageButton = (label, page, isActive = false, isDisabled = false) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `btn btn-sm fw-bold ${isActive ? 'btn-secondary' : 'btn-outline-secondary'}`;
                btn.style.borderRadius = '10px';
                btn.textContent = label;

                if (isDisabled) {
                    btn.disabled = true;
                    btn.style.cursor = 'default';
                    btn.style.opacity = '0.7';
                    return btn;
                }

                btn.addEventListener('click', () => {
                    currentPage = page;
                    renderTable();
                });
                return btn;
            };

            if (startPage > 1) {
                pageNumbers.appendChild(createPageButton('1', 1));
                if (startPage > 2) {
                    pageNumbers.appendChild(createPageButton('...', currentPage, false, true));
                }
            }

            for (let page = startPage; page <= endPage; page++) {
                pageNumbers.appendChild(createPageButton(String(page), page, page === currentPage));
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    pageNumbers.appendChild(createPageButton('...', currentPage, false, true));
                }
                pageNumbers.appendChild(createPageButton(String(totalPages), totalPages));
            }
        }
    }

    function apply() {
        currentPage = 1;
        renderTable();
    }

    document.querySelectorAll('[data-filter]').forEach((btn) => {
        btn.addEventListener('click', () => {
            activeFilter = btn.dataset.filter;

            document.querySelectorAll('[data-filter]').forEach((b) => {
                b.classList.remove('filter-btn-active');
            });

            btn.classList.add('filter-btn-active');

            apply();
        });
    });

    const clearRASearch = document.getElementById('clearRASearch');
    searchInput.addEventListener('input', function () {
        if (clearRASearch) clearRASearch.classList.toggle('d-none', this.value.length === 0);
        apply();
    });
    if (clearRASearch) {
        clearRASearch.addEventListener('click', function () {
            searchInput.value = '';
            this.classList.add('d-none');
            apply();
            searchInput.focus();
        });
    }
    sortSelect.addEventListener('change', apply);

    perPageSelect.addEventListener('change', () => {
        perPage = parseInt(perPageSelect.value, 10);
        currentPage = 1;
        renderTable();
    });

    prevPageBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    });

    nextPageBtn.addEventListener('click', () => {
        const totalRows = getSortedRows(getFilteredRows()).length;
        const totalPages = Math.max(1, Math.ceil(totalRows / perPage));

        if (currentPage < totalPages) {
            currentPage++;
            renderTable();
        }
    });

    sortSelect.value = 'new';
    renderTable();

</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mrner\Escritorio\PROYECTO\Sistema HE\ProyectoHospitalEscuela\Frontend\resources\views/reacciones_adversas/index.blade.php ENDPATH**/ ?>