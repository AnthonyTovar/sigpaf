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
                    <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i> Gestión de Grupos Etarios</h5>
                    <button class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalGrupoEtario">
                        <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Grupo
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Edad Mínima</th>
                                    <th>Edad Máxima</th>
                                    <th>Rango</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($grupos)): ?>
                                    <?php foreach ($grupos as $g): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary px-2 py-1"><?php echo $g['idGrupoEtareo']; ?></span>
                                            </td>
                                            <td class="fw-bold text-dark">
                                                <span><?php echo $g['nomGrupoEtareo']; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info text-dark"><?php echo $g['edadMin']; ?> años</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info text-dark"><?php echo $g['edadMax']; ?> años</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary"><?php echo $g['edadMin']; ?> - <?php echo $g['edadMax']; ?> años</span>
                                            </td>
                                            <td class="text-muted small">
                                                <span><?php echo $g['descGrupoEtareo'] ?? 'N/A'; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-warning btn-sm border-0 btn-editar"
                                                        data-id="<?php echo $g['idGrupoEtareo']; ?>">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-sm border-0 btn-eliminar"
                                                        data-id="<?php echo $g['idGrupoEtareo']; ?>">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="bi bi-info-circle me-1"></i> No hay grupos etarios registrados.
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
<div class="modal fade" id="modalGrupoEtario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formNuevoGrupoEtario" novalidate>
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-plus me-2"></i>Registrar Nuevo Grupo Etario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Nombre del Grupo</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-people"></i></span>
                            <input type="text" name="nomGrupoEtareo" class="form-control" maxlength="25" placeholder="Ej: De 0 a 5 Años">
                        </div>
                        <div class="invalid-feedback" id="error-nomGrupoEtareo"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Edad Mínima</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-arrow-down-circle"></i></span>
                                <input type="number" name="edadMin" class="form-control" min="0" max="120" placeholder="Ej: 5">
                                <span class="input-group-text">años</span>
                            </div>
                            <div class="invalid-feedback" id="error-edadMin"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Edad Máxima</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-arrow-up-circle"></i></span>
                                <input type="number" name="edadMax" class="form-control" min="0" max="120" placeholder="Ej: 12">
                                <span class="input-group-text">años</span>
                            </div>
                            <div class="invalid-feedback" id="error-edadMax"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Descripción</label>
                        <textarea name="descGrupoEtareo" class="form-control" rows="2" maxlength="250" placeholder="Breve descripción del grupo..."></textarea>
                        <div class="invalid-feedback" id="error-descGrupoEtareo"></div>
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

<!-- MODAL EDITAR -->
<div class="modal fade" id="modalEditarGrupoEtario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formEditarGrupoEtario" novalidate>
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Grupo Etario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="idGrupoEtareoEdit" id="idGrupoEtareoEdit">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre del Grupo</label>
                        <input type="text" name="nomGrupoEtareoEdit" id="nomGrupoEtareoEdit" class="form-control">
                        <div class="invalid-feedback" id="error-nomGrupoEtareoEdit"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Edad Mínima</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-arrow-down-circle"></i></span>
                                <input type="number" name="edadMinEdit" id="edadMinEdit" class="form-control" min="0" max="120">
                                <span class="input-group-text">años</span>
                            </div>
                            <div class="invalid-feedback" id="error-edadMinEdit"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Edad Máxima</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-arrow-up-circle"></i></span>
                                <input type="number" name="edadMaxEdit" id="edadMaxEdit" class="form-control" min="0" max="120">
                                <span class="input-group-text">años</span>
                            </div>
                            <div class="invalid-feedback" id="error-edadMaxEdit"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea name="descGrupoEtareoEdit" id="descGrupoEtareoEdit" class="form-control" rows="2"></textarea>
                        <div class="invalid-feedback" id="error-descGrupoEtareoEdit"></div>
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

<script src="view/public/js/grupoEtario.js"></script>