<?php
// Reporte de seguimiento de actividades

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
    body { font-family: Helvetica, Arial, sans-serif; font-size: 10.5px; color: #1e293b; margin: 0; }
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
    .st-verde    { background: #059669; }
    .st-rojo     { background: #dc2626; }
    .st-amarillo { background: #d97706; }
    .texto-c { text-align: center; }
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

<h2 class="titulo">Reporte de Seguimiento de Actividades</h2>
<p class="fecha">
    Fecha de emisión: <?php echo date('d/m/Y h:i A'); ?>
    <br>
    <span class="filtros-aplicados">
        <strong>Filtros aplicados:</strong>
        <?php
            echo !empty($filtrosDesc)
                ? implode(' &nbsp;|&nbsp; ', $filtrosDesc)
                : 'Todas las actividades &nbsp;|&nbsp; Todos los estatus';
        ?>
    </span>
</p>

<?php if (empty($actividades)): ?>
    <p class="sin-resultados">No hay actividades con seguimiento para los filtros seleccionados.</p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th style="width: 8%;">Código</th>
            <th style="width: 30%;">Actividad</th>
            <th style="width: 12%;" class="texto-c">Período</th>
            <th style="width: 12%;" class="texto-c">Sesiones (Reg/Plan)</th>
            <th style="width: 10%;" class="texto-c">Avance</th>
            <th style="width: 12%;" class="texto-c">Beneficiarios</th>
            <th style="width: 16%;" class="texto-c">Estatus</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($actividades as $a):
            $plan = (int) $a['sesiones_planificadas'];
            $reg = (int) $a['sesiones_registradas'];
            $pct = $plan > 0 ? round(($reg / $plan) * 100) : 0;
            $est = strtolower($a['nomEstatus'] ?? '');
            $claseEstatus = ((strpos($est, 'final') !== false || strpos($est, 'ejecutad') !== false)) ? 'st-verde'
                : ((strpos($est, 'cancel') !== false || strpos($est, 'espera') !== false) ? 'st-rojo' : 'st-amarillo');
        ?>
        <tr>
            <td><span class="codigo"><?php echo htmlspecialchars((string) $a['idActividad']); ?></span></td>
            <td>
                <strong><?php echo htmlspecialchars($a['nombreActividad']); ?></strong><br>
                <span class="texto-mute"><?php echo htmlspecialchars($a['nomTipoActividad'] ?? '-'); ?></span>
            </td>
            <td class="texto-c texto-mute">
                <?php echo date('d/m/Y', strtotime($a['fechainicioActividad'])); ?><br>
                <?php echo date('d/m/Y', strtotime($a['fechafinActividad'])); ?>
            </td>
            <td class="texto-c"><strong><?php echo $reg; ?>/<?php echo $plan; ?></strong></td>
            <td class="texto-c"><?php echo $pct; ?>%</td>
            <td class="texto-c"><strong><?php echo (int) $a['beneficiarios_atendidos']; ?></strong></td>
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
