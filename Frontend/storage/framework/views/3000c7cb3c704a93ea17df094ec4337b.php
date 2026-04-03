<?php $__env->startSection('title','Panel Principal - Hospital Escuela'); ?>
<?php $__env->startSection('header','Dashboard'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .inv-kpi-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(220px, 1fr));
        gap: 14px;
        margin-bottom: 26px;
    }
    .inv-kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-left: 5px solid;
        border-radius: 12px;
        padding: 14px 16px;
    }
    .inv-kpi-label {
        font-size: .8rem;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: .04em;
        color: #64748b;
    }
    .inv-kpi-value {
        font-size: 1.5rem;
        font-weight: 900;
        color: #111827;
        line-height: 1.2;
        margin-top: 4px;
    }
    .inv-kpi-total { border-left-color: #0284c7; }
    .inv-kpi-stock { border-left-color: #16a34a; }
    .inv-kpi-agotar { border-left-color: #eab308; }
    .inv-kpi-vencer { border-left-color: #d4a373; }
    .inv-kpi-cuarentena { border-left-color: #7c3aed; }
    .inv-kpi-baja { border-left-color: #f97316; }

    .welcome-card {
        background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
        border-radius: 16px;
        padding: 28px 32px;
        margin-bottom: 30px;
        color: white;
    }
    .welcome-card .welcome-label {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #5eead4;
        margin-bottom: 4px;
    }
    .welcome-card h2 {
        font-size: 1.75rem;
        font-weight: 800;
        margin: 0;
    }
    .welcome-card .welcome-sub {
        margin-top: 6px;
        font-size: 0.9rem;
        color: #9ca3af;
    }

    .ram-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-top: 8px;
    }
    .ram-panel {
        border-radius: 14px;
        padding: 16px;
        border: 1px solid;
        min-height: 380px;
    }
    .ram-panel h5 {
        margin-bottom: 2px;
        font-size: 1rem;
        font-weight: 800;
    }
    .ram-panel .ram-subtitle {
        font-size: .85rem;
        margin-bottom: 12px;
        opacity: .9;
    }
    .ram-blue {
        background: linear-gradient(180deg, #eff6ff 0%, #e0f2fe 100%);
        border-color: #93c5fd;
    }
    .ram-blue h5 { color: #1d4ed8; }
    .ram-blue .ram-subtitle { color: #1e40af; }

    .ram-red {
        background: linear-gradient(180deg, #fff1f2 0%, #fee2e2 100%);
        border-color: #fca5a5;
    }
    .ram-red h5 { color: #b91c1c; }
    .ram-red .ram-subtitle { color: #991b1b; }

    .ram-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-height: 300px;
        overflow: auto;
        padding-right: 4px;
    }
    .ram-item {
        background: rgba(255, 255, 255, .88);
        border-radius: 10px;
        border: 1px solid rgba(148, 163, 184, .32);
        padding: 10px 12px;
    }
    .ram-item .title {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
    }
    .ram-item .meta {
        font-size: .84rem;
        color: #334155;
    }
    .ram-item .desc {
        margin-top: 5px;
        font-size: .86rem;
        color: #1f2937;
        line-height: 1.35;
    }
    .ram-actions {
        margin-top: 8px;
    }
    .ram-empty {
        text-align: center;
        font-size: .9rem;
        color: #64748b;
        padding: 26px 10px;
    }

    @media (max-width: 992px) {
        .inv-kpi-grid {
            grid-template-columns: 1fr;
        }
        .ram-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="p-4 p-md-5">

    <?php
        $rolNombreDashboard = mb_strtoupper((string) ($usuario['rol_nombre'] ?? $usuario['rol'] ?? ''), 'UTF-8');
        $idRolDashboard = (int) ($usuario['id_rol'] ?? $usuario['rol_id'] ?? 0);
        $isAdminOrJefeDashboard = str_contains($rolNombreDashboard, 'ADMIN')
            || str_contains($rolNombreDashboard, 'JEFE')
            || $idRolDashboard === 1;
    ?>

    
    <div class="welcome-card">
        <div class="welcome-label"><i class="fas fa-hospital me-1"></i> Hospital Escuela</div>
        <h2>Bienvenido, <?php echo e($usuario['nombre'] ?? $usuario['usuario'] ?? 'Usuario'); ?> <?php echo e($usuario['apellido'] ?? ''); ?></h2>
        <div class="welcome-sub"><?php echo e(now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY')); ?></div>
    </div>

    <?php if($isAdminOrJefeDashboard): ?>
    <h5 class="fw-bold mb-3" style="color:#374151;">Resumen de Inventario</h5>
    <div class="inv-kpi-grid" id="inventario-kpis">
        <article class="inv-kpi-card inv-kpi-total">
            <div class="inv-kpi-label">Suministros Totales</div>
            <div class="inv-kpi-value" id="inv-kpi-total">0</div>
        </article>
        <article class="inv-kpi-card inv-kpi-stock">
            <div class="inv-kpi-label">En Stock</div>
            <div class="inv-kpi-value" id="inv-kpi-stock">0</div>
        </article>
        <article class="inv-kpi-card inv-kpi-agotar">
            <div class="inv-kpi-label">Bajo Stock</div>
            <div class="inv-kpi-value" id="inv-kpi-por-agotarse">0</div>
        </article>
        <article class="inv-kpi-card inv-kpi-vencer">
            <div class="inv-kpi-label">Pronto a Vencer</div>
            <div class="inv-kpi-value" id="inv-kpi-pronto-vencer">0</div>
        </article>
        <article class="inv-kpi-card inv-kpi-cuarentena">
            <div class="inv-kpi-label">En Cuarentena</div>
            <div class="inv-kpi-value" id="inv-kpi-cuarentena">0</div>
        </article>
        <article class="inv-kpi-card inv-kpi-baja">
            <div class="inv-kpi-label">Baja Rotación</div>
            <div class="inv-kpi-value" id="inv-kpi-baja-rotacion">0</div>
        </article>
    </div>
    <?php endif; ?>

    <h5 class="fw-bold mb-3" style="color:#374151;">Resumen de Reacciones Adversas</h5>
    <div class="ram-grid">
        <section class="ram-panel ram-blue">
            <h5>Pacientes con RAM</h5>
            <div class="ram-subtitle">Nombre, descripción y fecha de ingreso de la reacción.</div>
            <div class="ram-list" id="lista-pacientes-ra">
                <div class="ram-empty">Sin datos</div>
            </div>
        </section>

        <section class="ram-panel ram-red">
            <h5>Medicamentos relacionados</h5>
            <div class="ram-subtitle">Listado de medicamentos asociados a reacciones adversas.</div>
            <div class="ram-list" id="lista-medicamentos-ra">
                <div class="ram-empty">Sin datos</div>
            </div>
        </section>
    </div>

</div>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const canSeeInventarioCards = <?php echo json_encode($isAdminOrJefeDashboard, 15, 512) ?>;

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatFecha(fecha) {
        if (!fecha) return '-';
        const parts = String(fecha).split('-');
        if (parts.length !== 3) return fecha;
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    function inicioDelDia(fecha) {
        const d = new Date(fecha);
        d.setHours(0, 0, 0, 0);
        return d;
    }

    function parseFechaYmd(fechaYmd) {
        if (!fechaYmd) return null;
        const [y, m, d] = String(fechaYmd).split('-');
        if (!y || !m || !d) return null;
        return new Date(Number(y), Number(m) - 1, Number(d));
    }

    function parseFechaFlexible(valor) {
        if (!valor) return null;
        const fecha = new Date(valor);
        if (Number.isNaN(fecha.getTime())) return null;
        return fecha;
    }

    function estadoInventario(item) {
        const estadoActual = String(item?.estado || 'ACTIVO').toUpperCase();
        const saldo = Number(item?.saldo ?? 0);
        const hoy = inicioDelDia(new Date());

        if (estadoActual === 'EN_CUARENTENA') return 'EN_CUARENTENA';

        if (saldo === 0 || estadoActual === 'AGOTADO') return 'AGOTADO';

        const fechaVenc = parseFechaYmd(item?.vencimiento);
        if (fechaVenc && inicioDelDia(fechaVenc) < hoy) return 'VENCIDO';

        const fechaUltimoMov = parseFechaFlexible(item?.fecha_ultimo_movimiento);
        if (fechaUltimoMov) {
            const limiteBaja = new Date(hoy);
            limiteBaja.setMonth(limiteBaja.getMonth() - 3);
            if (inicioDelDia(fechaUltimoMov) <= limiteBaja) return 'BAJA_ROTACION';
        }

        if (fechaVenc) {
            const limiteVencer = new Date(hoy);
            limiteVencer.setDate(limiteVencer.getDate() + 30);
            const venc = inicioDelDia(fechaVenc);
            if (venc >= hoy && venc <= limiteVencer) return 'PRONTO_VENCER';
        }

        if (saldo <= 50) return 'BAJO_STOCK';

        return 'ACTIVO';
    }

    function pintarKpi(id, valor) {
        const el = document.getElementById(id);
        if (el) el.textContent = String(valor);
    }

    function renderInventarioCards(inventario) {
        if (!canSeeInventarioCards) return;
        const data = Array.isArray(inventario) ? inventario : [];

        let total = data.length;
        let enStock = 0;
        let porAgotarse = 0;
        let prontoVencer = 0;
        let enCuarentena = 0;
        let bajaRotacion = 0;

        data.forEach((item) => {
            const estado = estadoInventario(item);
            const saldo = Number(item?.saldo ?? 0);

            if (estado === 'EN_CUARENTENA') {
                enCuarentena++;
            }
            if (estado === 'BAJA_ROTACION') {
                bajaRotacion++;
            }
            if (estado === 'ACTIVO' && saldo > 0) {
                enStock++;
            }
            if (estado === 'BAJO_STOCK') {
                porAgotarse++;
            }
            if (estado === 'PRONTO_VENCER') {
                prontoVencer++;
            }
        });

        pintarKpi('inv-kpi-total', total);
        pintarKpi('inv-kpi-stock', enStock);
        pintarKpi('inv-kpi-por-agotarse', porAgotarse);
        pintarKpi('inv-kpi-pronto-vencer', prontoVencer);
        pintarKpi('inv-kpi-cuarentena', enCuarentena);
        pintarKpi('inv-kpi-baja-rotacion', bajaRotacion);
    }

    function renderMedicamentos(productos) {
        const contenedor = document.getElementById('lista-medicamentos-ra');
        if (!contenedor) return;

        if (!Array.isArray(productos) || productos.length === 0) {
            contenedor.innerHTML = '<div class="ram-empty">Sin medicamentos asociados.</div>';
            return;
        }

        contenedor.innerHTML = productos.map(item => `
            <article class="ram-item">
                <div class="title">${escapeHtml(item.medicamento || 'SIN NOMBRE')}</div>
            </article>
        `).join('');
    }

    function renderPacientes(pacientes) {
        const contenedor = document.getElementById('lista-pacientes-ra');
        if (!contenedor) return;

        if (!Array.isArray(pacientes) || pacientes.length === 0) {
            contenedor.innerHTML = '<div class="ram-empty">Sin pacientes con RAM registrados.</div>';
            return;
        }

        contenedor.innerHTML = pacientes.map(item => {
            const idReaccion = Number(item.id_reaccion || 0);
            const linkDetalle = idReaccion > 0
                ? `<?php echo e(url('/reacciones-adversas')); ?>/${idReaccion}`
                : '#';

            return `
                <article class="ram-item">
                    <div class="title">${escapeHtml(item.paciente || '-')}</div>
                    <div class="meta">Expediente: ${escapeHtml(item.numero_expediente || '-')}</div>
                    <div class="meta">Fecha de ingreso: ${escapeHtml(formatFecha(item.fecha_ingreso))}</div>
                    <div class="desc">${escapeHtml(item.descripcion_reaccion || 'Sin descripción registrada.')}</div>
                    <div class="ram-actions">
                        ${idReaccion > 0 ? `<a href="${linkDetalle}" class="btn btn-sm btn-outline-primary fw-semibold">Ver detalles</a>` : ''}
                    </div>
                </article>
            `;
        }).join('');
    }

    fetch('<?php echo e(route('ajax.reacciones_estadisticas_panel')); ?>', { credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : null)
        .then(json => {
            if (!json) return;

            renderMedicamentos(json.productos ?? []);
            renderPacientes(json.pacientes ?? []);
        })
        .catch(() => {
            renderMedicamentos([]);
            renderPacientes([]);
        });

    if (canSeeInventarioCards) {
        fetch('<?php echo e(route('inventario.datos')); ?>', { credentials: 'same-origin' })
            .then(r => r.ok ? r.json() : null)
            .then(json => {
                const inventario = json?.data ?? json ?? [];
                renderInventarioCards(inventario);
            })
            .catch(() => {
                renderInventarioCards([]);
            });
    }
})();
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Pao\OneDrive\Escritorio\Sistema HE\ProyectoHospitalEscuela\Frontend\resources\views/dashboard.blade.php ENDPATH**/ ?>