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
                                                // Color del estatus para el badge de la tabla
                                                $est = strtolower($a['nomEstatus']);
                                                $claseEstatus = (strpos($est, 'final') !== false) ? 'bg-success'
                                                    : ((strpos($est, 'cancel') !== false || strpos($est, 'espera') !== false) ? 'bg-danger' : 'bg-warning text-dark');
                                            ?>
                                            <td class="text-center">
                                                <span class="badge badge-estatus <?php echo $claseEstatus; ?>"><?php echo htmlspecialchars($a['nomEstatus']); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($plan > 0): ?>
                                                    <button class="btn btn-outline-primary btn-sm border-0 btn-seguimiento"
                                                        data-id="<?php echo $a['idActividad']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($a['nombreActividad']); ?>"
                                                        data-estatus="<?php echo htmlspecialchars($a['idEstatus']); ?>"
                                                        data-empleado="<?php echo htmlspecialchars($a['idEmpleado']); ?>"
                                                        title="Registrar seguimiento">
                                                        <i class="bi bi-clipboard2-pulse"></i>
                                                    </button>
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

<!-- ===== MODAL: REGISTRAR SEGUIMIENTO DE SESIÓN ===== -->
<div class="modal fade" id="modalSeguimiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formSeguimiento" novalidate>
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-clipboard2-pulse me-2"></i>Seguimiento de Sesión</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="idSegActividad" id="idSegActividad">

                    <div class="alert alert-light border small mb-3">
                        <i class="bi bi-calendar-event me-1 text-primary"></i>
                        <strong id="seg-nombre-actividad">Actividad</strong>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Sesión <span class="text-danger">*</span></label>
                            <select id="selectSesion" class="form-select" required>
                                <option value="">Cargando sesiones...</option>
                            </select>
                            <div class="invalid-feedback" id="error-idSegActividad"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Fecha de la Sesión</label>
                            <input type="text" id="fechaSesionTexto" class="form-control" readonly
                                style="background:#f8fafc;" value="-">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-secondary">Logro de la Sesión <span class="text-danger">*</span></label>
                            <textarea name="logroActividad" id="logroActividad" class="form-control" rows="2"
                                placeholder="¿Qué se logró en esta sesión?" maxlength="500"></textarea>
                            <div class="invalid-feedback" id="error-logroActividad"></div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-secondary">Observaciones y Obstáculos</label>
                            <textarea name="observObstaculo" id="observObstaculo" class="form-control" rows="2"
                                placeholder="Dificultades, incidencias o comentarios (opcional)..." maxlength="500"></textarea>
                            <div class="invalid-feedback" id="error-observObstaculo"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Tipo de Entrega <span class="text-danger">*</span></label>
                            <select name="idTipEntrega" id="idTipEntrega" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($tiposEntrega as $te): ?>
                                    <option value="<?php echo $te['idTipEntrega']; ?>">
                                        <?php echo htmlspecialchars($te['nomTipEntrega']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" id="error-idTipEntrega"></div>
                        </div>

                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-text text-muted w-100 mb-1">
                                <i class="bi bi-info-circle me-1"></i>
                                Asistentes marcados: <strong id="contadorAsistentes">0</strong>
                            </div>
                        </div>

                        <!-- ASISTENCIA -->
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-semibold text-secondary mb-0">Asistencia de Beneficiarios</label>
                                <button type="button" class="btn btn-sm btn-outline-success" id="btnNuevoBeneficiario"
                                    data-bs-toggle="modal" data-bs-target="#modalNuevoBeneficiario">
                                    <i class="bi bi-person-plus me-1"></i> Nuevo Beneficiario
                                </button>
                            </div>
                            <div id="lista-beneficiarios" class="border rounded p-2" style="max-height: 220px; overflow-y: auto;">
                                <p class="text-muted small mb-0 px-2 py-3 text-center">
                                    <i class="bi bi-people me-1"></i> Cargando beneficiarios...
                                </p>
                            </div>
                        </div>

                        <!-- ESTATUS DE LA ACTIVIDAD (solo responsable o administrador) -->
                        <div class="col-md-12" id="campoEstatusActividad" style="display:none;">
                            <div class="border rounded p-3" style="background:#f8fafc;">
                                <label class="form-label fw-semibold text-secondary mb-1">
                                    <i class="bi bi-flag me-1 text-primary"></i>Estatus de la Actividad
                                </label>
                                <select id="selectEstatusActividad" class="form-select"></select>
                                <div class="form-text text-muted mb-0 mt-1">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Al cambiar el estatus aparecerá una alerta de confirmación por seguridad.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Guardar Seguimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL: NUEVO BENEFICIARIO ===== -->
<div class="modal fade" id="modalNuevoBeneficiario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formNuevoBeneficiario" novalidate>
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Nuevo Beneficiario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Cédula <span class="text-danger">*</span></label>
                            <input type="text" name="cedulaBeneficiario" class="form-control" placeholder="V12345678" maxlength="9">
                            <div class="invalid-feedback" id="error-cedulaBeneficiario"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Nacionalidad <span class="text-danger">*</span></label>
                            <select name="nacionalidad" class="form-select">
                                <option value="">Seleccione...</option>
                                <option value="Venezolano">Venezolano</option>
                                <option value="Extranjero">Extranjero</option>
                            </select>
                            <div class="invalid-feedback" id="error-nacionalidad"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Nombres <span class="text-danger">*</span></label>
                            <input type="text" name="nombresBeneficiario" class="form-control" placeholder="Nombres" maxlength="250">
                            <div class="invalid-feedback" id="error-nombresBeneficiario"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" name="apellidosBeneficiario" class="form-control" placeholder="Apellidos" maxlength="250">
                            <div class="invalid-feedback" id="error-apellidosBeneficiario"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Fecha de Nacimiento</label>
                            <input type="date" name="fechaNacBeneficiario" class="form-control">
                            <div class="invalid-feedback" id="error-fechaNacBeneficiario"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Sexo <span class="text-danger">*</span></label>
                            <select name="sexoBeneficiario" class="form-select">
                                <option value="">Seleccione...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                            <div class="invalid-feedback" id="error-sexoBeneficiario"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Guardar Beneficiario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL: CONFIRMAR CAMBIO DE ESTATUS ===== -->
<div class="modal fade" id="modalConfirmarEstatus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning-subtle border-0">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>Confirmar cambio de estatus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-1 fw-semibold">Estás a punto de cambiar el estatus de esta actividad.</p>
                <p class="text-muted mb-3" id="textoConfirmarEstatus"></p>
                <div class="alert alert-light border small mb-0">
                    <i class="bi bi-info-circle me-1 text-primary"></i>
                    Este cambio queda registrado en la bitácora del sistema.
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning px-4" id="btnConfirmarEstatus">
                    <i class="bi bi-check-lg me-1"></i> Sí, cambiar estatus
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Configuración del módulo: estatus disponibles, empleado del usuario
// logueado y si es administrador (define quién puede cambiar el estatus)
window.SeguimientoCfg = {
    miEmpleado: <?php echo json_encode($idEmpleadoUsuario); ?>,
    esAdmin: <?php echo $esAdmin ? 'true' : 'false'; ?>,
    estatus: <?php echo json_encode(array_values($estatus)); ?>
};
</script>
<script src="view/public/js/seguimiento.js?v=2026-10-13.3"></script>
