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
                    <h5 class="mb-0"><i class="bi bi-activity me-2"></i> Gestión de Estatus</h5>
                    <div class="d-flex gap-2">
                        <a href="index.php?action=exportarPdfEstatus" target="_blank" id="btnExportarPdf" class="btn btn-danger btn-sm fw-bold">
                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Exportar PDF
                        </a>
                    </div>
                </div>

                <div class="card-body">
                                        <!-- Buscador del listado -->
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <div class="input-group input-group-sm" style="max-width: 320px;">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" id="buscadorModulo" class="form-control" placeholder="Buscar en el listado...">
                            <button class="btn btn-outline-secondary d-none" type="button" id="btnLimpiarBusqueda" title="Limpiar búsqueda">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <span id="contadorFiltro" class="small"></span>
                    </div>
<div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre del Estatus</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
// Vista de estatus


 if (!empty($estatus)): ?>
                                    <?php foreach ($estatus as $e): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary px-2 py-1">
                                                    <?php echo $e['idEstatus']; ?>
                                                </span></td>

                                            <td class="fw-bold text-dark">
                                                <?php echo $e['nomEstatus']; ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo $e['descEstatus']; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            <i class="bi bi-info-circle me-1"></i> No hay estatus registrados actualmente.
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

<!-- Módulo de solo lectura: los estatus se listan pero no se pueden agregar, modificar ni eliminar. -->
