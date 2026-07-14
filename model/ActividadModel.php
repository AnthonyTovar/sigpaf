<?php
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
        $sql = "SELECT idReaActividad FROM lugarRealizaActividad ORDER BY idReaActividad DESC LIMIT 1";
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
        $sql = "SELECT idGrupoEtareoActividad FROM grupoEtarioActividad ORDER BY idGrupoEtareoActividad DESC LIMIT 1";
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
        $sql = "SELECT idSegActividad FROM seguimientoActividad ORDER BY idSegActividad DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "SA0001";
        }

        $numero = substr($ultimoId, 2);
        $nuevoNumero = intval($numero) + 1;

        return "SA" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
    }

    // ========== LISTAR ==========
    public function listarActividades()
    {
        $sql = "SELECT a.*, 
                ta.nomTipoActividad, v.nombreVertice, ae.nomAreaE,
                e.nombres AS nomEmpleado, e.apellidos AS apeEmpleado,
                d.nombreDocente, d.apellidoDocente,
                es.nomEstatus, h.nomHorario, ed.nomEstDesarrollo,
                um.nomUnidadMedida, ge.nomGrupoEtnio
                FROM actividad a
                LEFT JOIN tipoActividad ta ON a.idTipoActividad = ta.idTipoActividad
                LEFT JOIN vertice v ON a.idVertice = v.idVertice
                LEFT JOIN areaEspecifica ae ON a.idAreaE = ae.idAreaE
                LEFT JOIN empleado e ON a.idEmpleado = e.idEmpleado
                LEFT JOIN docente d ON a.idDocente = d.idDocente
                LEFT JOIN estatus es ON a.idEstatus = es.idEstatus
                LEFT JOIN horario h ON a.idHorario = h.idHorario
                LEFT JOIN estrategiaDesarrollo ed ON a.idEstDesarrollo = ed.idEstDesarrollo
                LEFT JOIN unidadMedida um ON a.idUnidadMedida = um.idUnidadMedida
                LEFT JOIN grupoEtnio ge ON a.idGrupoEtnio = ge.idGrupoEtnio
                ORDER BY a.fechainicioActividad DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== DATOS MAESTROS PARA SELECTS ==========
    public function obtenerEstrategiasDesarrollo()
    {
        $sql = "SELECT idEstDesarrollo, nomEstDesarrollo FROM estrategiaDesarrollo ORDER BY nomEstDesarrollo";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTiposActividad()
    {
        $sql = "SELECT idTipoActividad, nomTipoActividad FROM tipoActividad ORDER BY nomTipoActividad";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerVertices()
    {
        $sql = "SELECT idVertice, nombreVertice FROM vertice ORDER BY nombreVertice";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerAreasEspecificas()
    {
        $sql = "SELECT idAreaE, nomAreaE FROM areaEspecifica ORDER BY nomAreaE";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerGruposEtarios()
    {
        $sql = "SELECT idGrupoEtareo, nomGrupoEtareo, edadMin, edadMax FROM grupoEtario ORDER BY edadMin";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerGruposEtnicos()
    {
        $sql = "SELECT idGrupoEtnio, nomGrupoEtnio FROM grupoEtnio ORDER BY nomGrupoEtnio";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerUnidadesMedida()
    {
        $sql = "SELECT idUnidadMedida, nomUnidadMedida FROM unidadMedida ORDER BY nomUnidadMedida";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerLugaresActividad()
    {
        $sql = "SELECT idLugarActividad, nomLugarActividad, esSede FROM lugarActividad ORDER BY nomLugarActividad";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEspaciosUtilizar()
    {
        $sql = "SELECT idEspacioUtilizar, nombreEspacioUtilizar, capacidad FROM espacioUtilizar ORDER BY nombreEspacioUtilizar";
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
        $sql = "SELECT idTipEntrega, nomTipEntrega FROM tipoEntrega ORDER BY nomTipEntrega";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== FECHAS OCUPADAS PARA CALENDARIO ==========
    public function obtenerFechasOcupadas()
    {
        $sql = "SELECT DISTINCT fechainicioActividad, fechafinActividad FROM actividad 
                WHERE fechafinActividad >= CURDATE()";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== HORARIOS OCUPADOS POR FECHA Y ESPACIO ==========
    public function obtenerHorariosOcupados($fecha, $idEspacio = null, $idLugar = null)
    {
        $params = [$fecha, $fecha];
        $sql = "SELECT DISTINCT a.idHorario, h.nomHorario 
                FROM actividad a
                INNER JOIN lugarRealizaActividad lra ON a.idActividad = lra.idActividad
                INNER JOIN horario h ON a.idHorario = h.idHorario
                WHERE a.fechainicioActividad <= ? AND a.fechafinActividad >= ?";
        
        if ($idEspacio) {
            $sql .= " AND lra.idEspacioUtilizar = ?";
            $params[] = $idEspacio;
        }
        if ($idLugar) {
            $sql .= " AND lra.idLugarActividad = ?";
            $params[] = $idLugar;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== REGISTRAR ACTIVIDAD COMPLETA ==========
    public function registrarActividad($datos)
    {
        try {
            $this->db->beginTransaction();

            $nuevoId = $this->generarNuevoId();

            // Insertar actividad principal
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

            // Insertar lugar realiza actividad
            $idLugarRealiza = $this->generarNuevoIdLugarRealiza();
            $sqlLugar = "INSERT INTO lugarRealizaActividad (idReaActividad, idEspacioUtilizar, idLugarActividad, idActividad)
                         VALUES (:id, :espacio, :lugar, :actividad)";
            $stmtLugar = $this->db->prepare($sqlLugar);
            $stmtLugar->execute([
                'id' => $idLugarRealiza,
                'espacio' => $datos['idEspacioUtilizar'],
                'lugar' => $datos['idLugarActividad'],
                'actividad' => $nuevoId
            ]);

            // Insertar grupos etarios seleccionados
            if (!empty($datos['gruposEtarios']) && is_array($datos['gruposEtarios'])) {
                foreach ($datos['gruposEtarios'] as $idGrupoEtario) {
                    $idGrupoEtAct = $this->generarNuevoIdGrupoEtarioAct();
                    $sqlGrupo = "INSERT INTO grupoEtarioActividad (idGrupoEtareoActividad, idGrupoEtareo, idActividad)
                                 VALUES (:id, :grupo, :actividad)";
                    $stmtGrupo = $this->db->prepare($sqlGrupo);
                    $stmtGrupo->execute([
                        'id' => $idGrupoEtAct,
                        'grupo' => $idGrupoEtario,
                        'actividad' => $nuevoId
                    ]);
                }
            }

            // Insertar seguimiento de sesiones
            if (!empty($datos['fechasSesiones']) && is_array($datos['fechasSesiones'])) {
                $nroSesion = 1;
                foreach ($datos['fechasSesiones'] as $fechaSesion) {
                    $idSeg = $this->generarNuevoIdSeguimiento();
                    $sqlSeg = "INSERT INTO seguimientoActividad (
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

    // ========== OBTENER ACTIVIDAD POR ID ==========
    public function obtenerActividadPorId($id)
    {
        $sql = "SELECT a.*, 
                ta.nomTipoActividad, v.nombreVertice, ae.nomAreaE,
                CONCAT(e.nombres, ' ', e.apellidos) AS nomEmpleado,
                CONCAT(d.nombreDocente, ' ', d.apellidoDocente) AS nomDocente,
                es.nomEstatus, h.nomHorario, ed.nomEstDesarrollo,
                um.nomUnidadMedida, ge.nomGrupoEtnio
                FROM actividad a
                LEFT JOIN tipoActividad ta ON a.idTipoActividad = ta.idTipoActividad
                LEFT JOIN vertice v ON a.idVertice = v.idVertice
                LEFT JOIN areaEspecifica ae ON a.idAreaE = ae.idAreaE
                LEFT JOIN empleado e ON a.idEmpleado = e.idEmpleado
                LEFT JOIN docente d ON a.idDocente = d.idDocente
                LEFT JOIN estatus es ON a.idEstatus = es.idEstatus
                LEFT JOIN horario h ON a.idHorario = h.idHorario
                LEFT JOIN estrategiaDesarrollo ed ON a.idEstDesarrollo = ed.idEstDesarrollo
                LEFT JOIN unidadMedida um ON a.idUnidadMedida = um.idUnidadMedida
                LEFT JOIN grupoEtnio ge ON a.idGrupoEtnio = ge.idGrupoEtnio
                WHERE a.idActividad = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerLugarActividadPorId($idActividad)
    {
        $sql = "SELECT lra.*, la.nomLugarActividad, la.esSede, eu.nombreEspacioUtilizar, eu.capacidad
                FROM lugarRealizaActividad lra
                LEFT JOIN lugarActividad la ON lra.idLugarActividad = la.idLugarActividad
                LEFT JOIN espacioUtilizar eu ON lra.idEspacioUtilizar = eu.idEspacioUtilizar
                WHERE lra.idActividad = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idActividad]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerGruposEtariosActividad($idActividad)
    {
        $sql = "SELECT gea.*, ge.nomGrupoEtareo, ge.edadMin, ge.edadMax
                FROM grupoEtarioActividad gea
                INNER JOIN grupoEtario ge ON gea.idGrupoEtareo = ge.idGrupoEtareo
                WHERE gea.idActividad = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idActividad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSeguimientoActividad($idActividad)
    {
        $sql = "SELECT sa.*, te.nomTipEntrega
                FROM seguimientoActividad sa
                LEFT JOIN tipoEntrega te ON sa.idTipEntrega = te.idTipEntrega
                WHERE sa.idActividad = :id ORDER BY sa.nroSesionPlanificada";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idActividad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== ELIMINAR ==========
    public function eliminarActividad($id)
    {
        try {
            $this->db->beginTransaction();

            // Eliminar seguimientos
            $sql = "DELETE FROM seguimientoActividad WHERE idActividad = ?";
            $this->db->prepare($sql)->execute([$id]);

            // Eliminar grupos etarios
            $sql = "DELETE FROM grupoEtarioActividad WHERE idActividad = ?";
            $this->db->prepare($sql)->execute([$id]);

            // Eliminar lugar realiza
            $sql = "DELETE FROM lugarRealizaActividad WHERE idActividad = ?";
            $this->db->prepare($sql)->execute([$id]);

            // Eliminar actividad
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

    // ========== EDITAR ACTIVIDAD COMPLETA ==========
    public function editarActividad($datos)
    {
        try {
            $this->db->beginTransaction();

            $id = $datos['idActividad'];

            // Actualizar actividad principal
            $sql = "UPDATE actividad SET
                nombreActividad = :nombre,
                fechainicioActividad = :fechaIni,
                fechafinActividad = :fechaFin,
                cantSesionesPlanificada = :cantSesiones,
                objetivoActividad = :objetivo,
                descActividad = :descripcion,
                cantPersoAtender = :cantPerso,
                observacion = :observacion,
                idTipoActividad = :tipoAct,
                idVertice = :vertice,
                idAreaE = :areaE,
                idEmpleado = :empleado,
                idUnidadMedida = :unidadMed,
                idGrupoEtnio = :grupoEtnio,
                idDocente = :docente,
                idEstatus = :estatus,
                idHorario = :horario,
                idEstDesarrollo = :estDesarrollo
                WHERE idActividad = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $id,
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

            // Actualizar lugar realiza actividad
            $sqlLugar = "UPDATE lugarRealizaActividad SET
                         idEspacioUtilizar = :espacio,
                         idLugarActividad = :lugar
                         WHERE idActividad = :actividad";
            $stmtLugar = $this->db->prepare($sqlLugar);
            $stmtLugar->execute([
                'espacio' => $datos['idEspacioUtilizar'],
                'lugar' => $datos['idLugarActividad'],
                'actividad' => $id
            ]);

            // Actualizar grupos etarios: eliminar y reinsertar
            $sqlDeleteGrupos = "DELETE FROM grupoEtarioActividad WHERE idActividad = ?";
            $this->db->prepare($sqlDeleteGrupos)->execute([$id]);

            if (!empty($datos['gruposEtarios']) && is_array($datos['gruposEtarios'])) {
                foreach ($datos['gruposEtarios'] as $idGrupoEtario) {
                    $idGrupoEtAct = $this->generarNuevoIdGrupoEtarioAct();
                    $sqlGrupo = "INSERT INTO grupoEtarioActividad (idGrupoEtareoActividad, idGrupoEtareo, idActividad)
                                 VALUES (:id, :grupo, :actividad)";
                    $stmtGrupo = $this->db->prepare($sqlGrupo);
                    $stmtGrupo->execute([
                        'id' => $idGrupoEtAct,
                        'grupo' => $idGrupoEtario,
                        'actividad' => $id
                    ]);
                }
            }

            // Actualizar seguimiento de sesiones: eliminar y reinsertar
            $sqlDeleteSeg = "DELETE FROM seguimientoActividad WHERE idActividad = ?";
            $this->db->prepare($sqlDeleteSeg)->execute([$id]);

            if (!empty($datos['fechasSesiones']) && is_array($datos['fechasSesiones'])) {
                $nroSesion = 1;
                foreach ($datos['fechasSesiones'] as $fechaSesion) {
                    $idSeg = $this->generarNuevoIdSeguimiento();
                    $sqlSeg = "INSERT INTO seguimientoActividad (
                        idSegActividad, nroSesionPlanificada, fechaSesion,
                        logroActividad, observObstaculo, idActividad, idTipEntrega
                    ) VALUES (:id, :nro, :fecha, '', '', :actividad, :tipoEntrega)";
                    $stmtSeg = $this->db->prepare($sqlSeg);
                    $stmtSeg->execute([
                        'id' => $idSeg,
                        'nro' => $nroSesion,
                        'fecha' => $fechaSesion,
                        'actividad' => $id,
                        'tipoEntrega' => $datos['idTipEntrega'] ?? 'TE0001'
                    ]);
                    $nroSesion++;
                }
            }

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error al editar actividad: " . $e->getMessage());
            return false;
        }
    }

    // ========== REGISTRAR NUEVO LUGAR DE ACTIVIDAD ==========
    public function registrarLugarActividad($nombre, $descripcion, $direccion, $esSede, $idParroquia)
    {
        try {
            $sql = "SELECT idLugarActividad FROM lugarActividad ORDER BY idLugarActividad DESC LIMIT 1";
            $stmt = $this->db->query($sql);
            $ultimoId = $stmt->fetchColumn();

            if (!$ultimoId) {
                $nuevoId = "LA0001";
            } else {
                $numero = substr($ultimoId, 2);
                $nuevoNumero = intval($numero) + 1;
                $nuevoId = "LA" . str_pad($nuevoNumero, 4, "0", STR_PAD_LEFT);
            }

            $sql = "INSERT INTO lugarActividad (idLugarActividad, nomLugarActividad, desLugarActividad, direccion, esSede, idParroquia)
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


    // ========== REGISTRAR NUEVO DOCENTE ==========
    public function registrarDocente($cedula, $nacionalidad, $nombres, $apellidos, $telefono)
    {
        try {
            // Verificar si la cédula ya existe
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