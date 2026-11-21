<?php
// Modelo de evaluación de actividades

require_once 'ConexionModel.php';

class EvaluacionModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function generarNuevoId()
    {
        $ultimoId = $this->db->query("SELECT idEvaluacion FROM evaluacionActividad ORDER BY idEvaluacion DESC LIMIT 1")->fetchColumn();
        if (!$ultimoId) {
            return "EV0001";
        }
        return "EV" . str_pad(intval(substr($ultimoId, 2)) + 1, 4, "0", STR_PAD_LEFT);
    }

    // Actividades para evaluar (solo super usuario las lista).
    // Filtros: fecha específica (dentro del rango o con sesión ese día) y año.
    public function listarActividadesEvaluacion($filtros = [])
    {
        $sql = "SELECT a.idActividad, a.nombreActividad, a.fechainicioActividad, a.fechafinActividad,
                a.cantSesionesPlanificada, a.idUsuarioCreador,
                ta.nomTipoActividad, es.nomEstatus,
                uc.nombreUsuario AS nomCreador,
                (SELECT COUNT(*) FROM evaluacionActividad ev WHERE ev.idActividad = a.idActividad) AS totalEvaluaciones,
                (SELECT ev2.requiereCambio FROM evaluacionActividad ev2 WHERE ev2.idActividad = a.idActividad
                 ORDER BY ev2.fechaEvaluacion DESC, ev2.idEvaluacion DESC LIMIT 1) AS ultimoRequiereCambio,
                (SELECT ev3.fechaEvaluacion FROM evaluacionActividad ev3 WHERE ev3.idActividad = a.idActividad
                 ORDER BY ev3.fechaEvaluacion DESC, ev3.idEvaluacion DESC LIMIT 1) AS ultimaEvaluacion,
                (SELECT ev4.confirmadoCambios FROM evaluacionActividad ev4 WHERE ev4.idActividad = a.idActividad
                 ORDER BY ev4.fechaEvaluacion DESC, ev4.idEvaluacion DESC LIMIT 1) AS ultimoConfirmado,
                (SELECT ev5.fechaConfirmacion FROM evaluacionActividad ev5 WHERE ev5.idActividad = a.idActividad
                 ORDER BY ev5.fechaEvaluacion DESC, ev5.idEvaluacion DESC LIMIT 1) AS ultimaConfirmacion
                FROM actividad a
                LEFT JOIN tipoActividad ta ON a.idTipoActividad = ta.idTipoActividad
                LEFT JOIN estatus es ON a.idEstatus = es.idEstatus
                LEFT JOIN usuarios uc ON a.idUsuarioCreador = uc.idUsuario
                WHERE 1 = 1";
        $params = [];

        if (!empty($filtros['fecha'])) {
            $sql .= " AND (a.fechainicioActividad <= :fecha AND a.fechafinActividad >= :fecha)";
            $params[':fecha'] = $filtros['fecha'];
        }
        if (!empty($filtros['anio'])) {
            $sql .= " AND (YEAR(a.fechainicioActividad) = :anio OR YEAR(a.fechafinActividad) = :anio)";
            $params[':anio'] = (int) $filtros['anio'];
        }
        if (!empty($filtros['idEstatus'])) {
            $sql .= " AND a.idEstatus = :idEstatus";
            $params[':idEstatus'] = $filtros['idEstatus'];
        }

        $sql .= " ORDER BY a.fechainicioActividad DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarEvaluacion($idActividad, $nota, $requiereCambio, $idUsuarioEvaluador)
    {
        try {
            $nuevoId = $this->generarNuevoId();
            $stmt = $this->db->prepare(
                "INSERT INTO evaluacionActividad
                    (idEvaluacion, notaEvaluacion, requiereCambio, idActividad, idUsuarioEvaluador)
                 VALUES (:id, :nota, :requiere, :actividad, :evaluador)"
            );
            $stmt->execute([
                'id' => $nuevoId,
                'nota' => $nota,
                'requiere' => $requiereCambio ? 1 : 0,
                'actividad' => $idActividad,
                'evaluador' => $idUsuarioEvaluador
            ]);
            return $nuevoId;
        } catch (PDOException $e) {
            error_log("Error al registrar evaluación: " . $e->getMessage());
            return false;
        }
    }

    // Historial completo de evaluaciones de una actividad (para el super usuario)
    public function obtenerEvaluacionesActividad($idActividad)
    {
        $stmt = $this->db->prepare(
            "SELECT ev.*, u.nombreUsuario AS nomEvaluador
             FROM evaluacionActividad ev
             LEFT JOIN usuarios u ON ev.idUsuarioEvaluador = u.idUsuario
             WHERE ev.idActividad = :id
             ORDER BY ev.fechaEvaluacion DESC, ev.idEvaluacion DESC"
        );
        $stmt->execute(['id' => $idActividad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerActividad($idActividad)
    {
        $stmt = $this->db->prepare(
            "SELECT a.idActividad, a.nombreActividad, a.idUsuarioCreador, uc.nombreUsuario AS nomCreador
             FROM actividad a
             LEFT JOIN usuarios uc ON a.idUsuarioCreador = uc.idUsuario
             WHERE a.idActividad = :id"
        );
        $stmt->execute(['id' => $idActividad]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // El planificador confirma que hizo los cambios pedidos en una evaluación
    public function confirmarCambios($idEvaluacion)
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE evaluacionActividad
                 SET confirmadoCambios = 1, fechaConfirmacion = NOW()
                 WHERE idEvaluacion = :id AND requiereCambio = 1 AND confirmadoCambios = 0"
            );
            $stmt->execute(['id' => $idEvaluacion]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al confirmar cambios: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerEvaluacionPorId($idEvaluacion)
    {
        $stmt = $this->db->prepare(
            "SELECT ev.*, a.nombreActividad, a.idUsuarioCreador
             FROM evaluacionActividad ev
             JOIN actividad a ON ev.idActividad = a.idActividad
             WHERE ev.idEvaluacion = :id"
        );
        $stmt->execute(['id' => $idEvaluacion]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerEstatus()
    {
        return $this->db->query("SELECT idEstatus, nomEstatus FROM estatus ORDER BY idEstatus")->fetchAll(PDO::FETCH_ASSOC);
    }

    // Años presentes en las actividades, para el filtro por año
    public function obtenerAnios()
    {
        $sql = "SELECT DISTINCT anio FROM (
                    SELECT YEAR(fechainicioActividad) AS anio FROM actividad
                    UNION
                    SELECT YEAR(fechafinActividad) AS anio FROM actividad
                ) anios ORDER BY anio DESC";
        return array_column($this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC), 'anio');
    }
}
