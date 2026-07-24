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
                    <h5 class="mb-0"><i class="bi bi-person-workspace me-2"></i> Gestión de Docentes</h5>
                    <button class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalDocente">
                        <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Docente
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Cédula</th>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th>Teléfono</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($docentes)): ?>
                                    <?php foreach ($docentes as $d): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary px-2 py-1"><?php echo $d['idDocente']; ?></span></td>
                                            <td><?php echo $d['cedDocente']; ?></td>
                                            <td class="fw-bold text-dark"><span><?php echo $d['nombreDocente']; ?></span></td>
                                            <td class="fw-bold text-dark"><span><?php echo $d['apellidoDocente']; ?></span></td>
                                            <td class="text-muted small"><?php echo $d['telfDocente'] ?? 'N/A'; ?></td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-warning btn-sm border-0 btn-editar" data-id="<?php echo $d['idDocente']; ?>"><i class="bi bi-pencil-square"></i></button>
                                                    <button class="btn btn-outline-danger btn-sm border-0 btn-eliminar" data-id="<?php echo $d['idDocente']; ?>"><i class="bi bi-trash3-fill"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay docentes registrados actualmente.</td></tr>
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
<div class="modal fade" id="modalDocente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formNuevoDocente" novalidate>
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-plus me-2"></i>Registrar Nuevo Docente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Cédula</label>
                            <div class="input-group">
                                <select name="nacionalidad" id="nacionalidad" class="form-select"
                                    style="max-width: 60px; flex: 0 0 60px; padding-left: 8px; padding-right: 20px; text-align: center;">
                                    <option value="V" selected>V</option>
                                    <option value="E">E</option>
                                </select>
                                <span class="input-group-text bg-white"><i class="bi bi-card-text"></i></span>
                                <input type="text" name="cedDocente" id="cedDocente" class="form-control" placeholder="Ej: 12345678" maxlength="9">
                            </div>
                            <div class="invalid-feedback" id="error-nacionalidad"></div>
                            <div class="invalid-feedback" id="error-cedDocente"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Teléfono</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="telfDocente" id="telfDocente" class="form-control mask-telefono" placeholder="Ej: 0412-1234567" maxlength="12">
                            </div>
                            <div class="invalid-feedback" id="error-telfDocente"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Nombres</label>
                            <input type="text" name="nombreDocente" class="form-control" placeholder="Ej: Juan José">
                            <div class="invalid-feedback" id="error-nombreDocente"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Apellidos</label>
                            <input type="text" name="apellidoDocente" class="form-control" placeholder="Ej: Pérez García">
                            <div class="invalid-feedback" id="error-apellidoDocente"></div>
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
<div class="modal fade" id="modalEditarDocente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formEditarDocente" novalidate>
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Docente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="idDocenteEdit" id="idDocenteEdit">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Cédula</label>
                            <div class="input-group">
                                <select name="nacionalidadEdit" id="nacionalidadEdit" class="form-select"
                                    style="max-width: 60px; flex: 0 0 60px; padding-left: 8px; padding-right: 20px; text-align: center;">
                                    <option value="V">V</option>
                                    <option value="E">E</option>
                                </select>
                                <input type="text" name="cedDocenteEdit" id="cedDocenteEdit" class="form-control" maxlength="9">
                            </div>
                            <div class="invalid-feedback" id="error-nacionalidadEdit"></div>
                            <div class="invalid-feedback" id="error-cedDocenteEdit"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Teléfono</label>
                            <input type="text" name="telfDocenteEdit" id="telfDocenteEdit" class="form-control mask-telefono" maxlength="12">
                            <div class="invalid-feedback" id="error-telfDocenteEdit"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombres</label>
                            <input type="text" name="nombreDocenteEdit" id="nombreDocenteEdit" class="form-control">
                            <div class="invalid-feedback" id="error-nombreDocenteEdit"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Apellidos</label>
                            <input type="text" name="apellidoDocenteEdit" id="apellidoDocenteEdit" class="form-control">
                            <div class="invalid-feedback" id="error-apellidoDocenteEdit"></div>
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

<script src="view/public/js/docente.js"></script>