<?php
// Modelo de panel principal



require_once 'ConexionModel.php';


class DashboardModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }


    public function obtenerResumen()
    {
        $resumen = [


            'actividades_activas'      => $this->contarEscalar("
                SELECT COUNT(*) FROM actividad
                WHERE idEstatus = 'ES0003'
            "),

            'actividades_semana'       => $this->contarEscalar("
                SELECT COUNT(*) FROM actividad
                WHERE fechainicioActividad >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            "),


            'beneficiarios_total'      => $this->contarEscalar("SELECT COUNT(*) FROM beneficiario"),

            'personas_planificadas'    => $this->contarEscalar("
                SELECT COALESCE(SUM(cantPersoAtender), 0) FROM actividad
            "),


            'sesiones_hoy'             => $this->contarEscalar("
                SELECT COUNT(*) FROM seguimientoactividad
                WHERE fechaSesion = CURDATE()
            "),
            'sesiones_semana'          => $this->contarEscalar("
                SELECT COUNT(*) FROM seguimientoactividad
                WHERE fechaSesion >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            "),


            'lugares_total'            => $this->contarEscalar("SELECT COUNT(*) FROM lugaractividad"),
            'sedes_total'              => $this->contarEscalar("
                SELECT COUNT(*) FROM lugaractividad WHERE esSede = 1
            "),


            'actividades_total'        => $this->contarEscalar("SELECT COUNT(*) FROM actividad"),
            'actividades_planificadas' => $this->contarEscalar("
                SELECT COUNT(*) FROM actividad WHERE idEstatus = 'ES0001'
            "),
        ];

        return $resumen;
    }


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

            error_log("Error en DashboardModel: " . $e->getMessage());
            return 0;
        }
    }
}
