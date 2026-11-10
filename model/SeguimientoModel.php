<?php
// Modelo de seguimiento



require_once 'model/ConexionModel.php';


class SeguimientoModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }


    private function generarNuevoIdBeneficiario()
    {
        $stmt = $this->db->query("SELECT idBeneficiario FROM beneficiario ORDER BY idBeneficiario DESC LIMIT 1");
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "BEN0001";
        }
        $numero = (int) substr($ultimoId, 3);
        return "BEN" . str_pad($numero + 1, 4, "0", STR_PAD_LEFT);
    }


    private function generarNuevoIdSeguimiento()
    {
        $stmt = $this->db->query("SELECT idSegActividad FROM seguimientoActividad ORDER BY idSegActividad DESC LIMIT 1");
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "SA0001";
        }
        return "SA" . str_pad(intval(substr($ultimoId, 2)) + 1, 4, "0", STR_PAD_LEFT);
    }


    private function ultimoNumeroAsistencia()
    {
        $stmt = $this->db->query("SELECT idAsistencia FROM asistencia ORDER BY idAsistencia DESC LIMIT 1");
        $ultimo = $stmt->fetchColumn();
        return $ultimo ? intval(substr($ultimo, 2)) : 0;
    }








    public function ensureSesiones($idActividad)
    {
        $sesiones = $this->obtenerSesiones($idActividad);
        if (count($sesiones) > 0) {
            return $sesiones;
        }

        $stmt = $this->db->prepare("SELECT fechainicioActividad, fechafinActividad, cantSesionesPlanificada
                                    FROM actividad WHERE idActividad = ?");
        $stmt->execute([$idActividad]);
        $act = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$act) {
            return [];
        }

        $inicio = new DateTime($act['fechainicioActividad']);
        $fin = new DateTime($act['fechafinActividad']);
        $cant = (int) $act['cantSesionesPlanificada'];
        if ($cant < 1) {
            $cant = 1;
        }

        $diasTotales = (int) $inicio->diff($fin)->format('%a') + 1;
        $intervalo = intdiv($diasTotales, $cant);
        if ($intervalo < 1) {
            $intervalo = 1;
        }


        $fechas = [];
        for ($i = 0; $i < $cant; $i++) {
            $f = clone $inicio;
            $f->modify('+' . ($i * $intervalo) . ' days');
            if ($f > $fin) {
                $f = clone $fin;
            }
            $fechas[$f->format('Y-m-d')] = true;
        }
        $fechas = array_keys($fechas);
        sort($fechas);



        $nro = 1;
        foreach ($fechas as $fecha) {
            $stmtIns = $this->db->prepare("INSERT INTO seguimientoActividad (
                    idSegActividad, nroSesionPlanificada, fechaSesion,
                    logroActividad, observObstaculo, idActividad, idTipEntrega
                ) VALUES (:id, :nro, :fecha, '', '', :act, 'TE0001')");
            $stmtIns->execute([
                'id' => $this->generarNuevoIdSeguimiento(),
                'nro' => $nro,
                'fecha' => $fecha,
                'act' => $idActividad
            ]);
            $nro++;
        }

        return $this->obtenerSesiones($idActividad);
    }

    // Actividades con progreso


    // Curso regular automático del estatus
    private function sincronizarEstatusAutomatico()
    {
        require_once 'model/ActividadModel.php';
        (new ActividadModel())->sincronizarEstatusAutomatico();
    }

    public function listarActividadesProgreso($filtros = [])
    {
        $this->sincronizarEstatusAutomatico();

        $sql = "SELECT
                    a.idActividad,
                    a.nombreActividad,
                    a.fechainicioActividad,
                    a.fechafinActividad,
                    a.cantPersoAtender,
                    a.idEmpleado,
                    a.idEstatus,
                    e.nomEstatus,
                    ta.nomTipoActividad,
                    (SELECT COUNT(*) FROM seguimientoActividad sa
                      WHERE sa.idActividad = a.idActividad) AS sesiones_planificadas,
                    (SELECT COUNT(*) FROM seguimientoActividad sa
                      WHERE sa.idActividad = a.idActividad
                        AND sa.logroActividad <> '') AS sesiones_registradas,
                    (SELECT COUNT(DISTINCT asi.idBeneficiario)
                       FROM asistencia asi
                       JOIN seguimientoActividad sa2 ON asi.idSegActividad = sa2.idSegActividad
                      WHERE sa2.idActividad = a.idActividad) AS beneficiarios_atendidos
                FROM actividad a
                JOIN estatus e ON e.idEstatus = a.idEstatus
                LEFT JOIN tipoActividad ta ON ta.idTipoActividad = a.idTipoActividad
                WHERE 1=1";
        $params = [];

        if (!empty($filtros['desde'])) {
            $sql .= " AND a.fechafinActividad >= :desde";
            $params['desde'] = $filtros['desde'];
        }
        if (!empty($filtros['hasta'])) {
            $sql .= " AND a.fechainicioActividad <= :hasta";
            $params['hasta'] = $filtros['hasta'];
        }
        if (!empty($filtros['idEstatus'])) {
            $sql .= " AND a.idEstatus = :estatus";
            $params['estatus'] = $filtros['idEstatus'];
        }

        $sql .= " ORDER BY a.fechainicioActividad DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Detalle completo de la actividad
    public function obtenerActividadDetalle($idActividad)
    {
        $sql = "SELECT a.idActividad, a.nombreActividad, a.objetivoActividad, a.descActividad,
                       a.fechainicioActividad, a.fechafinActividad, a.cantPersoAtender,
                       a.idEmpleado, a.idEstatus, e.nomEstatus, ta.nomTipoActividad
                FROM actividad a
                LEFT JOIN estatus e ON e.idEstatus = a.idEstatus
                LEFT JOIN tipoActividad ta ON ta.idTipoActividad = a.idTipoActividad
                WHERE a.idActividad = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idActividad]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Detalle de una sesión
    public function obtenerSesionDetalle($idSegActividad)
    {
        $sql = "SELECT sa.*, te.nomTipEntrega,
                       (SELECT COUNT(*) FROM asistencia asi
                         WHERE asi.idSegActividad = sa.idSegActividad) AS totalAsistentes
                FROM seguimientoActividad sa
                LEFT JOIN tipoEntrega te ON te.idTipEntrega = sa.idTipEntrega
                WHERE sa.idSegActividad = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idSegActividad]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Beneficiarios que asistieron a una sesión
    public function obtenerBeneficiariosSesion($idSegActividad)
    {
        $sql = "SELECT b.idBeneficiario, b.cedulaBeneficiario, b.nombresBeneficiario,
                       b.apellidosBeneficiario, b.nacionalidad, b.fechaNacBeneficiario, b.sexoBeneficiario
                FROM asistencia asi
                JOIN beneficiario b ON b.idBeneficiario = asi.idBeneficiario
                WHERE asi.idSegActividad = :id
                ORDER BY b.apellidosBeneficiario, b.nombresBeneficiario";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idSegActividad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public function obtenerEstatus()
    {
        return $this->db->query("SELECT idEstatus, nomEstatus FROM estatus ORDER BY nomEstatus")
                        ->fetchAll(PDO::FETCH_ASSOC);
    }


    public function existeEstatus($idEstatus)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM estatus WHERE idEstatus = ?");
        $stmt->execute([$idEstatus]);
        return (int) $stmt->fetchColumn() > 0;
    }




    public function actualizarEstatusActividad($idActividad, $idEstatus)
    {
        $stmt = $this->db->prepare("UPDATE actividad SET idEstatus = :est
                                    WHERE idActividad = :id");
        return $stmt->execute(['est' => $idEstatus, 'id' => $idActividad]);
    }





    public function contarSesionesRegistradas($idActividad)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM seguimientoActividad
                                    WHERE idActividad = :id AND logroActividad <> ''");
        $stmt->execute(['id' => $idActividad]);
        return (int) $stmt->fetchColumn();
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Sesiones de una actividad
    public function obtenerSesiones($idActividad)
    {
        $sql = "SELECT
                    sa.idSegActividad,
                    sa.nroSesionPlanificada,
                    sa.fechaSesion,
                    sa.logroActividad,
                    sa.observObstaculo,
                    sa.idTipEntrega,
                    te.nomTipEntrega,
                    (SELECT COUNT(*) FROM asistencia asi
                      WHERE asi.idSegActividad = sa.idSegActividad) AS totalAsistentes
                FROM seguimientoActividad sa
                LEFT JOIN tipoEntrega te ON te.idTipEntrega = sa.idTipEntrega
                WHERE sa.idActividad = :id
                ORDER BY sa.nroSesionPlanificada";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idActividad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerActividad($idActividad)
    {
        $sql = "SELECT idActividad, nombreActividad, cantPersoAtender, idEmpleado, idEstatus
                FROM actividad WHERE idActividad = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idActividad]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerRangosEtariosActividad($idActividad)
    {
        $sql = "SELECT ge.idGrupoEtareo, ge.nomGrupoEtareo, ge.edadMin, ge.edadMax
                FROM grupoEtarioActividad gea
                INNER JOIN grupoEtario ge ON ge.idGrupoEtareo = gea.idGrupoEtareo
                WHERE gea.idActividad = :id
                ORDER BY ge.edadMin, ge.edadMax";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idActividad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Guardar seguimiento de sesión


    public function guardarSeguimiento($idSegActividad, $logro, $obstaculo, $idTipEntrega, $asistentes)
    {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE seguimientoActividad SET
                        logroActividad = :logro,
                        observObstaculo = :obstaculo,
                        idTipEntrega = :tipoEntrega
                    WHERE idSegActividad = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'logro' => $logro,
                'obstaculo' => $obstaculo,
                'tipoEntrega' => $idTipEntrega,
                'id' => $idSegActividad
            ]);


            $sqlDel = "DELETE FROM asistencia WHERE idSegActividad = ?";
            $this->db->prepare($sqlDel)->execute([$idSegActividad]);

            if (!empty($asistentes) && is_array($asistentes)) {
                $sqlIns = "INSERT INTO asistencia (idAsistencia, idSegActividad, idBeneficiario)
                           VALUES (:id, :seg, :ben)";
                $stmtIns = $this->db->prepare($sqlIns);
                $numero = $this->ultimoNumeroAsistencia();
                foreach ($asistentes as $idBeneficiario) {

                    $numero++;
                    $idAsistencia = 'AS' . str_pad($numero, 6, '0', STR_PAD_LEFT);
                    $stmtIns->execute([
                        'id' => $idAsistencia,
                        'seg' => $idSegActividad,
                        'ben' => $idBeneficiario
                    ]);
                }
            }

            $this->db->commit();
            $this->sincronizarEstatusAutomatico();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }


    public function obtenerAsistentes($idSegActividad)
    {
        $sql = "SELECT idBeneficiario FROM asistencia WHERE idSegActividad = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idSegActividad]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'idBeneficiario');
    }

    // Beneficiarios vinculados a una actividad
    public function listarBeneficiariosActividad($idActividad)
    {
        $sql = "SELECT DISTINCT b.idBeneficiario, b.cedulaBeneficiario, b.nombresBeneficiario,
                       b.apellidosBeneficiario, b.nacionalidad, b.fechaNacBeneficiario, b.sexoBeneficiario
                FROM beneficiario b
                INNER JOIN asistencia asi ON asi.idBeneficiario = b.idBeneficiario
                INNER JOIN seguimientoActividad sa ON sa.idSegActividad = asi.idSegActividad
                WHERE sa.idActividad = :idActividad
                ORDER BY b.apellidosBeneficiario, b.nombresBeneficiario";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idActividad' => $idActividad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarBeneficiarios()
    {
        $sql = "SELECT idBeneficiario, cedulaBeneficiario, nombresBeneficiario,
                       apellidosBeneficiario, nacionalidad, fechaNacBeneficiario, sexoBeneficiario
                FROM beneficiario
                ORDER BY apellidosBeneficiario, nombresBeneficiario";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerBeneficiariosPorIds($ids)
    {
        $ids = array_values(array_unique(array_filter($ids, function ($id) {
            return trim((string) $id) !== '';
        })));
        if (empty($ids)) {
            return [];
        }

        $marcadores = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT idBeneficiario, cedulaBeneficiario, nombresBeneficiario,
                       apellidosBeneficiario, fechaNacBeneficiario
                FROM beneficiario
                WHERE idBeneficiario IN ($marcadores)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerBeneficiarioPorCedula($cedula)
    {
        $sql = "SELECT idBeneficiario, cedulaBeneficiario, nombresBeneficiario,
                       apellidosBeneficiario, nacionalidad, fechaNacBeneficiario, sexoBeneficiario
                FROM beneficiario WHERE cedulaBeneficiario = :ced";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ced' => $cedula]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrarBeneficiario($cedula, $nombres, $apellidos, $nacionalidad, $fechaNac, $sexo)
    {
        try {
            $nuevoId = $this->generarNuevoIdBeneficiario();
            $sql = "INSERT INTO beneficiario
                        (idBeneficiario, nombresBeneficiario, apellidosBeneficiario,
                         cedulaBeneficiario, nacionalidad, fechaNacBeneficiario, sexoBeneficiario)
                    VALUES (:id, :nom, :ape, :ced, :nac, :fnac, :sexo)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $nuevoId,
                'nom' => $nombres,
                'ape' => $apellidos,
                'ced' => $cedula,
                'nac' => $nacionalidad,
                'fnac' => $fechaNac,
                'sexo' => $sexo
            ]);
            return $nuevoId;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Registro por lotes
    public function registrarBeneficiariosLote($lista)
    {
        if (empty($lista)) {
            return [];
        }
        try {
            $this->db->beginTransaction();
            $guardados = [];
            foreach ($lista as $b) {
                $nuevoId = $this->generarNuevoIdBeneficiario();
                $stmt = $this->db->prepare("INSERT INTO beneficiario
                        (idBeneficiario, nombresBeneficiario, apellidosBeneficiario,
                         cedulaBeneficiario, nacionalidad, fechaNacBeneficiario, sexoBeneficiario)
                    VALUES (:id, :nom, :ape, :ced, :nac, :fnac, :sexo)");
                $stmt->execute([
                    'id' => $nuevoId,
                    'nom' => $b['nombres'],
                    'ape' => $b['apellidos'],
                    'ced' => $b['cedula'],
                    'nac' => $b['nacionalidad'],
                    'fnac' => $b['fechaNac'] !== '' ? $b['fechaNac'] : null,
                    'sexo' => $b['sexo']
                ]);
                $guardados[] = [
                    'idBeneficiario' => $nuevoId,
                    'cedulaBeneficiario' => $b['cedula'],
                    'nombresBeneficiario' => $b['nombres'],
                    'apellidosBeneficiario' => $b['apellidos'],
                    'nacionalidad' => $b['nacionalidad'],
                    'fechaNacBeneficiario' => $b['fechaNac'],
                    'sexoBeneficiario' => $b['sexo']
                ];
            }
            $this->db->commit();
            return $guardados;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // Importación de beneficiarios y asistencia de una sesión
    public $errorImportacion = '';

    public function importarAsistenciaDesdeFicha($idSegActividad, $lista)
    {
        $this->errorImportacion = '';
        try {
            $this->db->beginTransaction();
            $numeroAsistencia = $this->ultimoNumeroAsistencia();
            $beneficiarios = [];
            $nuevos = 0;
            $existentes = 0;
            $asistenciasNuevas = 0;
            $asistenciasExistentes = 0;

            $stmtBuscarAsistencia = $this->db->prepare("SELECT idAsistencia FROM asistencia
                                                        WHERE idSegActividad = :seg AND idBeneficiario = :ben");
            $stmtAsistencia = $this->db->prepare("INSERT INTO asistencia (idAsistencia, idSegActividad, idBeneficiario)
                                                  VALUES (:id, :seg, :ben)");
            $stmtBeneficiario = $this->db->prepare("INSERT INTO beneficiario
                    (idBeneficiario, nombresBeneficiario, apellidosBeneficiario,
                     cedulaBeneficiario, nacionalidad, fechaNacBeneficiario, sexoBeneficiario)
                    VALUES (:id, :nom, :ape, :ced, :nac, :fnac, :sexo)");

            foreach ($lista as $b) {
                $idBeneficiario = $b['idBeneficiario'] ?? '';
                if ($idBeneficiario === '') {
                    $idBeneficiario = $this->generarNuevoIdBeneficiario();
                    $stmtBeneficiario->execute([
                        'id' => $idBeneficiario,
                        'nom' => $b['nombres'],
                        'ape' => $b['apellidos'],
                        'ced' => $b['cedula'],
                        'nac' => $b['nacionalidad'],
                        'fnac' => $b['fechaNac'],
                        'sexo' => $b['sexo']
                    ]);
                    $nuevos++;
                } else {
                    $existentes++;
                }

                $stmtBuscarAsistencia->execute(['seg' => $idSegActividad, 'ben' => $idBeneficiario]);
                if ($stmtBuscarAsistencia->fetchColumn()) {
                    $asistenciasExistentes++;
                } else {
                    $numeroAsistencia++;
                    $stmtAsistencia->execute([
                        'id' => 'AS' . str_pad($numeroAsistencia, 6, '0', STR_PAD_LEFT),
                        'seg' => $idSegActividad,
                        'ben' => $idBeneficiario
                    ]);
                    $asistenciasNuevas++;
                }

                $beneficiarios[] = [
                    'idBeneficiario' => $idBeneficiario,
                    'cedulaBeneficiario' => $b['cedula'],
                    'nombresBeneficiario' => $b['nombres'],
                    'apellidosBeneficiario' => $b['apellidos'],
                    'nacionalidad' => $b['nacionalidad'],
                    'fechaNacBeneficiario' => $b['fechaNac'],
                    'sexoBeneficiario' => $b['sexo']
                ];
            }

            $this->db->commit();
            return [
                'beneficiarios' => $beneficiarios,
                'nuevos' => $nuevos,
                'existentes' => $existentes,
                'asistenciasNuevas' => $asistenciasNuevas,
                'asistenciasExistentes' => $asistenciasExistentes
            ];
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->errorImportacion = $e->getMessage();
            return false;
        }
    }

    // Datos completos para la ficha Excel
    public function obtenerDatosExportacionExcel()
    {
        $sql = "SELECT a.*, ae.nomAreaE, ta.nomTipoActividad, um.nomUnidadMedida,
                       ge.nomGrupoEtnio, ed.nomEstDesarrollo
                FROM actividad a
                LEFT JOIN areaEspecifica ae ON ae.idAreaE = a.idAreaE
                LEFT JOIN tipoActividad ta ON ta.idTipoActividad = a.idTipoActividad
                LEFT JOIN unidadMedida um ON um.idUnidadMedida = a.idUnidadMedida
                LEFT JOIN grupoEtnio ge ON ge.idGrupoEtnio = a.idGrupoEtnio
                LEFT JOIN estrategiaDesarrollo ed ON ed.idEstDesarrollo = a.idEstDesarrollo
                ORDER BY a.fechainicioActividad, a.idActividad";
        $actividades = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $stmtEmpleados = $this->db->prepare("SELECT e.idEmpleado, e.nombres, e.apellidos,
                       e.telefonoEmpleado, e.correoEmpleado, ue.nomUnidadEjecutora
                FROM actividadEmpleado rel
                INNER JOIN empleado e ON e.idEmpleado = rel.idEmpleado
                LEFT JOIN unidadEjecutora ue ON ue.idUnidadEjecutora = e.idUnidadEjecutora
                WHERE rel.idActividad = ?
                ORDER BY e.nombres, e.apellidos");
        $stmtPrincipal = $this->db->prepare("SELECT e.idEmpleado, e.nombres, e.apellidos,
                       e.telefonoEmpleado, e.correoEmpleado, ue.nomUnidadEjecutora
                FROM empleado e
                LEFT JOIN unidadEjecutora ue ON ue.idUnidadEjecutora = e.idUnidadEjecutora
                WHERE e.idEmpleado = ?");
        $stmtDocentes = $this->db->prepare("SELECT d.idDocente, d.nombreDocente, d.apellidoDocente, d.telfDocente
                FROM actividadDocente rel
                INNER JOIN docente d ON d.idDocente = rel.idDocente
                WHERE rel.idActividad = ?
                ORDER BY d.nombreDocente, d.apellidoDocente");
        $stmtLugares = $this->db->prepare("SELECT la.idLugarActividad, la.nomLugarActividad,
                       p.nombreParroquia, m.nombreMunicipio, e.nombreEstado
                FROM lugarRealizaActividad rel
                INNER JOIN lugarActividad la ON la.idLugarActividad = rel.idLugarActividad
                LEFT JOIN parroquia p ON p.idParroquia = la.idParroquia
                LEFT JOIN municipio m ON m.idMunicipio = p.idMunicipio
                LEFT JOIN estado e ON e.idEstado = m.idEstado
                WHERE rel.idActividad = ?
                ORDER BY la.nomLugarActividad");
        $stmtSesiones = $this->db->prepare("SELECT sa.*, te.nomTipEntrega, h.nomHorario,
                       la.nomLugarActividad AS lugarSesion, p.nombreParroquia AS parroquiaSesion,
                       m.nombreMunicipio AS municipioSesion
                FROM seguimientoActividad sa
                LEFT JOIN tipoEntrega te ON te.idTipEntrega = sa.idTipEntrega
                LEFT JOIN horario h ON h.idHorario = sa.idHorario
                LEFT JOIN lugarActividad la ON la.idLugarActividad = sa.idLugarActividad
                LEFT JOIN parroquia p ON p.idParroquia = la.idParroquia
                LEFT JOIN municipio m ON m.idMunicipio = p.idMunicipio
                WHERE sa.idActividad = ?
                ORDER BY sa.nroSesionPlanificada");
        $stmtDocentesSesion = $this->db->prepare("SELECT d.idDocente, d.nombreDocente,
                       d.apellidoDocente, d.telfDocente
                FROM seguimientoDocente rel
                INNER JOIN docente d ON d.idDocente = rel.idDocente
                WHERE rel.idSegActividad = ?
                ORDER BY d.nombreDocente, d.apellidoDocente");
        $stmtAsistentes = $this->db->prepare("SELECT DISTINCT b.idBeneficiario, b.cedulaBeneficiario,
                       b.nombresBeneficiario, b.apellidosBeneficiario, b.fechaNacBeneficiario,
                       b.sexoBeneficiario
                FROM asistencia asi
                INNER JOIN beneficiario b ON b.idBeneficiario = asi.idBeneficiario
                WHERE asi.idSegActividad = ?
                ORDER BY b.apellidosBeneficiario, b.nombresBeneficiario");

        foreach ($actividades as &$actividad) {
            $stmtEmpleados->execute([$actividad['idActividad']]);
            $empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);
            $idsEmpleados = array_column($empleados, 'idEmpleado');
            if (!in_array($actividad['idEmpleado'], $idsEmpleados, true)) {
                $stmtPrincipal->execute([$actividad['idEmpleado']]);
                $principal = $stmtPrincipal->fetch(PDO::FETCH_ASSOC);
                if ($principal) array_unshift($empleados, $principal);
            }
            $actividad['empleados'] = $empleados;

            $stmtDocentes->execute([$actividad['idActividad']]);
            $actividad['docentes'] = $stmtDocentes->fetchAll(PDO::FETCH_ASSOC);
            $stmtLugares->execute([$actividad['idActividad']]);
            $actividad['lugares'] = $stmtLugares->fetchAll(PDO::FETCH_ASSOC);
            $stmtSesiones->execute([$actividad['idActividad']]);
            $sesiones = $stmtSesiones->fetchAll(PDO::FETCH_ASSOC);

            foreach ($sesiones as &$sesion) {
                $stmtDocentesSesion->execute([$sesion['idSegActividad']]);
                $sesion['docentes'] = $stmtDocentesSesion->fetchAll(PDO::FETCH_ASSOC);
                $stmtAsistentes->execute([$sesion['idSegActividad']]);
                $sesion['asistentes'] = $stmtAsistentes->fetchAll(PDO::FETCH_ASSOC);
            }
            unset($sesion);
            $actividad['sesiones'] = $sesiones;
        }
        unset($actividad);
        return $actividades;
    }
}
