<?php
require_once 'model/ConexionModel.php';

/**
 * MÓDULO SEGUIMIENTO
 * Lógica: cada actividad tiene N sesiones planificadas (tabla seguimientoActividad,
 * creadas al registrar la actividad). Por cada sesión se registra:
 *   - logroActividad / observObstaculo (qué se logró y qué obstáculos hubo)
 *   - idTipEntrega (cómo se entregó)
 *   - asistencia (qué beneficiarios asistieron, tabla asistencia)
 * Una sesión "registrada" es la que ya tiene logro escrito.
 */
class SeguimientoModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ID correlativo: BEN0001, BEN0002...
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

    // ID correlativo de sesiones: SA0001, SA0002...
    private function generarNuevoIdSeguimiento()
    {
        $stmt = $this->db->query("SELECT idSegActividad FROM seguimientoActividad ORDER BY idSegActividad DESC LIMIT 1");
        $ultimoId = $stmt->fetchColumn();

        if (!$ultimoId) {
            return "SA0001";
        }
        return "SA" . str_pad(intval(substr($ultimoId, 2)) + 1, 4, "0", STR_PAD_LEFT);
    }

    // Último número de asistencia usado (base para insertar varias seguidas)
    private function ultimoNumeroAsistencia()
    {
        $stmt = $this->db->query("SELECT idAsistencia FROM asistencia ORDER BY idAsistencia DESC LIMIT 1");
        $ultimo = $stmt->fetchColumn();
        return $ultimo ? intval(substr($ultimo, 2)) : 0;
    }

    // Asegura que la actividad tenga sus sesiones planificadas creadas.
    // Las actividades guardadas antes de este módulo (o sin pulsar
    // "Generar fechas" en el formulario) no tienen filas en
    // seguimientoActividad; aquí se crean desde los datos de la propia
    // actividad, con el mismo criterio de reparto que el formulario
    // (fechas distribuidas entre inicio y fin). Así el seguimiento
    // siempre permite registrar/editar, con una sola sesión o varias.
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

        // Repartir las fechas entre inicio y fin (sin duplicar días)
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

        // Crear las sesiones (TE0001 queda como entrega por defecto; el
        // usuario elige la real al registrar el seguimiento de cada sesión)
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

    // ========== ACTIVIDADES CON PROGRESO ==========
    // Lista todas las actividades con: sesiones planificadas, registradas y
    // total de asistentes acumulados (para la barra de avance).
    public function listarActividadesProgreso()
    {
        $sql = "SELECT
                    a.idActividad,
                    a.nombreActividad,
                    a.fechainicioActividad,
                    a.fechafinActividad,
                    a.cantPersoAtender,
                    a.idEmpleado,
                    a.idEstatus,
                    e.nomEstatus,
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
                ORDER BY a.fechainicioActividad DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista de estatus disponibles (para el selector de cambio de estatus)
    public function obtenerEstatus()
    {
        return $this->db->query("SELECT idEstatus, nomEstatus FROM estatus ORDER BY nomEstatus")
                        ->fetchAll(PDO::FETCH_ASSOC);
    }

    // Valida que un id de estatus exista realmente en la base de datos
    public function existeEstatus($idEstatus)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM estatus WHERE idEstatus = ?");
        $stmt->execute([$idEstatus]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // Cambia el estatus de una actividad (solo lo puede pedir el
    // responsable de la actividad o un administrador, el controlador
    // es quien verifica esa regla)
    public function actualizarEstatusActividad($idActividad, $idEstatus)
    {
        $stmt = $this->db->prepare("UPDATE actividad SET idEstatus = :est
                                    WHERE idActividad = :id");
        return $stmt->execute(['est' => $idEstatus, 'id' => $idActividad]);
    }

    // Cuantas sesiones de la actividad ya tienen logro registrado.
    // Es el requisito para poder marcar la actividad como Finalizada:
    // no se puede "cumplir" una actividad sin haber registrado al
    // menos una sesión con su seguimiento.
    public function contarSesionesRegistradas($idActividad)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM seguimientoActividad
                                    WHERE idActividad = :id AND logroActividad <> ''");
        $stmt->execute(['id' => $idActividad]);
        return (int) $stmt->fetchColumn();
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== SESIONES DE UNA ACTIVIDAD ==========
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

    // ========== GUARDAR SEGUIMIENTO DE SESIÓN ==========
    // Recibe el id de la sesión (idSegActividad), logro, obstáculo, tipo de
    // entrega y la lista de idBeneficiario asistentes. Reemplaza la asistencia.
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

            // Asistencia: limpiar la anterior y registrar la nueva
            $sqlDel = "DELETE FROM asistencia WHERE idSegActividad = ?";
            $this->db->prepare($sqlDel)->execute([$idSegActividad]);

            if (!empty($asistentes) && is_array($asistentes)) {
                $sqlIns = "INSERT INTO asistencia (idAsistencia, idSegActividad, idBeneficiario)
                           VALUES (:id, :seg, :ben)";
                $stmtIns = $this->db->prepare($sqlIns);
                $numero = $this->ultimoNumeroAsistencia();
                foreach ($asistentes as $idBeneficiario) {
                    // idAsistencia: correlativo global de 8 caracteres (AS + 6 dígitos)
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
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // Asistentes actuales de una sesión (para reeditar)
    public function obtenerAsistentes($idSegActividad)
    {
        $sql = "SELECT idBeneficiario FROM asistencia WHERE idSegActividad = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idSegActividad]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'idBeneficiario');
    }

    // ========== BENEFICIARIOS ==========
    public function listarBeneficiarios()
    {
        $sql = "SELECT idBeneficiario, cedulaBeneficiario, nombresBeneficiario,
                       apellidosBeneficiario, nacionalidad, fechaNacBeneficiario, sexoBeneficiario
                FROM beneficiario
                ORDER BY apellidosBeneficiario, nombresBeneficiario";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerBeneficiarioPorCedula($cedula)
    {
        $sql = "SELECT idBeneficiario FROM beneficiario WHERE cedulaBeneficiario = :ced";
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
}
