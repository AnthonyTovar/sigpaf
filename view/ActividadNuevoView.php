<?php
// Convertir fechas ocupadas a JSON para el calendario
$fechasOcupadasJson = json_encode(array_map(function($f) {
    return [
        'inicio' => $f['fechainicioActividad'],
        'fin' => $f['fechafinActividad']
    ];
}, $fechasOcupadas));
?>

<div class="container-fluid py-3">
    <div style="height: 0; position: relative;">
        <div id="registro-alerta" class="alert shadow-sm"
            style="display:none; position: absolute; top: -45px; left: 0; right: 0; z-index: 999; margin: 0 1px;"
            role="alert">
            <i id="alerta-icono" class="bi"></i>
            <span id="alerta-texto"></span>
        </div>
    </div>

    <div class="row g-3" style="margin-top: 5px">
        <!-- ===== PANEL IZQUIERDO: WIZARD DE PASOS ===== -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 activity-wizard-card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i> Nueva Actividad</h5>
                    <span class="badge bg-light text-primary" id="paso-indicador">Paso 1 de 6</span>
                </div>
                <div class="card-body p-0">
                    <!-- Progress Bar -->
                    <div class="wizard-progress">
                        <div class="wizard-progress-bar" id="wizard-progress-bar" style="width: 16.66%"></div>
                    </div>

                    <!-- Steps Navigation -->
                    <div class="wizard-steps-nav">
                        <div class="wizard-step active" data-step="1">
                            <div class="step-circle">1</div>
                            <span class="step-label">Básico</span>
                        </div>
                        <div class="wizard-step" data-step="2">
                            <div class="step-circle">2</div>
                            <span class="step-label">Grupos</span>
                        </div>
                        <div class="wizard-step" data-step="3">
                            <div class="step-circle">3</div>
                            <span class="step-label">Fechas</span>
                        </div>
                        <div class="wizard-step" data-step="4">
                            <div class="step-circle">4</div>
                            <span class="step-label">Lugar</span>
                        </div>
                        <div class="wizard-step" data-step="5">
                            <div class="step-circle">5</div>
                            <span class="step-label">Responsables</span>
                        </div>
                        <div class="wizard-step" data-step="6">
                            <div class="step-circle">6</div>
                            <span class="step-label">Resumen</span>
                        </div>
                    </div>

                    <form id="formActividad" novalidate>
                        <!-- ===== PASO 1: INFORMACIÓN BÁSICA ===== -->
                        <div class="wizard-step-content active" id="step-1">
                            <div class="p-4">
                                <h6 class="step-title"><i class="bi bi-info-circle me-2 text-primary"></i>Información Básica</h6>
                                
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Nombre de la Actividad <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-pencil-square"></i></span>
                                            <input type="text" name="nombreActividad" class="form-control" placeholder="Ej: Taller de Robótica para Jóvenes" maxlength="250">
                                        </div>
                                        <div class="invalid-feedback" id="error-nombreActividad"></div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Objetivo <span class="text-danger">*</span></label>
                                        <textarea name="objetivoActividad" class="form-control" rows="2" placeholder="Objetivo principal de la actividad..." maxlength="250"></textarea>
                                        <div class="invalid-feedback" id="error-objetivoActividad"></div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Descripción <span class="text-danger">*</span></label>
                                        <textarea name="descActividad" class="form-control" rows="2" placeholder="Breve descripción de la actividad..." maxlength="250"></textarea>
                                        <div class="invalid-feedback" id="error-descActividad"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Estrategia de Desarrollo <span class="text-danger">*</span></label>
                                        <select name="idEstDesarrollo" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($estrategias as $e): ?>
                                                <option value="<?php echo $e['idEstDesarrollo']; ?>"><?php echo $e['nomEstDesarrollo']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-idEstDesarrollo"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Tipo de Actividad <span class="text-danger">*</span></label>
                                        <select name="idTipoActividad" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($tiposActividad as $t): ?>
                                                <option value="<?php echo $t['idTipoActividad']; ?>"><?php echo $t['nomTipoActividad']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-idTipoActividad"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Vértice <span class="text-danger">*</span></label>
                                        <select name="idVertice" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($vertices as $v): ?>
                                                <option value="<?php echo $v['idVertice']; ?>"><?php echo $v['nombreVertice']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-idVertice"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Área Específica <span class="text-danger">*</span></label>
                                        <select name="idAreaE" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($areasEspecificas as $a): ?>
                                                <option value="<?php echo $a['idAreaE']; ?>"><?php echo $a['nomAreaE']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-idAreaE"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ===== PASO 2: GRUPOS Y CAPACIDAD ===== -->
                        <div class="wizard-step-content" id="step-2">
                            <div class="p-4">
                                <h6 class="step-title"><i class="bi bi-people me-2 text-primary"></i>Grupos y Capacidad</h6>
                                
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Grupos Etarios <span class="text-danger">*</span></label>
                                        <div class="grupos-etarios-container">
                                            <?php foreach ($gruposEtarios as $ge): ?>
                                                <label class="grupo-etario-chip">
                                                    <input type="checkbox" name="gruposEtarios[]" value="<?php echo $ge['idGrupoEtareo']; ?>">
                                                    <span class="chip-content">
                                                        <i class="bi bi-check-circle-fill chip-check"></i>
                                                        <span class="chip-nombre"><?php echo $ge['nomGrupoEtareo']; ?></span>
                                                        <span class="chip-edad"><?php echo $ge['edadMin']; ?>-<?php echo $ge['edadMax']; ?> años</span>
                                                    </span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="invalid-feedback d-block" id="error-gruposEtarios"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Grupo Étnico <span class="text-danger">*</span></label>
                                        <select name="idGrupoEtnio" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($gruposEtnicos as $g): ?>
                                                <option value="<?php echo $g['idGrupoEtnio']; ?>"><?php echo $g['nomGrupoEtnio']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-idGrupoEtnio"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Unidad de Medida <span class="text-danger">*</span></label>
                                        <select name="idUnidadMedida" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($unidadesMedida as $u): ?>
                                                <option value="<?php echo $u['idUnidadMedida']; ?>"><?php echo $u['nomUnidadMedida']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-idUnidadMedida"></div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Cantidad de Personas a Atender <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-person-plus"></i></span>
                                            <input type="number" name="cantPersoAtender" class="form-control" min="1" max="99999" placeholder="Ej: 25">
                                        </div>
                                        <div class="invalid-feedback" id="error-cantPersoAtender"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ===== PASO 3: FECHAS Y SESIONES ===== -->
                        <div class="wizard-step-content" id="step-3">
                            <div class="p-4">
                                <h6 class="step-title"><i class="bi bi-calendar3 me-2 text-primary"></i>Fechas y Sesiones</h6>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Fecha de Inicio <span class="text-danger">*</span></label>
                                        <input type="date" name="fechainicioActividad" class="form-control" id="fechaInicio">
                                        <small class="text-muted">Las fechas en <span class="badge bg-danger">rojo</span> ya están ocupadas</small>
                                        <div class="invalid-feedback" id="error-fechainicioActividad"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Fecha de Fin <span class="text-danger">*</span></label>
                                        <input type="date" name="fechafinActividad" class="form-control" id="fechaFin">
                                        <div class="invalid-feedback" id="error-fechafinActividad"></div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Cantidad de Sesiones Planificadas <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-list-ol"></i></span>
                                            <input type="number" name="cantSesionesPlanificada" class="form-control" min="1" max="100" value="1" id="cantSesiones">
                                            <button type="button" class="btn btn-outline-primary" id="btnGenerarFechas"><i class="bi bi-magic"></i> Generar Fechas</button>
                                        </div>
                                        <div class="invalid-feedback" id="error-cantSesionesPlanificada"></div>
                                    </div>

                                    <!-- Calendario visual -->
                                    <div class="col-md-12">
                                        <div class="calendar-widget" id="calendar-widget">
                                            <div class="calendar-header">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="cal-prev"><i class="bi bi-chevron-left"></i></button>
                                                <span class="calendar-month-year" id="cal-month-year"></span>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="cal-next"><i class="bi bi-chevron-right"></i></button>
                                            </div>
                                            <div class="calendar-weekdays">
                                                <span>Dom</span><span>Lun</span><span>Mar</span><span>Mie</span>
                                                <span>Jue</span><span>Vie</span><span>Sab</span>
                                            </div>
                                            <div class="calendar-days" id="cal-days"></div>
                                            <div class="calendar-legend">
                                                <span class="legend-item"><span class="legend-dot occupied"></span> Ocupado</span>
                                                <span class="legend-item"><span class="legend-dot selected"></span> Seleccionado</span>
                                                <span class="legend-item"><span class="legend-dot today"></span> Hoy</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Fechas de sesiones generadas -->
                                    <div class="col-md-12" id="sesiones-container" style="display:none;">
                                        <label class="form-label fw-semibold">Fechas de Sesiones Generadas</label>
                                        <div class="sesiones-list" id="sesiones-list"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ===== PASO 4: LUGAR Y ESPACIO ===== -->
                        <div class="wizard-step-content" id="step-4">
                            <div class="p-4">
                                <h6 class="step-title"><i class="bi bi-geo-alt me-2 text-primary"></i>Lugar y Espacio</h6>
                                
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Lugar de Actividad <span class="text-danger">*</span></label>
                                        <select name="idLugarActividad" class="form-select" id="selectLugar">
                                            <option value="">Seleccione un lugar...</option>
                                            <?php foreach ($lugares as $l): ?>
                                                <option value="<?php echo $l['idLugarActividad']; ?>" data-sede="<?php echo $l['esSede'] ? '1' : '0'; ?>">
                                                    <?php echo $l['nomLugarActividad']; ?> <?php echo $l['esSede'] ? '(Sede)' : ''; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-idLugarActividad"></div>
                                    </div>

                                    <div class="col-md-12" id="espacio-container" style="display:none;">
                                        <label class="form-label fw-semibold">Espacio a Utilizar <span class="text-danger">*</span></label>
                                        <select name="idEspacioUtilizar" class="form-select" id="selectEspacio">
                                            <option value="">Seleccione un espacio...</option>
                                            <?php foreach ($espacios as $esp): ?>
                                                <option value="<?php echo $esp['idEspacioUtilizar']; ?>" data-capacidad="<?php echo $esp['capacidad']; ?>">
                                                    <?php echo $esp['nombreEspacioUtilizar']; ?> (Cap: <?php echo $esp['capacidad']; ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-idEspacioUtilizar"></div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Horario <span class="text-danger">*</span></label>
                                        <div class="horarios-container" id="horarios-container">
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle me-2"></i>Seleccione primero las fechas y el lugar para ver los horarios disponibles.
                                            </div>
                                        </div>
                                        <!-- Select oculto con horarios para que el JS pueda leer las opciones -->
<select name="idHorario_select" id="horariosSelect" style="display:none;">
    <option value="">Seleccione...</option>
    <?php foreach ($horarios as $h): ?>
        <option value="<?php echo $h['idHorario']; ?>"><?php echo $h['nomHorario']; ?></option>
    <?php endforeach; ?>
</select>
<input type="hidden" name="idHorario" id="idHorarioInput">
                                        <div class="invalid-feedback" id="error-idHorario"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ===== PASO 5: RESPONSABLES ===== -->
                        <div class="wizard-step-content" id="step-5">
                            <div class="p-4">
                                <h6 class="step-title"><i class="bi bi-person-badge me-2 text-primary"></i>Responsables</h6>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Empleado Responsable <span class="text-danger">*</span></label>
                                        <select name="idEmpleado" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($empleados as $emp): ?>
                                                <option value="<?php echo $emp['idEmpleado']; ?>">
                                                    <?php echo $emp['nombreCompleto']; ?> (<?php echo $emp['cedulaEmpleado']; ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-idEmpleado"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Docente <span class="text-danger">*</span></label>
                                        <select name="idDocente" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($docentes as $doc): ?>
                                                <option value="<?php echo $doc['idDocente']; ?>">
                                                    <?php echo $doc['nombreCompleto']; ?> (<?php echo $doc['cedDocente']; ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-idDocente"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Estatus <span class="text-danger">*</span></label>
                                        <select name="idEstatus" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($estatus as $es): ?>
                                                <option value="<?php echo $es['idEstatus']; ?>"><?php echo $es['nomEstatus']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-idEstatus"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Tipo de Entrega (para sesiones)</label>
                                        <select name="idTipEntrega" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($tiposEntrega as $te): ?>
                                                <option value="<?php echo $te['idTipEntrega']; ?>"><?php echo $te['nomTipEntrega']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Observaciones</label>
                                        <textarea name="observacion" class="form-control" rows="3" placeholder="Observaciones adicionales..." maxlength="500"></textarea>
                                        <div class="invalid-feedback" id="error-observacion"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ===== PASO 6: RESUMEN ===== -->
                        <div class="wizard-step-content" id="step-6">
                            <div class="p-4">
                                <h6 class="step-title"><i class="bi bi-check-circle me-2 text-primary"></i>Resumen y Confirmación</h6>
                                <p class="text-muted small mb-3">Revise toda la información antes de guardar. Puede retroceder para corregir.</p>
                                
                                <div class="resumen-content" id="resumen-content">
                                    <!-- Se llena dinámicamente con JS -->
                                </div>
                            </div>
                        </div>

                        <!-- Botones de navegación -->
                        <div class="wizard-footer p-4 border-top">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" id="btnAnterior" disabled>
                                    <i class="bi bi-arrow-left me-1"></i> Anterior
                                </button>
                                <button type="button" class="btn btn-primary" id="btnSiguiente">
                                    Siguiente <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                                <button type="submit" class="btn btn-success" id="btnGuardar" style="display:none;">
                                    <i class="bi bi-check-lg me-1"></i> Guardar Actividad
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ===== PANEL DERECHO: RESUMEN/CARRITO ===== -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px; z-index: 100;">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0"><i class="bi bi-cart-check me-2"></i> Resumen de la Actividad</h6>
                </div>
                <div class="card-body p-0">
                    <div class="resumen-carrito" id="resumen-carrito">
                        <!-- Estado vacío -->
                        <div class="carrito-vacio text-center py-5">
                            <i class="bi bi-clipboard-data display-4 text-muted"></i>
                            <p class="text-muted mt-2 mb-0">Complete los pasos para ver el resumen</p>
                        </div>

                        <!-- Secciones del resumen -->
                        <div class="carrito-seccion" id="carrito-paso1" style="display:none;">
                            <div class="carrito-header"><i class="bi bi-info-circle"></i> Información Básica</div>
                            <div class="carrito-body" id="carrito-body-paso1"></div>
                        </div>

                        <div class="carrito-seccion" id="carrito-paso2" style="display:none;">
                            <div class="carrito-header"><i class="bi bi-people"></i> Grupos y Capacidad</div>
                            <div class="carrito-body" id="carrito-body-paso2"></div>
                        </div>

                        <div class="carrito-seccion" id="carrito-paso3" style="display:none;">
                            <div class="carrito-header"><i class="bi bi-calendar3"></i> Fechas y Sesiones</div>
                            <div class="carrito-body" id="carrito-body-paso3"></div>
                        </div>

                        <div class="carrito-seccion" id="carrito-paso4" style="display:none;">
                            <div class="carrito-header"><i class="bi bi-geo-alt"></i> Lugar y Espacio</div>
                            <div class="carrito-body" id="carrito-body-paso4"></div>
                        </div>

                        <div class="carrito-seccion" id="carrito-paso5" style="display:none;">
                            <div class="carrito-header"><i class="bi bi-person-badge"></i> Responsables</div>
                            <div class="carrito-body" id="carrito-body-paso5"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Progreso</span>
                        <span class="badge bg-primary" id="carrito-progreso">0%</span>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-primary" id="carrito-progress-bar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.fechasOcupadas = <?php echo $fechasOcupadasJson; ?>;
</script>
<script src="view/public/js/actividad.js"></script>