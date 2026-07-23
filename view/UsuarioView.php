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

<!-- ============================================ -->
<!-- MODAL REGISTRAR -->
<!-- ============================================ -->
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
                    <!-- PASO 1: BUSCAR EMPLEADO POR CÉDULA -->
                    <div class="card border-primary mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-search me-2"></i> Paso 1: Buscar Empleado por Cédula
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold text-secondary">Cédula del Empleado</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-person-badge"></i></span>
                                        <input type="text" id="buscarCedulaEmpleado" class="form-control"
                                            placeholder="Ingrese la cédula del empleado..." autocomplete="off" maxlength="20">
                                        <button type="button" id="btnBuscarEmpleado" class="btn btn-primary">
                                            <i class="bi bi-search me-1"></i> Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- RESULTADOS DE BÚSQUEDA -->
                            <div id="resultadoBusquedaEmpleado" style="display:none;" class="mt-3">
                                <div id="infoEmpleadoEncontrado" class="alert alert-success d-none">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <strong>Empleado encontrado:</strong> <span id="nombreEmpleadoEncontrado"></span>
                                    <br><small class="text-muted">Cédula: <span id="cedulaEmpleadoEncontrado"></span> — No tiene usuario asignado. Puede continuar.</small>
                                </div>
                                <div id="infoEmpleadoYaTiene" class="alert alert-warning d-none">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <strong>Empleado encontrado:</strong> <span id="nombreEmpleadoYaTiene"></span>
                                    <br><small class="text-muted">Este empleado ya tiene un usuario asignado (<span id="usuarioAsignado"></span>).</small>
                                </div>
                                <div id="infoEmpleadoNoExiste" class="alert alert-danger d-none">
                                    <i class="bi bi-x-circle-fill me-2"></i>
                                    <strong>No se encontró empleado</strong> con la cédula <span id="cedulaNoExiste"></span>.
                                    <br><small class="text-muted">Verifique la cédula e intente nuevamente.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PASO 2: DATOS DEL USUARIO -->
                    <div class="card border-secondary">
                        <div class="card-header bg-secondary text-white">
                            <i class="bi bi-person-lock me-2"></i> Paso 2: Datos del Usuario
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="idEmpleado" id="idEmpleado">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold text-secondary">Nombre de Usuario</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                                        <input type="text" name="nombreUsuario" id="nombreUsuario" class="form-control"
                                            placeholder="Ej: jperez" autocomplete="off" maxlength="50">
                                    </div>
                                    <div class="invalid-feedback" id="error-nombreUsuario"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold text-secondary">Tipo de Usuario</label>
                                    <select name="idTipoUsuario" id="idTipoUsuario" class="form-select">
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
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold text-secondary">Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-key"></i></span>
                                        <input type="password" name="contrasena" id="contrasena" class="form-control"
                                            placeholder="Mínimo 6 caracteres" autocomplete="new-password">
                                    </div>
                                    <div class="invalid-feedback" id="error-contrasena"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold text-secondary">Confirmar Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-key-fill"></i></span>
                                        <input type="password" name="confirmarContrasena" id="confirmarContrasena"
                                            class="form-control" placeholder="Repita la contraseña" autocomplete="new-password">
                                    </div>
                                    <div class="invalid-feedback" id="error-confirmarContrasena"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" id="btnGuardarUsuario" class="btn btn-primary px-4">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL EDITAR (MISMO DISEÑO, SIN BÚSQUEDA) -->
<!-- ============================================ -->
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
                    <input type="hidden" name="idEmpleadoEdit" id="idEmpleadoEdit">

                    <!-- PERFIL DEL EMPLEADO (SOLO LECTURA) -->
                    <div class="card border-warning mb-4">
                        <div class="card-header bg-warning text-dark">
                            <i class="bi bi-person-badge me-2"></i> Empleado Asignado
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-center">
                                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center"
                                        style="width: 70px; height: 70px;">
                                        <i class="bi bi-person-fill" style="font-size: 2.5rem; color: #6c757d;"></i>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <h5 class="mb-1 fw-bold" id="nombreEmpleadoEdit"></h5>
                                    <p class="mb-1 text-muted">
                                        <i class="bi bi-credit-card-2-front me-1"></i>
                                        Cédula: <span id="cedulaEmpleadoEdit" class="fw-semibold"></span>
                                    </p>
                                    <p class="mb-0 text-muted small">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Este usuario pertenece a este empleado. No se puede cambiar.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DATOS DEL USUARIO -->
                    <div class="card border-secondary">
                        <div class="card-header bg-secondary text-white">
                            <i class="bi bi-person-lock me-2"></i> Datos del Usuario
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold text-secondary">Nombre de Usuario</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                                        <input type="text" name="nombreUsuarioEdit" id="nombreUsuarioEdit"
                                            class="form-control" autocomplete="off" maxlength="50">
                                    </div>
                                    <div class="invalid-feedback" id="error-nombreUsuarioEdit"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold text-secondary">Tipo de Usuario</label>
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
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold text-secondary">Contraseña (dejar en blanco para no cambiar)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-key"></i></span>
                                        <input type="password" name="contrasenaEdit" id="contrasenaEdit"
                                            class="form-control" placeholder="Nueva contraseña" autocomplete="new-password">
                                    </div>
                                    <div class="invalid-feedback" id="error-contrasenaEdit"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold text-secondary">Confirmar Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-key-fill"></i></span>
                                        <input type="password" name="confirmarContrasenaEdit" id="confirmarContrasenaEdit"
                                            class="form-control" placeholder="Repita la nueva contraseña" autocomplete="new-password">
                                    </div>
                                    <div class="invalid-feedback" id="error-confirmarContrasenaEdit"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning px-4">Actualizar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="view/public/js/usuario.js"></script>