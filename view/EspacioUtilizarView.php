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
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i> Gestión de Espacios a Utilizar</h5>
                    <button class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalEspacio">
                        <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Espacio
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre del Espacio</th>
                                    <th>Descripción</th>
                                    <th>Capacidad</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($espacios)): ?>
                                    <?php foreach ($espacios as $e): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary px-2 py-1">
                                                    <?php echo $e['idEspacioUtilizar']; ?>
                                                </span></td>
                                            <td class="fw-bold text-dark">
                                                <?php echo $e['nombreEspacioUtilizar']; ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo $e['descEspacio'] ?? 'N/A'; ?>
                                            </td>
                                            <td class="text-muted small">
                                                <span class="badge bg-info text-dark">
                                                    <i class="bi bi-people-fill me-1"></i><?php echo $e['capacidad']; ?> personas
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-warning btn-sm border-0 btn-editar"
                                                        data-id="<?php echo $e['idEspacioUtilizar']; ?>">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-sm border-0 btn-eliminar"
                                                        data-id="<?php echo $e['idEspacioUtilizar']; ?>">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="bi bi-info-circle me-1"></i> No hay espacios registrados actualmente.
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
<div class="modal fade" id="modalEspacio" tabindex="-1" aria-labelledby="modalEspacioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="formNuevoEspacio" novalidate>
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modalEspacioLabel"><i class="bi bi-file-earmark-plus me-2"></i>Registrar
                        Nuevo Espacio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Nombre del Espacio</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-building"></i></span>
                                <input type="text" name="nombreEspacioUtilizar" class="form-control" placeholder="Ej: Salón de Actos">
                            </div>
                            <div class="invalid-feedback" id="error-nombreEspacioUtilizar"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Capacidad</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-people"></i></span>
                                <input type="number" name="capacidad" class="form-control" placeholder="Ej: 50" min="1" max="9999">
                            </div>
                            <div class="invalid-feedback" id="error-capacidad"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold text-secondary">Descripción (opcional)</label>
                            <textarea name="descEspacio" class="form-control" rows="3" placeholder="Breve descripción del espacio..."></textarea>
                            <div class="invalid-feedback" id="error-descEspacio"></div>
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
<div class="modal fade" id="modalEditarEspacio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="formEditarEspacio" novalidate>
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Espacio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="idEspacioUtilizarEdit" id="idEspacioUtilizarEdit">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombre del Espacio</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-building"></i></span>
                                <input type="text" name="nombreEspacioUtilizarEdit" id="nombreEspacioUtilizarEdit" class="form-control">
                            </div>
                            <div class="invalid-feedback" id="error-nombreEspacioUtilizarEdit"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Capacidad</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-people"></i></span>
                                <input type="number" name="capacidadEdit" id="capacidadEdit" class="form-control" min="1" max="9999">
                            </div>
                            <div class="invalid-feedback" id="error-capacidadEdit"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Descripción (opcional)</label>
                            <textarea name="descEspacioEdit" id="descEspacioEdit" class="form-control" rows="3"></textarea>
                            <div class="invalid-feedback" id="error-descEspacioEdit"></div>
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

<script src="view/public/js/espacioUtilizar.js"></script>