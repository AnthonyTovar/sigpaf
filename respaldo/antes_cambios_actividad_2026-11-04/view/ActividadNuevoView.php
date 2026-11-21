<?php
// Formulario de actividades



$fechasOcupadasJson = json_encode(array_map(function($f) {
    return [
        'inicio' => $f['fechainicioActividad'],
        'fin' => $f['fechafinActividad']
    ];
}, $fechasOcupadas));


$esEdicion = (isset($modo) && $modo === 'editar');
$actividadData = $esEdicion && isset($actividad) ? $actividad : [];
$lugaresSeleccionados = $esEdicion && isset($lugaresActividad) ? $lugaresActividad : [];
$idsLugaresSeleccionados = array_column($lugaresSeleccionados, 'idLugarActividad');
$espaciosSeleccionados = [];
foreach ($lugaresSeleccionados as $lugarSeleccionado) {
    $espaciosSeleccionados[$lugarSeleccionado['idLugarActividad']] = $lugarSeleccionado['idEspacioUtilizar'];
}
$horariosSeleccionados = $esEdicion && isset($horariosActividad) ? $horariosActividad : [];
$empleadosSeleccionados = $esEdicion && isset($empleadosActividad) ? $empleadosActividad : [];
$docentesSeleccionados = $esEdicion && isset($docentesActividad) ? $docentesActividad : [];
$gruposSeleccionados = $esEdicion && isset($gruposEtariosActividad) ? $gruposEtariosActividad : [];
$fechasSesionesData = $esEdicion && isset($fechasSesiones) ? $fechasSesiones : [];
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
        <!-- Panel izquierdo: pasos -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 activity-wizard-card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-event me-2"></i>
                        <?php echo $esEdicion ? 'Editar Actividad' : 'Nueva Actividad'; ?>
                    </h5>
                    <span class="badge bg-light text-primary" id="paso-indicador">Paso 1 de 6</span>
                </div>
                <div class="card-body p-0">
                    
                    <div class="wizard-progress">
                        <div class="wizard-progress-bar" id="wizard-progress-bar" style="width: 16.66%"></div>
                    </div>

                    
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
                        
                        <input type="hidden" name="idEstatus" value="<?php echo $esEdicion ? htmlspecialchars($actividadData['idEstatus'] ?? 'ES0001') : 'ES0001'; ?>">
                        <?php if ($esEdicion): ?>
                            <input type="hidden" name="idActividad" id="idActividadEdit" value="<?php echo htmlspecialchars($actividadData['idActividad'] ?? ''); ?>">
                        <?php endif; ?>

                        <!-- Paso 1: información básica -->
                        <div class="wizard-step-content active" id="step-1">
                            <div class="p-4">
                                <h6 class="step-title"><i class="bi bi-info-circle me-2 text-primary"></i>Información Básica</h6>

                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Nombre de la Actividad <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-pencil-square"></i></span>
                                            <input type="text" name="nombreActividad" class="form-control" 
                                                placeholder="Ej: Taller de Robótica para Jóvenes" maxlength="250"
                                                value="<?php echo $esEdicion ? htmlspecialchars($actividadData['nombreActividad'] ?? '') : ''; ?>">
                                        </div>
                                        <div class="invalid-feedback" id="error-nombreActividad"></div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Objetivo <span class="text-danger">*</span></label>
                                        <textarea name="objetivoActividad" class="form-control" rows="2" 
                                            placeholder="Objetivo principal de la actividad..." maxlength="300"><?php echo $esEdicion ? htmlspecialchars($actividadData['objetivoActividad'] ?? '') : ''; ?></textarea>
                                        <div class="invalid-feedback" id="error-objetivoActividad"></div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Descripción <span class="text-danger">*</span></label>
                                        <textarea name="descActividad" class="form-control" rows="2" 
                                            placeholder="Breve descripción de la actividad..." maxlength="300"><?php echo $esEdicion ? htmlspecialchars($actividadData['descActividad'] ?? '') : ''; ?></textarea>
                                        <div class="invalid-feedback" id="error-descActividad"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Estrategia de Desarrollo <span class="text-danger">*</span></label>
                                        <select name="idEstDesarrollo" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($estrategias as $e): ?>
                                                <option value="<?php echo $e['idEstDesarrollo']; ?>" 
                                                    <?php echo ($esEdicion && ($actividadData['idEstDesarrollo'] ?? '') === $e['idEstDesarrollo']) ? 'selected' : ''; ?>>
                                                    <?php echo $e['nomEstDesarrollo']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-idEstDesarrollo"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Tipo de Actividad <span class="text-danger">*</span></label>
                                        <select name="idTipoActividad" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($tiposActividad as $t): ?>
                                                <option value="<?php echo $t['idTipoActividad']; ?>"
                                                    <?php echo ($esEdicion && ($actividadData['idTipoActividad'] ?? '') === $t['idTipoActividad']) ? 'selected' : ''; ?>>
                                                    <?php echo $t['nomTipoActividad']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-idTipoActividad"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Vértice <span class="text-danger">*</span></label>
                                        <select name="idVertice" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($vertices as $v): ?>
                                                <option value="<?php echo $v['idVertice']; ?>"
                                                    <?php echo ($esEdicion && ($actividadData['idVertice'] ?? '') === $v['idVertice']) ? 'selected' : ''; ?>>
                                                    <?php echo $v['nombreVertice']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-idVertice"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Área Específica <span class="text-danger">*</span></label>
                                        <select name="idAreaE" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($areasEspecificas as $a): ?>
                                                <option value="<?php echo $a['idAreaE']; ?>"
                                                    <?php echo ($esEdicion && ($actividadData['idAreaE'] ?? '') === $a['idAreaE']) ? 'selected' : ''; ?>>
                                                    <?php echo $a['nomAreaE']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-idAreaE"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 2: grupos y clasificación -->
                        <div class="wizard-step-content" id="step-2">
                            <div class="p-4">
                                <h6 class="step-title"><i class="bi bi-people me-2 text-primary"></i>Grupos y Clasificación</h6>

                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Grupos Etarios <span class="text-danger">*</span></label>
                                        <div class="grupos-etarios-container">
                                            <?php foreach ($gruposEtarios as $ge): ?>
                                                <label class="grupo-etario-chip">
                                                    <input type="checkbox" name="gruposEtarios[]" value="<?php echo $ge['idGrupoEtareo']; ?>"
                                                        <?php echo ($esEdicion && in_array($ge['idGrupoEtareo'], $gruposSeleccionados)) ? 'checked' : ''; ?>>
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
                                                <option value="<?php echo $g['idGrupoEtnio']; ?>"
                                                    <?php echo ($esEdicion && ($actividadData['idGrupoEtnio'] ?? '') === $g['idGrupoEtnio']) ? 'selected' : ''; ?>>
                                                    <?php echo $g['nomGrupoEtnio']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-idGrupoEtnio"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Unidad de Medida <span class="text-danger">*</span></label>
                                        <select name="idUnidadMedida" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($unidadesMedida as $u): ?>
                                                <option value="<?php echo $u['idUnidadMedida']; ?>"
                                                    <?php echo ($esEdicion && ($actividadData['idUnidadMedida'] ?? '') === $u['idUnidadMedida']) ? 'selected' : ''; ?>>
                                                    <?php echo $u['nomUnidadMedida']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-idUnidadMedida"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 3: fechas y sesiones -->
                        <div class="wizard-step-content" id="step-3">
                            <div class="p-4">
                                <h6 class="step-title"><i class="bi bi-calendar3 me-2 text-primary"></i>Fechas y Sesiones</h6>
                                <p class="text-muted small">Indique el rango y la cantidad de sesiones. El sistema distribuirá las sesiones únicamente en días disponibles.</p>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Fecha de Inicio <span class="text-danger">*</span></label>
                                        <input type="date" name="fechainicioActividad" class="form-control" id="fechaInicio"
                                            value="<?php echo $esEdicion ? htmlspecialchars($actividadData['fechainicioActividad'] ?? '') : ''; ?>">
                                        <div class="invalid-feedback" id="error-fechainicioActividad"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Fecha de Fin <span class="text-danger">*</span></label>
                                        <input type="date" name="fechafinActividad" class="form-control" id="fechaFin"
                                            value="<?php echo $esEdicion ? htmlspecialchars($actividadData['fechafinActividad'] ?? '') : ''; ?>">
                                        <div class="invalid-feedback" id="error-fechafinActividad"></div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Cantidad de Sesiones Planificadas <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-list-ol"></i></span>
                                            <input type="number" name="cantSesionesPlanificada" class="form-control"
                                                min="1" max="100" value="<?php echo $esEdicion ? (int) ($actividadData['cantSesionesPlanificada'] ?? 1) : 1; ?>"
                                                id="cantSesiones">
                                        </div>
                                        <small class="text-muted d-block mt-2" id="info-dias-rango">Seleccione las fechas para ver los días disponibles.</small>
                                        <div class="invalid-feedback" id="error-cantSesionesPlanificada"></div>
                                    </div>

                                    <div class="col-md-12" id="sesiones-container" style="display:none;">
                                        <label class="form-label fw-semibold">Sesiones planificadas</label>
                                        <div class="sesiones-list" id="sesiones-list"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 4: lugares, horarios y capacidad -->
                        <div class="wizard-step-content" id="step-4">
                            <div class="p-4">
                                <h6 class="step-title"><i class="bi bi-geo-alt me-2 text-primary"></i>Lugares, Horarios y Capacidad</h6>

                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-semibold mb-0">Lugares de la Actividad <span class="text-danger">*</span></label>
                                            <button type="button" class="btn btn-sm btn-outline-success" id="btnNuevoLugar" data-bs-toggle="modal" data-bs-target="#modalNuevoLugar">
                                                <i class="bi bi-plus-circle me-1"></i> Nuevo Lugar
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mb-2">Seleccione uno o varios lugares desde el desplegable.</small>
                                        <div class="selector-multiple" data-entidad="lugar" data-placeholder="Seleccione los lugares">
                                            <button type="button" class="selector-toggle" aria-expanded="false">
                                                <span class="selector-toggle-texto"><i class="bi bi-geo-alt"></i><span data-resumen>Seleccione los lugares</span></span>
                                                <i class="bi bi-chevron-down selector-flecha"></i>
                                            </button>
                                            <div class="selector-panel">
                                                <div class="selector-busqueda">
                                                    <i class="bi bi-search"></i>
                                                    <input type="search" class="form-control form-control-sm" placeholder="Buscar lugar..." data-buscar>
                                                </div>
                                                <div class="selector-opciones" id="lugares-container">
                                                    <?php foreach ($lugares as $l): ?>
                                                        <label class="selector-opcion">
                                                            <input type="checkbox" name="idLugarActividad[]" value="<?php echo htmlspecialchars($l['idLugarActividad']); ?>"
                                                                data-sede="<?php echo $l['esSede'] ? '1' : '0'; ?>"
                                                                data-nombre="<?php echo htmlspecialchars($l['nomLugarActividad']); ?>"
                                                                <?php echo in_array($l['idLugarActividad'], $idsLugaresSeleccionados) ? 'checked' : ''; ?>>
                                                            <span class="selector-opcion-icono"><i class="bi bi-geo-alt"></i></span>
                                                            <span class="selector-opcion-texto">
                                                                <strong><?php echo htmlspecialchars($l['nomLugarActividad']); ?></strong>
                                                                <small><?php echo $l['esSede'] ? 'Sede institucional' : 'Lugar externo'; ?></small>
                                                            </span>
                                                            <i class="bi bi-check-lg selector-check"></i>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <div class="selector-seleccionados" data-seleccionados></div>
                                        </div>
                                        <div class="invalid-feedback" id="error-idLugarActividad"></div>
                                    </div>

                                    <div class="col-md-12" id="espacio-container">
                                        <label class="form-label fw-semibold">Espacios a Utilizar</label>
                                        <small class="text-muted d-block mb-2">Seleccione un espacio para cada sede elegida.</small>
                                        <div id="espacios-por-lugar">
                                            <?php foreach ($lugares as $l): ?>
                                                <div class="espacio-lugar mb-2" data-lugar="<?php echo htmlspecialchars($l['idLugarActividad']); ?>"
                                                    data-sede="<?php echo $l['esSede'] ? '1' : '0'; ?>"
                                                    style="display: <?php echo in_array($l['idLugarActividad'], $idsLugaresSeleccionados) && $l['esSede'] ? 'block' : 'none'; ?>;">
                                                    <label class="form-label small mb-1"><?php echo htmlspecialchars($l['nomLugarActividad']); ?> <span class="text-danger">*</span></label>
                                                    <select name="idEspacioUtilizar[<?php echo htmlspecialchars($l['idLugarActividad']); ?>]" class="form-select espacio-select"
                                                        data-lugar="<?php echo htmlspecialchars($l['idLugarActividad']); ?>"
                                                        <?php echo in_array($l['idLugarActividad'], $idsLugaresSeleccionados) && $l['esSede'] ? '' : 'disabled'; ?>>
                                                        <option value="">Seleccione un espacio...</option>
                                                        <?php foreach ($espacios as $esp): ?>
                                                            <option value="<?php echo htmlspecialchars($esp['idEspacioUtilizar']); ?>"
                                                                data-capacidad="<?php echo (int) $esp['capacidad']; ?>"
                                                                <?php echo ($espaciosSeleccionados[$l['idLugarActividad']] ?? '') === $esp['idEspacioUtilizar'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($esp['nombreEspacioUtilizar']); ?> (Capacidad: <?php echo (int) $esp['capacidad']; ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="invalid-feedback" id="error-idEspacioUtilizar"></div>
                                        <div class="form-text text-muted" id="capacidad-info" style="display:none;">
                                            <i class="bi bi-info-circle me-1"></i> Capacidad del espacio más restrictivo: <strong id="capacidad-maxima">0</strong> personas
                                        </div>
                                    </div>

                                    <div class="col-md-12" id="cant-personas-container">
                                        <label class="form-label fw-semibold">Cantidad de Personas a Atender <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-person-plus"></i></span>
                                            <input type="number" name="cantPersoAtender" class="form-control"
                                                min="1" max="99999" placeholder="Ej: 25" id="cantPersoAtender"
                                                value="<?php echo $esEdicion ? (int) ($actividadData['cantPersoAtender'] ?? 0) : ''; ?>">
                                        </div>
                                        <div class="invalid-feedback" id="error-cantPersoAtender"></div>
                                        <div class="form-text text-danger" id="error-capacidad" style="display:none;"></div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Horarios <span class="text-danger">*</span></label>
                                        <small class="text-muted d-block mb-2">Puede seleccionar uno o varios horarios. Los ocupados se mostrarán en rojo.</small>
                                        <div class="horarios-container" id="horarios-container">
                                            <?php foreach ($horarios as $h): ?>
                                                <label class="horario-chip">
                                                    <input type="checkbox" name="idHorario[]" value="<?php echo htmlspecialchars($h['idHorario']); ?>"
                                                        <?php echo in_array($h['idHorario'], $horariosSeleccionados) ? 'checked' : ''; ?>>
                                                    <span class="horario-content"><i class="bi bi-clock"></i><?php echo htmlspecialchars($h['nomHorario']); ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="invalid-feedback" id="error-idHorario"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 5: responsables -->
                        <div class="wizard-step-content" id="step-5">
                            <div class="p-4">
                                <h6 class="step-title"><i class="bi bi-person-badge me-2 text-primary"></i>Responsables</h6>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Empleados Responsables <span class="text-danger">*</span></label>
                                        <?php $idEmpleadoLogueado = $_SESSION['idEmpleado'] ?? ($_SESSION['empleado_id'] ?? ''); ?>
                                        <small class="text-muted d-block mb-2">Pueden participar en varias actividades simultáneas.</small>
                                        <div class="selector-multiple" data-entidad="responsable" data-placeholder="Seleccione los responsables">
                                            <button type="button" class="selector-toggle" aria-expanded="false">
                                                <span class="selector-toggle-texto"><i class="bi bi-people"></i><span data-resumen>Seleccione los responsables</span></span>
                                                <i class="bi bi-chevron-down selector-flecha"></i>
                                            </button>
                                            <div class="selector-panel">
                                                <div class="selector-busqueda">
                                                    <i class="bi bi-search"></i>
                                                    <input type="search" class="form-control form-control-sm" placeholder="Buscar por nombre o cédula..." data-buscar>
                                                </div>
                                                <div class="selector-opciones" id="empleados-container">
                                                    <?php foreach ($empleados as $emp): ?>
                                                        <label class="selector-opcion">
                                                            <input type="checkbox" name="idEmpleado[]" value="<?php echo htmlspecialchars($emp['idEmpleado']); ?>"
                                                                data-nombre="<?php echo htmlspecialchars($emp['nombreCompleto']); ?>"
                                                                <?php echo (in_array($emp['idEmpleado'], $empleadosSeleccionados) || (!$esEdicion && $idEmpleadoLogueado === $emp['idEmpleado'])) ? 'checked' : ''; ?>>
                                                            <span class="selector-opcion-icono"><i class="bi bi-person"></i></span>
                                                            <span class="selector-opcion-texto">
                                                                <strong><?php echo htmlspecialchars($emp['nombreCompleto']); ?></strong>
                                                                <small>C.I. <?php echo htmlspecialchars($emp['cedulaEmpleado']); ?></small>
                                                            </span>
                                                            <i class="bi bi-check-lg selector-check"></i>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <div class="selector-seleccionados" data-seleccionados></div>
                                        </div>
                                        <div class="invalid-feedback" id="error-idEmpleado"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-semibold mb-0">Docentes <span class="text-danger">*</span></label>
                                            <button type="button" class="btn btn-sm btn-outline-success" id="btnNuevoDocente" data-bs-toggle="modal" data-bs-target="#modalNuevoDocente">
                                                <i class="bi bi-plus-circle me-1"></i> Nuevo Docente
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mb-2">Se validará su disponibilidad por fecha y horario.</small>
                                        <div class="selector-multiple" data-entidad="docente" data-placeholder="Seleccione los docentes">
                                            <button type="button" class="selector-toggle" aria-expanded="false">
                                                <span class="selector-toggle-texto"><i class="bi bi-person-badge"></i><span data-resumen>Seleccione los docentes</span></span>
                                                <i class="bi bi-chevron-down selector-flecha"></i>
                                            </button>
                                            <div class="selector-panel">
                                                <div class="selector-busqueda">
                                                    <i class="bi bi-search"></i>
                                                    <input type="search" class="form-control form-control-sm" placeholder="Buscar por nombre o cédula..." data-buscar>
                                                </div>
                                                <div class="selector-opciones" id="docentes-container">
                                                    <?php foreach ($docentes as $doc): ?>
                                                        <label class="selector-opcion">
                                                            <input type="checkbox" name="idDocente[]" value="<?php echo htmlspecialchars($doc['idDocente']); ?>"
                                                                data-nombre="<?php echo htmlspecialchars($doc['nombreCompleto']); ?>"
                                                                <?php echo in_array($doc['idDocente'], $docentesSeleccionados) ? 'checked' : ''; ?>>
                                                            <span class="selector-opcion-icono"><i class="bi bi-person-badge"></i></span>
                                                            <span class="selector-opcion-texto">
                                                                <strong><?php echo htmlspecialchars($doc['nombreCompleto']); ?></strong>
                                                                <small>C.I. <?php echo htmlspecialchars($doc['cedDocente']); ?></small>
                                                            </span>
                                                            <i class="bi bi-check-lg selector-check"></i>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <div class="selector-seleccionados" data-seleccionados></div>
                                        </div>
                                        <div class="invalid-feedback" id="error-idDocente"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Tipo de Entrega (para sesiones)</label>
                                        <select name="idTipEntrega" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($tiposEntrega as $te): ?>
                                                <option value="<?php echo $te['idTipEntrega']; ?>"
                                                    <?php echo ($esEdicion && ($actividadData['idTipEntrega'] ?? '') === $te['idTipEntrega']) ? 'selected' : ''; ?>>
                                                    <?php echo $te['nomTipEntrega']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Observaciones</label>
                                        <textarea name="observacion" class="form-control" rows="3" placeholder="Observaciones adicionales..." maxlength="300"><?php echo $esEdicion ? htmlspecialchars($actividadData['observacion'] ?? '') : ''; ?></textarea>
                                        <div class="invalid-feedback" id="error-observacion"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 6: resumen -->
                        <div class="wizard-step-content" id="step-6">
                            <div class="p-4">
                                <h6 class="step-title"><i class="bi bi-check-circle me-2 text-primary"></i>Resumen y Confirmación</h6>
                                <p class="text-muted small mb-3">Revise toda la información antes de guardar. Puede retroceder para corregir.</p>

                                <div class="resumen-content" id="resumen-content">
                                </div>
                            </div>
                        </div>

                        
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

        <!-- Panel derecho: resumen -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px; z-index: 100;">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0"><i class="bi bi-cart-check me-2"></i> Resumen de la Actividad</h6>
                </div>
                <div class="card-body p-0">
                    <div class="resumen-carrito" id="resumen-carrito">
                        
                        <div class="carrito-vacio text-center py-5">
                            <i class="bi bi-clipboard-data display-4 text-muted"></i>
                            <p class="text-muted mt-2 mb-0">Complete los pasos para ver el resumen</p>
                        </div>

                        
                        <div class="carrito-seccion" id="carrito-paso1" style="display:none;">
                            <div class="carrito-header"><i class="bi bi-info-circle"></i> Información Básica</div>
                            <div class="carrito-body" id="carrito-body-paso1"></div>
                        </div>

                        <div class="carrito-seccion" id="carrito-paso2" style="display:none;">
                            <div class="carrito-header"><i class="bi bi-people"></i> Grupos y Clasificación</div>
                            <div class="carrito-body" id="carrito-body-paso2"></div>
                        </div>

                        <div class="carrito-seccion" id="carrito-paso3" style="display:none;">
                            <div class="carrito-header"><i class="bi bi-calendar3"></i> Fechas y Sesiones</div>
                            <div class="carrito-body" id="carrito-body-paso3"></div>
                        </div>

                        <div class="carrito-seccion" id="carrito-paso4" style="display:none;">
                            <div class="carrito-header"><i class="bi bi-geo-alt"></i> Lugar y Capacidad</div>
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

<!-- Modal: nuevo lugar de actividad -->
<div class="modal fade" id="modalNuevoLugar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-geo-alt-fill me-2"></i>Nuevo Lugar de Actividad</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formNuevoLugar" novalidate>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Nombre del Lugar <span class="text-danger">*</span></label>
                            <input type="text" name="nomLugarActividad" class="form-control" placeholder="Ej: Sede Principal Fundacite" maxlength="100" required>
                            <div class="invalid-feedback" id="error-nomLugarActividad"></div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea name="desLugarActividad" class="form-control" rows="2" placeholder="Descripción del lugar..." maxlength="255"></textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Dirección <span class="text-danger">*</span></label>
                            <textarea name="direccion" class="form-control" rows="2" placeholder="Dirección completa..." maxlength="255" required></textarea>
                            <div class="invalid-feedback" id="error-direccion"></div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="esSede" id="checkEsSede" value="1">
                                <label class="form-check-label fw-semibold" for="checkEsSede">¿Es Sede? (requiere espacio)</label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Parroquia <span class="text-danger">*</span></label>
                            <select name="idParroquia" class="form-select" required>
                                <option value="">Seleccione una parroquia...</option>
                                <?php 

                                $parroquias = [];
                                if (isset($model) && method_exists($model, 'obtenerParroquias')) {
                                    $parroquias = $model->obtenerParroquias();
                                }
                                foreach ($parroquias as $p): 
                                ?>
                                    <option value="<?php echo $p['idParroquia']; ?>">
                                        <?php echo $p['nombreParroquia']; ?> (<?php echo $p['nombreMunicipio']; ?>, <?php echo $p['nombreEstado']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" id="error-idParroquia"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarLugar">
                    <i class="bi bi-check-lg me-1"></i> Guardar Lugar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: nuevo docente -->
<div class="modal fade" id="modalNuevoDocente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-person-badge-fill me-2"></i>Nuevo Docente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formNuevoDocente" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cédula <span class="text-danger">*</span></label>
                            <input type="text" name="cedDocente" class="form-control" placeholder="V12345678" maxlength="9" required>
                            <div class="invalid-feedback" id="error-cedDocente"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nacionalidad <span class="text-danger">*</span></label>
                            <select name="nacionalidad" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <option value="Venezolano">Venezolano</option>
                                <option value="Extranjero">Extranjero</option>
                            </select>
                            <div class="invalid-feedback" id="error-nacionalidad"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombres <span class="text-danger">*</span></label>
                            <input type="text" name="nombreDocente" class="form-control" placeholder="Nombres" maxlength="50" required>
                            <div class="invalid-feedback" id="error-nombreDocente"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" name="apellidoDocente" class="form-control" placeholder="Apellidos" maxlength="50" required>
                            <div class="invalid-feedback" id="error-apellidoDocente"></div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" name="telfDocente" class="form-control" placeholder="0412-1234567" maxlength="20">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarDocente">
                    <i class="bi bi-check-lg me-1"></i> Guardar Docente
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    window.fechasOcupadas = <?php echo $fechasOcupadasJson; ?>;
    window.modoEdicion = <?php echo $esEdicion ? 'true' : 'false'; ?>;
    window.idActividadActual = <?php echo json_encode($esEdicion ? ($actividadData['idActividad'] ?? '') : ''); ?>;
    window.fechasSesionesIniciales = <?php echo json_encode(array_values($fechasSesionesData)); ?>;
</script>
<script src="view/public/js/actividad.js?v=2026-10-29.3"></script>
