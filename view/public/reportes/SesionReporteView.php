<?php
// Ficha PDF de una sesión: resumen + lista de beneficiarios

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
    .resumen {
        background: #eef6f5;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 14px;
    }
    .resumen table { width: 100%; border-collapse: collapse; }
    .resumen td { padding: 4px 6px; font-size: 9.5px; vertical-align: top; border: none; }
    .resumen .etiqueta { color: #64748b; font-size: 8.5px; text-transform: uppercase; }
    .subtitulo {
        margin: 10px 0 6px;
        font-size: 11px;
        color: #16305e;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    table.datos { width: 100%; border-collapse: collapse; }
    table.datos th {
        background: #16305e;
        color: #fff;
        padding: 7px 8px;
        text-align: left;
        font-size: 9px;
        text-transform: uppercase;
    }
    table.datos td { padding: 6px 8px; border-bottom: 1px solid #cbd5e1; vertical-align: top; font-size: 9.5px; }
    table.datos tr:nth-child(even) td { background: #eef6f5; }
    .texto-c { text-align: center; }
    .sin-resultados {
        text-align: center;
        color: #64748b;
        margin-top: 30px;
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

<h2 class="titulo">Ficha de Sesión — Lista de Beneficiarios</h2>
<p class="fecha">Fecha de emisión: <?php echo date('d/m/Y h:i A'); ?></p>

<div class="resumen">
    <table>
        <tr>
            <td colspan="3">
                <span class="etiqueta">Actividad</span><br>
                <strong><?php echo htmlspecialchars($actividad['nombreActividad'] ?? '-'); ?></strong>
                (<?php echo htmlspecialchars($actividad['nomTipoActividad'] ?? '-'); ?>)
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <span class="etiqueta">Objetivo de la actividad</span><br>
                <?php echo htmlspecialchars($actividad['objetivoActividad'] ?? '-'); ?>
            </td>
        </tr>
        <tr>
            <td style="width: 25%;">
                <span class="etiqueta">Sesión N°</span><br>
                <strong><?php echo htmlspecialchars((string) $sesion['nroSesionPlanificada']); ?></strong>
            </td>
            <td style="width: 25%;">
                <span class="etiqueta">Fecha de la sesión</span><br>
                <strong><?php echo date('d/m/Y', strtotime($sesion['fechaSesion'])); ?></strong>
            </td>
            <td style="width: 25%;">
                <span class="etiqueta">Tipo de entrega</span><br>
                <strong><?php echo htmlspecialchars($sesion['nomTipEntrega'] ?? 'Sin asignar'); ?></strong>
            </td>
        </tr>
        <?php if (!empty($sesion['logroActividad'])): ?>
        <tr>
            <td colspan="3">
                <span class="etiqueta">Logro de la sesión</span><br>
                <?php echo htmlspecialchars($sesion['logroActividad']); ?>
            </td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($sesion['observObstaculo'])): ?>
        <tr>
            <td colspan="3">
                <span class="etiqueta">Observaciones / obstáculos</span><br>
                <?php echo htmlspecialchars($sesion['observObstaculo']); ?>
            </td>
        </tr>
        <?php endif; ?>
    </table>
</div>

<h3 class="subtitulo">Beneficiarios de la sesión (<?php echo count($beneficiarios); ?>)</h3>

<?php if (empty($beneficiarios)): ?>
    <p class="sin-resultados">Esta sesión no tiene beneficiarios registrados.</p>
<?php else: ?>
<table class="datos">
    <thead>
        <tr>
            <th style="width: 5%;" class="texto-c">#</th>
            <th style="width: 15%;">Cédula</th>
            <th style="width: 27%;">Apellidos</th>
            <th style="width: 27%;">Nombres</th>
            <th style="width: 8%;" class="texto-c">Sexo</th>
            <th style="width: 18%;" class="texto-c">Fecha de Nac.</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($beneficiarios as $i => $b): ?>
        <tr>
            <td class="texto-c"><?php echo $i + 1; ?></td>
            <td><?php echo htmlspecialchars($b['cedulaBeneficiario']); ?></td>
            <td><?php echo htmlspecialchars($b['apellidosBeneficiario']); ?></td>
            <td><?php echo htmlspecialchars($b['nombresBeneficiario']); ?></td>
            <td class="texto-c"><?php echo htmlspecialchars($b['sexoBeneficiario'] ?? '-'); ?></td>
            <td class="texto-c">
                <?php echo !empty($b['fechaNacBeneficiario']) ? date('d/m/Y', strtotime($b['fechaNacBeneficiario'])) : '-'; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<div class="pie">© <?php echo date('Y'); ?> — UPTYAB | FUNDACITE Yaracuy</div>

</body>
</html>
