<?php
require_once 'model/EspacioUtilizarModel.php';

class EspacioUtilizarController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new EspacioUtilizarModel();
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

        $espacios = $this->model->listarEspacios();
        $this->renderizar('view/EspacioUtilizarView', [
            'espacios' => $espacios
        ]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombreEspacio = $_POST['nombreEspacioUtilizar'] ?? '';
            $descEspacio = $_POST['descEspacio'] ?? '';
            $capacidad = $_POST['capacidad'] ?? '';

            if (empty($nombreEspacio) || $capacidad === '') {
                echo json_encode([
                    "status" => "error",
                    "message" => "Los campos obligatorios son: Nombre del Espacio y Capacidad."
                ]);
                exit();
            }

            $nuevoId = $this->model->registrarEspacio($nombreEspacio, $descEspacio, $capacidad);

            if ($nuevoId) {
                echo json_encode([
                    "status" => "success",
                    "message" => "¡Espacio registrado con éxito!",
                    "id" => $nuevoId,
                    "nombreEspacio" => $nombreEspacio,
                    "descEspacio" => $descEspacio,
                    "capacidad" => $capacidad
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Hubo un error en la base de datos."
                ]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idEspacioUtilizar'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del espacio"]);
            exit;
        }

        $resultado = $this->model->eliminarEspacio($id);

        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Espacio eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este espacio está asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el espacio."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $espacio = $this->model->obtenerEspacioPorId($id);
        echo json_encode($espacio);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idEspacioUtilizarEdit'] ?? '';
            $nombreEspacio = $_POST['nombreEspacioUtilizarEdit'] ?? '';
            $descEspacio = $_POST['descEspacioEdit'] ?? '';
            $capacidad = $_POST['capacidadEdit'] ?? '';

            if (empty($id) || empty($nombreEspacio) || $capacidad === '') {
                echo json_encode([
                    "status" => "error",
                    "message" => "Los campos obligatorios son: Nombre del Espacio y Capacidad."
                ]);
                exit();
            }

            $resultado = $this->model->actualizarEspacio($id, $nombreEspacio, $descEspacio, $capacidad);

            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "¡Espacio actualizado con éxito!"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se realizaron cambios o hubo un error."
                ]);
            }
            exit();
        }
    }
}