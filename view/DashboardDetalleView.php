<?php
// Vista de listados del panel principal

$configuracion = [
    'actividades' => [
        'titulo' => 'Actividades en Ejecución',
        'descripcion' => 'Actividades que se encuentran actualmente en desarrollo.',
        'icono' => 'bi-activity',
        'vacio' => 'No hay actividades en ejecución en este momento.'
    ],
    'beneficiarios' => [
        'titulo' => 'Beneficiarios Registrados',
        'descripcion' => 'Listado histórico de beneficiarios registrados en el sistema.',
        'icono' => 'bi-people',
        'vacio' => 'No hay beneficiarios registrados en el sistema.'
    ],
    'sesiones' => [
        'titulo' => 'Sesiones de Hoy',
        'descripcion' => 'Sesiones programadas o registradas para la fecha de hoy.',
        'icono' => 'bi-calendar-check',
        'vacio' => 'No hay sesiones registradas para hoy.'
    ],
    'lugares' => [
        'titulo' => 'Lugares Registrados',
        'descripcion' => 'Lugares disponibles para la realización de actividades.',
        'icono' => 'bi-geo-alt',
        'vacio' => 'No hay lugares registrados en el sistema.'
    ]
];
$actual = $configuracion[$tipo] ?? $configuracion['actividades'];

$texto = static function ($valor) {
    return htmlspecialchars((string) ($valor === null || $valor === '' ? 'No especificado' : $valor), ENT_QUOTES, 'UTF-8');
};
$fecha = static function ($valor) {
    return !empty($valor) ? date('d/m/Y', strtotime($valor)) : 'No especificada';
};
?>

<link rel="stylesheet" href="view/public/css/dashboard-listados.css">

