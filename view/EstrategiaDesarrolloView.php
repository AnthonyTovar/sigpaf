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
                    <h5 class="mb-0"><i class="bi bi-lightbulb me-2"></i> Gestion de Estrategias de Desarrollo</h5>
                    <button class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalEstrategia">
                        <i class="bi bi-plus-circle-fill me-1"></i> Nueva Estrategia
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Codigo</th>
                                    <th>Nombre</th>
                                    <th>Descripcion</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($estrategias)): ?>
                                    <?php foreach ($estrategias as $e): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary px-2 py-1"><?php echo $e['idEstDesarrollo']; ?></span></td>
                                            <td class="fw-bold text-dark"><span><?php echo $e['nomEstDesarrollo']; ?></span></td>
                                            <td class="text-muted small"><?php echo $e['descEstDesarrollo'] ?? 'N/A'; ?></td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-warning btn-sm border-0 btn-editar" data-id="<?php echo $e['idEstDesarrollo']; ?>"><i class="bi bi-pencil-square"></i></button>
                                                    <button class="btn btn-outline-danger btn-sm border-0 btn-eliminar" data-id="<?php echo $e['idEstDesarrollo']; ?>"><i class="bi bi-trash3-fill"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay estrategias de desarrollo registradas actualmente.</td></tr>
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
<div class="modal fade" id="modalEstrategia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formNuevaEstrategia" novalidate>
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-plus me-2"></i>Registrar Nueva Estrategia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Nombre de la Estrategia</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-lightbulb"></i></span>
                            <input type="text" name="nomEstDesarrollo" class="form-control" placeholder="Ej: Innovacion Tecnologica">
                        </div>
                        <div class="invalid-feedback" id="error-nomEstDesarrollo"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Descripcion (opcional)</label>
                        <textarea name="descEstDesarrollo" class="form-control" rows="3" placeholder="Breve descripcion de la estrategia..."></textarea>
                        <div class="invalid-feedback" id="error-descEstDesarrollo"></div>
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
<div class="modal fade" id="modalEditarEstrategia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formEditarEstrategia" novalidate>
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Estrategia de Desarrollo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="idEstDesarrolloEdit" id="idEstDesarrolloEdit">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre de la Estrategia</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-lightbulb"></i></span>
                            <input type="text" name="nomEstDesarrolloEdit" id="nomEstDesarrolloEdit" class="form-control">
                        </div>
                        <div class="invalid-feedback" id="error-nomEstDesarrolloEdit"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripcion (opcional)</label>
                        <textarea name="descEstDesarrolloEdit" id="descEstDesarrolloEdit" class="form-control" rows="3"></textarea>
                        <div class="invalid-feedback" id="error-descEstDesarrolloEdit"></div>
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

<script src="view/public/js/estrategiaDesarrollo.js"></script>