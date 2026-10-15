<?php
require_once 'model/ConexionModel.php';

/**
 * MÓDULO REPORTES
 * Genera el reporte de actividades realizadas por FUNDACITI en PDF.
 * Fuentes (tablas reales del sistema):
 *   - actividad: datos de la actividad y su período
 *   - seguimientoActividad: sesiones planificadas y su registro (logro)
 *   - asistencia + beneficiario: beneficiarios atendidos por actividad
 *   - docente / empleado / unidadejecutora / lugar / tipoactividad / estatus
 */
class ReporteModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ===== FILTROS =====
    // El reporte es POR MES y trae SOLO actividades Finalizada (ES0006):
    // una actividad "realizada" en un mes es la que culminó (fecha de fin)
    // dentro de ese mes/año. El filtro de estatus es fijo, no depende de lo
    // que el usuario elija en pantalla.
    // Docente es filtro adicional opcional.
    // $alias: nombre de la tabla actividad en la consulta (a / ax).
    private function construirWhere($filtros, $alias = 'a')
    {
        $where = [];
        $params = [];

        $where[] = "$alias.idEstatus = 'ES0006'";

        $mes = (int) ($filtros['mes'] ?? 0);
        $anio = (int) ($filtros['anio'] ?? 0);
        if ($mes >= 1 && $mes <= 12 && $anio >= 2000 && $anio <= 2100) {
            $inicioMes = sprintf('%04d-%02d-01', $anio, $mes);
            $finMes = date('Y-m-t', strtotime($inicioMes));
            $where[] = "$alias.fechafinActividad BETWEEN :inicioMes_$alias AND :finMes_$alias";
            $params["finMes_$alias"] = $finMes;
            $params["inicioMes_$alias"] = $inicioMes;
        }
        if (!empty($filtros['idDocente'])) {
            $where[] = "$alias.idDocente = :docente";
            $params['docente'] = $filtros['idDocente'];
        }

        $sqlWhere = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
        return [$sqlWhere, $params];
    }

    // ===== ACTIVIDADES PARA EL REPORTE =====
    public function obtenerActividadesReporte($filtros)
    {
        [$sqlWhere, $params] = $this->construirWhere($filtros);

        $sql = "SELECT
                    a.idActividad,
                    a.nombreActividad,
                    a.fechainicioActividad,
                    a.fechafinActividad,
                    a.cantPersoAtender,
                    a.objetivoActividad,
                    ta.nomTipoActividad,
                    e.nomEstatus,
                    CONCAT(d.nombreDocente, ' ', d.apellidoDocente) AS docente,
                    CONCAT(emp.nombres, ' ', emp.apellidos) AS responsable,
                    ue.nomUnidadEjecutora,
                    la.nomLugarActividad,
                    (SELECT COUNT(*) FROM seguimientoActividad sa
                      WHERE sa.idActividad = a.idActividad) AS sesiones_planificadas,
                    (SELECT COUNT(*) FROM seguimientoActividad sa
                      WHERE sa.idActividad = a.idActividad
                        AND sa.logroActividad <> '') AS sesiones_registradas,
                    (SELECT COUNT(*) FROM asistencia asi
                       JOIN seguimientoActividad sa2 ON asi.idSegActividad = sa2.idSegActividad
                      WHERE sa2.idActividad = a.idActividad) AS total_asistencias,
                    (SELECT COUNT(DISTINCT asi.idBeneficiario)
                       FROM asistencia asi
                       JOIN seguimientoActividad sa3 ON asi.idSegActividad = sa3.idSegActividad
                      WHERE sa3.idActividad = a.idActividad) AS beneficiarios_atendidos
                FROM actividad a
                LEFT JOIN tipoactividad ta ON ta.idTipoActividad = a.idTipoActividad
                LEFT JOIN estatus e ON e.idEstatus = a.idEstatus
                LEFT JOIN docente d ON d.idDocente = a.idDocente
                LEFT JOIN empleado emp ON emp.idEmpleado = a.idEmpleado
                LEFT JOIN unidadejecutora ue ON ue.idUnidadEjecutora = emp.idUnidadEjecutora
                LEFT JOIN lugarrealizaactividad lar ON lar.idActividad = a.idActividad
                LEFT JOIN lugaractividad la ON la.idLugarActividad = lar.idLugarActividad
                $sqlWhere
                ORDER BY a.fechainicioActividad DESC, a.idActividad DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===== RESUMEN GENERAL (mismos filtros) =====
    public function obtenerResumen($filtros)
    {
        // Los sub-queries unen actividad con alias ax; la consulta externa con a
        [$sqlWhereAx, $paramsAx] = $this->construirWhere($filtros, 'ax');
        [$sqlWhere, $params] = $this->construirWhere($filtros, 'a');

        $sql = "SELECT
                    COUNT(DISTINCT a.idActividad) AS total_actividades,
                    (SELECT COUNT(*) FROM seguimientoActividad sa
                       JOIN actividad ax ON ax.idActividad = sa.idActividad
                       $sqlWhereAx) AS sesiones_planificadas,
                    (SELECT COUNT(*) FROM seguimientoActividad sa
                       JOIN actividad ax ON ax.idActividad = sa.idActividad
                       $sqlWhereAx AND sa.logroActividad <> '') AS sesiones_registradas,
                    (SELECT COUNT(*) FROM asistencia asi
                       JOIN seguimientoActividad sa ON asi.idSegActividad = sa.idSegActividad
                       JOIN actividad ax ON ax.idActividad = sa.idActividad
                       $sqlWhereAx) AS total_asistencias,
                    (SELECT COUNT(DISTINCT asi.idBeneficiario) FROM asistencia asi
                       JOIN seguimientoActividad sa ON asi.idSegActividad = sa.idSegActividad
                       JOIN actividad ax ON ax.idActividad = sa.idActividad
                       $sqlWhereAx) AS beneficiarios_atendidos
                FROM actividad a
                $sqlWhere";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($paramsAx, $params));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ===== SESIONES DE UNA ACTIVIDAD (detalle del PDF) =====
    public function obtenerSesionesActividad($idActividad)
    {
        $sql = "SELECT
                    sa.nroSesionPlanificada,
                    sa.fechaSesion,
                    sa.logroActividad,
                    sa.observObstaculo,
                    te.nomTipEntrega,
                    (SELECT COUNT(*) FROM asistencia asi
                      WHERE asi.idSegActividad = sa.idSegActividad) AS totalAsistentes
                FROM seguimientoActividad sa
                LEFT JOIN tipoentrega te ON te.idTipEntrega = sa.idTipEntrega
                WHERE sa.idActividad = :id
                ORDER BY sa.nroSesionPlanificada";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idActividad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===== BENEFICIARIOS ATENDIDOS POR UNA ACTIVIDAD (detalle del PDF) =====
    public function obtenerBeneficiariosActividad($idActividad)
    {
        $sql = "SELECT DISTINCT
                    b.cedulaBeneficiario,
                    b.nombresBeneficiario,
                    b.apellidosBeneficiario,
                    b.nacionalidad,
                    b.sexoBeneficiario,
                    (SELECT COUNT(*) FROM asistencia asi
                       JOIN seguimientoActividad sa ON asi.idSegActividad = sa.idSegActividad
                      WHERE sa.idActividad = :id2
                        AND asi.idBeneficiario = b.idBeneficiario) AS sesiones_asistidas
                FROM beneficiario b
                JOIN asistencia asi ON asi.idBeneficiario = b.idBeneficiario
                JOIN seguimientoActividad sa ON asi.idSegActividad = sa.idSegActividad
                WHERE sa.idActividad = :id
                ORDER BY b.apellidosBeneficiario, b.nombresBeneficiario";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idActividad, 'id2' => $idActividad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===== DATOS PARA LOS FILTROS DE LA PANTALLA =====
    public function obtenerEstatus()
    {
        return $this->db->query("SELECT idEstatus, nomEstatus FROM estatus ORDER BY nomEstatus")
                        ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDocentes()
    {
        return $this->db->query("SELECT idDocente, nombreDocente, apellidoDocente FROM docente ORDER BY apellidoDocente, nombreDocente")
                        ->fetchAll(PDO::FETCH_ASSOC);
    }
}
