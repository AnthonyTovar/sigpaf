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
                    <h5 class="mb-0"><i class="bi bi-geo-alt-fill me-2"></i> Gestión de Lugares de Actividad</h5>
                    <button class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalLugar">
                        <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Lugar
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre del Lugar</th>
                                    <th>Descripción</th>
                                    <th>Dirección</th>
                                    <th>Es Sede</th>
                                    <th>Parroquia</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($lugares)): ?>
                                    <?php foreach ($lugares as $l): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary px-2 py-1">
                                                    <?php echo $l['idLugarActividad']; ?>
                                                </span></td>
                                            <td class="fw-bold text-dark">
                                                <?php echo $l['nomLugarActividad']; ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo $l['desLugarActividad'] ?? 'N/A'; ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo $l['direccion']; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($l['esSede']): ?>
                                                    <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Sí</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><i class="bi bi-x-circle-fill me-1"></i>No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo ($l['nombreParroquia'] ?? 'N/A') . ', ' . ($l['nombreMunicipio'] ?? '') . ', ' . ($l['nombreEstado'] ?? ''); ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-warning btn-sm border-0 btn-editar"
                                                        data-id="<?php echo $l['idLugarActividad']; ?>">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-sm border-0 btn-eliminar"
                                                        data-id="<?php echo $l['idLugarActividad']; ?>">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="bi bi-info-circle me-1"></i> No hay lugares de actividad registrados actualmente.
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

<!-- MODAL REGISTRAR -->
<div class="modal fade" id="modalLugar" tabindex="-1" aria-labelledby="modalLugarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="formNuevoLugar" novalidate>
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modalLugarLabel"><i class="bi bi-file-earmark-plus me-2"></i>Registrar
                        Nuevo Lugar de Actividad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Nombre del Lugar</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" name="nomLugarActividad" class="form-control" placeholder="Ej: Casa de la Cultura">
                            </div>
                            <div class="invalid-feedback" id="error-nomLugarActividad"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Parroquia</label>
                            <select name="idParroquia" class="form-select">
                                <option value="" selected disabled>Seleccione una parroquia...</option>
                                <?php if (!empty($parroquias)): ?>
                                    <?php foreach ($parroquias as $p): ?>
                                        <option value="<?php echo $p['idParroquia']; ?>">
                                            <?php echo $p['nombreParroquia'] . ' - ' . $p['nombreMunicipio'] . ', ' . $p['nombreEstado']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback" id="error-idParroquia"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold text-secondary">Dirección</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-signpost"></i></span>
                                <input type="text" name="direccion" class="form-control" placeholder="Ej: Av. Principal, Edificio Central, Piso 2">
                            </div>
                            <div class="invalid-feedback" id="error-direccion"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-semibold text-secondary">Descripción (opcional)</label>
                            <textarea name="desLugarActividad" class="form-control" rows="3" placeholder="Breve descripción del lugar..."></textarea>
                            <div class="invalid-feedback" id="error-desLugarActividad"></div>
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-center">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="esSede" id="esSede" value="1">
                                <label class="form-check-label fw-semibold text-secondary" for="esSede">
                                    <i class="bi bi-building me-1"></i> Es Sede Principal
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary px-4">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDITAR -->
<div class="modal fade" id="modalEditarLugar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="formEditarLugar" novalidate>
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Lugar de Actividad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="idLugarActividadEdit" id="idLugarActividadEdit">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombre del Lugar</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" name="nomLugarActividadEdit" id="nomLugarActividadEdit" class="form-control">
                            </div>
                            <div class="invalid-feedback" id="error-nomLugarActividadEdit"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Parroquia</label>
                            <select name="idParroquiaEdit" id="idParroquiaEdit" class="form-select">
                                <option value="" selected disabled>Seleccione una parroquia...</option>
                                <?php if (!empty($parroquias)): ?>
                                    <?php foreach ($parroquias as $p): ?>
                                        <option value="<?php echo $p['idParroquia']; ?>">
                                            <?php echo $p['nombreParroquia'] . ' - ' . $p['nombreMunicipio'] . ', ' . $p['nombreEstado']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback" id="error-idParroquiaEdit"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Dirección</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-signpost"></i></span>
                                <input type="text" name="direccionEdit" id="direccionEdit" class="form-control">
                            </div>
                            <div class="invalid-feedback" id="error-direccionEdit"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Descripción (opcional)</label>
                            <textarea name="desLugarActividadEdit" id="desLugarActividadEdit" class="form-control" rows="3"></textarea>
                            <div class="invalid-feedback" id="error-desLugarActividadEdit"></div>
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-center">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="esSedeEdit" id="esSedeEdit" value="1">
                                <label class="form-check-label fw-bold" for="esSedeEdit">
                                    <i class="bi bi-building me-1"></i> Es Sede Principal
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="view/public/js/lugarActividad.js"></script>