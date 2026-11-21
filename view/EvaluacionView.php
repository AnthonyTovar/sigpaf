<?php
// Vista del módulo de evaluación (solo super usuario)
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
                    <h5 class="mb-0"><i class="bi bi-clipboard2-data me-2"></i> Evaluación de Actividades</h5>
                </div>

                <div class="card-body">
                    <!-- Filtros -->
                    <form method="GET" action="index.php" class="border rounded bg-light p-3 mb-3">
                        <input type="hidden" name="action" value="evaluacion">
                        <div class="row g-2 align-items-end">
                            <div class="col-6 col-md-3">
                                <label for="filtro-fecha" class="form-label small fw-bold text-muted mb-1">
                                    <i class="bi bi-calendar-event me-1"></i>Fecha específica
                                </label>
                                <input type="date" id="filtro-fecha" name="fecha" class="form-control form-control-sm"
                                       value="<?php echo htmlspecialchars($filtros['fecha']); ?>">
                            </div>
                            <div class="col-6 col-md-2">
                                <label for="filtro-anio" class="form-label small fw-bold text-muted mb-1">
                                    <i class="bi bi-calendar3 me-1"></i>Año
                                </label>
                                <select id="filtro-anio" name="anio" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <?php foreach ($anios as $anio): ?>
                                        <option value="<?php echo $anio; ?>"
                                            <?php echo ((string) $filtros['anio'] === (string) $anio) ? 'selected' : ''; ?>>
                                            <?php echo $anio; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="filtro-estatus" class="form-label small fw-bold text-muted mb-1">
                                    <i class="bi bi-flag me-1"></i>Estatus
                                </label>
                                <select id="filtro-estatus" name="idEstatus" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <?php foreach ($estatus as $e): ?>
                                        <option value="<?php echo $e['idEstatus']; ?>"
                                            <?php echo ($filtros['idEstatus'] === $e['idEstatus']) ? 'selected' : ''; ?>>
                                            <?php echo $e['nomEstatus']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-4 d-flex gap-2 justify-content-md-end">
                                <button type="submit" class="btn btn-primary btn-sm fw-bold">
                                    <i class="bi bi-funnel-fill me-1"></i> Filtrar
                                </button>
                                <a href="index.php?action=evaluacion" class="btn btn-outline-secondary btn-sm fw-bold" title="Limpiar filtros">
                                    <i class="bi bi-eraser-fill"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                    <!-- Fin filtros -->

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Actividad</th>
                                    <th>Fechas</th>
                                    <th>Planificada por</th>
                                    <th>Estatus</th>
                                    <th>Evaluación</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($actividades)): ?>
                                    <?php foreach ($actividades as $a): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary px-2 py-1"><?php echo $a['idActividad']; ?></span></td>
                                            <td>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($a['nombreActividad']); ?></div>
                                                <div class="text-muted small"><?php echo htmlspecialchars($a['nomTipoActividad'] ?? ''); ?></div>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <i class="bi bi-calendar-event text-primary me-1"></i>
                                                    <?php echo date('d/m/Y', strtotime($a['fechainicioActividad'])); ?>
                                                </div>
                                                <div class="small text-muted">
                                                    <i class="bi bi-calendar-check text-success me-1"></i>
                                                    <?php echo date('d/m/Y', strtotime($a['fechafinActividad'])); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="small"><i class="bi bi-person-check me-1 text-primary"></i><?php echo htmlspecialchars($a['nomCreador'] ?? 'No registrado'); ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                    $est = strtolower($a['nomEstatus'] ?? '');
                                                    $claseEstatus = ((strpos($est, 'final') !== false || strpos($est, 'ejecutad') !== false)) ? 'bg-success'
                                                        : ((strpos($est, 'cancel') !== false || strpos($est, 'espera') !== false) ? 'bg-danger' : 'bg-warning text-dark');
                                                ?>
                                                <span class="badge <?php echo $claseEstatus; ?>"><?php echo $a['nomEstatus']; ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                    $totalEval = (int) $a['totalEvaluaciones'];
                                                    $requiere = (int) ($a['ultimoRequiereCambio'] ?? 0) === 1;
                                                    $confirmado = (int) ($a['ultimoConfirmado'] ?? 0) === 1;
                                                    if ($totalEval === 0) {
                                                        echo '<span class="badge bg-secondary">Sin evaluar</span>';
                                                    } elseif ($requiere && $confirmado) {
                                                        echo '<span class="badge bg-primary"><i class="bi bi-bell-fill me-1"></i>Cambios realizados</span>';
                                                        if (!empty($a['ultimaConfirmacion'])) {
                                                            echo '<div class="small text-muted mt-1">El planificador confirmó los cambios el '
                                                                . date('d/m/Y h:i a', strtotime($a['ultimaConfirmacion'])) . '</div>';
                                                        }
                                                    } elseif ($requiere) {
                                                        echo '<span class="badge bg-danger">Requiere cambios</span>';
                                                        echo '<div class="small text-muted mt-1">Esperando que el planificador confirme los cambios</div>';
                                                    } else {
                                                        echo '<span class="badge bg-success">Revisada</span>';
                                                    }
                                                    if ($totalEval > 0) {
                                                        echo '<div class="small text-muted mt-1">' . $totalEval . ' evaluación(es) · última: '
                                                            . date('d/m/Y', strtotime($a['ultimaEvaluacion'])) . '</div>';
                                                    }
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-primary btn-sm border-0 btn-evaluar"
                                                        data-id="<?php echo $a['idActividad']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($a['nombreActividad']); ?>"
                                                        title="Evaluar actividad">
                                                        <i class="bi bi-clipboard2-check"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info btn-sm border-0 btn-historial"
                                                        data-id="<?php echo $a['idActividad']; ?>"
                                                        title="Historial de evaluaciones">
                                                        <i class="bi bi-clock-history"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay actividades con los filtros seleccionados.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal evaluar actividad -->
<div class="modal fade" id="modalEvaluar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-clipboard2-check me-2"></i>Evaluar Actividad</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">
                    Actividad: <strong id="eval-nombre-actividad"></strong><br>
                    Si marcas que requiere cambios, la nota la verá <strong>únicamente</strong> el usuario que planificó la actividad.
                </p>
                <form id="formEvaluacion">
                    <input type="hidden" id="eval-idActividad" name="idActividad">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="eval-requiereCambio" name="requiereCambio" value="1">
                        <label class="form-check-label fw-bold" for="eval-requiereCambio">La actividad requiere cambios</label>
                    </div>
                    <div class="mb-2">
                        <label for="eval-nota" class="form-label small fw-bold text-muted">Nota de evaluación</label>
                        <textarea id="eval-nota" name="notaEvaluacion" class="form-control" rows="4" maxlength="500"
                            placeholder="Observaciones, cambios requeridos o comentarios de la evaluación..."></textarea>
                        <div class="form-text" id="eval-nota-ayuda">La nota es opcional si no se requieren cambios; obligatoria si los requiere.</div>
                    </div>
                    <div id="eval-mensaje" class="alert" style="display:none;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary fw-bold" id="btn-guardar-evaluacion">
                    <i class="bi bi-check-circle me-1"></i>Guardar evaluación
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal historial de evaluaciones -->
<div class="modal fade" id="modalHistorialEval" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-clock-history me-2"></i>Historial de Evaluaciones</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="historial-eval-content">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="view/public/js/evaluacion.js?v=2026-11-04.2"></script>
