<?php
require_once 'ConexionModel.php';

/**
 * MODELO DEL DASHBOARD (Inicio).
 *
 * Centraliza las consultas agregadas que alimentan la pantalla principal
 * con datos reales de la base de datos (sin texto de ejemplo).
 */
class DashboardModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Tarjetas de estadísticas superiores.
     * Cada clave es un dato REAL calculado sobre las tablas del sistema.
     */
    public function obtenerResumen()
    {
        $resumen = [
            // --- Tarjeta 1: Actividades ---
            // "Activas" = en ejecución actualmente
            'actividades_activas'      => $this->contarEscalar("
                SELECT COUNT(*) FROM actividad
                WHERE idEstatus = 'ES0003'
            "),
            // Iniciadas en los últimos 7 días (para la línea de tendencia)
            'actividades_semana'       => $this->contarEscalar("
                SELECT COUNT(*) FROM actividad
                WHERE fechainicioActividad >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            "),

            // --- Tarjeta 2: Beneficiarios ---
            'beneficiarios_total'      => $this->contarEscalar("SELECT COUNT(*) FROM beneficiario"),
            // Personas planificadas por atender según las actividades registradas
            'personas_planificadas'    => $this->contarEscalar("
                SELECT COALESCE(SUM(cantPersoAtender), 0) FROM actividad
            "),

            // --- Tarjeta 3: Sesiones ---
            'sesiones_hoy'             => $this->contarEscalar("
                SELECT COUNT(*) FROM seguimientoActividad
                WHERE fechaSesion = CURDATE()
            "),
            'sesiones_semana'          => $this->contarEscalar("
                SELECT COUNT(*) FROM seguimientoActividad
                WHERE fechaSesion >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            "),

            // --- Tarjeta 4: Lugares ---
            'lugares_total'            => $this->contarEscalar("SELECT COUNT(*) FROM lugarActividad"),
            'sedes_total'              => $this->contarEscalar("
                SELECT COUNT(*) FROM lugarActividad WHERE esSede = 1
            "),

            // Extras para la lista de estado
            'actividades_total'        => $this->contarEscalar("SELECT COUNT(*) FROM actividad"),
            'actividades_planificadas' => $this->contarEscalar("
                SELECT COUNT(*) FROM actividad WHERE idEstatus = 'ES0001'
            "),
        ];

        return $resumen;
    }

    /**
     * Próximas actividades para el mini-calendario:
     * las que aún no han finalizado (vigentes o futuras), ordenadas por fecha.
     */
    public function obtenerProximasActividades($limite = 5)
    {
        $sql = "SELECT a.idActividad, a.nombreActividad, a.fechainicioActividad,
                       a.fechafinActividad, e.nomEstatus
                FROM actividad a
                INNER JOIN estatus e ON e.idEstatus = a.idEstatus
                WHERE a.fechafinActividad >= CURDATE()
                  AND a.idEstatus NOT IN ('ES0005', 'ES0006')
                ORDER BY a.fechainicioActividad ASC
                LIMIT " . (int) $limite;
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actividades recientes para la lista de "Estado de Actividades".
     * Se ordenan por fecha de inicio (las más recientes primero).
     */
    public function obtenerActividadesRecientes($limite = 6)
    {
        $sql = "SELECT a.idActividad, a.nombreActividad, a.fechainicioActividad,
                       a.fechafinActividad, a.cantPersoAtender, e.nomEstatus
                FROM actividad a
                INNER JOIN estatus e ON e.idEstatus = a.idEstatus
                ORDER BY a.fechainicioActividad DESC, a.idActividad DESC
                LIMIT " . (int) $limite;
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function contarEscalar($sql)
    {
        try {
            $stmt = $this->db->query($sql);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            // El dashboard nunca debe tumbar el sistema; devuelve 0 y registra
            error_log("Error en DashboardModel: " . $e->getMessage());
            return 0;
        }
    }
}
