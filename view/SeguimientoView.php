<?php
// Vista de seguimiento

$parametrosPdf = array_filter($filtros ?? []);
$urlPdf = 'index.php?action=exportarPdfSeguimiento';
if (!empty($parametrosPdf)) {
    $urlPdf .= '&' . http_build_query($parametrosPdf);
}
?>
<div class="container-fluid py-4">
    <div style="height: 0; position: relative;">
        <div id="registro-alerta" class="alert shadow-sm"
            style="display:none; position: absolute; top: -50px; left: 0; right: 0; z-index: 1; margin: 0 1px;"
            role="alert">
            <i id="alerta-icono" class="bi"></i>
            <span id="alerta-texto"></span>
        </div>
    </div>

    <div class="row" style="margin-top: 10px">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clipboard2-check me-2"></i> Seguimiento de Actividades</h5>
                    <a href="<?php echo $urlPdf; ?>" class="btn btn-danger btn-sm fw-bold"
                        target="_blank" title="Ver el seguimiento en PDF con los filtros aplicados">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Exportar PDF
                    </a>
                </div>

                <div class="card-body">
                    <!-- Filtros -->
                    <form method="GET" action="index.php" class="border rounded bg-light p-3 mb-3">
                        <input type="hidden" name="action" value="seguimiento">
                        <div class="row g-2 align-items-end">
                            <div class="col-6 col-md-3">
                                <label for="filtro-desde" class="form-label small fw-bold text-muted mb-1">
                                    <i class="bi bi-calendar-event me-1"></i>Desde
                                </label>
                                <input type="date" id="filtro-desde" name="desde" class="form-control form-control-sm"
                                       value="<?php echo htmlspecialchars($filtros['desde'] ?? ''); ?>">
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="filtro-hasta" class="form-label small fw-bold text-muted mb-1">
                                    <i class="bi bi-calendar-event-fill me-1"></i>Hasta
                                </label>
                                <input type="date" id="filtro-hasta" name="hasta" class="form-control form-control-sm"
                                       value="<?php echo htmlspecialchars($filtros['hasta'] ?? ''); ?>">
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="filtro-estatus" class="form-label small fw-bold text-muted mb-1">
                                    <i class="bi bi-flag me-1"></i>Estatus
                                </label>
                                <select id="filtro-estatus" name="idEstatus" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <?php foreach ($estatus as $e): ?>
                                        <option value="<?php echo $e['idEstatus']; ?>"
                                            <?php echo (($filtros['idEstatus'] ?? '') === $e['idEstatus']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($e['nomEstatus']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm fw-bold flex-grow-1">
                                    <i class="bi bi-funnel-fill me-1"></i> Filtrar
                                </button>
                                <a href="index.php?action=seguimiento" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Actividad</th>
                                    <th class="text-center">Período</th>
                                    <th class="text-center" style="min-width: 220px;">Avance de Sesiones</th>
                                    <th class="text-center">Beneficiarios</th>
                                    <th class="text-center">Estatus</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($actividades)): ?>
                                    <?php foreach ($actividades as $a):
                                        $plan = (int) $a['sesiones_planificadas'];
                                        $reg = (int) $a['sesiones_registradas'];
                                        $pct = $plan > 0 ? round(($reg / $plan) * 100) : 0;
                                        $fInicio = date('d/m/Y', strtotime($a['fechainicioActividad']));
                                        $fFin = date('d/m/Y', strtotime($a['fechafinActividad']));
                                    ?>
                                        <tr data-empleado="<?php echo htmlspecialchars($a['idEmpleado']); ?>">
                                            <td class="fw-bold text-dark">
                                                <?php echo htmlspecialchars($a['nombreActividad']); ?>
                                                <div class="text-muted small fw-normal">Meta: <?php echo (int) $a['cantPersoAtender']; ?> persona(s)</div>
                                            </td>
                                            <td class="text-center text-muted small">
                                                <?php echo $fInicio; ?><br><?php echo $fFin; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($plan > 0): ?>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="progress flex-grow-1" style="height: 8px;">
                                                            <div class="progress-bar <?php echo $pct === 100 ? 'bg-success' : 'bg-primary'; ?>"
                                                                style="width: <?php echo $pct; ?>%"></div>
                                                        </div>
                                                        <span class="small fw-bold text-nowrap"><?php echo $reg; ?>/<?php echo $plan; ?> (<?php echo $pct; ?>%)</span>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Sin sesiones planificadas</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info text-dark px-2 py-1">
                                                    <i class="bi bi-people-fill me-1"></i><?php echo (int) $a['beneficiarios_atendidos']; ?>
                                                </span>
                                            </td>
                                            <?php
                                                $est = strtolower($a['nomEstatus']);
                                                $claseEstatus = ((strpos($est, 'final') !== false || strpos($est, 'ejecutad') !== false)) ? 'bg-success'
                                                    : ((strpos($est, 'cancel') !== false || strpos($est, 'espera') !== false) ? 'bg-danger' : 'bg-warning text-dark');
                                            ?>
                                            <td class="text-center">
                                                <span class="badge badge-estatus <?php echo $claseEstatus; ?>"><?php echo htmlspecialchars($a['nomEstatus']); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-outline-info btn-sm border-0 btn-ver-sesiones"
                                                        data-id="<?php echo htmlspecialchars($a['idActividad']); ?>"
                                                        data-nombre="<?php echo htmlspecialchars($a['nombreActividad']); ?>"
                                                        title="Ver sesiones y fichas">
                                                        <i class="bi bi-calendar2-week"></i>
                                                    </button>
                                                    <?php if ($plan > 0): ?>
                                                        <a href="index.php?action=registrarSeguimiento&id=<?php echo urlencode($a['idActividad']); ?>"
                                                            class="btn btn-outline-primary btn-sm border-0" title="Registrar seguimiento">
                                                            <i class="bi bi-clipboard2-pulse"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bi bi-info-circle me-1"></i> No hay actividades registradas.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: sesiones de la actividad -->
<div class="modal fade" id="modalSesiones" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-calendar2-week me-2"></i>Sesiones de la Actividad</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">
                    Actividad: <strong class="text-dark" id="sesiones-nombre-actividad">-</strong>
                </p>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 80px;">Sesión</th>
                                <th class="text-center">Fecha</th>
                                <th>Tipo de Entrega</th>
                                <th class="text-center">Asistentes</th>
                                <th class="text-center">Registro</th>
                                <th class="text-center">Ficha</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-sesiones-body">
                            <tr><td colspan="6" class="text-center py-4 text-muted">Cargando sesiones...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: ficha de la sesión -->
<div class="modal fade" id="modalFichaSesion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-dark">
                <h5 class="modal-title"><i class="bi bi-file-earmark-text me-2"></i>Ficha de la Sesión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="ficha-sesion-contenido">
                <p class="text-muted text-center py-4">Cargando ficha...</p>
            </div>
            <div class="modal-footer bg-light border-0 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
                <a href="#" id="btnPdfSesion" class="btn btn-danger px-4 fw-bold" target="_blank">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Exportar PDF de la Sesión
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Aviso de seguimiento guardado
$(document).ready(function () {
    try {
        var pendiente = sessionStorage.getItem('sigpaf_seguimiento_aviso');
        if (pendiente) {
            sessionStorage.removeItem('sigpaf_seguimiento_aviso');
            var datos = JSON.parse(pendiente);
            if (window.Validar && Validar.aviso) {
                Validar.aviso(datos.mensaje, datos.tipo);
            }
        }
    } catch (e) {}
});
</script>
<script src="view/public/js/seguimientoListar.js?v=2026-11-04.1"></script>
