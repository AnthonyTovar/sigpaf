<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    @page { margin: 12mm 10mm 20mm 10mm; }
    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 10px;
        color: #1f2937;
        line-height: 1.4;
        <?php if ($fondoBase64 !== ''): ?>
        /* Fondo institucional sutil y muy difuminado (una por hoja) */
        background-image: url('<?php echo $fondoBase64; ?>');
        background-repeat: no-repeat;
        background-position: top left;
        <?php endif; ?>
    }

    /* ===== BANNER INSTITUCIONAL (arriba del documento) ===== */
    .banner { margin-bottom: 8px; }
    .banner img { width: 100%; }

    /* ===== ENCABEZADO ===== */
    .header { width: 100%; border-bottom: 3px solid #14467c; padding-bottom: 10px; margin-bottom: 12px; }
    .header td { vertical-align: middle; }
    .header-izq { width: 55%; }
    .header-der { width: 45%; text-align: right; }
    .institucion { font-size: 20px; font-weight: bold; color: #14467c; letter-spacing: 1px; }
    .sistema { font-size: 9px; color: #6b7280; margin-top: 2px; }
    .titulo-reporte { font-size: 14px; font-weight: bold; color: #14467c; }
    .titulo-mes { font-size: 11px; font-weight: bold; color: #333f50; margin-top: 2px; }
    .meta-reporte { font-size: 8.5px; color: #6b7280; margin-top: 3px; }

    /* ===== CAJA DE TOTAL ===== */
    .resumen { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin-bottom: 14px; }
    .resumen td {
        width: 25%;
        background: #14467c;
        color: #ffffff;
        text-align: center;
        padding: 8px 4px;
        border-radius: 4px;
    }
    .resumen .numero { font-size: 16px; font-weight: bold; display: block; }
    .resumen .etiqueta { font-size: 8px; display: block; margin-top: 2px; color: #cfe0f5; }

    /* ===== TÍTULOS DE SECCIÓN ===== */
    h2.seccion {
        font-size: 12px;
        color: #14467c;
        border-left: 4px solid #14467c;
        padding-left: 8px;
        margin: 14px 0 8px 0;
    }

    /* ===== TABLA DE DATOS ===== */
    table.tabla { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    table.tabla th {
        background: #14467c;
        color: #ffffff;
        font-size: 8.5px;
        padding: 5px 6px;
        text-align: left;
        border: 1px solid #14467c;
    }
    table.tabla td {
        font-size: 8.5px;
        padding: 4px 6px;
        border: 1px solid #d3dce6;
        vertical-align: top;
        word-wrap: break-word;
    }
    table.tabla tr:nth-child(even) td { background: #f6f9fc; }
    .texto-c { text-align: center; }
    .negrita { font-weight: bold; }

    .vacio {
        font-size: 8.5px;
        color: #9ca3af;
        font-style: italic;
        padding: 4px 0;
    }

    /* ===== PIE DE PÁGINA ===== */
    .footer {
        position: fixed;
        bottom: 20px;
        left: 0;
        right: 0;
    }
    .footer table { width: 100%; border-collapse: collapse; }
    .footer td {
        font-size: 8px;
        color: #6b7280;
        border-top: 1px solid #d3dce6;
        padding-top: 5px;
    }
    .footer .der { text-align: right; }
</style>
</head>
<body>

<!-- Pie de página en cada hoja (el número de página lo dibuja el
     controlador con el canvas de dompdf: {PAGE_NUM} de {PAGE_COUNT}) -->
<div class="footer">
    <table>
        <tr>
            <td>SIGPAF — Sistema de Gestión · <?php echo htmlspecialchars($tituloModulo); ?></td>
            <td class="der"></td>
        </tr>
    </table>
</div>

<!-- ===== BANNER INSTITUCIONAL ===== -->
<?php if ($bannerBase64 !== ''): ?>
<div class="banner">
    <img src="<?php echo $bannerBase64; ?>" alt="República Bolivariana de Venezuela">
</div>
<?php endif; ?>

<!-- ===== ENCABEZADO DEL DOCUMENTO ===== -->
<table class="header">
    <tr>
        <td class="header-izq">
            <div class="institucion">FUNDACITI</div>
            <div class="sistema">SIGPAF — Sistema de Gestión de Planificación de Actividades</div>
        </td>
        <td class="header-der">
            <div class="titulo-reporte"><?php echo htmlspecialchars(strtoupper($tituloModulo)); ?></div>
            <div class="titulo-mes">Módulo de Configuración del Sistema</div>
            <div class="meta-reporte">
                Emitido: <?php echo date('d/m/Y H:i'); ?> por
                <?php echo htmlspecialchars($_SESSION['username'] ?? 'usuario'); ?>
            </div>
        </td>
    </tr>
</table>

<!-- ===== TOTAL DE REGISTROS ===== -->
<table class="resumen">
    <tr>
        <td>
            <span class="numero"><?php echo (int) $total; ?></span>
            <span class="etiqueta">REGISTROS</span>
        </td>
    </tr>
</table>

<!-- ===== LISTADO ===== -->
<h2 class="seccion"><?php echo htmlspecialchars($tituloModulo); ?></h2>
<?php if (empty($filas)): ?>
    <p class="vacio">No hay registros en este módulo actualmente.</p>
<?php else: ?>
    <?php
    // Reparte el ancho: la primera columna (código) es más angosta
    $nCols = count($encabezados);
    $anchoPrimera = 10;
    $anchoResto = ($nCols > 1) ? round((100 - $anchoPrimera) / ($nCols - 1), 2) : 100;
    ?>
    <table class="tabla">
        <thead>
            <tr>
                <?php foreach ($encabezados as $i => $etiqueta): ?>
                    <th style="width: <?php echo $i === 0 ? $anchoPrimera : $anchoResto; ?>%;"><?php echo htmlspecialchars($etiqueta); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($filas as $fila): ?>
                <tr>
                    <?php foreach ($fila as $i => $valor): ?>
                        <td class="<?php echo $i === 0 ? 'negrita' : ''; ?>"><?php echo htmlspecialchars((string) $valor); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>
