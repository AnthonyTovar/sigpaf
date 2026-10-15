<?php
// Verificar si la sesión fue invalidada por login en otro lugar
if (isset($_GET['error']) && $_GET['error'] === 'sesion_invalidada'):
?>
    <div style="background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 500;">
        <i class="bi bi-shield-lock" style="font-size: 1.5rem; margin-right: 8px;"></i>
        🔒 Tu sesión fue cerrada porque iniciaste sesión en otro dispositivo o navegador.
    </div>
<?php endif; ?>

<?php
// ========== HELPERS DE PRESENTACIÓN (datos reales) ==========

// Nombres de días y meses en español (abreviados)
$diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
$mesesAnio  = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

// Mapea el estatus de la actividad a las clases visuales existentes del sistema
if (!function_exists('estatusVisual')) {
    function estatusVisual($nomEstatus)
    {
        switch ($nomEstatus) {
            case 'Finalizada':
                return ['badge' => 'en-curso',  'dot' => 'green'];
            case 'En ejecución':
                return ['badge' => 'en-curso',  'dot' => 'orange'];
            case 'Planificada':
                return ['badge' => 'planificado', 'dot' => 'orange'];
            case 'Replanificada':
                return ['badge' => 'planificado', 'dot' => 'red'];
            case 'En espera':
            case 'Cancelada':
                return ['badge' => 'urgente',   'dot' => 'red'];
            default:
                return ['badge' => 'planificado', 'dot' => 'orange'];
        }
    }
}

// Etiqueta del día para el mini-calendario: Hoy / Mañana / día de semana
if (!function_exists('etiquetaDia')) {
    function etiquetaDia($fecha, $diasSemana)
    {
        $ts = strtotime($fecha);
        if (date('Y-m-d', $ts) === date('Y-m-d')) {
            return 'Hoy';
        }
        if (date('Y-m-d', $ts) === date('Y-m-d', strtotime('+1 day'))) {
            return 'Mañana';
        }
        return $diasSemana[date('w', $ts)];
    }
}
?>

