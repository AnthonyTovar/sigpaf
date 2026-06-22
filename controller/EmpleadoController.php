<?php
require_once 'model/EmpleadoModel.php';

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

        require 'view/layout.php';
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
            $cedula = $_POST['cedulaEmpleado'] ?? '';
            $nombres = $_POST['nombres'] ?? '';
            $apellidos = $_POST['apellidos'] ?? '';
            $fechaNac = $_POST['fechaNacimiento'] ?? '';
            $telefono = $_POST['telefonoEmpleado'] ?? '';
            $correo = $_POST['correoEmpleado'] ?? '';
            $idCargo = $_POST['idCargo'] ?? '';
            $idUnidadEjecutora = $_POST['idUnidadEjecutora'] ?? '';

            if (!empty($cedula) && !empty($nombres) && !empty($apellidos) && !empty($fechaNac) && !empty($idCargo) && !empty($idUnidadEjecutora)) {
                $nuevoId = $this->model->registrarEmpleado($cedula, $nombres, $apellidos, $fechaNac, $telefono, $correo, $idCargo, $idUnidadEjecutora);

                if ($nuevoId) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Empleado registrado con éxito!",
                        "id" => $nuevoId,
                        "cedula" => $cedula,
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
                        "message" => "Hubo un error en la base de datos."
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Los campos obligatorios son: Cédula, Nombres, Apellidos, Fecha de Nacimiento, Cargo y Unidad Ejecutora."
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

        $resultado = $this->model->eliminarEmpleado($id);

        if ($resultado === true) {
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
            $cedula = $_POST['cedulaEmpleadoEdit'] ?? '';
            $nombres = $_POST['nombresEdit'] ?? '';
            $apellidos = $_POST['apellidosEdit'] ?? '';
            $fechaNac = $_POST['fechaNacimientoEdit'] ?? '';
            $telefono = $_POST['telefonoEmpleadoEdit'] ?? '';
            $correo = $_POST['correoEmpleadoEdit'] ?? '';
            $idCargo = $_POST['idCargoEdit'] ?? '';
            $idUnidadEjecutora = $_POST['idUnidadEjecutoraEdit'] ?? '';

            if (!empty($id) && !empty($cedula) && !empty($nombres) && !empty($apellidos) && !empty($fechaNac) && !empty($idCargo) && !empty($idUnidadEjecutora)) {
                $resultado = $this->model->actualizarEmpleado($id, $cedula, $nombres, $apellidos, $fechaNac, $telefono, $correo, $idCargo, $idUnidadEjecutora);

                if ($resultado) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Empleado actualizado con éxito!"
                    ]);
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "No se realizaron cambios o hubo un error."
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Datos incompletos."
                ]);
            }
            exit();
        }
    }
}