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
                    <h5 class="mb-0"><i class="bi bi-geo-alt-fill me-2"></i> Gestión de Parroquias</h5>
                    <button class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalParroquia">
                        <i class="bi bi-plus-circle-fill me-1"></i> Nueva Parroquia
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre de la Parroquia</th>
                                    <th>Municipio</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($parroquias)): ?>
                                    <?php foreach ($parroquias as $p): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary px-2 py-1"><?php echo $p['idParroquia']; ?></span></td>
                                            <td class="fw-bold text-dark"><span><?php echo $p['nombreParroquia']; ?></span></td>
                                            <td class="text-muted small"><span><?php echo $p['nombreMunicipio'] ?? 'N/A'; ?></span></td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-warning btn-sm border-0 btn-editar" data-id="<?php echo $p['idParroquia']; ?>"><i class="bi bi-pencil-square"></i></button>
                                                    <button class="btn btn-outline-danger btn-sm border-0 btn-eliminar" data-id="<?php echo $p['idParroquia']; ?>"><i class="bi bi-trash3-fill"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay parroquias registradas actualmente.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalParroquia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formNuevaParroquia">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-plus me-2"></i>Registrar Nueva Parroquia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Nombre de la Parroquia</label>
                        <input type="text" name="nombreParroquia" class="form-control" maxlength="25" placeholder="Ej: San Pablo">
                        <div class="invalid-feedback" id="error-nombreParroquia"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Municipio</label>
                        <select name="idMunicipio" class="form-select">
                            <option value="" selected disabled>Seleccione un municipio...</option>
                            <?php if (!empty($municipios)): ?>
                                <?php foreach ($municipios as $m): ?>
                                    <option value="<?php echo $m['idMunicipio']; ?>"><?php echo $m['nombreMunicipio']; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback" id="error-idMunicipio"></div>
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

<div class="modal fade" id="modalEditarParroquia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formEditarParroquia">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Parroquia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="idParroquiaEdit" id="idParroquiaEdit">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre de la Parroquia</label>
                        <input type="text" name="nombreParroquiaEdit" id="nombreParroquiaEdit" class="form-control" maxlength="25">
                        <div class="invalid-feedback" id="error-nombreParroquiaEdit"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Municipio</label>
                        <select name="idMunicipioEdit" id="idMunicipioEdit" class="form-select">
                            <option value="" selected disabled>Seleccione un municipio...</option>
                            <?php if (!empty($municipios)): ?>
                                <?php foreach ($municipios as $m): ?>
                                    <option value="<?php echo $m['idMunicipio']; ?>"><?php echo $m['nombreMunicipio']; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback" id="error-idMunicipioEdit"></div>
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

<script src="view/public/js/parroquia.js"></script>