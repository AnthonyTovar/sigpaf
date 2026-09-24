<?php
// Colores de badge por tipo de accion
$badgeAccion = [
    'LOGIN'         => 'bg-success',
    'LOGOUT'        => 'bg-secondary',
    'CREATE'        => 'bg-primary',
    'UPDATE'        => 'bg-warning text-dark',
    'DELETE'        => 'bg-danger',
    'ACCESS_DENIED' => 'bg-dark'
];

$txtAccion = [
    'LOGIN'         => 'Inicio de sesion',
    'LOGOUT'        => 'Cierre de sesion',
    'CREATE'        => 'Creacion',
    'UPDATE'        => 'Actualizacion',
    'DELETE'        => 'Eliminacion',
    'ACCESS_DENIED' => 'Acceso denegado'
];

// Construye query string conservando los filtros para la paginacion
$queryFiltros = http_build_query([
    'action'    => 'bitacora',
    'accion_f'  => $filtros['accion'],
    'usuario'   => $filtros['usuario'],
    'desde'     => $filtros['desde'],
    'hasta'     => $filtros['hasta'],
    'buscar'    => $filtros['buscar']
]);
?>

<div class="container-fluid py-4">
    <div class="row" style="margin-top: 10px">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i> Bitacora del Sistema</h5>
                    <span class="badge bg-light text-dark"><?php echo $total; ?> registro(s)</span>
                </div>

                <!-- Filtros -->
                <div class="card-body border-bottom bg-light">
                    <form method="GET" action="index.php" class="row g-2 align-items-end">
                        <input type="hidden" name="action" value="bitacora">

                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-secondary small mb-1">Accion</label>
                            <select name="accion_f" class="form-select form-select-sm">
                                <option value="">Todas</option>
                                <?php foreach ($txtAccion as $valor => $texto): ?>
                                    <option value="<?php echo $valor; ?>"
                                        <?php echo ($filtros['accion'] === $valor) ? 'selected' : ''; ?>>
                                        <?php echo $texto; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-secondary small mb-1">Usuario</label>
                            <input type="text" name="usuario" class="form-control form-control-sm"
                                placeholder="Nombre de usuario"
                                value="<?php echo htmlspecialchars($filtros['usuario']); ?>">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-secondary small mb-1">Desde</label>
                            <input type="date" name="desde" class="form-control form-control-sm"
                                value="<?php echo htmlspecialchars($filtros['desde']); ?>">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-secondary small mb-1">Hasta</label>
                            <input type="date" name="hasta" class="form-control form-control-sm"
                                value="<?php echo htmlspecialchars($filtros['hasta']); ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary small mb-1">Buscar</label>
                            <input type="text" name="buscar" class="form-control form-control-sm"
                                placeholder="Texto en descripcion o modulo"
                                value="<?php echo htmlspecialchars($filtros['buscar']); ?>">
                        </div>

                        <div class="col-md-1 d-flex gap-1">
                            <button type="submit" class="btn btn-dark btn-sm w-100" title="Filtrar">
                                <i class="bi bi-funnel-fill"></i>
                            </button>
                            <a href="index.php?action=bitacora" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Tabla -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha y Hora</th>
                                    <th>Usuario</th>
                                    <th>Accion</th>
                                    <th>Modulo</th>
                                    <th>Descripcion</th>
                                    <th>Direccion IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($registros)): ?>
                                    <?php foreach ($registros as $r): ?>
                                        <tr>
                                            <td class="text-nowrap small text-muted">
                                                <?php echo date('d/m/Y h:i:s A', strtotime($r['fecha_hora'])); ?>
                                            </td>
                                            <td class="fw-bold text-dark">
                                                <?php echo htmlspecialchars($r['usuario_nombre']); ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $badgeAccion[$r['accion']] ?? 'bg-light text-dark'; ?> px-2 py-1">
                                                    <?php echo $txtAccion[$r['accion']] ?? $r['accion']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border px-2 py-1">
                                                    <?php echo htmlspecialchars($r['modulo']); ?>
                                                </span>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo htmlspecialchars($r['descripcion']); ?>
                                            </td>
                                            <td class="small text-muted text-nowrap">
                                                <i class="bi bi-hdd-network me-1"></i><?php echo htmlspecialchars($r['ip']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bi bi-info-circle me-1"></i> No hay registros en la bitacora con los filtros aplicados.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginacion -->
                    <?php if ($paginas > 1): ?>
                        <nav class="mt-3">
                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                <?php for ($i = 1; $i <= $paginas; $i++): ?>
                                    <li class="page-item <?php echo ($i == $pagina) ? 'active' : ''; ?>">
                                        <a class="page-link" href="index.php?<?php echo $queryFiltros; ?>&pagina=<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
