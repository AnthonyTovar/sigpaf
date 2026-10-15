<?php
// ---------- Configuracion visual por tipo de accion ----------
$confAccion = [
    'LOGIN'         => ['badge' => 'bg-success',            'texto' => 'Inicio de sesión',  'icono' => 'bi-box-arrow-in-right'],
    'LOGOUT'        => ['badge' => 'bg-secondary',          'texto' => 'Cierre de sesión',  'icono' => 'bi-box-arrow-right'],
    'CREATE'        => ['badge' => 'bg-primary',            'texto' => 'Creación',          'icono' => 'bi-plus-circle'],
    'UPDATE'        => ['badge' => 'bg-warning text-dark',  'texto' => 'Actualización',     'icono' => 'bi-pencil-square'],
    'DELETE'        => ['badge' => 'bg-danger',             'texto' => 'Eliminación',       'icono' => 'bi-trash'],
    'ACCESS_DENIED' => ['badge' => 'bg-dark',               'texto' => 'Acceso denegado',   'icono' => 'bi-shield-lock'],
];

/**
 * Formato de fecha legible: destaca los eventos de hoy y ayer.
 * Devuelve [texto, esDestacada]
 */
if (!function_exists('fechaLegible')) {
    function fechaLegible($fechaHora)
    {
        $ts = strtotime($fechaHora);
        $hoy  = date('Y-m-d');
        $ayer = date('Y-m-d', strtotime('-1 day'));

        $hora = date('h:i A', $ts);
        $fecha = date('Y-m-d', $ts);

        if ($fecha === $hoy) {
            return ['Hoy • ' . $hora, true];
        }
        if ($fecha === $ayer) {
            return ['Ayer • ' . $hora, true];
        }
        return [date('d/m/Y', $ts) . ' ' . $hora, false];
    }
}

// Query string con los filtros activos (para paginacion y chips)
if (!function_exists('urlBitacora')) {
    function urlBitacora($filtros, $extra = [])
    {
        $params = array_merge([
            'action'    => 'bitacora',
            'accion_f'  => $filtros['accion'],
            'usuario'   => $filtros['usuario'],
            'desde'     => $filtros['desde'],
            'hasta'     => $filtros['hasta'],
            'buscar'    => $filtros['buscar'],
        ], $extra);

        // Quitar parametros vacios para URLs limpias
        $params = array_filter($params, function ($v) { return $v !== '' && $v !== null; });

        return 'index.php?' . http_build_query($params);
    }
}

// Numeros de pagina a mostrar: inicio, fin, y ventana alrededor de la actual
if (!function_exists('ventanaPaginas')) {
    function ventanaPaginas($pagina, $paginas)
    {
        if ($paginas <= 7) {
            return range(1, $paginas);
        }
        $ventana = range(max(1, $pagina - 2), min($paginas, $pagina + 2));
        if ($ventana[0] > 1) array_unshift($ventana, 1, '…');
        if (end($ventana) < $paginas) array_push($ventana, '…', $paginas);
        return $ventana;
    }
}
?>

