<?php
// Vista de reportes


$queryPdf = http_build_query([
    'action' => 'exportarPdfReportes',
    'mes' => $filtros['mes'],
    'anio' => $filtros['anio'],
    'idDocente' => $filtros['idDocente']
]);

$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

$totalAct = (int) ($resumen['total_actividades'] ?? 0);
$totalPlan = (int) ($resumen['sesiones_planificadas'] ?? 0);
$totalReg = (int) ($resumen['sesiones_registradas'] ?? 0);
$totalAsis = (int) ($resumen['total_asistencias'] ?? 0);
$totalBen = (int) ($resumen['beneficiarios_atendidos'] ?? 0);
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-bar-graph me-2"></i> Reportes</h5>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <a href="index.php?action=exportarExcelSeguimiento" class="btn btn-success btn-sm fw-bold"
                            title="Descargar la ficha de seguimiento">
                            <i class="bi bi-file-earmark-excel me-1"></i> Exportar ficha de seguimiento
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filtros (reporte por mes) -->
                    <form method="get" action="index.php" class="row g-3 align-items-end mb-4">
                        <input type="hidden" name="action" value="reportes">

                        <div class="col-md-3 col-sm-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Mes</label>
                            <select name="mes" class="form-select">
                                <?php foreach ($meses as $num => $nombre): ?>
                                    <option value="<?php echo $num; ?>"
                                        <?php echo (int) $filtros['mes'] === $num ? 'selected' : ''; ?>>
                                        <?php echo $nombre; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Año</label>
                            <input type="number" name="anio" class="form-control" min="2000" max="2100"
                                value="<?php echo (int) $filtros['anio']; ?>">
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label fw-semibold text-secondary mb-1">Docente</label>
                            <select name="idDocente" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach ($docentes as $d): ?>
                                    <option value="<?php echo $d['idDocente']; ?>"
                                        <?php echo $filtros['idDocente'] === $d['idDocente'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['apellidoDocente'] . ', ' . $d['nombreDocente']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="bi bi-funnel me-1"></i> Filtrar
                            </button>
                            <a href="index.php?action=reportes" class="btn btn-outline-secondary" title="Volver al mes actual">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        </div>
                    </form>

                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Actividades de:</strong> <?php echo htmlspecialchars($filtrosTexto); ?>
                    </p>

                    <!-- Resumen -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body py-3 text-center">
                                    <div class="fs-4 fw-bold text-primary"><?php echo $totalAct; ?></div>
                                    <div class="small text-muted">Actividades</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body py-3 text-center">
                                    <div class="fs-4 fw-bold text-success"><?php echo $totalReg; ?><span class="text-muted fs-6 fw-normal">/<?php echo $totalPlan; ?></span></div>
                                    <div class="small text-muted">Sesiones registradas</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body py-3 text-center">
                                    <div class="fs-4 fw-bold text-info"><?php echo $totalAsis; ?></div>
                                    <div class="small text-muted">Asistencias</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body py-3 text-center">
                                    <div class="fs-4 fw-bold text-warning"><?php echo $totalBen; ?></div>
                                    <div class="small text-muted">Beneficiarios atendidos</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vista previa del documento -->
                    <div id="area-reporte">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h6 class="mb-0 text-secondary"><i class="bi bi-table me-1"></i> Vista previa del documento</h6>
                        <a href="index.php?<?php echo $queryPdf; ?>" class="btn btn-danger btn-sm fw-bold"
                            target="_blank" title="Ver el reporte en PDF con estos filtros">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Exportar PDF
                        </a>
                    </div>

                    <!-- Tabla -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Actividad</th>
                                    <th>Tipo</th>
                                    <th>Docente</th>
                                    <th>Responsable</th>
                                    <th class="text-center">Período</th>
                                    <th>Lugar</th>
                                    <th class="text-center">Estatus</th>
                                    <th class="text-center">Sesiones</th>
                                    <th class="text-center">Benef.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($actividades)): ?>
                                    <?php foreach ($actividades as $a):
                                        $plan = (int) $a['sesiones_planificadas'];
                                        $reg = (int) $a['sesiones_registradas'];
                                        $est = strtolower($a['nomEstatus'] ?? '');
                                        $badge = ((strpos($est, 'final') !== false || strpos($est, 'ejecutad') !== false)) ? 'bg-success'
                                            : ((strpos($est, 'cancel') !== false || strpos($est, 'espera') !== false) ? 'bg-danger' : 'bg-warning text-dark');
                                    ?>
                                        <tr>
                                            <td class="fw-bold text-dark">
                                                <?php echo htmlspecialchars($a['nombreActividad']); ?>
                                                <div class="text-muted small fw-normal">Meta: <?php echo (int) $a['cantPersoAtender']; ?> persona(s)</div>
                                            </td>
                                            <td class="small"><?php echo htmlspecialchars($a['nomTipoActividad'] ?? '-'); ?></td>
                                            <td class="small"><?php echo htmlspecialchars($a['docente'] ?? '-'); ?></td>
                                            <td class="small">
                                                <?php echo htmlspecialchars($a['responsable'] ?? '-'); ?>
                                                <?php if (!empty($a['nomUnidadEjecutora'])): ?>
                                                    <div class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($a['nomUnidadEjecutora']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center text-muted small text-nowrap">
                                                <?php echo date('d/m/Y', strtotime($a['fechainicioActividad'])); ?><br>
                                                <?php echo date('d/m/Y', strtotime($a['fechafinActividad'])); ?>
                                            </td>
                                            <td class="small"><?php echo htmlspecialchars($a['nomLugarActividad'] ?? '-'); ?></td>
                                            <td class="text-center"><span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($a['nomEstatus'] ?? '-'); ?></span></td>
                                            <td class="text-center small fw-bold"><?php echo $reg; ?>/<?php echo $plan; ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-info text-dark px-2 py-1">
                                                    <i class="bi bi-people-fill me-1"></i><?php echo (int) $a['beneficiarios_atendidos']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <i class="bi bi-info-circle me-1"></i> No hay actividades que coincidan con los filtros.
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
</div>

<!-- Animación de exportación de la ficha -->
<div class="modal fade" id="modalExportandoFicha" tabindex="-1" aria-labelledby="tituloExportandoFicha"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <div id="estadoGenerandoFicha">
                    <div class="spinner-border text-success" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Generando archivo</span>
                    </div>
                    <h5 class="mt-3 mb-2" id="tituloExportandoFicha">Generando ficha de seguimiento</h5>
                    <p class="text-muted small mb-0">
                        Estamos organizando las actividades y los beneficiarios. La descarga comenzará automáticamente.
                    </p>
                </div>
                <div id="estadoErrorFicha" class="d-none">
                    <i class="bi bi-exclamation-circle text-danger" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 mb-2">No se pudo exportar la ficha</h5>
                    <p class="text-muted small" id="mensajeErrorFicha"></p>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Descarga de la ficha con animación de progreso
document.addEventListener('DOMContentLoaded', function () {
    const boton = document.querySelector('a[href="index.php?action=exportarExcelSeguimiento"]');
    const elementoModal = document.getElementById('modalExportandoFicha');

    if (!boton || !elementoModal || typeof bootstrap === 'undefined') return;

    const modal = new bootstrap.Modal(elementoModal);
    const estadoGenerando = document.getElementById('estadoGenerandoFicha');
    const estadoError = document.getElementById('estadoErrorFicha');
    const mensajeError = document.getElementById('mensajeErrorFicha');
    const contenidoBoton = boton.innerHTML;
    let exportando = false;

    boton.addEventListener('click', async function (event) {
        event.preventDefault();
        if (exportando) return;

        exportando = true;
        boton.classList.add('disabled');
        boton.setAttribute('aria-disabled', 'true');
        boton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generando...';
        estadoGenerando.classList.remove('d-none');
        estadoError.classList.add('d-none');
        mensajeError.textContent = '';
        modal.show();

        try {
            const respuesta = await fetch(boton.href, { credentials: 'same-origin' });

            if (!respuesta.ok) {
                const detalle = await respuesta.text();
                throw new Error(detalle.trim() || 'El servidor no pudo generar el archivo.');
            }

            const tipoArchivo = respuesta.headers.get('Content-Type') || '';
            if (!tipoArchivo.includes('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')) {
                throw new Error('El servidor no devolvió un archivo Excel válido.');
            }

            const archivo = await respuesta.blob();
            if (archivo.size === 0) throw new Error('El archivo generado está vacío.');

            const url = URL.createObjectURL(archivo);
            const descarga = document.createElement('a');
            descarga.href = url;
            descarga.download = 'Ficha de Seguimiento AE.xlsx';
            document.body.appendChild(descarga);
            descarga.click();
            descarga.remove();
            setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
            modal.hide();
        } catch (error) {
            estadoGenerando.classList.add('d-none');
            estadoError.classList.remove('d-none');
            mensajeError.textContent = error.message || 'Ocurrió un error durante la exportación.';
        } finally {
            exportando = false;
            boton.classList.remove('disabled');
            boton.removeAttribute('aria-disabled');
            boton.innerHTML = contenidoBoton;
        }
    });
});
</script>
