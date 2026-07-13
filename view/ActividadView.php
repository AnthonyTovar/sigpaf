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
                    <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i> Gestión de Actividades</h5>
                    <a href="index.php?action=nuevaActividad" class="btn btn-light btn-sm fw-bold">
                        <i class="bi bi-plus-circle-fill me-1"></i> Nueva Actividad
                    </a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Actividad</th>
                                    <th>Fechas</th>
                                    <th>Sesiones</th>
                                    <th>Responsables</th>
                                    <th>Lugar</th>
                                    <th>Estatus</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($actividades)): ?>
                                    <?php foreach ($actividades as $a): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary px-2 py-1"><?php echo $a['idActividad']; ?></span></td>
                                            <td>
                                                <div class="fw-bold text-dark"><?php echo $a['nombreActividad']; ?></div>
                                                <div class="text-muted small"><?php echo $a['nomTipoActividad']; ?> | <?php echo $a['nombreVertice']; ?></div>
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
                                            <td class="text-center">
                                                <span class="badge bg-info"><?php echo $a['cantSesionesPlanificada']; ?> sesiones</span>
                                            </td>
                                            <td>
                                                <div class="small"><i class="bi bi-person me-1 text-primary"></i><?php echo $a['nomEmpleado'] . ' ' . $a['apeEmpleado']; ?></div>
                                                <div class="small text-muted"><i class="bi bi-person-badge me-1 text-warning"></i><?php echo $a['nombreDocente'] . ' ' . $a['apellidoDocente']; ?></div>
                                            </td>
                                            <td>
                                                <span class="small text-muted"><?php echo $a['nomAreaE']; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo ($a['nomEstatus'] == 'Activo' || $a['nomEstatus'] == 'En Curso') ? 'success' : 
                                                         (($a['nomEstatus'] == 'Planificado' || $a['nomEstatus'] == 'Pendiente') ? 'warning' : 
                                                         (($a['nomEstatus'] == 'Finalizado' || $a['nomEstatus'] == 'Completado') ? 'info' : 'secondary')); 
                                                ?>">
                                                    <?php echo $a['nomEstatus']; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-info btn-sm border-0 btn-ver" data-id="<?php echo $a['idActividad']; ?>" title="Ver detalle">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning btn-sm border-0 btn-editar" data-id="<?php echo $a['idActividad']; ?>" title="Editar">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-sm border-0 btn-eliminar" data-id="<?php echo $a['idActividad']; ?>" title="Eliminar">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay actividades registradas actualmente.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL VER DETALLE -->
<div class="modal fade" id="modalVerActividad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Detalle de Actividad</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="detalle-actividad-content">
                <!-- Se carga dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="view/public/js/actividadListar.js"></script>