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
                    <h5 class="mb-0"><i class="bi bi-person-vcard me-2"></i> Gestión de Empleados</h5>
                    <button class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalEmpleado">
                        <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Empleado
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Cédula</th>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th>Fecha Nac.</th>
                                    <th>Teléfono</th>
                                    <th>Correo</th>
                                    <th>Cargo</th>
                                    <th>Unidad Ejecutora</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($empleados)): ?>
                                    <?php foreach ($empleados as $e): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary px-2 py-1">
                                                    <?php echo $e['idEmpleado']; ?>
                                                </span></td>
                                            <td><?php echo $e['cedulaEmpleado']; ?></td>
                                            <td class="fw-bold text-dark">
                                                <?php echo $e['nombres']; ?>
                                            </td>
                                            <td class="fw-bold text-dark">
                                                <?php echo $e['apellidos']; ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo date('d/m/Y', strtotime($e['fechaNacimiento'])); ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo $e['telefonoEmpleado'] ?? 'N/A'; ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo $e['correoEmpleado'] ? '<i class="bi bi-envelope-fill me-1 text-primary"></i>' . $e['correoEmpleado'] : 'N/A'; ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo $e['nombreCargo'] ?? 'N/A'; ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo $e['nomUnidadEjecutora'] ?? 'N/A'; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-outline-warning btn-sm border-0 btn-editar"
                                                        data-id="<?php echo $e['idEmpleado']; ?>">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-sm border-0 btn-eliminar"
                                                        data-id="<?php echo $e['idEmpleado']; ?>">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">
                                            <i class="bi bi-info-circle me-1"></i> No hay empleados registrados actualmente.
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
<div class="modal fade" id="modalEmpleado" tabindex="-1" aria-labelledby="modalEmpleadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="formNuevoEmpleado" novalidate>
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modalEmpleadoLabel"><i
                            class="bi bi-file-earmark-plus me-2"></i>Registrar
                        Nuevo Empleado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Cédula</label>
                            <div class="input-group">
                                <select name="nacionalidad" id="nacionalidad" class="form-select"
                                    style="max-width: 60px; flex: 0 0 60px; padding-left: 8px; padding-right: 20px; text-align: center;">
                                    <option value="V" selected>V</option>
                                    <option value="E">E</option>
                                </select>
                                <span class="input-group-text bg-white"><i class="bi bi-card-text"></i></span>
                                <input type="text" name="cedulaEmpleado" id="cedulaEmpleado" class="form-control"
                                    placeholder="Ej: 12345678" maxlength="9">
                            </div>
                            <div class="invalid-feedback" id="error-nacionalidad"></div>
                            <div class="invalid-feedback" id="error-cedulaEmpleado"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Teléfono</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="telefonoEmpleado" class="form-control"
                                    placeholder="Ej: 0412-1234567">
                            </div>
                            <div class="invalid-feedback" id="error-telefonoEmpleado"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Nombres</label>
                            <input type="text" name="nombres" class="form-control" placeholder="Ej: Juan José">
                            <div class="invalid-feedback" id="error-nombres"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Apellidos</label>
                            <input type="text" name="apellidos" class="form-control" placeholder="Ej: Pérez García">
                            <div class="invalid-feedback" id="error-apellidos"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Fecha de Nacimiento</label>
                            <input type="date" name="fechaNacimiento" class="form-control">
                            <div class="invalid-feedback" id="error-fechaNacimiento"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="correoEmpleado" class="form-control"
                                    placeholder="Ej: juan.perez@correo.com">
                            </div>
                            <div class="invalid-feedback" id="error-correoEmpleado"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Cargo</label>
                            <select name="idCargo" class="form-select">
                                <option value="" selected disabled>Seleccione un cargo...</option>
                                <?php if (!empty($cargos)): ?>
                                    <?php foreach ($cargos as $c): ?>
                                        <option value="<?php echo $c['idCargo']; ?>">
                                            <?php echo $c['nombreCargo']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback" id="error-idCargo"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Unidad Ejecutora</label>
                            <select name="idUnidadEjecutora" class="form-select">
                                <option value="" selected disabled>Seleccione una unidad...</option>
                                <?php if (!empty($unidades)): ?>
                                    <?php foreach ($unidades as $u): ?>
                                        <option value="<?php echo $u['idUnidadEjecutora']; ?>">
                                            <?php echo $u['nomUnidadEjecutora']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback" id="error-idUnidadEjecutora"></div>
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
<div class="modal fade" id="modalEditarEmpleado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="formEditarEmpleado" novalidate>
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Empleado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="idEmpleadoEdit" id="idEmpleadoEdit">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Cédula</label>
                            <div class="input-group">
                                <select name="nacionalidadEdit" id="nacionalidadEdit" class="form-select"
                                    style="max-width: 60px; flex: 0 0 60px; padding-left: 8px; padding-right: 20px; text-align: center;">
                                    <option value="V">V</option>
                                    <option value="E">E</option>
                                </select>
                                <input type="text" name="cedulaEmpleadoEdit" id="cedulaEmpleadoEdit"
                                    class="form-control" maxlength="9">
                            </div>
                            <div class="invalid-feedback" id="error-nacionalidadEdit"></div>
                            <div class="invalid-feedback" id="error-cedulaEmpleadoEdit"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Teléfono</label>
                            <input type="text" name="telefonoEmpleadoEdit" id="telefonoEmpleadoEdit"
                                class="form-control">
                            <div class="invalid-feedback" id="error-telefonoEmpleadoEdit"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombres</label>
                            <input type="text" name="nombresEdit" id="nombresEdit" class="form-control">
                            <div class="invalid-feedback" id="error-nombresEdit"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Apellidos</label>
                            <input type="text" name="apellidosEdit" id="apellidosEdit" class="form-control">
                            <div class="invalid-feedback" id="error-apellidosEdit"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fecha de Nacimiento</label>
                            <input type="date" name="fechaNacimientoEdit" id="fechaNacimientoEdit" class="form-control">
                            <div class="invalid-feedback" id="error-fechaNacimientoEdit"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="correoEmpleadoEdit" id="correoEmpleadoEdit"
                                    class="form-control" placeholder="Ej: juan.perez@correo.com">
                            </div>
                            <div class="invalid-feedback" id="error-correoEmpleadoEdit"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Cargo</label>
                            <select name="idCargoEdit" id="idCargoEdit" class="form-select">
                                <option value="" selected disabled>Seleccione un cargo...</option>
                                <?php if (!empty($cargos)): ?>
                                    <?php foreach ($cargos as $c): ?>
                                        <option value="<?php echo $c['idCargo']; ?>">
                                            <?php echo $c['nombreCargo']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback" id="error-idCargoEdit"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Unidad Ejecutora</label>
                            <select name="idUnidadEjecutoraEdit" id="idUnidadEjecutoraEdit" class="form-select">
                                <option value="" selected disabled>Seleccione una unidad...</option>
                                <?php if (!empty($unidades)): ?>
                                    <?php foreach ($unidades as $u): ?>
                                        <option value="<?php echo $u['idUnidadEjecutora']; ?>">
                                            <?php echo $u['nomUnidadEjecutora']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback" id="error-idUnidadEjecutoraEdit"></div>
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

<script src="view/public/js/empleado.js"></script>