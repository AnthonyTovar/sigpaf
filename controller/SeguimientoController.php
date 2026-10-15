<?php
require_once 'model/SeguimientoModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';
require_once 'RolHelper.php';

/**
 * MÓDULO SEGUIMIENTO - Controlador
 * Operativo: cualquier usuario logueado puede registrar seguimiento.
 * Cambio de estatus: solo el responsable de la actividad (quien la
 * registró) o un administrador.
 */
class SeguimientoController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new SeguimientoModel();
    }

    private function renderizar($nombreVista, $datos = [])
    {
        extract($datos);
        ob_start();
        require $nombreVista . '.php';
        $content = ob_get_clean();
        require 'view/Layout.php';
    }

    // Pantalla principal: actividades con su progreso de sesiones
    public function listar()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $actividades = $this->model->listarActividadesProgreso();
        $tiposEntrega = $this->obtenerTiposEntrega();

        // El empleado vinculado al usuario logueado: es el "responsable"
        // que puede cambiar el estatus de sus propias actividades.
        // Los administradores pueden cambiar el estatus de cualquiera.
        $this->renderizar('view/SeguimientoView', [
            'actividades' => $actividades,
            'tiposEntrega' => $tiposEntrega,
            'estatus' => $this->model->obtenerEstatus(),
            'idEmpleadoUsuario' => $_SESSION['idEmpleado'] ?? '',
            'esAdmin' => RolHelper::esAdministrador()
        ]);
    }

    private function obtenerTiposEntrega()
    {
        $db = Database::getConnection();
        return $db->query("SELECT idTipEntrega, nomTipEntrega FROM tipoEntrega ORDER BY nomTipEntrega")
                  ->fetchAll(PDO::FETCH_ASSOC);
    }

    // JSON: sesiones de una actividad + beneficiarios + asistentes de cada sesión
    public function obtenerSesiones()
    {
        header('Content-Type: application/json');

        $idActividad = $_GET['idActividad'] ?? '';
        if (empty($idActividad)) {
            echo json_encode(['status' => 'error', 'message' => 'No se recibió la actividad']);
            exit;
        }

        // ensureSesiones: si la actividad no tiene sesiones creadas (por
        // haberse guardado antes de este módulo o sin pulsar "Generar
        // fechas"), las genera automáticamente desde sus fechas y cantidad
        // planificada, para que siempre se pueda registrar/editar.
        $sesiones = $this->model->ensureSesiones($idActividad);
        $actividad = $this->model->obtenerActividad($idActividad);
        $beneficiarios = $this->model->listarBeneficiarios();
        $asistentesPorSesion = [];
        foreach ($sesiones as $s) {
            $asistentesPorSesion[$s['idSegActividad']] = $this->model->obtenerAsistentes($s['idSegActividad']);
        }

        echo json_encode([
            'status' => 'success',
            'actividad' => $actividad,
            'sesiones' => $sesiones,
            'beneficiarios' => $beneficiarios,
            'asistentes' => $asistentesPorSesion,
            'hoy' => date('Y-m-d')
        ]);
        exit;
    }

    // Guardar seguimiento de una sesión (logro, obstáculo, entrega, asistencia)
    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["status" => "error", "message" => "Método no permitido"]);
            exit();
        }

        $idSeg = trim($_POST['idSegActividad'] ?? '');
        $logro = trim($_POST['logroActividad'] ?? '');
        $obstaculo = trim($_POST['observObstaculo'] ?? '');
        $idTipEntrega = trim($_POST['idTipEntrega'] ?? '');
        $asistentes = $_POST['asistentes'] ?? [];

        // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
        ValidationHelper::exigirRequerido($idSeg, 'No se recibió la sesión a registrar.');
        ValidationHelper::exigirRequerido($logro, 'Debe describir el logro de la sesión.');
        ValidationHelper::exigirMaxLongitud($logro, 'El logro no puede exceder los 500 caracteres.', 500);
        ValidationHelper::exigirMaxLongitud($obstaculo, 'Las observaciones no pueden exceder los 500 caracteres.', 500);
        ValidationHelper::exigirRequerido($idTipEntrega, 'Debe seleccionar el tipo de entrega de la sesión.');

        // Lógica: la fecha de la sesión no puede ser futura
        $sesion = $this->buscarSesion($idSeg);
        if (!$sesion) {
            ValidationHelper::error('La sesión indicada no existe.');
        }
        if ($sesion['fechaSesion'] > date('Y-m-d')) {
            ValidationHelper::error('No se puede registrar una sesión con fecha futura (' .
                date('d/m/Y', strtotime($sesion['fechaSesion'])) . ').');
        }

        $asistentesLimpios = [];
        if (is_array($asistentes)) {
            foreach ($asistentes as $idBen) {
                $idBen = trim($idBen);
                if ($idBen !== '') {
                    $asistentesLimpios[] = $idBen;
                }
            }
        }

        $resultado = $this->model->guardarSeguimiento($idSeg, $logro, $obstaculo, $idTipEntrega, $asistentesLimpios);

        if ($resultado) {
            Logger::actualizar('Seguimiento', $idSeg, 'Sesión ' . $sesion['nroSesionPlanificada']);

            echo json_encode([
                "status" => "success",
                "message" => "Seguimiento de la sesión " . $sesion['nroSesionPlanificada'] . " registrado correctamente."
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al registrar el seguimiento."]);
        }
        exit();
    }

    private function buscarSesion($idSeg)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT idSegActividad, nroSesionPlanificada, fechaSesion
                              FROM seguimientoActividad WHERE idSegActividad = ?");
        $stmt->execute([$idSeg]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // JSON: registrar beneficiario rápido desde el modal de asistencia
    public function guardarBeneficiario()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["status" => "error", "message" => "Método no permitido"]);
            exit();
        }

        $cedula = trim($_POST['cedulaBeneficiario'] ?? '');
        $nombres = trim($_POST['nombresBeneficiario'] ?? '');
        $apellidos = trim($_POST['apellidosBeneficiario'] ?? '');
        $nacionalidad = trim($_POST['nacionalidad'] ?? '');
        $fechaNac = trim($_POST['fechaNacBeneficiario'] ?? '');
        $sexo = trim($_POST['sexoBeneficiario'] ?? '');

        // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
        ValidationHelper::exigirRequerido($cedula, 'La cédula del beneficiario es obligatoria.');
        if (!ValidationHelper::cedula($cedula)) {
            ValidationHelper::error('La cédula no tiene un formato válido (V12345678).');
        }
        ValidationHelper::exigirRequerido($nombres, 'Los nombres del beneficiario son obligatorios.');
        ValidationHelper::exigirRequerido($apellidos, 'Los apellidos del beneficiario son obligatorios.');
        ValidationHelper::exigirRequerido($nacionalidad, 'Debe seleccionar la nacionalidad.');
        ValidationHelper::exigirMaxLongitud($nombres, 'Los nombres no pueden exceder los 250 caracteres.', 250);
        ValidationHelper::exigirMaxLongitud($apellidos, 'Los apellidos no pueden exceder los 250 caracteres.', 250);
        if ($fechaNac !== '' && !ValidationHelper::fecha($fechaNac)) {
            ValidationHelper::error('La fecha de nacimiento no es válida.');
        }
        if (!in_array($sexo, ['M', 'F'])) {
            ValidationHelper::error('Debe seleccionar el sexo del beneficiario.');
        }

        // No duplicar cédula
        $existente = $this->model->obtenerBeneficiarioPorCedula($cedula);
        if ($existente) {
            echo json_encode([
                "status" => "error",
                "message" => "Ya existe un beneficiario con esa cédula.",
                "idBeneficiario" => $existente['idBeneficiario']
            ]);
            exit();
        }

        $nuevoId = $this->model->registrarBeneficiario($cedula, $nombres, $apellidos, $nacionalidad, $fechaNac, $sexo);

        if ($nuevoId) {
            Logger::crear('Beneficiario', $nuevoId, $nombres . ' ' . $apellidos);

            echo json_encode([
                "status" => "success",
                "message" => "Beneficiario registrado correctamente.",
                "id" => $nuevoId,
                "cedula" => $cedula,
                "nombres" => $nombres,
                "apellidos" => $apellidos
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al registrar el beneficiario."]);
        }
        exit();
    }

    // Cambia el estatus de una actividad desde la tabla de seguimiento.
    // Reglas de lógica:
    //  - Solo el responsable de la actividad (su idEmpleado = el del
    //    usuario logueado) o un administrador pueden cambiar el estatus.
    //  - Para marcar una actividad como Finalizada (ES0006) debe tener
    //    al menos una sesión con seguimiento registrado: una actividad
    //    "cumplida" es la que ya tiene evidencia de ejecución.
    //  - Solo las actividades Finalizada entran en el reporte mensual.
    public function cambiarEstatus()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["status" => "error", "message" => "Método no permitido"]);
            exit();
        }

        $idActividad = trim($_POST['idActividad'] ?? '');
        $idEstatus = trim($_POST['idEstatus'] ?? '');

        // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
        ValidationHelper::exigirRequerido($idActividad, 'No se recibió la actividad.');
        ValidationHelper::exigirRequerido($idEstatus, 'Debe seleccionar el estatus.');

        if (!ValidationHelper::maxLongitud($idActividad, 10)) {
            ValidationHelper::error('El identificador de la actividad no es válido.');
        }

        $actividad = $this->model->obtenerActividad($idActividad);
        if (!$actividad) {
            ValidationHelper::error('La actividad indicada no existe.');
        }

        if (!$this->model->existeEstatus($idEstatus)) {
            ValidationHelper::error('El estatus seleccionado no existe.');
        }

        // ===== PERMISO: responsable de la actividad o administrador =====
        $idEmpleadoUsuario = $_SESSION['idEmpleado'] ?? '';
        if (!RolHelper::esAdministrador() && $actividad['idEmpleado'] !== $idEmpleadoUsuario) {
            ValidationHelper::error('Solo el responsable de la actividad puede cambiar su estatus.');
        }

        // ===== LÓGICA: no se finaliza sin seguimiento registrado =====
        if ($idEstatus === 'ES0006') {
            $registradas = $this->model->contarSesionesRegistradas($idActividad);
            if ($registradas < 1) {
                ValidationHelper::error(
                    'No se puede marcar como Finalizada: la actividad no tiene ninguna sesión con seguimiento registrado. ' .
                    'Registre el seguimiento de al menos una sesión.'
                );
            }
        }

        $resultado = $this->model->actualizarEstatusActividad($idActividad, $idEstatus);

        if ($resultado) {
            $nombreEstatus = '';
            foreach ($this->model->obtenerEstatus() as $e) {
                if ($e['idEstatus'] === $idEstatus) {
                    $nombreEstatus = $e['nomEstatus'];
                    break;
                }
            }

            Logger::actualizar('Actividad', $idActividad,
                $actividad['nombreActividad'] . ' → estatus: ' . $nombreEstatus);

            echo json_encode([
                "status" => "success",
                "message" => 'Estatus actualizado a "' . $nombreEstatus . '".'
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al actualizar el estatus."]);
        }
        exit();
    }
}
