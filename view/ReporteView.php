<?php
// Cadena de consulta con los filtros actuales, para el botón de PDF
$queryPdf = http_build_query([
    'action' => 'reportePDF',
    'mes' => $filtros['mes'],
    'anio' => $filtros['anio'],
    'idDocente' => $filtros['idDocente']
]);

$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

$totalAct = (int) ($resumen['total_actividades'] ?? 0);
$totalPlan = (int) ($resumen['sesiones_planificadas'] ?? 0);
$totalReg = (int) ($resumen['sesiones_registradas'] ?? 0);
$totalAsis = (int) ($resumen['total_asistencias'] ?? 0);
$totalBen = (int) ($resumen['beneficiarios_atendidos'] ?? 0);
?>

<!-- Estilos de impresión: al imprimir desde la vista previa solo sale el
     documento (área #area-reporte); el resto de la página se oculta -->
<style>
    @media print {
        body * { visibility: hidden !important; }
        #area-reporte, #area-reporte * { visibility: visible !important; }
        #area-reporte {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 8mm;
        }
        .no-imprimir { display: none !important; }
    }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-bar-graph me-2"></i> Reportes</h5>
                    <span class="badge bg-light text-primary">Actividades realizadas por FUNDACITI</span>
                </div>

                <div class="card-body">
                    <!-- ===== FILTROS (reporte por MES) ===== -->
                    <form method="get" action="index.php" class="row g-3 align-items-end mb-4">
                        <input type="hidden" name="action" value="reportes">

                        <div class="col-md-3 col-sm-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Mes</label>
                            <select name="mes" class="form-select">
                                <?php foreach ($meses as $num => $nombre): ?>
                                    <option value="<?php echo $num; ?>"
                                        <?php echo (int) $filtros['mes'] === $num ? 'selected' : ''; ?>>
                                        <?php echo $nombre; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Año</label>
                            <input type="number" name="anio" class="form-control" min="2000" max="2100"
                                value="<?php echo (int) $filtros['anio']; ?>">
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Docente</label>
                            <select name="idDocente" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach ($docentes as $d): ?>
                                    <option value="<?php echo $d['idDocente']; ?>"
                                        <?php echo $filtros['idDocente'] === $d['idDocente'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['apellidoDocente'] . ', ' . $d['nombreDocente']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="bi bi-funnel me-1"></i> Filtrar
                            </button>
                            <a href="index.php?action=reportes" class="btn btn-outline-secondary" title="Volver al mes actual">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        </div>
                    </form>

                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Actividades de:</strong> <?php echo htmlspecialchars($filtrosTexto); ?>
                    </p>

                    <!-- ===== RESUMEN ===== -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body py-3 text-center">
                                    <div class="fs-4 fw-bold text-primary"><?php echo $totalAct; ?></div>
                                    <div class="small text-muted">Actividades</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body py-3 text-center">
                                    <div class="fs-4 fw-bold text-success"><?php echo $totalReg; ?><span class="text-muted fs-6 fw-normal">/<?php echo $totalPlan; ?></span></div>
                                    <div class="small text-muted">Sesiones registradas</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body py-3 text-center">
                                    <div class="fs-4 fw-bold text-info"><?php echo $totalAsis; ?></div>
                                    <div class="small text-muted">Asistencias</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body py-3 text-center">
                                    <div class="fs-4 fw-bold text-warning"><?php echo $totalBen; ?></div>
                                    <div class="small text-muted">Beneficiarios atendidos</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== VISTA PREVIA DEL DOCUMENTO (imprimible) ===== -->
                    <div id="area-reporte">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 no-imprimir">
                        <h6 class="mb-0 text-secondary"><i class="bi bi-table me-1"></i> Vista previa del documento</h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary px-4" onclick="window.print()"
                                title="Imprimir esta vista previa">
                                <i class="bi bi-printer me-1"></i> Imprimir
                            </button>
                            <a href="index.php?<?php echo $queryPdf; ?>" class="btn btn-danger px-4"
                                title="Descargar el reporte en PDF con estos filtros">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Descargar PDF
                            </a>
                        </div>
                    </div>

                    <!-- ===== TABLA ===== -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Actividad</th>
                                    <th>Tipo</th>
                                    <th>Docente</th>
                                    <th>Responsable</th>
                                    <th class="text-center">Período</th>
                                    <th>Lugar</th>
                                    <th class="text-center">Estatus</th>
                                    <th class="text-center">Sesiones</th>
                                    <th class="text-center">Benef.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($actividades)): ?>
                                    <?php foreach ($actividades as $a):
                                        $plan = (int) $a['sesiones_planificadas'];
                                        $reg = (int) $a['sesiones_registradas'];
                                        $est = strtolower($a['nomEstatus'] ?? '');
                                        $badge = (strpos($est, 'final') !== false) ? 'bg-success'
                                            : ((strpos($est, 'cancel') !== false || strpos($est, 'espera') !== false) ? 'bg-danger' : 'bg-warning text-dark');
                                    ?>
                                        <tr>
                                            <td class="fw-bold text-dark">
                                                <?php echo htmlspecialchars($a['nombreActividad']); ?>
                                                <div class="text-muted small fw-normal">Meta: <?php echo (int) $a['cantPersoAtender']; ?> persona(s)</div>
                                            </td>
                                            <td class="small"><?php echo htmlspecialchars($a['nomTipoActividad'] ?? '-'); ?></td>
                                            <td class="small"><?php echo htmlspecialchars($a['docente'] ?? '-'); ?></td>
                                            <td class="small">
                                                <?php echo htmlspecialchars($a['responsable'] ?? '-'); ?>
                                                <?php if (!empty($a['nomUnidadEjecutora'])): ?>
                                                    <div class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($a['nomUnidadEjecutora']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center text-muted small text-nowrap">
                                                <?php echo date('d/m/Y', strtotime($a['fechainicioActividad'])); ?><br>
                                                <?php echo date('d/m/Y', strtotime($a['fechafinActividad'])); ?>
                                            </td>
                                            <td class="small"><?php echo htmlspecialchars($a['nomLugarActividad'] ?? '-'); ?></td>
                                            <td class="text-center"><span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($a['nomEstatus'] ?? '-'); ?></span></td>
                                            <td class="text-center small fw-bold"><?php echo $reg; ?>/<?php echo $plan; ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-info text-dark px-2 py-1">
                                                    <i class="bi bi-people-fill me-1"></i><?php echo (int) $a['beneficiarios_atendidos']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <i class="bi bi-info-circle me-1"></i> No hay actividades que coincidan con los filtros.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    </div><!-- /#area-reporte -->
                </div>
            </div>
        </div>
    </div>
</div>