<div class="dashboard-home" style="display: flex; flex-direction: column; flex: 1; min-height: 0; gap: 10px;">

    <!-- Tarjetas de estadísticas (datos reales) -->
    <div class="stats-row" style="flex-shrink: 0;">
        <div class="stat-card teal">
            <div class="stat-card-header">
                <div class="stat-card-icon"><i class="bi bi-activity"></i></div>
            </div>
            <div class="stat-card-value"><?php echo (int) $resumen['actividades_activas']; ?></div>
            <div class="stat-card-label">Actividades en Ejecución</div>
            <div class="stat-card-trend <?php echo $resumen['actividades_semana'] > 0 ? 'up' : ''; ?>">
                <?php echo $resumen['actividades_semana'] > 0
                    ? '↑ ' . (int) $resumen['actividades_semana'] . ' iniciada(s) esta semana'
                    : 'Sin inicios esta semana'; ?>
            </div>
        </div>
        <div class="stat-card coral">
            <div class="stat-card-header">
                <div class="stat-card-icon"><i class="bi bi-people"></i></div>
            </div>
            <div class="stat-card-value"><?php echo (int) $resumen['beneficiarios_total']; ?></div>
            <div class="stat-card-label">Beneficiarios</div>
            <div class="stat-card-trend">
                <?php echo (int) $resumen['personas_planificadas']; ?> persona(s) planificada(s) por atender
            </div>
        </div>
        <div class="stat-card blue">
            <div class="stat-card-header">
                <div class="stat-card-icon"><i class="bi bi-calendar-check"></i></div>
            </div>
            <div class="stat-card-value"><?php echo (int) $resumen['sesiones_hoy']; ?></div>
            <div class="stat-card-label">Sesiones Hoy</div>
            <div class="stat-card-trend <?php echo $resumen['sesiones_semana'] > 0 ? 'up' : ''; ?>">
                <?php echo $resumen['sesiones_semana'] > 0
                    ? (int) $resumen['sesiones_semana'] . ' registrada(s) esta semana'
                    : 'Sin sesiones esta semana'; ?>
            </div>
        </div>
        <div class="stat-card purple">
            <div class="stat-card-header">
                <div class="stat-card-icon"><i class="bi bi-geo-alt"></i></div>
            </div>
            <div class="stat-card-value"><?php echo (int) $resumen['lugares_total']; ?></div>
            <div class="stat-card-label">Lugares Registrados</div>
            <div class="stat-card-trend <?php echo $resumen['sedes_total'] > 0 ? 'up' : ''; ?>">
                <?php echo (int) $resumen['sedes_total']; ?> sede(s) principal(es)
            </div>
        </div>
    </div>

    <!-- Grid: Calendario + Actividades -->
    <div class="dashboard-grid" style="flex: 1; min-height: 0;">

        <!-- Calendario de actividades cercanas (datos reales) -->
        <div class="dashboard-section" style="display: flex; flex-direction: column; min-height: 0;">
            <div class="section-title" style="flex-shrink: 0;">
                <i class="bi bi-calendar-week"></i>
                Próximas Actividades
            </div>
            <div class="activity-calendar" style="flex-shrink: 0;">
                <?php if (!empty($proximas)): ?>
                    <?php foreach ($proximas as $act):
                        $ts = strtotime($act['fechainicioActividad']);
                        $esHoy = date('Y-m-d', $ts) === date('Y-m-d');
                    ?>
                        <div class="calendar-card <?php echo $esHoy ? 'today' : ''; ?>">
                            <div class="calendar-day-name"><?php echo etiquetaDia($act['fechainicioActividad'], $diasSemana); ?></div>
                            <div class="calendar-date"><?php echo date('j', $ts); ?></div>
                            <div class="calendar-month"><?php echo $mesesAnio[date('n', $ts) - 1]; ?></div>
                            <div class="calendar-event" title="<?php echo htmlspecialchars($act['nombreActividad']); ?>">
                                <?php echo htmlspecialchars(mb_strimwidth($act['nombreActividad'], 0, 22, '…')); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding: 20px; text-align: center; color: var(--text-muted, #6c757d); width: 100%;">
                        <i class="bi bi-calendar-x" style="font-size: 1.5rem; display: block; margin-bottom: 6px;"></i>
                        No hay actividades próximas registradas.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actividades recientes (datos reales) -->
        <div class="dashboard-section" style="display: flex; flex-direction: column; min-height: 0;">
            <div class="section-title" style="flex-shrink: 0;">
                <i class="bi bi-lightning-charge"></i>
                Estado de Actividades
            </div>
            <div class="activity-list" style="flex: 1; min-height: 0; overflow-y: auto;">
                <?php if (!empty($recientes)): ?>
                    <?php foreach ($recientes as $act):
                        $vis = estatusVisual($act['nomEstatus']);
                        $fInicio = date('d/m/Y', strtotime($act['fechainicioActividad']));
                        $fFin    = date('d/m/Y', strtotime($act['fechafinActividad']));
                    ?>
                        <div class="activity-item">
                            <div class="activity-dot <?php echo $vis['dot']; ?>"></div>
                            <div class="activity-info">
                                <div class="activity-title"><?php echo htmlspecialchars($act['nombreActividad']); ?></div>
                                <div class="activity-meta">
                                    <?php echo $fInicio; ?> - <?php echo $fFin; ?>
                                    • <?php echo (int) $act['cantPersoAtender']; ?> persona(s)
                                </div>
                            </div>
                            <span class="activity-badge <?php echo $vis['badge']; ?>">
                                <?php echo htmlspecialchars($act['nomEstatus']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding: 20px; text-align: center; color: var(--text-muted, #6c757d);">
                        <i class="bi bi-inbox" style="font-size: 1.5rem; display: block; margin-bottom: 6px;"></i>
                        Aún no hay actividades registradas en el sistema.
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Accesos rápidos -->
    <div class="dashboard-section" style="flex-shrink: 0; padding: 10px 14px;">
        <div class="section-title" style="margin-bottom: 8px;">
            <i class="bi bi-grid"></i>
            Accesos Rápidos
        </div>
        <div class="quick-access-bar">
            <a href="index.php?action=actividades" class="btn">
                <i class="bi bi-calendar-event"></i>Actividades
            </a>
            <a href="index.php?action=estatus" class="btn">
                <i class="bi bi-activity"></i>Estatus
            </a>
            <a href="index.php?action=empleados" class="btn">
                <i class="bi bi-person-vcard"></i>Empleados
            </a>
            <a href="index.php?action=docentes" class="btn">
                <i class="bi bi-person-workspace"></i>Docentes
            </a>
            <a href="index.php?action=tiposActividad" class="btn">
                <i class="bi bi-list-task"></i>Tipos Actividad
            </a>
            <a href="index.php?action=espacios" class="btn">
                <i class="bi bi-building"></i>Espacios
            </a>
            <a href="index.php?action=horarios" class="btn">
                <i class="bi bi-clock"></i>Horarios
            </a>
        </div>
    </div>

</div>