<div class="container-fluid py-4">
    <div class="row" style="margin-top: 10px">
        <div class="col-12">
            <div class="card shadow-sm border-0">

                <!-- Encabezado -->
                <div class="card-header bitacora-header text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i> Bitácora del Sistema</h5>
                    <span class="badge badge-total rounded-pill"><?php echo (int) $total; ?> registro(s)</span>
                </div>

                <!-- Chips de resumen por tipo de accion (conteo real, respeta los filtros) -->
                <div class="card-body border-bottom bg-light py-3">
                    <div class="bitacora-resumen">
                        <?php foreach ($confAccion as $valor => $conf):
                            $cantidad = $resumenAcciones[$valor] ?? 0;
                            $esActivo = ($filtros['accion'] === $valor);
                            // Al hacer clic en el chip activo se quita ese filtro (toggle)
                            $url = $esActivo
                                ? urlBitacora($filtros, ['accion_f' => ''])
                                : urlBitacora($filtros, ['accion_f' => $valor]);
                        ?>
                            <a href="<?php echo $url; ?>" class="bitacora-chip chip-<?php echo $valor; ?> <?php echo $esActivo ? 'activo' : ''; ?> text-decoration-none">
                                <i class="bi <?php echo $conf['icono']; ?>"></i>
                                <?php echo $conf['texto']; ?>
                                <span class="chip-numero"><?php echo $cantidad; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card-body border-bottom bg-light">
                    <form method="GET" action="index.php" class="row g-2 align-items-end">
                        <input type="hidden" name="action" value="bitacora">

                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-secondary small mb-1">Acción</label>
                            <select name="accion_f" class="form-select form-select-sm">
                                <option value="">Todas</option>
                                <?php foreach ($confAccion as $valor => $conf): ?>
                                    <option value="<?php echo $valor; ?>"
                                        <?php echo ($filtros['accion'] === $valor) ? 'selected' : ''; ?>>
                                        <?php echo $conf['texto']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-secondary small mb-1">Usuario</label>
                            <input type="text" name="usuario" class="form-control form-control-sm"
                                placeholder="Nombre de usuario"
                                list="usuarios-bitacora"
                                value="<?php echo htmlspecialchars($filtros['usuario']); ?>">
                            <datalist id="usuarios-bitacora">
                                <?php foreach ($usuariosBitacora as $u): ?>
                                    <option value="<?php echo htmlspecialchars($u); ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
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
                                placeholder="Texto en descripción o módulo"
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
                    <div class="bitacora-scroll">
                        <table class="table table-hover align-middle mb-0 bitacora-tabla">
                            <thead>
                                <tr>
                                    <th><i class="bi bi-clock me-1"></i>Fecha y Hora</th>
                                    <th><i class="bi bi-person me-1"></i>Usuario</th>
                                    <th><i class="bi bi-lightning me-1"></i>Acción</th>
                                    <th><i class="bi bi-grid me-1"></i>Módulo</th>
                                    <th><i class="bi bi-card-text me-1"></i>Descripción</th>
                                    <th><i class="bi bi-hdd-network me-1"></i>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($registros)): ?>
                                    <?php foreach ($registros as $r):
                                        $conf = $confAccion[$r['accion']] ?? ['badge' => 'bg-light text-dark', 'texto' => $r['accion'], 'icono' => 'bi-dot'];
                                        list($fechaTxt, $fechaDestacada) = fechaLegible($r['fecha_hora']);
                                    ?>
                                        <tr>
                                            <td class="text-nowrap small <?php echo $fechaDestacada ? 'fecha-destacada' : 'text-muted'; ?>">
                                                <?php echo $fechaTxt; ?>
                                            </td>
                                            <td class="fw-bold text-dark">
                                                <?php echo htmlspecialchars($r['usuario_nombre']); ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $conf['badge']; ?> px-2 py-1">
                                                    <i class="bi <?php echo $conf['icono']; ?> me-1"></i><?php echo $conf['texto']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border px-2 py-1">
                                                    <?php echo htmlspecialchars($r['modulo']); ?>
                                                </span>
                                            </td>
                                            <td class="text-muted small bitacora-descripcion" title="<?php echo htmlspecialchars($r['descripcion']); ?>">
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
                                            <i class="bi bi-info-circle me-1"></i> No hay registros en la bitácora con los filtros aplicados.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginacion (ventana: anterior/siguiente + numeros con puntos) -->
                    <?php if ($paginas > 1): ?>
                        <nav class="mt-3">
                            <ul class="pagination pagination-sm justify-content-center mb-0 bitacora-paginacion">
                                <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo urlBitacora($filtros, ['pagina' => max(1, $pagina - 1)]); ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>

                                <?php foreach (ventanaPaginas($pagina, $paginas) as $p): ?>
                                    <?php if ($p === '…'): ?>
                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                    <?php else: ?>
                                        <li class="page-item <?php echo ($p == $pagina) ? 'active' : ''; ?>">
                                            <a class="page-link" href="<?php echo urlBitacora($filtros, ['pagina' => $p]); ?>">
                                                <?php echo $p; ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <li class="page-item <?php echo ($pagina >= $paginas) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo urlBitacora($filtros, ['pagina' => min($paginas, $pagina + 1)]); ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
