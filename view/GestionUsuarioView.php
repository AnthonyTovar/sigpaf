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
                    <h5 class="mb-0"><i class="bi bi-person-gear me-2"></i> Gestión de Mi Perfil</h5>
                    <button class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalEditarPerfil">
                        <i class="bi bi-pencil-square me-1"></i> Editar Perfil
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
                                <?php if (!empty($usuario)): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary px-2 py-1"><?php echo $usuario['idUsuario']; ?></span>
                                        </td>
                                        <td class="fw-bold text-dark">
                                            <span><?php echo $usuario['nombreUsuario']; ?></span>
                                        </td>
                                        <td class="text-muted small">
                                            <span><?php echo $usuario['rolUsuario'] ?? 'N/A'; ?></span>
                                        </td>
                                        <td class="text-muted small">
                                            <span><?php echo ($usuario['nombres'] ?? '') . ' ' . ($usuario['apellidos'] ?? ''); ?></span>
                                        </td>
                                        <td class="text-muted small">
                                            <span><?php echo $usuario['cedulaEmpleado'] ?? 'N/A'; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-outline-warning btn-sm border-0 btn-editar"
                                                    data-id="<?php echo $usuario['idUsuario']; ?>">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bi bi-info-circle me-1"></i> No se encontró información del usuario.
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

<!-- MODAL EDITAR PERFIL -->
<div class="modal fade" id="modalEditarPerfil" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formEditarPerfil" novalidate>
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Mi Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="idUsuarioPerfil" id="idUsuarioPerfil" value="<?php echo $usuario['idUsuario']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre de Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                            <input type="text" name="nombreUsuarioPerfil" id="nombreUsuarioPerfil" 
                                class="form-control" value="<?php echo $usuario['nombreUsuario']; ?>" autocomplete="off">
                        </div>
                        <div class="invalid-feedback" id="error-nombreUsuarioPerfil"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nueva Contraseña (dejar en blanco para no cambiar)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-key"></i></span>
                            <input type="password" name="contrasenaPerfil" id="contrasenaPerfil" 
                                class="form-control" placeholder="Mínimo 6 caracteres" autocomplete="new-password">
                        </div>
                        <div class="invalid-feedback" id="error-contrasenaPerfil"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Confirmar Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-key-fill"></i></span>
                            <input type="password" name="confirmarContrasenaPerfil" id="confirmarContrasenaPerfil" 
                                class="form-control" placeholder="Repita la contraseña" autocomplete="new-password">
                        </div>
                        <div class="invalid-feedback" id="error-confirmarContrasenaPerfil"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar Perfil</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="view/public/js/gestionUsuario.js"></script>