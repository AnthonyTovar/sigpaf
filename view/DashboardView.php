<?php
// Verificar si la sesión fue invalidada por login en otro lugar
if (isset($_GET['error']) && $_GET['error'] === 'sesion_invalidada'): 
?>
    <div style="background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 500;">
        <i class="bi bi-shield-lock" style="font-size: 1.5rem; margin-right: 8px;"></i>
        🔒 Tu sesión fue cerrada porque iniciaste sesión en otro dispositivo o navegador.
    </div>
<?php endif; ?>

<div class="dashboard-home" style="display: flex; flex-direction: column; flex: 1; min-height: 0; gap: 10px;">

    <!-- Tarjetas de estadísticas -->
    <div class="stats-row" style="flex-shrink: 0;">
        <div class="stat-card teal">
            <div class="stat-card-header">
                <div class="stat-card-icon"><i class="bi bi-activity"></i></div>
            </div>
            <div class="stat-card-value">12</div>
            <div class="stat-card-label">Actividades Activas</div>
            <div class="stat-card-trend up">↑ 3 esta semana</div>
        </div>
        <div class="stat-card coral">
            <div class="stat-card-header">
                <div class="stat-card-icon"><i class="bi bi-people"></i></div>
            </div>
            <div class="stat-card-value">248</div>
            <div class="stat-card-label">Beneficiarios</div>
            <div class="stat-card-trend up">↑ 15% vs mes pasado</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-card-header">
                <div class="stat-card-icon"><i class="bi bi-calendar-check"></i></div>
            </div>
            <div class="stat-card-value">5</div>
            <div class="stat-card-label">Sesiones Hoy</div>
            <div class="stat-card-trend">2 completadas</div>
        </div>
        <div class="stat-card purple">
            <div class="stat-card-header">
                <div class="stat-card-icon"><i class="bi bi-geo-alt"></i></div>
            </div>
            <div class="stat-card-value">8</div>
            <div class="stat-card-label">Lugares Activos</div>
            <div class="stat-card-trend down">↓ 1 inactivo</div>
        </div>
    </div>

    <!-- Grid: Calendario + Actividades -->
    <div class="dashboard-grid" style="flex: 1; min-height: 0;">

        <!-- Calendario de actividades cercanas -->
        <div class="dashboard-section" style="display: flex; flex-direction: column; min-height: 0;">
            <div class="section-title" style="flex-shrink: 0;">
                <i class="bi bi-calendar-week"></i>
                Próximas Actividades
            </div>
            <div class="activity-calendar" style="flex-shrink: 0;">
                <div class="calendar-card today">
                    <div class="calendar-day-name">Hoy</div>
                    <div class="calendar-date">20</div>
                    <div class="calendar-month">Jun</div>
                    <div class="calendar-event">3 sesiones</div>
                </div>
                <div class="calendar-card">
                    <div class="calendar-day-name">Mañana</div>
                    <div class="calendar-date">21</div>
                    <div class="calendar-month">Jun</div>
                    <div class="calendar-event">Taller Robótica</div>
                </div>
                <div class="calendar-card">
                    <div class="calendar-day-name">Sáb</div>
                    <div class="calendar-date">22</div>
                    <div class="calendar-month">Jun</div>
                    <div class="calendar-event">Formación</div>
                </div>
                <div class="calendar-card">
                    <div class="calendar-day-name">Lun</div>
                    <div class="calendar-date">24</div>
                    <div class="calendar-month">Jun</div>
                    <div class="calendar-event">Reunión</div>
                </div>
                <div class="calendar-card">
                    <div class="calendar-day-name">Mar</div>
                    <div class="calendar-date">25</div>
                    <div class="calendar-month">Jun</div>
                    <div class="calendar-event">Evaluación</div>
                </div>
            </div>
        </div>

        <!-- Actividades recientes -->
        <div class="dashboard-section" style="display: flex; flex-direction: column; min-height: 0;">
            <div class="section-title" style="flex-shrink: 0;">
                <i class="bi bi-lightning-charge"></i>
                Estado de Actividades
            </div>
            <div class="activity-list" style="flex: 1; min-height: 0; overflow-y: auto;">
                <div class="activity-item">
                    <div class="activity-dot green"></div>
                    <div class="activity-info">
                        <div class="activity-title">Taller de Programación Básica</div>
                        <div class="activity-meta">Completado hoy • 25 beneficiarios</div>
                    </div>
                    <span class="activity-badge planificado">Completado</span>
                </div>
                <div class="activity-item">
                    <div class="activity-dot orange"></div>
                    <div class="activity-info">
                        <div class="activity-title">Formación en Robótica Educativa</div>
                        <div class="activity-meta">En curso • 18 beneficiarios</div>
                    </div>
                    <span class="activity-badge en-curso">En Curso</span>
                </div>
                <div class="activity-item">
                    <div class="activity-dot red"></div>
                    <div class="activity-info">
                        <div class="activity-title">Capacitación Docente STEM</div>
                        <div class="activity-meta">Pendiente • Mañana 9:00 AM</div>
                    </div>
                    <span class="activity-badge urgente">Pendiente</span>
                </div>
                <div class="activity-item">
                    <div class="activity-dot green"></div>
                    <div class="activity-info">
                        <div class="activity-title">Club de Ciencias - Semana 4</div>
                        <div class="activity-meta">Completado ayer • 32 beneficiarios</div>
                    </div>
                    <span class="activity-badge planificado">Completado</span>
                </div>
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