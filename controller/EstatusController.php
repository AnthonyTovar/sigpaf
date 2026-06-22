<?php
require_once 'model/EstatusModel.php';

class EstatusController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new EstatusModel();
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

        $estatus = $this->model->listarEstatus();
        $this->renderizar('view/EstatusView', ['estatus' => $estatus]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nomEstatus'] ?? '';
            $desc = $_POST['descEstatus'] ?? '';

            if (!empty($nombre) && !empty($desc)) {
                $nuevoId = $this->model->registrarEstatus($nombre, $desc);

                if ($nuevoId) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Estatus registrado con éxito!",
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
                    "message" => "Todos los campos son obligatorios."
                ]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idEstatus'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del estatus"]);
            exit;
        }

        $resultado = $this->model->eliminarEstatus($id);

        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Estatus eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este estatus está asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el estatus."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $estatus = $this->model->obtenerEstatusPorId($id);
        echo json_encode($estatus);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idEstatusEdit'] ?? '';
            $nombre = $_POST['nomEstatusEdit'] ?? '';
            $desc = $_POST['descEstatusEdit'] ?? '';

            if (!empty($id) && !empty($nombre) && !empty($desc)) {
                $resultado = $this->model->actualizarEstatus($id, $nombre, $desc);

                if ($resultado) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Estatus actualizado con éxito!"
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