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
                    <h5 class="mb-0"><i class="bi bi-clock me-2"></i> Gestión de Horarios</h5>
                    <button class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalHorario">
                        <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Horario
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre del Horario</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($horarios)): ?>
                                    <?php foreach ($horarios as $h): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary px-2 py-1"><?php echo $h['idHorario']; ?></span></td>
                                            <td class="fw-bold text-dark"><span><?php echo $h['nomHorario']; ?></span></td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-warning btn-sm border-0 btn-editar" data-id="<?php echo $h['idHorario']; ?>"><i class="bi bi-pencil-square"></i></button>
                                                    <button class="btn btn-outline-danger btn-sm border-0 btn-eliminar" data-id="<?php echo $h['idHorario']; ?>"><i class="bi bi-trash3-fill"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No hay horarios registrados actualmente.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalHorario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formNuevoHorario">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-plus me-2"></i>Registrar Nuevo Horario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Rango de Horario</label>
                        <input type="text" name="nomHorario" class="form-control" placeholder="08:00 AM - 12:00 PM" maxlength="22">
                        <div class="invalid-feedback" id="error-nomHorario"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary px-4">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarHorario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formEditarHorario">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Horario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="idHorarioEdit" id="idHorarioEdit">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rango de Horario</label>
                        <input type="text" name="nomHorarioEdit" id="nomHorarioEdit" class="form-control" placeholder="08:00 AM - 12:00 PM" maxlength="22">
                        <div class="invalid-feedback" id="error-nomHorarioEdit"></div>
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

<script src="view/public/js/horario.js"></script>