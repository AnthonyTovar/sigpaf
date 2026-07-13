<?php
require_once 'model/VerticeModel.php';

class VerticeController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new VerticeModel();
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
        $Vertice = $this->model->listarVertice();
        $this->renderizar('view/VerticeView', [
            'Vertice' => $Vertice
        ]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nombreVertice'] ?? '';
            $desc = $_POST['descripcionVertice'] ?? '';

            if (!empty($nombre)) {
                $nuevoId = $this->model->registrarVertice($nombre, $desc);
                if ($nuevoId) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Vértice registrado con éxito!",
                        "id" => $nuevoId,
                        "nombre" => $nombre,
                        "descripcion" => $desc
                    ]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error al guardar en la base de datos."]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "El nombre del vértice es obligatorio."]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');
        $id = $_POST['idVertice'] ?? null;
        if (!$id) {
            echo json_encode(["status" => "error", "message" => "ID no recibido."]);
            exit;
        }
        $resultado = $this->model->eliminarVertice($id);
        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Vértice eliminado correctamente."]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Tiene registros asociados."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al eliminar."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $vertice = $this->model->obtenerVerticePorId($id);
        header('Content-Type: application/json');
        echo json_encode($vertice);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idVerticeEdit'] ?? '';
            $nombre = $_POST['nombreVerticeEdit'] ?? '';
            $desc = $_POST['descripcionVerticeEdit'] ?? '';

            if (!empty($id) && !empty($nombre)) {
                $resultado = $this->model->actualizarVertice($id, $nombre, $desc);
                if ($resultado) {
                    echo json_encode(["status" => "success", "message" => "¡Vértice actualizado con éxito!"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se detectaron cambios o hubo un error."]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Datos incompletos."]);
            }
            exit();
        }
    }
}