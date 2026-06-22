<?php
require_once 'model/TipoActividadModel.php';

class TipoActividadController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new TipoActividadModel();
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

        $tiposActividad = $this->model->listarTiposActividad();
        $this->renderizar('view/TipoActividadView', ['tiposActividad' => $tiposActividad]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nomTipoActividad'] ?? '';
            $desc = $_POST['descTipoActividad'] ?? '';

            if (!empty($nombre) && !empty($desc)) {
                $nuevoId = $this->model->registrarTipoActividad($nombre, $desc);

                if ($nuevoId) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Tipo de actividad registrado con éxito!",
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

        $id = $_POST['idTipoActividad'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del tipo de actividad"]);
            exit;
        }

        $resultado = $this->model->eliminarTipoActividad($id);

        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Tipo de actividad eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este tipo de actividad está asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el tipo de actividad."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $tipoActividad = $this->model->obtenerTipoActividadPorId($id);
        echo json_encode($tipoActividad);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idTipoActividadEdit'] ?? '';
            $nombre = $_POST['nomTipoActividadEdit'] ?? '';
            $desc = $_POST['descTipoActividadEdit'] ?? '';

            if (!empty($id) && !empty($nombre) && !empty($desc)) {
                $resultado = $this->model->actualizarTipoActividad($id, $nombre, $desc);

                if ($resultado) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Tipo de actividad actualizado con éxito!"
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