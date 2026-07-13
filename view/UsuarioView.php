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
                    <h5 class="mb-0"><i class="bi bi-person-lock me-2"></i> Gestión de Usuarios</h5>
                    <button class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                        <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Usuario
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre de Usuario</th>
                                    <th>Tipo de Usuario</th>
                                    <th>Empleado Asignado</th>
                                    <th>Cédula</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($usuarios)): ?>
                                    <?php foreach ($usuarios as $u): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary px-2 py-1">
                                                    <?php echo $u['idUsuario']; ?>
                                                </span></td>
                                            <td class="fw-bold text-dark">
                                                <?php echo $u['nombreUsuario']; ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo $u['rolUsuario'] ?? 'N/A'; ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo ($u['nombres'] ?? '') . ' ' . ($u['apellidos'] ?? ''); ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo $u['cedulaEmpleado'] ?? 'N/A'; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-warning btn-sm border-0 btn-editar"
                                                        data-id="<?php echo $u['idUsuario']; ?>">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-sm border-0 btn-eliminar"
                                                        data-id="<?php echo $u['idUsuario']; ?>">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bi bi-info-circle me-1"></i> No hay usuarios registrados actualmente.
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
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="formNuevoUsuario" novalidate>
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modalUsuarioLabel"><i class="bi bi-file-earmark-plus me-2"></i>Registrar
                        Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Nombre de Usuario</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                                <input type="text" name="nombreUsuario" class="form-control" placeholder="Ej: jperez" autocomplete="off">
                            </div>
                            <div class="invalid-feedback" id="error-nombreUsuario"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-key"></i></span>
                                <input type="password" name="contrasena" class="form-control" placeholder="Mínimo 6 caracteres" autocomplete="new-password">
                            </div>
                            <div class="invalid-feedback" id="error-contrasena"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Tipo de Usuario</label>
                            <select name="idTipoUsuario" class="form-select">
                                <option value="" selected disabled>Seleccione un tipo...</option>
                                <?php if (!empty($tiposUsuario)): ?>
                                    <?php foreach ($tiposUsuario as $tu): ?>
                                        <option value="<?php echo $tu['idTipoUsuario']; ?>">
                                            <?php echo $tu['rolUsuario']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback" id="error-idTipoUsuario"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Empleado</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                <input type="text" id="buscarEmpleado" class="form-control" placeholder="Buscar por cédula..." autocomplete="off">
                            </div>
                            <select name="idEmpleado" id="idEmpleado" class="form-select mt-2">
                                <option value="" selected disabled>Seleccione un empleado...</option>
                                <?php if (!empty($empleados)): ?>
                                    <?php foreach ($empleados as $e): ?>
                                        <option value="<?php echo $e['idEmpleado']; ?>" data-cedula="<?php echo $e['cedulaEmpleado']; ?>">
                                            <?php echo $e['cedulaEmpleado'] . ' - ' . $e['nombres'] . ' ' . $e['apellidos']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback" id="error-idEmpleado"></div>
                            <div id="empleadoInfo" class="mt-2 small text-muted" style="display:none;">
                                <i class="bi bi-person-check me-1 text-success"></i><span id="empleadoSeleccionadoTexto"></span>
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
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="formEditarUsuario" novalidate>
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="idUsuarioEdit" id="idUsuarioEdit">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombre de Usuario</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                                <input type="text" name="nombreUsuarioEdit" id="nombreUsuarioEdit" class="form-control" autocomplete="off">
                            </div>
                            <div class="invalid-feedback" id="error-nombreUsuarioEdit"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Contraseña (dejar en blanco para no cambiar)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-key"></i></span>
                                <input type="password" name="contrasenaEdit" id="contrasenaEdit" class="form-control" placeholder="Nueva contraseña" autocomplete="new-password">
                            </div>
                            <div class="invalid-feedback" id="error-contrasenaEdit"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tipo de Usuario</label>
                            <select name="idTipoUsuarioEdit" id="idTipoUsuarioEdit" class="form-select">
                                <option value="" selected disabled>Seleccione un tipo...</option>
                                <?php if (!empty($tiposUsuario)): ?>
                                    <?php foreach ($tiposUsuario as $tu): ?>
                                        <option value="<?php echo $tu['idTipoUsuario']; ?>">
                                            <?php echo $tu['rolUsuario']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback" id="error-idTipoUsuarioEdit"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Empleado</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                <input type="text" id="buscarEmpleadoEdit" class="form-control" placeholder="Buscar por cédula..." autocomplete="off">
                            </div>
                            <select name="idEmpleadoEdit" id="idEmpleadoEdit" class="form-select mt-2">
                                <option value="" selected disabled>Seleccione un empleado...</option>
                                <?php if (!empty($empleados)): ?>
                                    <?php foreach ($empleados as $e): ?>
                                        <option value="<?php echo $e['idEmpleado']; ?>" data-cedula="<?php echo $e['cedulaEmpleado']; ?>">
                                            <?php echo $e['cedulaEmpleado'] . ' - ' . $e['nombres'] . ' ' . $e['apellidos']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback" id="error-idEmpleadoEdit"></div>
                            <div id="empleadoInfoEdit" class="mt-2 small text-muted" style="display:none;">
                                <i class="bi bi-person-check me-1 text-success"></i><span id="empleadoSeleccionadoTextoEdit"></span>
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

<script src="view/public/js/usuario.js"></script>