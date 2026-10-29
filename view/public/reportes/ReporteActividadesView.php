<?php
// Reporte de actividades




date_default_timezone_set('America/Caracas');


$rutaBanner = __DIR__ . '/../img/banner_institucional.png';
$bannerSrc  = '';
if (file_exists($rutaBanner)) {
    $bannerSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($rutaBanner));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 20px 28px; }
    body {
        font-family: Helvetica, Arial, sans-serif;
        font-size: 10.5px;
        color: #1e293b;
        margin: 0;
        <?php if ($fondoBase64 !== ''): ?>
        /* Fondo institucional */
        background-image: url('<?php echo $fondoBase64; ?>');
        background-repeat: no-repeat;
        background-position: top left;
        <?php endif; ?>
    }
    .banner { width: 100%; }
    .titulo {
        text-align: center;
        margin: 14px 0 4px;
        font-size: 14px;
        color: #16305e;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .fecha  { text-align: center; font-size: 9.5px; color: #64748b; margin-bottom: 12px; }
    .filtros-aplicados {
        display: inline-block;
        margin-top: 4px;
        padding: 3px 10px;
        background: #eef6f5;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 9px;
        color: #334155;
    }
    /* ===== CAJAS DE RESUMEN ===== */
    .resumen { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin-bottom: 14px; }
    .resumen td {
        width: 25%;
        background: #16305e;
        color: #ffffff;
        text-align: center;
        padding: 8px 4px;
        border-radius: 4px;
    }
    .resumen .numero { font-size: 16px; font-weight: bold; display: block; }
    .resumen .etiqueta { font-size: 8px; display: block; margin-top: 2px; color: #cfe0f5; }
    /* ===== TABLA DE ACTIVIDADES ===== */
    table { width: 100%; border-collapse: collapse; }
    th {
        background: #16305e;
        color: #fff;
        padding: 7px 8px;
        text-align: left;
        font-size: 9px;
        text-transform: uppercase;
    }
    td { padding: 6px 8px; border-bottom: 1px solid #cbd5e1; vertical-align: top; font-size: 9.5px; }
    tr:nth-child(even) td { background: #eef6f5; }
    .codigo {
        background: #475569; color: #fff;
        padding: 2px 7px; border-radius: 6px; font-size: 9px;
    }
    .estatus { padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; color: #fff; }
    .st-verde { background: #059669; }
    .st-rojo  { background: #dc2626; }
    .texto-c { text-align: center; }
    .negrita { font-weight: bold; }
    .texto-mute { color: #64748b; font-size: 8.5px; }
    .sin-resultados {
        text-align: center;
        color: #64748b;
        margin-top: 50px;
        font-size: 11px;
    }
    .pie {
        position: fixed; bottom: -10px; left: 0; right: 0;
        text-align: center; font-size: 9px; color: #94a3b8;
    }
</style>
</head>
<body>

<?php if ($bannerSrc): ?>
<img class="banner" src="<?php echo $bannerSrc; ?>">
<?php endif; ?>

<h2 class="titulo">Reporte de Actividades Realizadas</h2>
<p class="fecha">
    Fecha de emisión: <?php echo date('d/m/Y h:i A'); ?>
    <br>
    <span class="filtros-aplicados">
        <strong>Correspondiente al mes de:</strong> <?php echo ucfirst($nombreMes) . ' ' . $filtros['anio']; ?>
        &nbsp;|&nbsp; <?php echo htmlspecialchars($filtrosTexto); ?>
    </span>
</p>

<!-- Resumen general del mes -->
<table class="resumen">
    <tr>
        <td>
            <span class="numero"><?php echo (int) $resumen['total_actividades']; ?></span>
            <span class="etiqueta">ACTIVIDADES</span>
        </td>
        <td>
            <span class="numero"><?php echo (int) $resumen['sesiones_registradas']; ?>/<?php echo (int) $resumen['sesiones_planificadas']; ?></span>
            <span class="etiqueta">SESIONES REGISTRADAS</span>
        </td>
        <td>
            <span class="numero"><?php echo (int) $resumen['total_asistencias']; ?></span>
            <span class="etiqueta">ASISTENCIAS</span>
        </td>
        <td>
            <span class="numero"><?php echo (int) $resumen['beneficiarios_atendidos']; ?></span>
            <span class="etiqueta">BENEFICIARIOS ATENDIDOS</span>
        </td>
    </tr>
</table>

<!-- Lista de actividades del mes -->
<?php if (empty($actividades)): ?>
    <p class="sin-resultados">No hay actividades finalizadas que coincidan con los filtros seleccionados.</p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th style="width: 7%;">Código</th>
            <th style="width: 22%;">Actividad</th>
            <th style="width: 12%;">Docente</th>
            <th style="width: 15%;">Responsable</th>
            <th style="width: 10%;">Período</th>
            <th style="width: 12%;">Lugar</th>
            <th style="width: 8%;" class="texto-c">Sesiones</th>
            <th style="width: 6%;" class="texto-c">Benef.</th>
            <th style="width: 8%;" class="texto-c">Estatus</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($actividades as $a):
            $est = strtolower($a['nomEstatus'] ?? '');
            $claseEstatus = (strpos($est, 'final') !== false) ? 'st-verde' : 'st-rojo';
        ?>
        <tr>
            <td><span class="codigo"><?php echo htmlspecialchars((string) $a['idActividad']); ?></span></td>
            <td>
                <strong><?php echo htmlspecialchars($a['nombreActividad']); ?></strong><br>
                <span class="texto-mute">
                    <?php echo htmlspecialchars($a['nomTipoActividad'] ?? '-'); ?>
                    | Meta: <?php echo (int) $a['cantPersoAtender']; ?> persona(s)
                </span>
            </td>
            <td><?php echo htmlspecialchars($a['docente'] ?? '-'); ?></td>
            <td>
                <?php echo htmlspecialchars($a['responsable'] ?? '-'); ?><br>
                <span class="texto-mute"><?php echo htmlspecialchars($a['nomUnidadEjecutora'] ?? ''); ?></span>
            </td>
            <td class="texto-mute">
                <?php echo date('d/m/Y', strtotime($a['fechainicioActividad'])); ?><br>
                <?php echo date('d/m/Y', strtotime($a['fechafinActividad'])); ?>
            </td>
            <td><?php echo htmlspecialchars($a['nomLugarActividad'] ?? '-'); ?></td>
            <td class="texto-c negrita">
                <?php echo (int) $a['sesiones_registradas']; ?>/<?php echo (int) $a['sesiones_planificadas']; ?>
            </td>
            <td class="texto-c negrita"><?php echo (int) $a['beneficiarios_atendidos']; ?></td>
            <td class="texto-c">
                <span class="estatus <?php echo $claseEstatus; ?>">
                    <?php echo htmlspecialchars($a['nomEstatus'] ?? '-'); ?>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<div class="pie">© <?php echo date('Y'); ?> — UPTYAB | FUNDACITE Yaracuy</div>

</body>
</html>
