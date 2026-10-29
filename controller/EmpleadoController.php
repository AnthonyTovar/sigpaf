<?php
// Controlador de empleados



require_once 'model/EmpleadoModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class EmpleadoController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new EmpleadoModel();
    }

    private function renderizar($nombreVista, $datos = [])
    {
        extract($datos);
        ob_start();
        require $nombreVista . '.php';
        $content = ob_get_clean();

        require 'view/Layout.php';
    }

    public function listar()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $empleados = $this->model->listarEmpleados();
        $cargos = $this->model->listarCargos();
        $unidades = $this->model->listarUnidadesEjecutoras();
        $this->renderizar('view/EmpleadoView', [
            'empleados' => $empleados,
            'cargos' => $cargos,
            'unidades' => $unidades
        ]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $prefijoNacionalidad = $_POST['nacionalidad'] ?? 'V';
            $cedulaCompleta = $_POST['cedulaEmpleado'] ?? '';
            $nombres = $_POST['nombres'] ?? '';
            $apellidos = $_POST['apellidos'] ?? '';
            $fechaNac = $_POST['fechaNacimiento'] ?? '';
            $telefono = $_POST['telefonoEmpleado'] ?? '';
            $correo = $_POST['correoEmpleado'] ?? '';
            $idCargo = $_POST['idCargo'] ?? '';
            $idUnidadEjecutora = $_POST['idUnidadEjecutora'] ?? '';

            // Validación unificada (validationhelper)
            if (!(ValidationHelper::cedula($cedulaCompleta))) {
                ValidationHelper::error('La cédula debe tener entre 6 y 9 dígitos.');
            }
            ValidationHelper::exigirRequerido($nombres, 'Los nombres del empleado son obligatorios.');
            ValidationHelper::exigirRequerido($apellidos, 'Los apellidos del empleado son obligatorios.');
            if (!(ValidationHelper::fecha($fechaNac))) {
                ValidationHelper::error('La fecha de nacimiento no es válida.');
            }
            if (!($fechaNac <= date('Y-m-d'))) {
                ValidationHelper::error('La fecha de nacimiento no puede ser futura.');
            }
            if (!(ValidationHelper::telefono($telefono))) {
                ValidationHelper::error('El teléfono debe tener el formato 04XX-XXXXXXX.');
            }
            if (!(ValidationHelper::correo($correo))) {
                ValidationHelper::error('El correo electrónico no es válido.');
            }
            ValidationHelper::exigirRequerido($idCargo, 'Debe seleccionar el cargo.');
            ValidationHelper::exigirRequerido($idUnidadEjecutora, 'Debe seleccionar la unidad ejecutora.');

            if (empty($cedulaCompleta) || empty($nombres) || empty($apellidos) || empty($fechaNac) || empty($idCargo) || empty($idUnidadEjecutora)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Los campos obligatorios son: Cédula, Nombres, Apellidos, Fecha de Nacimiento, Cargo y Unidad Ejecutora."
                ]);
                exit();
            }

            $prefijo = substr($cedulaCompleta, 0, 1);
            $numeroCedula = substr($cedulaCompleta, 1);


            if (!in_array(strtoupper($prefijo), ['V', 'E'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Formato de cédula inválido. Debe comenzar con V o E."
                ]);
                exit();
            }

            $prefijoFinal = strtoupper($prefijoNacionalidad);
            if (!in_array($prefijoFinal, ['V', 'E'])) {
                $prefijoFinal = strtoupper($prefijo);
            }

            $nuevoId = $this->model->registrarEmpleado(
                $prefijoFinal,
                $cedulaCompleta,
                $nombres,
                $apellidos,
                $fechaNac,
                $telefono,
                $correo,
                $idCargo,
                $idUnidadEjecutora
            );

            if ($nuevoId === 'cedula_duplicada') {
                echo json_encode([
                    "status" => "error",
                    "message" => "Ya existe un empleado con la cédula '{$cedulaCompleta}'. Verifique e inténtelo con otro."
                ]);
            } else if ($nuevoId) {

     Logger::crear('Empleado', $nuevoId, $nombres . ' ' . $apellidos);
                echo json_encode([
                    "status" => "success",
                    "message" => "¡Empleado registrado con éxito!",
                    "id" => $nuevoId,
                    "nacionalidad" => $prefijoFinal,
                    "cedula" => $cedulaCompleta,
                    "nombres" => $nombres,
                    "apellidos" => $apellidos,
                    "fechaNac" => $fechaNac,
                    "telefono" => $telefono,
                    "correo" => $correo,
                    "idCargo" => $idCargo,
                    "idUnidadEjecutora" => $idUnidadEjecutora
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Hubo un error en la base de datos al registrar el empleado."
                ]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idEmpleado'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del empleado"]);
            exit;
        }


        $reg = $this->model->obtenerEmpleadoPorId($id);

        $resultado = $this->model->eliminarEmpleado($id);

        if ($resultado === true) {

            Logger::eliminar('Empleado', $id, $reg ? $reg['nombres'] . ' ' . $reg['apellidos'] : 'desconocido');
            echo json_encode(["status" => "success", "message" => "Empleado eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este empleado está asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el empleado."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $empleado = $this->model->obtenerEmpleadoPorId($id);
        echo json_encode($empleado);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idEmpleadoEdit'] ?? '';
            $prefijoNacionalidad = $_POST['nacionalidadEdit'] ?? 'V';
            $cedulaCompleta = $_POST['cedulaEmpleadoEdit'] ?? '';
            $nombres = $_POST['nombresEdit'] ?? '';
            $apellidos = $_POST['apellidosEdit'] ?? '';
            $fechaNac = $_POST['fechaNacimientoEdit'] ?? '';
            $telefono = $_POST['telefonoEmpleadoEdit'] ?? '';
            $correo = $_POST['correoEmpleadoEdit'] ?? '';
            $idCargo = $_POST['idCargoEdit'] ?? '';
            $idUnidadEjecutora = $_POST['idUnidadEjecutoraEdit'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($id, 'No se recibió el empleado a editar.');
            if (!(ValidationHelper::cedula($cedulaCompleta))) {
                ValidationHelper::error('La cédula debe tener entre 6 y 9 dígitos.');
            }
            ValidationHelper::exigirRequerido($nombres, 'Los nombres del empleado son obligatorios.');
            ValidationHelper::exigirRequerido($apellidos, 'Los apellidos del empleado son obligatorios.');
            if (!(ValidationHelper::fecha($fechaNac))) {
                ValidationHelper::error('La fecha de nacimiento no es válida.');
            }
            if (!($fechaNac <= date('Y-m-d'))) {
                ValidationHelper::error('La fecha de nacimiento no puede ser futura.');
            }
            if (!(ValidationHelper::telefono($telefono))) {
                ValidationHelper::error('El teléfono debe tener el formato 04XX-XXXXXXX.');
            }
            if (!(ValidationHelper::correo($correo))) {
                ValidationHelper::error('El correo electrónico no es válido.');
            }
            ValidationHelper::exigirRequerido($idCargo, 'Debe seleccionar el cargo.');
            ValidationHelper::exigirRequerido($idUnidadEjecutora, 'Debe seleccionar la unidad ejecutora.');
            if (empty($id) || empty($cedulaCompleta) || empty($nombres) || empty($apellidos) || empty($fechaNac) || empty($idCargo) || empty($idUnidadEjecutora)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Datos incompletos."
                ]);
                exit();
            }

            $prefijoFinal = strtoupper($prefijoNacionalidad);
            if (!in_array($prefijoFinal, ['V', 'E'])) {
                $prefijoFinal = 'V';
            }

            $resultado = $this->model->actualizarEmpleado(
                $id,
                $prefijoFinal,
                $cedulaCompleta,
                $nombres,
                $apellidos,
                $fechaNac,
                $telefono,
                $correo,
                $idCargo,
                $idUnidadEjecutora
            );

            if ($resultado === 'cedula_duplicada') {
                echo json_encode([
                    "status" => "error",
                    "message" => "Ya existe otro empleado con la cédula '{$cedulaCompleta}'. Verifique e inténtelo con otro."
                ]);
            } else if ($resultado) {

     Logger::actualizar('Empleado', $id, $nombres . ' ' . $apellidos);
                echo json_encode([
                    "status" => "success",
                    "message" => "¡Empleado actualizado con éxito!"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se realizaron cambios o hubo un error en la base de datos."
                ]);
            }
            exit();
        }
    }


    public function exportarPdf()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        require_once 'lib/dompdf/autoload.inc.php';
        require_once 'Logger.php';

        $registros = $this->model->listarEmpleados();


        $filtro = trim((string) ($_GET['q'] ?? ''));
        if ($filtro !== '') {
            $registros = array_values(array_filter($registros, function ($r) use ($filtro) {
                foreach ($r as $v) {
                    if (mb_stripos((string) $v, $filtro) !== false) {
                        return true;
                    }
                }
                return false;
            }));
        }

        ob_start();
        require 'view/public/reportes/EmpleadoReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'landscape');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Empleados');


        $dompdf->stream("reporte_empleados_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}