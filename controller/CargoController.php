<?php
require_once 'model/CargoModel.php';

class CargoController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new CargoModel();
    }

    /**
     * MÉTODO RENDERIZAR
     */
    private function renderizar($nombreVista, $datos = [])
    {
        extract($datos);
        ob_start();
        require $nombreVista . '.php';
        $content = ob_get_clean();
        require 'view/Layout.php';
    }

    // Listado de Cargos
    public function listar()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $cargos = $this->model->listarCargos();

        $this->renderizar('view/CargoView', ['cargos' => $cargos]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nombreCargo'] ?? '';
            $desc = $_POST['descripcionCargo'] ?? '';

            if (!empty($nombre)) {
                $nuevoId = $this->model->registrarCargo($nombre, $desc);

                if ($nuevoId) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Cargo registrado con éxito!",
                        "id" => $nuevoId,
                        "nombre" => $nombre,
                        "descripcion" => $desc
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
                    "message" => "El nombre del cargo es obligatorio."
                ]);
            }
            exit();
        }
    }
    // Eliminar cargo
    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idCargo'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del cargo"]);
            exit;
        }

        $resultado = $this->model->eliminarCargo($id);

        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Cargo eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este cargo está asignado a empleados activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el cargo."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $cargo = $this->model->obtenerCargoPorId($id);
        echo json_encode($cargo);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json'); // Misma lógica que guardar

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idCargoEdit'] ?? '';
            $nombre = $_POST['nombreCargoEdit'] ?? '';
            $desc = $_POST['descripcionCargoEdit'] ?? '';

            if (!empty($id) && !empty($nombre)) {
                $resultado = $this->model->actualizarCargo($id, $nombre, $desc);

                if ($resultado) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Cargo actualizado con éxito!"
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