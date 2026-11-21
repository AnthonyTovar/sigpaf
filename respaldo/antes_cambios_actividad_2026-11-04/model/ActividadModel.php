<?php
// Modelo de actividades



require_once 'ConexionModel.php';

class ActividadModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function generarNuevoId()
    {
        $sql = "SELECT idActividad FROM actividad ORDER BY idActividad DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "AC0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "AC" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    private function generarNuevoIdLugarRealiza()
    {
        $sql = "SELECT idReaActividad FROM lugarrealizaactividad ORDER BY idReaActividad DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "LR0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "LR" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    private function generarNuevoIdGrupoEtarioAct()
    {
        $sql = "SELECT idGrupoEtareoActividad FROM grupoetarioactividad ORDER BY idGrupoEtareoActividad DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "GEA001";
        }

        $numero = substr($ultimoId, 3);
        $nuevoNumero = intval($numero) + 1;

        return "GEA" . str_pad($nuevoNumero, 3, "0", STR_PAD_LEFT);
    }

    private function generarNuevoIdSeguimiento()
    {
        $sql = "SELECT idSegActividad FROM seguimientoactividad ORDER BY idSegActividad DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "SA0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "SA" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    private function generarNuevoIdRelacion($tabla, $columna, $prefijo)
    {
        $stmt = $this->db->query("SELECT `$columna` FROM `$tabla` ORDER BY `$columna` DESC LIMIT 1");
        $ultimoId = $stmt->fetchColumn();
        $numero = $ultimoId ? intval(substr($ultimoId, strlen($prefijo))) + 1 : 1;
        return $prefijo . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    private function insertarRelaciones($idActividad, $datos)
    {
        $configuraciones = [
            ['tabla' => 'actividadhorario', 'columnaId' => 'idActividadHorario', 'columnaValor' => 'idHorario', 'prefijo' => 'AH', 'valores' => $datos['idHorarios']],
            ['tabla' => 'actividadempleado', 'columnaId' => 'idActividadEmpleado', 'columnaValor' => 'idEmpleado', 'prefijo' => 'AE', 'valores' => $datos['idEmpleados']],
            ['tabla' => 'actividaddocente', 'columnaId' => 'idActividadDocente', 'columnaValor' => 'idDocente', 'prefijo' => 'AD', 'valores' => $datos['idDocentes']]
        ];

        foreach ($configuraciones as $config) {
            $sql = "INSERT INTO {$config['tabla']} ({$config['columnaId']}, idActividad, {$config['columnaValor']})
                    VALUES (:idRelacion, :idActividad, :valor)";
            $stmt = $this->db->prepare($sql);
            foreach (array_unique($config['valores']) as $valor) {
                $stmt->execute([
                    'idRelacion' => $this->generarNuevoIdRelacion($config['tabla'], $config['columnaId'], $config['prefijo']),
                    'idActividad' => $idActividad,
                    'valor' => $valor
                ]);
            }
        }
    }

    private function insertarLugares($idActividad, $datos)
    {
        $sql = "INSERT INTO lugarrealizaactividad (idReaActividad, idEspacioUtilizar, idLugarActividad, idActividad)
                VALUES (:id, :espacio, :lugar, :actividad)";
        $stmt = $this->db->prepare($sql);
        foreach ($datos['idLugares'] as $idLugar) {
            $stmt->execute([
                'id' => $this->generarNuevoIdLugarRealiza(),
                'espacio' => $datos['espaciosPorLugar'][$idLugar] ?? null,
                'lugar' => $idLugar,
                'actividad' => $idActividad
            ]);
        }
    }

    // Listar



    public function listarActividades($filtros = [])
    {
        $sql = "SELECT a.*,
                ta.nomTipoActividad, v.nombreVertice, ae.nomAreaE,
                e.nombres AS nomEmpleado, e.apellidos AS apeEmpleado,
                d.nombreDocente, d.apellidoDocente,
                es.nomEstatus, h.nomHorario, ed.nomEstDesarrollo,
                um.nomUnidadMedida, ge.nomGrupoEtnio
                FROM actividad a
                LEFT JOIN tipoactividad ta ON a.idTipoActividad = ta.idTipoActividad
                LEFT JOIN vertice v ON a.idVertice = v.idVertice
                LEFT JOIN areaespecifica ae ON a.idAreaE = ae.idAreaE
                LEFT JOIN empleado e ON a.idEmpleado = e.idEmpleado
                LEFT JOIN docente d ON a.idDocente = d.idDocente
                LEFT JOIN estatus es ON a.idEstatus = es.idEstatus
                LEFT JOIN horario h ON a.idHorario = h.idHorario
                LEFT JOIN estrategiadesarrollo ed ON a.idEstDesarrollo = ed.idEstDesarrollo
                LEFT JOIN unidadmedida um ON a.idUnidadMedida = um.idUnidadMedida
                LEFT JOIN grupoetnio ge ON a.idGrupoEtnio = ge.idGrupoEtnio
                WHERE 1 = 1";
        $params = [];


        if (!empty($filtros['desde'])) {
            $sql .= " AND a.fechafinActividad >= :desde";
            $params[':desde'] = $filtros['desde'];
        }
        if (!empty($filtros['hasta'])) {
            $sql .= " AND a.fechainicioActividad <= :hasta";
            $params[':hasta'] = $filtros['hasta'];
        }


        if (!empty($filtros['idEstatus'])) {
            $sql .= " AND a.idEstatus = :idEstatus";
            $params[':idEstatus'] = $filtros['idEstatus'];
        }


        if (!empty($filtros['idTipoActividad'])) {
            $sql .= " AND a.idTipoActividad = :idTipoActividad";
            $params[':idTipoActividad'] = $filtros['idTipoActividad'];
        }


        if (!empty($filtros['responsable'])) {
            $sql .= " AND (EXISTS (SELECT 1 FROM actividadempleado ae2 WHERE ae2.idActividad = a.idActividad AND ae2.idEmpleado = :respEmp)
                          OR EXISTS (SELECT 1 FROM actividaddocente ad2 WHERE ad2.idActividad = a.idActividad AND ad2.idDocente = :respDoc))";
            $params[':respEmp'] = $filtros['responsable'];
            $params[':respDoc'] = $filtros['responsable'];
        }

        $sql .= " ORDER BY a.fechainicioActividad DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($actividades as &$actividad) {
            $actividad['empleadosLista'] = $this->obtenerEmpleadosActividad($actividad['idActividad']);
            $actividad['docentesLista'] = $this->obtenerDocentesActividad($actividad['idActividad']);
            $actividad['horariosLista'] = $this->obtenerHorariosActividad($actividad['idActividad']);
            $actividad['lugaresLista'] = $this->obtenerLugaresActividadPorId($actividad['idActividad']);
        }
        unset($actividad);
        return $actividades;
    }

    // Datos maestros para selects
    public function obtenerEstrategiasDesarrollo()
    {
        $sql = "SELECT idEstDesarrollo, nomEstDesarrollo FROM estrategiadesarrollo ORDER BY nomEstDesarrollo";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTiposActividad()
    {
        $sql = "SELECT idTipoActividad, nomTipoActividad FROM tipoactividad ORDER BY nomTipoActividad";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerVertices()
    {
        $sql = "SELECT idVertice, nombreVertice FROM vertice ORDER BY nombreVertice";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerAreasEspecificas()
    {
        $sql = "SELECT idAreaE, nomAreaE FROM areaespecifica ORDER BY nomAreaE";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerGruposEtarios()
    {
        $sql = "SELECT idGrupoEtareo, nomGrupoEtareo, edadMin, edadMax FROM grupoetario ORDER BY edadMin";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerGruposEtnicos()
    {
        $sql = "SELECT idGrupoEtnio, nomGrupoEtnio FROM grupoetnio ORDER BY nomGrupoEtnio";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerUnidadesMedida()
    {
        $sql = "SELECT idUnidadMedida, nomUnidadMedida FROM unidadmedida ORDER BY nomUnidadMedida";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerLugaresActividad()
    {
        $sql = "SELECT idLugarActividad, nomLugarActividad, esSede FROM lugaractividad ORDER BY nomLugarActividad";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEspaciosUtilizar()
    {
        $sql = "SELECT idEspacioUtilizar, nombreEspacioUtilizar, capacidad FROM espacioutilizar ORDER BY nombreEspacioUtilizar";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerHorarios()
    {
        $sql = "SELECT idHorario, nomHorario FROM horario ORDER BY idHorario";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEmpleados()
    {
        $sql = "SELECT idEmpleado, CONCAT(nombres, ' ', apellidos) AS nombreCompleto, cedulaEmpleado FROM empleado ORDER BY nombres";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDocentes()
    {
        $sql = "SELECT idDocente, CONCAT(nombreDocente, ' ', apellidoDocente) AS nombreCompleto, cedDocente FROM docente ORDER BY nombreDocente";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEstatus()
    {
        $sql = "SELECT idEstatus, nomEstatus FROM estatus ORDER BY nomEstatus";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTiposEntrega()
    {
        $sql = "SELECT idTipEntrega, nomTipEntrega FROM tipoentrega ORDER BY nomTipEntrega";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }



    public function obtenerFechasOcupadas($excluirIdActividad = null)
    {
        $params = [];
        $sql = "SELECT DISTINCT fechainicioActividad, fechafinActividad
                FROM actividad
                WHERE fechafinActividad >= CURDATE() AND idEstatus <> 'ES0005'";
        if ($excluirIdActividad) {
            $sql .= " AND idActividad <> ?";
            $params[] = $excluirIdActividad;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function condicionFechas($fechaInicio, $fechaFin, $fechasSesiones = [])
    {
        $params = [];
        $fechasSesiones = array_values(array_filter($fechasSesiones));
        if ($fechasSesiones) {
            $marcadores = implode(',', array_fill(0, count($fechasSesiones), '?'));
            $sql = "((sa.fechaSesion IN ($marcadores)) OR
                     (sa.idSegActividad IS NULL AND a.fechainicioActividad <= ? AND a.fechafinActividad >= ?))";
            $params = array_merge($fechasSesiones, [$fechaFin, $fechaInicio]);
        } else {
            $sql = "((sa.fechaSesion BETWEEN ? AND ?) OR
                     (sa.idSegActividad IS NULL AND a.fechainicioActividad <= ? AND a.fechafinActividad >= ?))";
            $params = [$fechaInicio, $fechaFin, $fechaFin, $fechaInicio];
        }
        return [$sql, $params];
    }


    private function condicionHorariosSolapados($horarios)
    {
        $marcadores = implode(',', array_fill(0, count($horarios), '?'));
        $sql = "EXISTS (
                    SELECT 1 FROM horario h_nuevo
                    WHERE h_nuevo.idHorario IN ($marcadores)
                      AND STR_TO_DATE(SUBSTRING_INDEX(h.nomHorario, ' - ', 1), '%h:%i %p') <
                          STR_TO_DATE(SUBSTRING_INDEX(h_nuevo.nomHorario, ' - ', -1), '%h:%i %p')
                      AND STR_TO_DATE(SUBSTRING_INDEX(h_nuevo.nomHorario, ' - ', 1), '%h:%i %p') <
                          STR_TO_DATE(SUBSTRING_INDEX(h.nomHorario, ' - ', -1), '%h:%i %p')
                )";
        return [$sql, array_values($horarios)];
    }

    public function obtenerHorariosOcupados($fechaInicio, $fechaFin, $fechasSesiones = [], $lugares = [], $espacios = [], $excluirIdActividad = null, $horarios = [])
    {
        if (!$fechaInicio || !$fechaFin || (!$lugares && !$espacios)) return [];
        [$condicionFechas, $params] = $this->condicionFechas($fechaInicio, $fechaFin, $fechasSesiones);

        $sql = "SELECT DISTINCT ah.idHorario, h.nomHorario
                FROM actividad a
                INNER JOIN actividadhorario ah ON ah.idActividad = a.idActividad
                INNER JOIN horario h ON h.idHorario = ah.idHorario
                INNER JOIN lugarrealizaactividad lra ON lra.idActividad = a.idActividad
                LEFT JOIN seguimientoactividad sa ON sa.idActividad = a.idActividad
                WHERE a.idEstatus <> 'ES0005' AND $condicionFechas";
        if ($horarios) {
            [$condicionHorarios, $paramsHorarios] = $this->condicionHorariosSolapados($horarios);
            $sql .= " AND $condicionHorarios";
            $params = array_merge($params, $paramsHorarios);
        }

        $condicionesLugar = [];
        if ($lugares) {
            $condicionesLugar[] = 'lra.idLugarActividad IN (' . implode(',', array_fill(0, count($lugares), '?')) . ')';
            $params = array_merge($params, array_values($lugares));
        }
        if ($espacios) {
            $condicionesLugar[] = 'lra.idEspacioUtilizar IN (' . implode(',', array_fill(0, count($espacios), '?')) . ')';
            $params = array_merge($params, array_values($espacios));
        }
        $sql .= ' AND (' . implode(' OR ', $condicionesLugar) . ')';
        if ($excluirIdActividad) {
            $sql .= ' AND a.idActividad <> ?';
            $params[] = $excluirIdActividad;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDocentesOcupados($fechaInicio, $fechaFin, $fechasSesiones, $horarios, $excluirIdActividad = null)
    {
        if (!$fechaInicio || !$fechaFin || !$horarios) return [];
        [$condicionFechas, $paramsFechas] = $this->condicionFechas($fechaInicio, $fechaFin, $fechasSesiones);
        [$condicionHorarios, $paramsHorarios] = $this->condicionHorariosSolapados($horarios);

        $sql = "SELECT DISTINCT ad.idDocente
                FROM actividad a
                INNER JOIN actividaddocente ad ON ad.idActividad = a.idActividad
                INNER JOIN actividadhorario ah ON ah.idActividad = a.idActividad
                INNER JOIN horario h ON h.idHorario = ah.idHorario
                LEFT JOIN seguimientoactividad sa ON sa.idActividad = a.idActividad
                WHERE a.idEstatus <> 'ES0005'
                  AND $condicionHorarios
                  AND $condicionFechas";
        $params = array_merge($paramsHorarios, $paramsFechas);
        if ($excluirIdActividad) {
            $sql .= ' AND a.idActividad <> ?';
            $params[] = $excluirIdActividad;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function obtenerConflictosDocentes($docentes, $horarios, $fechaInicio, $fechaFin, $fechasSesiones = [], $excluirIdActividad = null)
    {
        if (!$docentes || !$horarios || !$fechaInicio || !$fechaFin) return [];
        [$condicionFechas, $paramsFechas] = $this->condicionFechas($fechaInicio, $fechaFin, $fechasSesiones);
        [$condicionHorarios, $paramsHorarios] = $this->condicionHorariosSolapados($horarios);
        $marcadoresDocentes = implode(',', array_fill(0, count($docentes), '?'));

        $sql = "SELECT DISTINCT a.idActividad, a.nombreActividad,
                       a.fechainicioActividad, a.fechafinActividad, sa.fechaSesion,
                       h.nomHorario, d.idDocente,
                       CONCAT(d.nombreDocente, ' ', d.apellidoDocente) AS nomDocente
                FROM actividad a
                INNER JOIN actividaddocente ad ON ad.idActividad = a.idActividad
                INNER JOIN actividadhorario ah ON ah.idActividad = a.idActividad
                INNER JOIN docente d ON d.idDocente = ad.idDocente
                INNER JOIN horario h ON h.idHorario = ah.idHorario
                LEFT JOIN seguimientoactividad sa ON sa.idActividad = a.idActividad
                WHERE a.idEstatus <> 'ES0005'
                  AND ad.idDocente IN ($marcadoresDocentes)
                  AND $condicionHorarios
                  AND $condicionFechas";
        $params = array_merge(array_values($docentes), $paramsHorarios, $paramsFechas);
        if ($excluirIdActividad) {
            $sql .= ' AND a.idActividad <> ?';
            $params[] = $excluirIdActividad;
        }
        $sql .= ' ORDER BY sa.fechaSesion, a.fechainicioActividad';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Registrar actividad
    public function registrarActividad($datos)
    {
        try {
            $this->db->beginTransaction();

            $nuevoId = $this->generarNuevoId();

            $sql = "INSERT INTO actividad (
                idActividad, nombreActividad, fechainicioActividad, fechafinActividad,
                cantSesionesPlanificada, objetivoActividad, descActividad, cantPersoAtender,
                observacion, idTipoActividad, idVertice, idAreaE, idEmpleado,
                idUnidadMedida, idGrupoEtnio, idDocente, idEstatus, idHorario, idEstDesarrollo
            ) VALUES (
                :id, :nombre, :fechaIni, :fechaFin, :cantSesiones, :objetivo, :descripcion,
                :cantPerso, :observacion, :tipoAct, :vertice, :areaE, :empleado,
                :unidadMed, :grupoEtnio, :docente, :estatus, :horario, :estDesarrollo
            )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $nuevoId,
                'nombre' => $datos['nombreActividad'],
                'fechaIni' => $datos['fechainicioActividad'],
                'fechaFin' => $datos['fechafinActividad'],
                'cantSesiones' => $datos['cantSesionesPlanificada'],
                'objetivo' => $datos['objetivoActividad'],
                'descripcion' => $datos['descActividad'],
                'cantPerso' => $datos['cantPersoAtender'],
                'observacion' => $datos['observacion'],
                'tipoAct' => $datos['idTipoActividad'],
                'vertice' => $datos['idVertice'],
                'areaE' => $datos['idAreaE'],
                'empleado' => $datos['idEmpleado'],
                'unidadMed' => $datos['idUnidadMedida'],
                'grupoEtnio' => $datos['idGrupoEtnio'],
                'docente' => $datos['idDocente'],
                'estatus' => $datos['idEstatus'],
                'horario' => $datos['idHorario'],
                'estDesarrollo' => $datos['idEstDesarrollo']
            ]);

            $this->insertarLugares($nuevoId, $datos);
            $this->insertarRelaciones($nuevoId, $datos);

            if (!empty($datos['gruposEtarios']) && is_array($datos['gruposEtarios'])) {
                foreach ($datos['gruposEtarios'] as $idGrupoEtario) {
                    $idGrupoEtAct = $this->generarNuevoIdGrupoEtarioAct();
                    $sqlGrupo = "INSERT INTO grupoetarioactividad (idGrupoEtareoActividad, idGrupoEtareo, idActividad)
                                 VALUES (:id, :grupo, :actividad)";
                    $stmtGrupo = $this->db->prepare($sqlGrupo);
                    $stmtGrupo->execute([
                        'id' => $idGrupoEtAct,
                        'grupo' => $idGrupoEtario,
                        'actividad' => $nuevoId
                    ]);
                }
            }

            if (!empty($datos['fechasSesiones']) && is_array($datos['fechasSesiones'])) {
                $nroSesion = 1;
                foreach ($datos['fechasSesiones'] as $fechaSesion) {
                    $idSeg = $this->generarNuevoIdSeguimiento();
                    $sqlSeg = "INSERT INTO seguimientoactividad (
                        idSegActividad, nroSesionPlanificada, fechaSesion, 
                        logroActividad, observObstaculo, idActividad, idTipEntrega
                    ) VALUES (:id, :nro, :fecha, '', '', :actividad, :tipoEntrega)";
                    $stmtSeg = $this->db->prepare($sqlSeg);
                    $stmtSeg->execute([
                        'id' => $idSeg,
                        'nro' => $nroSesion,
                        'fecha' => $fechaSesion,
                        'actividad' => $nuevoId,
                        'tipoEntrega' => $datos['idTipEntrega'] ?? 'TE0001'
                    ]);
                    $nroSesion++;
                }
            }

            $this->db->commit();
            return $nuevoId;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error al registrar actividad: " . $e->getMessage());
            return false;
        }
    }

    // Obtener actividad por id
    public function obtenerActividadPorId($id)
    {
        $sql = "SELECT a.*, 
                ta.nomTipoActividad, v.nombreVertice, ae.nomAreaE,
                CONCAT(e.nombres, ' ', e.apellidos) AS nomEmpleado,
                CONCAT(d.nombreDocente, ' ', d.apellidoDocente) AS nomDocente,
                es.nomEstatus, h.nomHorario, ed.nomEstDesarrollo,
                um.nomUnidadMedida, ge.nomGrupoEtnio
                FROM actividad a
                LEFT JOIN tipoactividad ta ON a.idTipoActividad = ta.idTipoActividad
                LEFT JOIN vertice v ON a.idVertice = v.idVertice
                LEFT JOIN areaespecifica ae ON a.idAreaE = ae.idAreaE
                LEFT JOIN empleado e ON a.idEmpleado = e.idEmpleado
                LEFT JOIN docente d ON a.idDocente = d.idDocente
                LEFT JOIN estatus es ON a.idEstatus = es.idEstatus
                LEFT JOIN horario h ON a.idHorario = h.idHorario
                LEFT JOIN estrategiadesarrollo ed ON a.idEstDesarrollo = ed.idEstDesarrollo
                LEFT JOIN unidadmedida um ON a.idUnidadMedida = um.idUnidadMedida
                LEFT JOIN grupoetnio ge ON a.idGrupoEtnio = ge.idGrupoEtnio
                WHERE a.idActividad = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerLugaresActividadPorId($idActividad)
    {
        $sql = "SELECT lra.*, la.nomLugarActividad, la.esSede, eu.nombreEspacioUtilizar, eu.capacidad
                FROM lugarrealizaactividad lra
                LEFT JOIN lugaractividad la ON lra.idLugarActividad = la.idLugarActividad
                LEFT JOIN espacioutilizar eu ON lra.idEspacioUtilizar = eu.idEspacioUtilizar
                WHERE lra.idActividad = :id
                ORDER BY lra.created_at, lra.idReaActividad";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idActividad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerLugarActividadPorId($idActividad)
    {
        $lugares = $this->obtenerLugaresActividadPorId($idActividad);
        return $lugares ? $lugares[0] : false;
    }

    public function obtenerHorariosActividad($idActividad)
    {
        $stmt = $this->db->prepare("SELECT ah.idHorario, h.nomHorario
                FROM actividadhorario ah
                INNER JOIN horario h ON h.idHorario = ah.idHorario
                WHERE ah.idActividad = ? ORDER BY h.nomHorario");
        $stmt->execute([$idActividad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEmpleadosActividad($idActividad)
    {
        $stmt = $this->db->prepare("SELECT ae.idEmpleado, CONCAT(e.nombres, ' ', e.apellidos) AS nombreCompleto, e.cedulaEmpleado
                FROM actividadempleado ae
                INNER JOIN empleado e ON e.idEmpleado = ae.idEmpleado
                WHERE ae.idActividad = ? ORDER BY e.nombres, e.apellidos");
        $stmt->execute([$idActividad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDocentesActividad($idActividad)
    {
        $stmt = $this->db->prepare("SELECT ad.idDocente, CONCAT(d.nombreDocente, ' ', d.apellidoDocente) AS nombreCompleto, d.cedDocente
                FROM actividaddocente ad
                INNER JOIN docente d ON d.idDocente = ad.idDocente
                WHERE ad.idActividad = ? ORDER BY d.nombreDocente, d.apellidoDocente");
        $stmt->execute([$idActividad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerGruposEtariosActividad($idActividad)
    {
        $sql = "SELECT gea.*, ge.nomGrupoEtareo, ge.edadMin, ge.edadMax
                FROM grupoetarioactividad gea
                INNER JOIN grupoetario ge ON gea.idGrupoEtareo = ge.idGrupoEtareo
                WHERE gea.idActividad = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idActividad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSeguimientoActividad($idActividad)
    {
        $sql = "SELECT sa.*, te.nomTipEntrega
                FROM seguimientoactividad sa
                LEFT JOIN tipoentrega te ON sa.idTipEntrega = te.idTipEntrega
                WHERE sa.idActividad = :id ORDER BY sa.nroSesionPlanificada";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idActividad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Eliminar
    public function eliminarActividad($id)
    {
        try {
            $this->db->beginTransaction();



            $sql = "DELETE asi FROM asistencia asi
                    JOIN seguimientoactividad sa ON asi.idSegActividad = sa.idSegActividad
                    WHERE sa.idActividad = ?";
            $this->db->prepare($sql)->execute([$id]);


            $sql = "DELETE FROM seguimientoactividad WHERE idActividad = ?";
            $this->db->prepare($sql)->execute([$id]);


            $sql = "DELETE FROM grupoetarioactividad WHERE idActividad = ?";
            $this->db->prepare($sql)->execute([$id]);


            $sql = "DELETE FROM lugarrealizaactividad WHERE idActividad = ?";
            $this->db->prepare($sql)->execute([$id]);


            $sql = "DELETE FROM actividad WHERE idActividad = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            $this->db->commit();
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // Editar actividad
    public function editarActividad($datos)
    {
        try {
            $this->db->beginTransaction();
            $id = $datos['idActividad'];

            $sql = "UPDATE actividad SET
                nombreActividad = :nombre, fechainicioActividad = :fechaIni,
                fechafinActividad = :fechaFin, cantSesionesPlanificada = :cantSesiones,
                objetivoActividad = :objetivo, descActividad = :descripcion,
                cantPersoAtender = :cantPerso, observacion = :observacion,
                idTipoActividad = :tipoAct, idVertice = :vertice, idAreaE = :areaE,
                idEmpleado = :empleado, idUnidadMedida = :unidadMed,
                idGrupoEtnio = :grupoEtnio, idDocente = :docente,
                idEstatus = :estatus, idHorario = :horario, idEstDesarrollo = :estDesarrollo
                WHERE idActividad = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $id, 'nombre' => $datos['nombreActividad'],
                'fechaIni' => $datos['fechainicioActividad'], 'fechaFin' => $datos['fechafinActividad'],
                'cantSesiones' => $datos['cantSesionesPlanificada'], 'objetivo' => $datos['objetivoActividad'],
                'descripcion' => $datos['descActividad'], 'cantPerso' => $datos['cantPersoAtender'],
                'observacion' => $datos['observacion'], 'tipoAct' => $datos['idTipoActividad'],
                'vertice' => $datos['idVertice'], 'areaE' => $datos['idAreaE'],
                'empleado' => $datos['idEmpleado'], 'unidadMed' => $datos['idUnidadMedida'],
                'grupoEtnio' => $datos['idGrupoEtnio'], 'docente' => $datos['idDocente'],
                'estatus' => $datos['idEstatus'], 'horario' => $datos['idHorario'],
                'estDesarrollo' => $datos['idEstDesarrollo']
            ]);

            $this->db->prepare("DELETE FROM lugarrealizaactividad WHERE idActividad = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM actividadhorario WHERE idActividad = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM actividadempleado WHERE idActividad = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM actividaddocente WHERE idActividad = ?")->execute([$id]);
            $this->insertarLugares($id, $datos);
            $this->insertarRelaciones($id, $datos);

            $this->db->prepare("DELETE FROM grupoetarioactividad WHERE idActividad = ?")->execute([$id]);
            $sqlGrupo = "INSERT INTO grupoetarioactividad (idGrupoEtareoActividad, idGrupoEtareo, idActividad)
                         VALUES (:id, :grupo, :actividad)";
            $stmtGrupo = $this->db->prepare($sqlGrupo);
            foreach ($datos['gruposEtarios'] as $idGrupoEtario) {
                $stmtGrupo->execute([
                    'id' => $this->generarNuevoIdGrupoEtarioAct(),
                    'grupo' => $idGrupoEtario, 'actividad' => $id
                ]);
            }



            $stmtExistentes = $this->db->prepare("SELECT * FROM seguimientoactividad WHERE idActividad = ? ORDER BY nroSesionPlanificada");
            $stmtExistentes->execute([$id]);
            $existentes = $stmtExistentes->fetchAll(PDO::FETCH_ASSOC);
            $idsUtilizados = [];
            $sqlActualizar = "UPDATE seguimientoactividad SET nroSesionPlanificada = ?, fechaSesion = ?, idTipEntrega = ? WHERE idSegActividad = ?";
            $stmtActualizar = $this->db->prepare($sqlActualizar);
            $sqlInsertar = "INSERT INTO seguimientoactividad
                (idSegActividad, nroSesionPlanificada, fechaSesion, logroActividad, observObstaculo, idActividad, idTipEntrega)
                VALUES (?, ?, ?, '', '', ?, ?)";
            $stmtInsertar = $this->db->prepare($sqlInsertar);

            foreach (array_values($datos['fechasSesiones']) as $indice => $fechaSesion) {
                $nro = $indice + 1;
                $tipoEntrega = $datos['idTipEntrega'] ?? 'TE0001';
                if (isset($existentes[$indice])) {
                    $idSeg = $existentes[$indice]['idSegActividad'];
                    $stmtActualizar->execute([$nro, $fechaSesion, $tipoEntrega, $idSeg]);
                } else {
                    $idSeg = $this->generarNuevoIdSeguimiento();
                    $stmtInsertar->execute([$idSeg, $nro, $fechaSesion, $id, $tipoEntrega]);
                }
                $idsUtilizados[] = $idSeg;
            }

            foreach (array_slice($existentes, count($datos['fechasSesiones'])) as $sobrante) {
                $stmtAsistencia = $this->db->prepare("SELECT COUNT(*) FROM asistencia WHERE idSegActividad = ?");
                $stmtAsistencia->execute([$sobrante['idSegActividad']]);
                if ($stmtAsistencia->fetchColumn() > 0) {
                    throw new RuntimeException('No se pueden eliminar sesiones que ya tienen asistencia registrada.');
                }
                $this->db->prepare("DELETE FROM seguimientoactividad WHERE idSegActividad = ?")->execute([$sobrante['idSegActividad']]);
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log("Error al editar actividad: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nuevo lugar de actividad
    public function registrarLugarActividad($nombre, $descripcion, $direccion, $esSede, $idParroquia)
    {
        try {
            $sql = "SELECT idLugarActividad FROM lugaractividad ORDER BY idLugarActividad DESC LIMIT 1";
            $stmt = $this->db->query($sql);
            $ultimoId = $stmt->fetchColumn();

            if (!$ultimoId) {
                $nuevoId = "LA0001";
            } else {
                $numero = substr($ultimoId, 2);
                $nuevoNumero = intval($numero) + 1;
                $nuevoId = "LA" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
            }

            $sql = "INSERT INTO lugaractividad (idLugarActividad, nomLugarActividad, desLugarActividad, direccion, esSede, idParroquia)
                    VALUES (:id, :nombre, :descripcion, :direccion, :esSede, :idParroquia)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $nuevoId,
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'direccion' => $direccion,
                'esSede' => $esSede ? 1 : 0,
                'idParroquia' => $idParroquia
            ]);

            return $nuevoId;
        } catch (PDOException $e) {
            error_log("Error al registrar lugar: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerParroquias()
    {
        $sql = "SELECT p.idParroquia, p.nombreParroquia, m.nombreMunicipio, e.nombreEstado
                FROM parroquia p
                INNER JOIN municipio m ON p.idMunicipio = m.idMunicipio
                INNER JOIN estado e ON m.idEstado = e.idEstado
                ORDER BY e.nombreEstado, m.nombreMunicipio, p.nombreParroquia";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }


    // Registrar nuevo docente
    public function registrarDocente($cedula, $nacionalidad, $nombres, $apellidos, $telefono)
    {
        try {
            $check = $this->db->prepare("SELECT idDocente FROM docente WHERE cedDocente = ?");
            $check->execute([$cedula]);
            if ($check->fetch()) {
                return false;
            }

            $sql = "SELECT idDocente FROM docente ORDER BY idDocente DESC LIMIT 1";
            $stmt = $this->db->query($sql);
            $ultimoId = $stmt->fetchColumn();

            if (!$ultimoId) {
                $nuevoId = "DC0001";
            } else {
                $numero = substr($ultimoId, 2);
                $nuevoNumero = intval($numero) + 1;
                $nuevoId = "DC" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
            }

            $sql = "INSERT INTO docente (idDocente, cedDocente, nacionalidad, nombreDocente, apellidoDocente, telfDocente)
                    VALUES (:id, :cedula, :nacionalidad, :nombres, :apellidos, :telefono)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $nuevoId,
                'cedula' => $cedula,
                'nacionalidad' => $nacionalidad,
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'telefono' => $telefono
            ]);

            return $nuevoId;
        } catch (PDOException $e) {
            error_log("Error al registrar docente: " . $e->getMessage());
            return false;
        }
    }

}