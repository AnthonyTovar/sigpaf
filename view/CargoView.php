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
                    <h5 class="mb-0"><i class="bi bi-briefcase-fill me-2"></i> Gestión de Cargos</h5>
                    <button class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalCargo">
                        <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Cargo
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre del Cargo</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($cargos)): ?>
                                    <?php foreach ($cargos as $c): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary px-2 py-1">
                                                    <?php echo $c['idCargo']; ?>
                                                </span></td>

                                            <td class="fw-bold text-dark">
                                                <?php echo $c['nombreCargo']; ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo $c['descripcionCargo']; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-warning btn-sm border-0 btn-editar"
                                                        data-id="<?php echo $c['idCargo']; ?>">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-sm border-0 btn-eliminar"
                                                        data-id="<?php echo $c['idCargo']; ?>">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="bi bi-info-circle me-1"></i> No hay cargos registrados actualmente.
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

<div class="modal fade" id="modalCargo" tabindex="-1" aria-labelledby="modalCargoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formNuevoCargo">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modalCargoLabel"><i class="bi bi-file-earmark-plus me-2"></i>Registrar
                        Nuevo Cargo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Nombre del Cargo</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-person-badge"></i></span>
                            <input type="text" name="nombreCargo" class="form-control" placeholder="Ej: Informatico">
                        </div>
                        <div class="invalid-feedback" id="error-nombreCargo"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Descripción Detallada</label>
                        <textarea name="descripcionCargo" class="form-control" rows="3"
                            placeholder="Breve descripción..."></textarea>
                        <div class="invalid-feedback" id="error-descripcionCargo"></div>
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

<div class="modal fade" id="modalEditarCargo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formEditarCargo">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Cargo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="idCargoEdit" id="idCargoEdit">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre del Cargo</label>
                        <input type="text" name="nombreCargoEdit" id="nombreCargoEdit" class="form-control" placeholder="Ej: Informatico">
                        <div class="invalid-feedback" id="error-nombreCargoEdit"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea name="descripcionCargoEdit" id="descripcionCargoEdit" class="form-control"
                            rows="3"></textarea>
                        <div class="invalid-feedback" id="error-descripcionCargoEdit"></div>
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

<script src="view/public/js/cargo.js"></script>