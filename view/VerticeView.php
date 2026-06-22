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
                    <h5 class="mb-0"><i class="bi bi-diagram-3-fill me-2"></i> Gestión de Vértices</h5>
                    <button class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalVertice">
                        <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Vértice
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre del Vértice</th>
                                    <th>Descripción</th>
                                    <th>Área Específica</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($Vertice)): ?>
                                    <?php foreach ($Vertice as $v): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary px-2 py-1"><?php echo $v['idVertice']; ?></span>
                                            </td>
                                            <td class="fw-bold text-dark">
                                                <span><?php echo $v['nombreVertice']; ?></span>
                                            </td>
                                            <td class="text-muted small">
                                                <span><?php echo $v['descVertice']; ?></span>
                                            </td>
                                            <td class="text-muted small">
                                                <span><?php echo $v['nomAreaE'] ?? 'N/A'; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-warning btn-sm border-0 btn-editar"
                                                        data-id="<?php echo $v['idVertice']; ?>">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-sm border-0 btn-eliminar"
                                                        data-id="<?php echo $v['idVertice']; ?>">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="bi bi-info-circle me-1"></i> No hay Vértices registrados actualmente.
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
<div class="modal fade" id="modalVertice" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formNuevoVertice" novalidate>
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-plus me-2"></i>Registrar Nuevo Vértice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Nombre del Vértice</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-tag"></i></span>
                            <input type="text" name="nombreVertice" class="form-control" maxlength="150"
                                placeholder="Ej: Vértice Social">
                        </div>
                        <div class="invalid-feedback" id="error-nombreVertice"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Descripción</label>
                        <textarea name="descripcionVertice" class="form-control" rows="3" maxlength="250"
                            placeholder="Breve descripción..."></textarea>
                        <div class="invalid-feedback" id="error-descripcionVertice"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Área Específica</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-grid-3x3-gap"></i></span>
                            <select name="idAreaE" class="form-select">
                                <option value="" selected disabled>Seleccione un área...</option>
                                <?php if (!empty($areas)): ?>
                                    <?php foreach ($areas as $a): ?>
                                        <option value="<?php echo $a['idAreaE']; ?>">
                                            <?php echo $a['nomAreaE']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="invalid-feedback" id="error-idAreaE"></div>
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
<div class="modal fade" id="modalEditarVertice" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formEditarVertice" novalidate>
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Vértice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="idVerticeEdit" id="idVerticeEdit">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre del Vértice</label>
                        <input type="text" name="nombreVerticeEdit" id="nombreVerticeEdit" class="form-control">
                        <div class="invalid-feedback" id="error-nombreVerticeEdit"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea name="descripcionVerticeEdit" id="descripcionVerticeEdit" class="form-control"
                            rows="3"></textarea>
                        <div class="invalid-feedback" id="error-descripcionVerticeEdit"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Área Específica</label>
                        <select name="idAreaEEdit" id="idAreaEEdit" class="form-select">
                            <option value="" selected disabled>Seleccione un área...</option>
                            <?php if (!empty($areas)): ?>
                                <?php foreach ($areas as $a): ?>
                                    <option value="<?php echo $a['idAreaE']; ?>">
                                        <?php echo $a['nomAreaE']; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback" id="error-idAreaEEdit"></div>
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

<script src="view/public/js/vertice.js"></script>