
<?php $__env->startSection('title', 'Bitácora - Hospital Escuela'); ?>
<?php $__env->startSection('header', 'Bitácora'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .card-table { border-radius: 12px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.02); overflow: hidden; background-color: #ffffff; }
    .table thead th { color: #374151; font-weight: 700; font-size: 0.95rem; padding: 15px 20px; border-bottom: 2px solid #edf2f7; background-color: #ffffff; }
    .table tbody td { padding: 15px 20px; vertical-align: middle; border-bottom: 1px solid #f3f4f6; font-size: 0.9rem; }
    .page-item.active .page-link { background-color: #0d6efd; color: #ffffff; }
    .btn-bitacora { border-radius: 8px; font-weight: 600; font-size: 0.85rem; padding: 8px 16px; transition: all .15s; }
    .filter-panel { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; margin-bottom: 20px; }
    .filter-panel .form-label { font-size: 0.8rem; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .4px; }
    .badge-accion { background-color: #dbeafe; color: #1d4ed8; font-size: 0.78rem; padding: 4px 10px; border-radius: 20px; font-weight: 600; }
    .select-round { border-radius: 8px; border: 1px solid #d1d5db; padding: 5px 10px; font-size: 0.85rem; background-color: #fff; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="p-4 p-md-5">

    
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1f2937;">Bitácora de Auditoría</h4>
            <p class="text-muted mb-0">Consulta de acciones registradas del sistema.</p>
        </div>

        
        <div class="d-flex flex-wrap gap-2 align-items-center">

                <button type="button"
                    class="btn btn-dark btn-bitacora shadow-sm"
                    title="Imprimir reporte PDF de la bitácora"
                    onclick="exportarPDFBitacora()">
                <i class="fas fa-file-pdf me-2"></i>Reporte
                </button>

            
            <button type="button"
                    class="btn btn-outline-secondary btn-bitacora shadow-sm"
                    data-bs-toggle="collapse"
                    data-bs-target="#filtrosPanel"
                    aria-expanded="<?php echo e(!empty($filtros) ? 'true' : 'false'); ?>"
                    title="Mostrar / ocultar filtros de búsqueda">
                <i class="fas fa-filter me-2"></i>Filtros
                <?php if(!empty($filtros)): ?>
                    <span class="badge bg-primary ms-1"><?php echo e(count($filtros)); ?></span>
                <?php endif; ?>
            </button>

        </div>
    </div>

    
    <div class="collapse <?php echo e(!empty($filtros) ? 'show' : ''); ?>" id="filtrosPanel">
        <div class="filter-panel">
            <form method="GET" action="<?php echo e(route('bitacora')); ?>" id="formFiltros">
                <input type="hidden" name="page" value="1">
                <input type="hidden" name="per_page" value="<?php echo e(request('per_page', 10)); ?>">
                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <label class="form-label">Usuario</label>
                        <input type="text" name="usuario" class="form-control form-control-sm"
                               placeholder="Ej. ADMIN"
                               value="<?php echo e(request('usuario')); ?>">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Acción</label>
                        <input type="text" name="accion" class="form-control form-control-sm"
                               placeholder="Ej. INICIAR SESION"
                               value="<?php echo e(request('accion')); ?>">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Fecha Desde</label>
                        <input type="date" name="fecha_desde" class="form-control form-control-sm"
                               value="<?php echo e(request('fecha_desde')); ?>">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control form-control-sm"
                               value="<?php echo e(request('fecha_hasta')); ?>">
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                    <a href="<?php echo e(route('bitacora')); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                        <i class="fas fa-times me-1"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    
    <div class="d-flex justify-content-start align-items-center gap-3 mb-2 px-1">
        <div class="d-flex align-items-center">
            <span class="text-muted fw-bold small me-2">MOSTRAR:</span>
            <form method="GET" action="<?php echo e(route('bitacora')); ?>">
                <input type="hidden" name="page" value="1">
                <?php $__currentLoopData = request()->except(['per_page','page']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <input type="hidden" name="<?php echo e($k); ?>" value="<?php echo e($v); ?>">
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <select id="per_page" name="per_page" class="select-round" onchange="this.form.submit()">
                    <option value="5"  <?php echo e((int) request('per_page', 10) === 5  ? 'selected' : ''); ?>>5</option>
                    <option value="10" <?php echo e((int) request('per_page', 10) === 10 ? 'selected' : ''); ?>>10</option>
                    <option value="15" <?php echo e((int) request('per_page', 10) === 15 ? 'selected' : ''); ?>>15</option>
                </select>
            </form>
        </div>
        <?php if(!empty($filtros)): ?>
            <span class="text-muted small">
                Mostrando resultados filtrados
                <a href="<?php echo e(route('bitacora')); ?>" class="text-danger ms-1 small fw-bold">
                    <i class="fas fa-times-circle"></i> Quitar filtros
                </a>
            </span>
        <?php endif; ?>
    </div>

    
    <div class="card card-table">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha / Hora</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Descripción</th>
                    <th>Ruta</th>
                    <th>Dirección IP</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $registros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="bitacora-row"
                        data-id="<?php echo e((int) ($reg['id_bitacora'] ?? 0)); ?>"
                        data-fecha="<?php echo e($reg['fecha'] ?? ''); ?>"
                        data-usuario="<?php echo e($reg['nombre_usuario'] ?? ''); ?>"
                        data-accion="<?php echo e($reg['tipo_accion'] ?? ''); ?>"
                        data-descripcion="<?php echo e($reg['descripcion'] ?? ''); ?>"
                        data-ruta="<?php echo e($reg['ruta'] ?? ''); ?>"
                        data-ip="<?php echo e($reg['direccion_ip'] ?? ''); ?>">
                        <td class="text-muted">#<?php echo e($reg['id_bitacora'] ?? ''); ?></td>
                        <td class="fw-bold"><?php echo e($reg['fecha'] ?? ''); ?></td>
                        <td><span class="badge bg-light text-dark border"><?php echo e($reg['nombre_usuario'] ?? ''); ?></span></td>
                        <td><span class="badge-accion"><?php echo e($reg['tipo_accion'] ?? ''); ?></span></td>
                        <td class="text-muted small"><?php echo e($reg['descripcion'] ?? ''); ?></td>
                        <td class="font-monospace small text-muted"><?php echo e($reg['ruta'] ?? ''); ?></td>
                        <td class="font-monospace small text-secondary"><?php echo e($reg['direccion_ip'] ?? ''); ?></td>
                        <td class="text-center">
                            <button
                                type="button"
                                class="btn btn-action btn-report"
                                title="Imprimir"
                                aria-label="Imprimir"
                                onclick="descargarPDFIndividualBitacora(<?php echo e((int) ($reg['id_bitacora'] ?? 0)); ?>)"
                            >
                                <i class="fas fa-file-pdf"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4">No hay registros de auditoría</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <?php if(is_object($registros) && method_exists($registros, 'currentPage') && method_exists($registros, 'lastPage')): ?>
        <?php
            $currentPage = (int) $registros->currentPage();
            $totalPages  = (int) $registros->lastPage();
            $startPage   = max(1, $currentPage - 2);
            $endPage     = min($totalPages, $currentPage + 2);
        ?>

        <?php if($totalPages > 1): ?>
            <div class="d-flex justify-content-end align-items-center mt-4 mb-2 px-2">
                <span class="text-muted small fw-bold me-3">Página <?php echo e($currentPage); ?> de <?php echo e($totalPages); ?></span>
                <nav>
                    <ul class="pagination pagination-sm mb-0 shadow-sm" style="border-radius:8px;overflow:hidden;">
                        <li class="page-item <?php echo e($currentPage <= 1 ? 'disabled' : ''); ?>">
                            <a class="page-link border-0 text-dark fw-bold px-3"
                               href="<?php echo e(route('bitacora', array_merge(request()->query(), ['page' => $currentPage - 1]))); ?>">
                                <i class="fas fa-chevron-left me-1"></i> Anterior
                            </a>
                        </li>
                        <?php if($startPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link border-0 text-dark fw-bold px-3"
                                   href="<?php echo e(route('bitacora', array_merge(request()->query(), ['page' => 1]))); ?>">1</a>
                            </li>
                            <?php if($startPage > 2): ?>
                                <li class="page-item disabled"><span class="page-link border-0 text-dark fw-bold px-3">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php for($page = $startPage; $page <= $endPage; $page++): ?>
                            <li class="page-item <?php echo e($page === $currentPage ? 'active' : ''); ?>">
                                <a class="page-link border-0 text-dark fw-bold px-3"
                                   href="<?php echo e(route('bitacora', array_merge(request()->query(), ['page' => $page]))); ?>"><?php echo e($page); ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if($endPage < $totalPages): ?>
                            <?php if($endPage < $totalPages - 1): ?>
                                <li class="page-item disabled"><span class="page-link border-0 text-dark fw-bold px-3">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link border-0 text-dark fw-bold px-3"
                                   href="<?php echo e(route('bitacora', array_merge(request()->query(), ['page' => $totalPages]))); ?>"><?php echo e($totalPages); ?></a>
                            </li>
                        <?php endif; ?>
                        <li class="page-item <?php echo e($currentPage >= $totalPages ? 'disabled' : ''); ?>">
                            <a class="page-link border-0 text-dark fw-bold px-3"
                               href="<?php echo e(route('bitacora', array_merge(request()->query(), ['page' => $currentPage + 1]))); ?>">
                                Siguiente <i class="fas fa-chevron-right ms-1"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script>
    const bitacoraData = <?php echo json_encode(is_object($registros) && method_exists($registros, 'items') ? $registros->items() : (array) $registros, 512) ?>;
    const bitacoraById = new Map((bitacoraData || []).map((item) => [Number(item.id_bitacora || 0), item]));
    const filtrosBitacora = {
        usuario: <?php echo json_encode((string) request('usuario', ''), 512) ?>,
        accion: <?php echo json_encode((string) request('accion', ''), 512) ?>,
        fecha_desde: <?php echo json_encode((string) request('fecha_desde', ''), 512) ?>,
        fecha_hasta: <?php echo json_encode((string) request('fecha_hasta', ''), 512) ?>,
    };
    const usuarioActualPDFBitacora = <?php echo json_encode($usuario['usuario'] ?? $usuario['nombre'] ?? session('usuario_nombre') ?? 'USUARIO', 15, 512) ?>;
    let logoDataUrlBitacora = null;

    function formatearFechaHoraBitacora(fecha) {
        const dd = String(fecha.getDate()).padStart(2, '0');
        const mm = String(fecha.getMonth() + 1).padStart(2, '0');
        const yyyy = fecha.getFullYear();
        const hh = String(fecha.getHours()).padStart(2, '0');
        const mi = String(fecha.getMinutes()).padStart(2, '0');
        const ss = String(fecha.getSeconds()).padStart(2, '0');
        return `${dd}/${mm}/${yyyy} ${hh}:${mi}:${ss}`;
    }

    function valorSeguroBitacora(valor) {
        const txt = String(valor ?? '').trim();
        return txt === '' ? '-' : txt;
    }

    async function obtenerLogoDataUrlBitacora() {
        if (logoDataUrlBitacora !== null) return logoDataUrlBitacora;
        try {
            const response = await fetch('<?php echo e(asset('login-assets/images/logo-circle.png')); ?>', { cache: 'force-cache' });
            if (!response.ok) throw new Error('No se pudo cargar el logo');
            const blob = await response.blob();
            logoDataUrlBitacora = await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(blob);
            });
        } catch (error) {
            console.warn('No se pudo cargar el logo para PDF Bitacora:', error);
            logoDataUrlBitacora = '';
        }
        return logoDataUrlBitacora;
    }

    function obtenerRegistrosVisiblesBitacora() {
        return Array.from(document.querySelectorAll('.bitacora-row')).map((fila) => ({
            id_bitacora: Number(fila.dataset.id || 0),
            fecha: fila.dataset.fecha || '',
            nombre_usuario: fila.dataset.usuario || '',
            tipo_accion: fila.dataset.accion || '',
            descripcion: fila.dataset.descripcion || '',
            ruta: fila.dataset.ruta || '',
            direccion_ip: fila.dataset.ip || '',
        }));
    }

    async function exportarPDFBitacora() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
        const now = new Date();
        const fechaHoraStr = formatearFechaHoraBitacora(now);
        const logoDataUrl = await obtenerLogoDataUrlBitacora();
        const navyBlue = [30, 58, 107];
        const accentBlue = [37, 99, 235];
        const footerGray = [240, 244, 248];
        const margin = 14;
        const headerH = 26;

        const datosExport = obtenerRegistrosVisiblesBitacora();
        const hayFiltros = !!(filtrosBitacora.usuario || filtrosBitacora.accion || filtrosBitacora.fecha_desde || filtrosBitacora.fecha_hasta);

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
            doc.text('Departamento de Seguridad — Bitacora de Auditoria', pw / 2, 19, { align: 'center' });
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
            hayFiltros ? 'BITACORA — RESULTADOS FILTRADOS' : 'BITACORA — REPORTE GENERAL',
            margin + 4, startY + 5
        );
        startY += 11;

        if (hayFiltros) {
            const partes = [];
            if (filtrosBitacora.usuario) partes.push(`Usuario: ${String(filtrosBitacora.usuario).toUpperCase()}`);
            if (filtrosBitacora.accion) partes.push(`Accion: ${String(filtrosBitacora.accion).toUpperCase()}`);
            if (filtrosBitacora.fecha_desde) partes.push(`Desde: ${filtrosBitacora.fecha_desde}`);
            if (filtrosBitacora.fecha_hasta) partes.push(`Hasta: ${filtrosBitacora.fecha_hasta}`);

            doc.setFillColor(240, 244, 248);
            doc.roundedRect(margin, startY, doc.internal.pageSize.width - margin * 2, 7, 1.5, 1.5, 'F');
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(7.5);
            doc.setTextColor(55, 65, 81);
            doc.text(`${partes.join(' | ')}   |   Registros mostrados: ${datosExport.length}`, margin + 3, startY + 4.5);
            startY += 10;
        }

        const columns = [
            { header: 'ID', dataKey: 'id' },
            { header: 'FECHA / HORA', dataKey: 'fecha' },
            { header: 'USUARIO', dataKey: 'usuario' },
            { header: 'ACCION', dataKey: 'accion' },
            { header: 'DESCRIPCION', dataKey: 'descripcion' },
            { header: 'RUTA', dataKey: 'ruta' },
            { header: 'DIRECCION IP', dataKey: 'ip' },
        ];

        const filas = datosExport.map((r) => ({
            id: valorSeguroBitacora(r.id_bitacora),
            fecha: valorSeguroBitacora(r.fecha),
            usuario: valorSeguroBitacora(r.nombre_usuario),
            accion: valorSeguroBitacora(r.tipo_accion),
            descripcion: valorSeguroBitacora(r.descripcion),
            ruta: valorSeguroBitacora(r.ruta),
            ip: valorSeguroBitacora(r.direccion_ip),
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
            columnStyles: { id: { halign: 'center', cellWidth: 18 } },
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
            doc.text(`Hospital Escuela  |  Descargado por: ${usuarioActualPDFBitacora}`, margin, ph - 4.5);
            doc.setTextColor(71, 85, 105);
            doc.setFontSize(7);
            doc.text(`Página ${i} de ${totalPaginas}`, pw - margin, ph - 4.5, { align: 'right' });
        }

        const tipo = hayFiltros ? 'filtrado' : 'general';
        window.openJsPdfPreview(doc, {
            title: 'Reporte de Bitácora',
            fileName: `bitacora_${tipo}_${now.toISOString().split('T')[0]}.pdf`
        });
    }

    async function descargarPDFIndividualBitacora(idBitacora) {
        const item = bitacoraById.get(Number(idBitacora));
        if (!item) return;

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        const now = new Date();
        const fechaHoraStr = formatearFechaHoraBitacora(now);
        const logoDataUrl = await obtenerLogoDataUrlBitacora();
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
            doc.text('Departamento de Seguridad — Registro de Bitacora', pw / 2, 19, { align: 'center' });
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
        doc.text('FICHA DE REGISTRO DE BITACORA', margin + 4, startY + 5);
        startY += 11;

        const campos = [
            ['ID BITACORA', valorSeguroBitacora(item.id_bitacora)],
            ['FECHA / HORA', valorSeguroBitacora(item.fecha)],
            ['USUARIO', valorSeguroBitacora(item.nombre_usuario)],
            ['ACCION', valorSeguroBitacora(item.tipo_accion)],
            ['DESCRIPCION', valorSeguroBitacora(item.descripcion)],
            ['RUTA', valorSeguroBitacora(item.ruta)],
            ['DIRECCION IP', valorSeguroBitacora(item.direccion_ip)],
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
            doc.text(`Hospital Escuela  |  Descargado por: ${usuarioActualPDFBitacora}`, margin, ph - 4.5);
            doc.setTextColor(71, 85, 105);
            doc.text(`Página ${i} de ${totalPaginas}`, pw - margin, ph - 4.5, { align: 'right' });
        }

        window.openJsPdfPreview(doc, {
            title: `Reporte de Bitácora #${valorSeguroBitacora(item.id_bitacora)}`,
            fileName: `bitacora_${valorSeguroBitacora(item.id_bitacora)}_${now.toISOString().split('T')[0]}.pdf`
        });
    }
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Pao\OneDrive\Escritorio\Sistema HE\ProyectoHospitalEscuela\Frontend\resources\views/bitacora.blade.php ENDPATH**/ ?>