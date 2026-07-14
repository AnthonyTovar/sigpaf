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
                    <h5 class="mb-0"><i class="bi bi-geo-alt-fill me-2"></i> Gestión de Municipios</h5>
                    <button class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalMunicipio">
                        <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Municipio
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Municipio</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($municipios)): ?>
                                    <?php foreach ($municipios as $m): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary px-2 py-1"><?php echo $m['idMunicipio']; ?></span></td>
                                            <td class="fw-bold text-dark"><span><?php echo $m['nombreMunicipio']; ?></span></td>
                                            <td class="text-muted small"><span><?php echo $m['nombreEstado']; ?></span></td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-warning btn-sm border-0 btn-editar" data-id="<?php echo $m['idMunicipio']; ?>"><i class="bi bi-pencil-square"></i></button>
                                                    <button class="btn btn-outline-danger btn-sm border-0 btn-eliminar" data-id="<?php echo $m['idMunicipio']; ?>"><i class="bi bi-trash3-fill"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay municipios registrados actualmente.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMunicipio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formNuevoMunicipio">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-geo-fill me-2"></i>Registrar Nuevo Municipio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Nombre del Municipio</label>
                        <input type="text" name="nombreMunicipio" class="form-control" placeholder="Ej: San Felipe" maxlength="50">
                        <div class="invalid-feedback" id="error-nombreMunicipio"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Estado</label>
                        <select name="idEstado" class="form-select">
                            <option value="">Seleccione un estado...</option>
                            <?php if (!empty($estados)): ?>
                                <?php foreach ($estados as $est): ?>
                                    <option value="<?php echo $est['idEstado']; ?>"><?php echo htmlspecialchars($est['nombreEstado']); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback" id="error-idEstado"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarMunicipio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formEditarMunicipio">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Municipio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="idMunicipioEdit" id="idMunicipioEdit">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre del Municipio</label>
                        <input type="text" name="nombreMunicipioEdit" id="nombreMunicipioEdit" class="form-control" maxlength="50">
                        <div class="invalid-feedback" id="error-nombreMunicipioEdit"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Estado</label>
                        <select name="idEstadoEdit" id="idEstadoEdit" class="form-select">
                            <?php if (!empty($estados)): ?>
                                <?php foreach ($estados as $est): ?>
                                    <option value="<?php echo $est['idEstado']; ?>"><?php echo htmlspecialchars($est['nombreEstado']); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback" id="error-idEstadoEdit"></div>
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

<script src="view/public/js/municipio.js"></script>