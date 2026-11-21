<?php
// Reporte de estrategias de desarrollo




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
    table { width: 100%; border-collapse: collapse; }
    th {
        background: #16305e;
        color: #fff;
        padding: 7px 8px;
        text-align: left;
        font-size: 9.5px;
        text-transform: uppercase;
    }
    td { padding: 6px 8px; border-bottom: 1px solid #cbd5e1; vertical-align: top; }
    tr:nth-child(even) td { background: #eef6f5; }
    .codigo {
        background: #475569; color: #fff;
        padding: 2px 7px; border-radius: 6px; font-size: 9.5px;
    }
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

<h2 class="titulo">Reporte de Estrategias de Desarrollo Registradas</h2>
<p class="fecha">Fecha de emisión: <?php echo date('d/m/Y h:i A'); ?></p><p class="fecha">Fecha de emisión: <?php echo date('d/m/Y h:i A'); ?></p>
<?php if (!empty($filtro)): ?>
<p class="fecha" style="color:#b45309; font-weight:bold;">Filtro aplicado: "<?php echo htmlspecialchars($filtro); ?>" — <?php echo count($registros); ?> resultado(s)</p>
<?php endif; ?>

<?php if (empty($registros)): ?>
    <p class="sin-resultados">No hay estrategias de desarrollo registrados en el sistema.</p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th style="width:12%;">Código</th>
            <th style="width:30%;">Estrategia</th>
            <th>Descripción</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($registros as $r): ?>
        <tr>
            <td><span class="codigo"><?php echo htmlspecialchars((string)(($r['idEstDesarrollo'] ?? '') === '' ? '—' : $r['idEstDesarrollo'])); ?></span></td>
            <td><strong><?php echo htmlspecialchars((string)(($r['nomEstDesarrollo'] ?? '') === '' ? '—' : $r['nomEstDesarrollo'])); ?></strong></td>
            <td><?php echo htmlspecialchars((string)(($r['descEstDesarrollo'] ?? '') === '' ? '—' : $r['descEstDesarrollo'])); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<div class="pie">© <?php echo date('Y'); ?> — UPTYAB | FUNDACITE Yaracuy</div>

</body>
</html>
