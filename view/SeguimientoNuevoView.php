<div class="container-fluid py-4">
    <div class="row g-3" style="margin-top: 5px">
        <div class="col-12">
            <div class="card shadow-sm border-0 activity-wizard-card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard2-pulse me-2"></i>
                        Registrar Seguimiento
                    </h5>
                    <span class="badge bg-light text-primary" id="paso-indicador">Paso 1 de 3</span>
                </div>
                <div class="card-body p-0">
                    
                    <div class="wizard-progress">
                        <div class="wizard-progress-bar" id="wizard-progress-bar" style="width: 33.33%"></div>
                    </div>

                    
                    <div class="wizard-steps-nav">
                        <div class="wizard-step active" data-step="1">
                            <div class="step-circle">1</div>
                            <span class="step-label">Sesión</span>
                        </div>
                        <div class="wizard-step" data-step="2">
                            <div class="step-circle">2</div>
                            <span class="step-label">Desarrollo</span>
                        </div>
                        <div class="wizard-step" data-step="3">
                            <div class="step-circle">3</div>
                            <span class="step-label">Asistencia y Estatus</span>
                        </div>
                    </div>

                    <form id="formSeguimiento" novalidate>
                        <!-- Paso 1: selección de sesión -->
                        <div class="wizard-step-content active" id="step-1">
                            <div class="p-4">
                                <h6 class="step-title"><i class="bi bi-calendar-event me-2 text-primary"></i>Selección de Sesión</h6>

                                <div class="alert alert-light border small mb-3">
                                    <i class="bi bi-clipboard2-check me-1 text-primary"></i>
                                    <strong><?php
