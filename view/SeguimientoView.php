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
                    <span class="badge bg-light text-primary">Sesiones por actividad</span>
                </div>

                <div class="card-body">
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
                                <?php
// Vista de seguimiento


 if (!empty($actividades)): ?>
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
                                                $claseEstatus = (strpos($est, 'final') !== false) ? 'bg-success'
                                                    : ((strpos($est, 'cancel') !== false || strpos($est, 'espera') !== false) ? 'bg-danger' : 'bg-warning text-dark');
                                            ?>
                                            <td class="text-center">
                                                <span class="badge badge-estatus <?php echo $claseEstatus; ?>"><?php echo htmlspecialchars($a['nomEstatus']); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($plan > 0): ?>
                                                    <a href="index.php?action=registrarSeguimiento&id=<?php echo urlencode($a['idActividad']); ?>"
                                                        class="btn btn-outline-primary btn-sm border-0" title="Registrar seguimiento">
                                                        <i class="bi bi-clipboard2-pulse"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
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
