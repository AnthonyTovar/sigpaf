<?php
// Zona horaria
date_default_timezone_set('America/Caracas');

// Banner incrustado en base64
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

<h2 class="titulo">Reporte de Actividades Registradas</h2>
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
    <p class="sin-resultados">No hay actividades registradas en el sistema.</p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th style="width: 7%;">Código</th>
            <th style="width: 23%;">Actividad</th>
            <th style="width: 10%;">Fechas</th>
            <th style="width: 7%;" class="texto-c">Sesiones</th>
            <th style="width: 18%;">Responsables</th>
            <th style="width: 14%;">Área</th>
            <th style="width: 9%;">Vértice</th>
            <th style="width: 12%;" class="texto-c">Estatus</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($actividades as $a):
            // Mismo criterio de colores que el resto del sistema:
            // verde = finalizada, rojo = cancelada/en espera,
            // amarillo = los demás estatus
            $est = strtolower($a['nomEstatus'] ?? '');
            $claseEstatus = (strpos($est, 'final') !== false) ? 'st-verde'
                : ((strpos($est, 'cancel') !== false || strpos($est, 'espera') !== false) ? 'st-rojo' : 'st-amarillo');
        ?>
        <tr>
            <td><span class="codigo"><?php echo htmlspecialchars((string) $a['idActividad']); ?></span></td>
            <td>
                <strong><?php echo htmlspecialchars($a['nombreActividad']); ?></strong><br>
                <span class="texto-mute"><?php echo htmlspecialchars($a['nomTipoActividad'] ?? '-'); ?></span>
            </td>
            <td class="texto-mute">
                <?php echo date('d/m/Y', strtotime($a['fechainicioActividad'])); ?><br>
                <?php echo date('d/m/Y', strtotime($a['fechafinActividad'])); ?>
            </td>
            <td class="texto-c">
                <strong><?php echo (int) $a['cantSesionesPlanificada']; ?></strong>
            </td>
            <td>
                <?php echo htmlspecialchars(trim(($a['nomEmpleado'] ?? '') . ' ' . ($a['apeEmpleado'] ?? ''))); ?><br>
                <span class="texto-mute"><?php echo htmlspecialchars(trim(($a['nombreDocente'] ?? '') . ' ' . ($a['apellidoDocente'] ?? ''))); ?></span>
            </td>
            <td><?php echo htmlspecialchars($a['nomAreaE'] ?? '-'); ?></td>
            <td><?php echo htmlspecialchars($a['nombreVertice'] ?? '-'); ?></td>
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
