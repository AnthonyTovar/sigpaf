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

    /* ===== CAJAS DE RESUMEN ===== */
    .resumen { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin-bottom: 14px; }
    .resumen td {
        width: 20%;
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

    /* ===== TABLAS DE DATOS ===== */
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
    /* Nota: dompdf posiciona "bottom" respecto al BORDE de la página
       (valores negativos quedan fuera y se recortan): se usa 20px. */
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
            <td>SIGPAF — Sistema de Gestión · Reporte de Actividades FUNDACITI</td>
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

<!-- ===== ENCABEZADO DEL REPORTE ===== -->
<table class="header">
    <tr>
        <td class="header-izq">
            <div class="institucion">FUNDACITI</div>
            <div class="sistema">SIGPAF — Sistema de Gestión de Planificación de Actividades</div>
        </td>
        <td class="header-der">
            <div class="titulo-reporte">REPORTE DE ACTIVIDADES REALIZADAS</div>
            <div class="titulo-mes">Correspondiente al mes de <?php echo ucfirst($nombreMes) . ' ' . $filtros['anio']; ?></div>
            <div class="meta-reporte">
                Emitido: <?php echo date('d/m/Y H:i'); ?> por
                <?php echo htmlspecialchars($_SESSION['username'] ?? 'usuario'); ?>
            </div>
        </td>
    </tr>
</table>

<!-- ===== RESUMEN GENERAL ===== -->
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

<!-- ===== RESUMEN DE ACTIVIDADES DEL MES ===== -->
<h2 class="seccion">Resumen de actividades del mes de <?php echo ucfirst($nombreMes) . ' ' . $filtros['anio']; ?></h2>
<?php if (empty($actividades)): ?>
    <p class="vacio">No hay actividades que coincidan con los filtros seleccionados.</p>
<?php else: ?>
    <table class="tabla">
        <thead>
            <tr>
                <th style="width: 4%;">N°</th>
                <th style="width: 24%;">Actividad</th>
                <th style="width: 11%;">Tipo</th>
                <th style="width: 13%;">Docente</th>
                <th style="width: 14%;">Responsable</th>
                <th style="width: 10%;">Período</th>
                <th style="width: 10%;">Estatus</th>
                <th style="width: 7%;" class="texto-c">Sesiones</th>
                <th style="width: 7%;" class="texto-c">Benef.</th>
            </tr>
        </thead>
        <tbody>
            <?php $n = 1; foreach ($actividades as $a): ?>
                <tr>
                    <td class="texto-c"><?php echo $n++; ?></td>
                    <td class="negrita"><?php echo htmlspecialchars($a['nombreActividad']); ?></td>
                    <td><?php echo htmlspecialchars($a['nomTipoActividad'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($a['docente'] ?? '-'); ?></td>
                    <td>
                        <?php echo htmlspecialchars($a['responsable'] ?? '-'); ?>
                        <?php if (!empty($a['nomUnidadEjecutora'])): ?>
                            <br><span style="color: #6b7280; font-size: 7.5px;"><?php echo htmlspecialchars($a['nomUnidadEjecutora']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo date('d/m/Y', strtotime($a['fechainicioActividad'])); ?> -
                        <?php echo date('d/m/Y', strtotime($a['fechafinActividad'])); ?>
                    </td>
                    <td><?php echo htmlspecialchars($a['nomEstatus'] ?? '-'); ?></td>
                    <td class="texto-c negrita"><?php echo (int) $a['sesiones_registradas']; ?>/<?php echo (int) $a['sesiones_planificadas']; ?></td>
                    <td class="texto-c negrita"><?php echo (int) $a['beneficiarios_atendidos']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>