// Vista de Seguimiento Nuevo


 echo htmlspecialchars($actividad['nombreActividad']); ?></strong>
                                    <span class="text-muted">— Meta: <?php echo (int) $actividad['cantPersoAtender']; ?> persona(s)</span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input type="hidden" id="idSegActividad" name="idSegActividad">
                                        <label class="form-label fw-semibold text-secondary">Sesión <span class="text-danger">*</span></label>
                                        <select id="selectSesion" class="form-select" required>
                                            <option value="">Cargando sesiones...</option>
                                        </select>
                                        <div class="invalid-feedback" id="error-idSegActividad"></div>
                                        <div class="form-text text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Las sesiones futuras no se pueden registrar todavía. Las ya registradas aparecen marcadas y pueden editarse.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-secondary">Fecha de la Sesión</label>
                                        <input type="text" id="fechaSesionTexto" class="form-control" readonly
                                            style="background:#f8fafc;" value="-">
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold text-secondary">Avance de la Actividad</label>
                                        <div class="border rounded p-3" style="background:#f8fafc;">
                                            <div class="d-flex justify-content-between small mb-1">
                                                <span class="text-muted">Sesiones registradas</span>
                                                <strong id="textoProgreso">-</strong>
                                            </div>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar bg-primary" id="barraProgreso" style="width: 0%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 2: desarrollo de la sesión -->
                        <div class="wizard-step-content" id="step-2">
                            <div class="p-4">
                                <h6 class="step-title"><i class="bi bi-journal-text me-2 text-primary"></i>Desarrollo de la Sesión</h6>

                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold text-secondary">Logro de la Sesión <span class="text-danger">*</span></label>
                                        <textarea name="logroActividad" id="logroActividad" class="form-control" rows="3"
                                            placeholder="¿Qué se logró en esta sesión?" maxlength="500"></textarea>
                                        <div class="d-flex justify-content-between">
                                            <div class="invalid-feedback d-block" id="error-logroActividad" style="display:none;"></div>
                                            <small class="text-muted ms-auto" id="contadorLogro">0/500</small>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold text-secondary">Observaciones y Obstáculos</label>
                                        <textarea name="observObstaculo" id="observObstaculo" class="form-control" rows="3"
                                            placeholder="Dificultades, incidencias o comentarios (opcional)..." maxlength="500"></textarea>
                                        <div class="d-flex justify-content-end">
                                            <small class="text-muted" id="contadorObserv">0/500</small>
                                        </div>
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
                                </div>
                            </div>
                        </div>

                        <!-- Paso 3: asistencia y estatus -->
                        <div class="wizard-step-content" id="step-3">
                            <div class="p-4">
                                <h6 class="step-title"><i class="bi bi-people me-2 text-primary"></i>Asistencia y Estatus</h6>

                                <div class="row g-3">
                                    <!-- Asistencia -->
                                    <div class="col-md-12">
                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                            <label class="form-label fw-semibold text-secondary mb-0">Asistencia de Beneficiarios</label>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="collapse" data-bs-target="#panelImportarExcel" aria-expanded="false">
                                                    <i class="bi bi-file-earmark-excel me-1"></i> Importar Excel
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" id="btnNuevoBeneficiario"
                                                    data-bs-toggle="collapse" data-bs-target="#panelNuevoBeneficiario" aria-expanded="false">
                                                    <i class="bi bi-person-plus me-1"></i> Carga manual
                                                </button>
                                            </div>
                                        </div>

                                        <div class="collapse mb-3" id="panelImportarExcel">
                                            <div class="border rounded p-3" style="background:#f8fafc;">
                                                <h6 class="small fw-semibold text-secondary"><i class="bi bi-file-earmark-spreadsheet me-1 text-primary"></i>Importar ficha de seguimiento</h6>
                                                <p class="small text-muted">Use la hoja <strong>BEN-AE1</strong> del formato original. La fecha y el número de actividad de cada fila deben corresponder a la sesión seleccionada.</p>
                                                <div class="row g-2 align-items-end">
                                                    <div class="col-md-9">
                                                        <label for="archivoExcelSeguimiento" class="form-label small mb-1">Archivo Excel <span class="text-danger">*</span></label>
                                                        <input type="file" class="form-control form-control-sm" id="archivoExcelSeguimiento" accept=".xlsx">
                                                    </div>
                                                    <div class="col-md-3 d-grid">
                                                        <button type="button" class="btn btn-sm btn-primary" id="btnImportarExcel">
                                                            <i class="bi bi-upload me-1"></i> Importar asistencia
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="alert small py-2 px-3 mt-2 mb-0" id="mensajeImportarExcel" style="display:none;" role="alert"></div>
                                            </div>
                                        </div>

                                        <div class="collapse mb-3" id="panelNuevoBeneficiario">
                                            <div class="border rounded p-3" style="background:#f8fafc;">
                                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                                    <span class="small fw-semibold text-secondary">
                                                        <i class="bi bi-people-fill me-1 text-success"></i>
                                                        Carga manual con los mismos datos de la ficha Excel.
                                                    </span>
                                                    <span class="badge bg-success" id="contadorFilasLote">0 filas</span>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-sm align-middle mb-2" id="tablaLoteBeneficiarios">
                                                        <thead>
                                                            <tr class="small text-muted">
                                                                <th style="width:32%;">Nombre y apellido <span class="text-danger">*</span></th>
                                                                <th style="width:18%;">Fecha de nacimiento <span class="text-danger">*</span></th>
                                                                <th style="width:17%;">Cédula <span class="text-danger">*</span></th>
                                                                <th style="width:13%;">Edad</th>
                                                                <th style="width:12%;">Sexo <span class="text-danger">*</span></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="lote-beneficiarios-body"></tbody>
                                                    </table>
                                                </div>
                                                <div class="d-flex gap-2 justify-content-between flex-wrap">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnMasFilasBeneficiario">
                                                        <i class="bi bi-plus-lg me-1"></i> Cargar 10 más
                                                    </button>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="collapse"
                                                            data-bs-target="#panelNuevoBeneficiario">
                                                            <i class="bi bi-x-lg me-1"></i> Cerrar
                                                        </button>
                                                        <button type="button" id="btnGuardarLoteBeneficiarios" class="btn btn-sm btn-success">
                                                            <i class="bi bi-check-lg me-1"></i> Guardar página de beneficiarios
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="alert small py-2 px-3 mt-2 mb-0" id="mensajeLoteBeneficiarios" style="display:none;" role="alert"></div>
                                                <div class="form-text text-muted mt-2">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    La edad se calcula automáticamente. Si la cédula no tiene prefijo se considera venezolana; use E para extranjeros. Las filas vacías y las cédulas ya registradas se omiten con un aviso.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="alert alert-light border small py-2 px-3">
                                            <i class="bi bi-funnel me-1 text-primary"></i>
                                            Rangos permitidos: <strong id="textoRangoEdad">Cargando...</strong><br>
                                            <span class="text-muted">La edad se calcula en la fecha de la sesión seleccionada. Solo se puede marcar a quienes cumplan el rango.</span>
                                        </div>

                                        <div id="lista-beneficiarios" class="border rounded p-2" style="max-height: 260px; overflow-y: auto;">
                                            <p class="text-muted small mb-0 px-2 py-3 text-center">
                                                <i class="bi bi-people me-1"></i> Cargando beneficiarios...
                                            </p>
                                        </div>
                                        <div class="form-text text-muted mt-1">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Asistentes marcados: <strong id="contadorAsistentes">0</strong>
                                        </div>
                                    </div>

                                    
                                    <?php if ($puedeEditarEstatus): ?>
                                        <div class="col-md-12">
                                            <div class="border rounded p-3" style="background:#f8fafc;">
                                                <label class="form-label fw-semibold text-secondary mb-1">
                                                    <i class="bi bi-flag me-1 text-primary"></i>Estatus de la Actividad
                                                </label>
                                                <select id="selectEstatusActividad" class="form-select">
                                                    <?php foreach ($estatus as $e): ?>
                                                        <option value="<?php echo $e['idEstatus']; ?>"
                                                            <?php echo $e['idEstatus'] === $actividad['idEstatus'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($e['nomEstatus']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="form-text text-muted mb-0 mt-1">
                                                    <i class="bi bi-shield-check me-1"></i>
                                                    Al cambiar el estatus aparecerá una alerta de confirmación por seguridad.
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        
                        <div class="wizard-footer p-4 border-top">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="index.php?action=seguimiento" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left me-1"></i> Volver al listado
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary ms-2" id="btnAnterior" disabled>
                                        <i class="bi bi-arrow-left me-1"></i> Anterior
                                    </button>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-primary" id="btnSiguiente">
                                        Siguiente <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                    <button type="submit" class="btn btn-success" id="btnGuardar" style="display:none;">
                                        <i class="bi bi-check-lg me-1"></i> Guardar Seguimiento
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: confirmar cambio de estatus -->
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
// Configuración del asistente
window.SeguimientoNuevoCfg = {
    idActividad: <?php echo json_encode($actividad['idActividad']); ?>,
    nombreActividad: <?php echo json_encode($actividad['nombreActividad']); ?>,
    estatusOriginal: <?php echo json_encode($actividad['idEstatus']); ?>,
    estatus: <?php echo json_encode(array_values($estatus)); ?>
};
</script>
<script src="view/public/js/seguimientoNuevo.js?v=2026-11-09.3"></script>