<div class="dashboard-detalle">
    <div class="detalle-encabezado">
        <div class="detalle-titulo-grupo">
            <div class="detalle-icono"><i class="bi <?php echo $actual['icono']; ?>"></i></div>
            <div>
                <h4><?php echo $actual['titulo']; ?></h4>
                <p><?php echo $actual['descripcion']; ?></p>
            </div>
        </div>
        <div class="detalle-encabezado-acciones">
            <span class="detalle-total"><?php echo count($registros); ?> registro(s)</span>
            <a href="index.php?action=dashboard" class="btn-detalle-volver">
                <i class="bi bi-arrow-left"></i> Volver al dashboard
            </a>
        </div>
    </div>

    <?php if ($tipo === 'actividades'): ?>
        <?php if (empty($registros)): ?>
            <div class="detalle-vacio">
                <i class="bi bi-calendar-x"></i>
                <h5>Sin actividades en ejecución</h5>
                <p><?php echo $actual['vacio']; ?></p>
            </div>
        <?php else: ?>
            <div class="detalle-grid">
                <?php foreach ($registros as $r): ?>
                    <article class="detalle-tarjeta">
                        <div class="detalle-tarjeta-superior">
                            <div>
                                <span class="detalle-codigo"><?php echo $texto($r['idActividad']); ?></span>
                                <h5><?php echo $texto($r['nombreActividad']); ?></h5>
                            </div>
                            <span class="detalle-badge"><?php echo $texto($r['nomEstatus']); ?></span>
                        </div>
                        <p class="detalle-descripcion"><?php echo $texto($r['descActividad']); ?></p>
                        <div class="detalle-datos">
                            <div class="detalle-dato">
                                <i class="bi bi-calendar-range"></i>
                                <span>Del <?php echo $fecha($r['fechainicioActividad']); ?> al <?php echo $fecha($r['fechafinActividad']); ?></span>
                            </div>
                            <div class="detalle-dato">
                                <i class="bi bi-bookmark"></i>
                                <span><?php echo $texto($r['nomTipoActividad']); ?> · <?php echo $texto($r['nomAreaE']); ?></span>
                            </div>
                            <div class="detalle-dato">
                                <i class="bi bi-geo-alt"></i>
                                <span><?php echo $texto($r['lugares']); ?></span>
                            </div>
                            <div class="detalle-dato">
                                <i class="bi bi-clock"></i>
                                <span><?php echo $texto($r['horarios']); ?></span>
                            </div>
                        </div>
                        <div class="detalle-pie-tarjeta">
                            <span class="detalle-mini-dato"><i class="bi bi-people"></i> <?php echo (int) $r['cantPersoAtender']; ?> persona(s) propuestas</span>
                            <span class="detalle-mini-dato"><i class="bi bi-collection"></i> <?php echo (int) $r['cantSesionesPlanificada']; ?> sesión(es)</span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php elseif ($tipo === 'sesiones'): ?>
        <?php if (empty($registros)): ?>
            <div class="detalle-vacio">
                <i class="bi bi-calendar2-x"></i>
                <h5>Sin sesiones para hoy</h5>
                <p><?php echo $actual['vacio']; ?></p>
            </div>
        <?php else: ?>
            <div class="detalle-grid">
                <?php foreach ($registros as $r): ?>
                    <article class="detalle-tarjeta sesion">
                        <div class="detalle-tarjeta-superior">
                            <div>
                                <span class="detalle-codigo">SESIÓN N.º <?php echo (int) $r['nroSesionPlanificada']; ?></span>
                                <h5><?php echo $texto($r['nombreActividad']); ?></h5>
                            </div>
                            <span class="detalle-badge"><?php echo $texto($r['nomEstatus']); ?></span>
                        </div>
                        <p class="detalle-descripcion"><?php echo $texto($r['logroActividad']); ?></p>
                        <div class="detalle-datos">
                            <div class="detalle-dato">
                                <i class="bi bi-calendar-day"></i>
                                <span><?php echo $fecha($r['fechaSesion']); ?></span>
                            </div>
                            <div class="detalle-dato">
                                <i class="bi bi-clock"></i>
                                <span><?php echo $texto($r['nomHorario']); ?></span>
                            </div>
                            <div class="detalle-dato">
                                <i class="bi bi-geo-alt"></i>
                                <span><?php echo $texto($r['nomLugarActividad']); ?></span>
                            </div>
                            <div class="detalle-dato">
                                <i class="bi bi-box-seam"></i>
                                <span><?php echo $texto($r['nomTipEntrega']); ?></span>
                            </div>
                        </div>
                        <div class="detalle-pie-tarjeta">
                            <span class="detalle-mini-dato"><i class="bi bi-people"></i> <?php echo (int) $r['totalAsistentes']; ?> asistente(s)</span>
                            <span class="detalle-mini-dato"><i class="bi bi-upc"></i> <?php echo $texto($r['idSegActividad']); ?></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php elseif ($tipo === 'lugares'): ?>
        <?php if (empty($registros)): ?>
            <div class="detalle-vacio">
                <i class="bi bi-geo"></i>
                <h5>Sin lugares registrados</h5>
                <p><?php echo $actual['vacio']; ?></p>
            </div>
        <?php else: ?>
            <div class="detalle-grid">
                <?php foreach ($registros as $r): ?>
                    <article class="detalle-tarjeta lugar">
                        <div class="detalle-tarjeta-superior">
                            <div>
                                <span class="detalle-codigo"><?php echo $texto($r['idLugarActividad']); ?></span>
                                <h5><?php echo $texto($r['nomLugarActividad']); ?></h5>
                            </div>
                            <?php if ((int) $r['esSede'] === 1): ?>
                                <span class="detalle-badge morado"><i class="bi bi-building me-1"></i> Sede</span>
                            <?php endif; ?>
                        </div>
                        <p class="detalle-descripcion"><?php echo $texto($r['desLugarActividad']); ?></p>
                        <div class="detalle-datos">
                            <div class="detalle-dato">
                                <i class="bi bi-signpost"></i>
                                <span><?php echo $texto($r['direccion']); ?></span>
                            </div>
                            <div class="detalle-dato">
                                <i class="bi bi-pin-map"></i>
                                <span><?php echo $texto($r['nombreParroquia']); ?>, <?php echo $texto($r['nombreMunicipio']); ?>, <?php echo $texto($r['nombreEstado']); ?></span>
                            </div>
                        </div>
                        <div class="detalle-pie-tarjeta">
                            <span class="detalle-mini-dato"><i class="bi bi-calendar-event"></i> <?php echo (int) $r['totalActividades']; ?> actividad(es)</span>
                            <span class="detalle-mini-dato"><i class="bi bi-grid"></i> <?php echo (int) $r['totalEspacios']; ?> espacio(s)</span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php elseif ($tipo === 'beneficiarios'): ?>
        <div class="beneficiarios-panel">
            <div class="beneficiarios-herramientas">
                <div class="buscador-beneficiarios">
                    <i class="bi bi-search"></i>
                    <input type="search" id="buscadorBeneficiarios"
                        placeholder="Buscar por cédula, nombre o apellido..."
                        autocomplete="off" aria-label="Buscar beneficiarios">
                    <button type="button" class="btn-limpiar-busqueda" id="limpiarBusquedaBeneficiarios"
                        title="Limpiar búsqueda" aria-label="Limpiar búsqueda"><i class="bi bi-x-lg"></i></button>
                </div>

                <?php if ($puedeExportar): ?>
                    <form method="post" id="formExportarBeneficiarios" class="beneficiarios-exportar">
                        <button type="submit" class="btn-exportar-dashboard pdf"
                            formaction="index.php?action=exportarBeneficiariosDashboardPdf" formtarget="_blank">
                            <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
                        </button>
                        <button type="submit" class="btn-exportar-dashboard excel"
                            formaction="index.php?action=exportarBeneficiariosDashboardExcel">
                            <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="beneficiarios-estado">
                <span id="beneficiariosVisibles"><?php echo count($registros); ?> de <?php echo count($registros); ?> beneficiario(s)</span>
                <?php if ($puedeExportar): ?>
                    <div class="seleccion-beneficiarios">
                        <span id="textoExportacionBeneficiarios">Sin selección: se exportarán todos los beneficiarios registrados.</span>
                        <strong id="beneficiariosSeleccionados">0 seleccionado(s)</strong>
                        <button type="button" id="limpiarSeleccionBeneficiarios">Limpiar selección</button>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tabla-beneficiarios-contenedor">
                <table class="tabla-beneficiarios">
                    <thead>
                        <tr>
                            <?php if ($puedeExportar): ?>
                                <th class="text-center" title="Seleccionar todos los resultados visibles">
                                    <input type="checkbox" id="seleccionarBeneficiariosVisibles" class="check-beneficiario"
                                        aria-label="Seleccionar todos los beneficiarios visibles" <?php echo empty($registros) ? 'disabled' : ''; ?>>
                                </th>
                            <?php endif; ?>
                            <th>Código</th>
                            <th>Cédula</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Fecha Nac.</th>
                            <th class="text-center">Edad</th>
                            <th>Nacionalidad</th>
                            <th class="text-center">Sexo</th>
                            <th class="text-center">Asistencias</th>
                            <th class="text-center">Actividades</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registros as $r): ?>
                            <tr class="fila-beneficiario" data-busqueda="<?php echo $texto($r['cedulaBeneficiario'] . ' ' . $r['nombresBeneficiario'] . ' ' . $r['apellidosBeneficiario']); ?>">
                                <?php if ($puedeExportar): ?>
                                    <td class="text-center">
                                        <input type="checkbox" class="check-beneficiario" name="ids[]"
                                            value="<?php echo $texto($r['idBeneficiario']); ?>" form="formExportarBeneficiarios"
                                            aria-label="Seleccionar a <?php echo $texto($r['nombresBeneficiario'] . ' ' . $r['apellidosBeneficiario']); ?>">
                                    </td>
                                <?php endif; ?>
                                <td class="codigo-beneficiario"><?php echo $texto($r['idBeneficiario']); ?></td>
                                <td class="cedula-beneficiario"><?php echo $texto($r['cedulaBeneficiario']); ?></td>
                                <td class="nombre-beneficiario"><?php echo $texto($r['nombresBeneficiario']); ?></td>
                                <td><?php echo $texto($r['apellidosBeneficiario']); ?></td>
                                <td><?php echo $fecha($r['fechaNacBeneficiario']); ?></td>
                                <td class="text-center"><?php echo $r['edad'] === null ? '—' : (int) $r['edad']; ?></td>
                                <td><?php echo $texto($r['nacionalidad']); ?></td>
                                <td class="text-center"><?php echo $texto($r['sexoBeneficiario']); ?></td>
                                <td class="text-center"><?php echo (int) $r['totalAsistencias']; ?></td>
                                <td class="text-center"><?php echo (int) $r['totalActividades']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="sinResultadosBeneficiarios" <?php echo !empty($registros) ? 'hidden' : ''; ?>>
                            <td colspan="<?php echo $puedeExportar ? 11 : 10; ?>">
                                <i class="bi bi-search me-2"></i>No se encontraron beneficiarios con ese criterio.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="view/public/js/dashboard-listados.js"></script>
