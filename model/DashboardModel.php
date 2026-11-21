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


            'beneficiarios_total'      => $this->contarEscalar("
                SELECT COALESCE(SUM(total), 0)
                FROM (
                    SELECT COUNT(DISTINCT asi.idBeneficiario) AS total
                    FROM asistencia asi
                    INNER JOIN seguimientoActividad sa
                        ON sa.idSegActividad = asi.idSegActividad
                    GROUP BY sa.idActividad
                ) AS atendidos
            "),

            'personas_planificadas'    => $this->contarEscalar("
                SELECT COALESCE(SUM(cantPersoAtender), 0) FROM actividad
            "),


            'sesiones_hoy'             => $this->contarEscalar("
                SELECT COUNT(*) FROM seguimientoActividad
                WHERE fechaSesion = CURDATE()
            "),
            'sesiones_semana'          => $this->contarEscalar("
                SELECT COUNT(*) FROM seguimientoActividad
                WHERE fechaSesion >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            "),


            'lugares_total'            => $this->contarEscalar("SELECT COUNT(*) FROM lugarActividad"),
            'sedes_total'              => $this->contarEscalar("
                SELECT COUNT(*) FROM lugarActividad WHERE esSede = 1
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

    public function listarActividadesEjecucion()
    {
        $sql = "SELECT a.idActividad, a.nombreActividad, a.descActividad,
                       a.fechainicioActividad, a.fechafinActividad,
                       a.cantPersoAtender, a.cantSesionesPlanificada,
                       ta.nomTipoActividad, ae.nomAreaE, es.nomEstatus,
                       (SELECT GROUP_CONCAT(DISTINCT la.nomLugarActividad ORDER BY la.nomLugarActividad SEPARATOR ', ')
                        FROM lugarRealizaActividad lra
                        INNER JOIN lugarActividad la ON la.idLugarActividad = lra.idLugarActividad
                        WHERE lra.idActividad = a.idActividad) AS lugares,
                       (SELECT GROUP_CONCAT(DISTINCT h.nomHorario ORDER BY h.nomHorario SEPARATOR ', ')
                        FROM actividadHorario ah
                        INNER JOIN horario h ON h.idHorario = ah.idHorario
                        WHERE ah.idActividad = a.idActividad) AS horarios
                FROM actividad a
                LEFT JOIN tipoActividad ta ON ta.idTipoActividad = a.idTipoActividad
                LEFT JOIN areaEspecifica ae ON ae.idAreaE = a.idAreaE
                INNER JOIN estatus es ON es.idEstatus = a.idEstatus
                WHERE a.idEstatus = 'ES0003'
                ORDER BY a.fechainicioActividad ASC, a.nombreActividad ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarSesionesHoy()
    {
        $sql = "SELECT sa.idSegActividad, sa.nroSesionPlanificada, sa.fechaSesion,
                       sa.logroActividad, a.idActividad, a.nombreActividad,
                       es.nomEstatus, te.nomTipEntrega,
                       COALESCE(hs.nomHorario, ha.nomHorario) AS nomHorario,
                       COALESCE(la.nomLugarActividad,
                           (SELECT GROUP_CONCAT(DISTINCT la2.nomLugarActividad ORDER BY la2.nomLugarActividad SEPARATOR ', ')
                            FROM lugarRealizaActividad lra
                            INNER JOIN lugarActividad la2 ON la2.idLugarActividad = lra.idLugarActividad
                            WHERE lra.idActividad = a.idActividad)) AS nomLugarActividad,
                       (SELECT COUNT(*) FROM asistencia asi
                        WHERE asi.idSegActividad = sa.idSegActividad) AS totalAsistentes
                FROM seguimientoActividad sa
                INNER JOIN actividad a ON a.idActividad = sa.idActividad
                INNER JOIN estatus es ON es.idEstatus = a.idEstatus
                LEFT JOIN horario hs ON hs.idHorario = sa.idHorario
                LEFT JOIN horario ha ON ha.idHorario = a.idHorario
                LEFT JOIN lugarActividad la ON la.idLugarActividad = sa.idLugarActividad
                LEFT JOIN tipoEntrega te ON te.idTipEntrega = sa.idTipEntrega
                WHERE sa.fechaSesion = CURDATE()
                ORDER BY nomHorario ASC, a.nombreActividad ASC, sa.nroSesionPlanificada ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarLugaresRegistrados()
    {
        $sql = "SELECT l.idLugarActividad, l.nomLugarActividad, l.desLugarActividad,
                       l.direccion, l.esSede, p.nombreParroquia, m.nombreMunicipio,
                       e.nombreEstado,
                       (SELECT COUNT(DISTINCT lra.idActividad)
                        FROM lugarRealizaActividad lra
                        WHERE lra.idLugarActividad = l.idLugarActividad) AS totalActividades,
                       (SELECT COUNT(*) FROM espacioUtilizar eu
                        WHERE eu.idLugarActividad = l.idLugarActividad) AS totalEspacios
                FROM lugarActividad l
                LEFT JOIN parroquia p ON p.idParroquia = l.idParroquia
                LEFT JOIN municipio m ON m.idMunicipio = p.idMunicipio
                LEFT JOIN estado e ON e.idEstado = m.idEstado
                ORDER BY l.esSede DESC, l.nomLugarActividad ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarBeneficiariosRegistrados($ids = [])
    {
        $ids = array_values(array_unique(array_filter($ids, 'is_string')));
        $sql = "SELECT b.idBeneficiario, b.cedulaBeneficiario, b.nombresBeneficiario,
                       b.apellidosBeneficiario, b.nacionalidad, b.fechaNacBeneficiario,
                       b.sexoBeneficiario,
                       CASE WHEN b.fechaNacBeneficiario IS NULL THEN NULL
                            ELSE TIMESTAMPDIFF(YEAR, b.fechaNacBeneficiario, CURDATE()) END AS edad,
                       (SELECT COUNT(*) FROM asistencia asi
                        WHERE asi.idBeneficiario = b.idBeneficiario) AS totalAsistencias,
                       (SELECT COUNT(DISTINCT sa.idActividad)
                        FROM asistencia asi
                        INNER JOIN seguimientoActividad sa ON sa.idSegActividad = asi.idSegActividad
                        WHERE asi.idBeneficiario = b.idBeneficiario) AS totalActividades
                FROM beneficiario b";
        $params = [];
        if (!empty($ids)) {
            $marcadores = implode(',', array_fill(0, count($ids), '?'));
            $sql .= " WHERE b.idBeneficiario IN ($marcadores)";
            $params = $ids;
        }
        $sql .= " ORDER BY b.apellidosBeneficiario ASC, b.nombresBeneficiario ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
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
