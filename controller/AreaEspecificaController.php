<?php
require_once 'model/AreaEspecificaModel.php';

class AreaEspecificaController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new AreaEspecificaModel();
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
        $areas = $this->model->listarAreas();
        $this->renderizar('view/AreaEspecificaView', ['areas' => $areas]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nomAreaE'] ?? '';

            if (!empty($nombre)) {
                $nuevoId = $this->model->registrarArea($nombre);
                if ($nuevoId) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Área específica registrada con éxito!",
                        "id" => $nuevoId,
                        "nombre" => $nombre
                    ]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error al guardar en la base de datos."]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "El nombre del área es obligatorio."]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');
        $id = $_POST['idAreaE'] ?? null;
        if (!$id) {
            echo json_encode(["status" => "error", "message" => "ID no recibido."]);
            exit;
        }
        $resultado = $this->model->eliminarArea($id);
        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Área específica eliminada correctamente."]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Tiene registros asociados (vértices)."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al eliminar."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $area = $this->model->obtenerAreaPorId($id);
        header('Content-Type: application/json');
        echo json_encode($area);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idAreaEEdit'] ?? '';
            $nombre = $_POST['nomAreaEEdit'] ?? '';

            if (!empty($id) && !empty($nombre)) {
                $resultado = $this->model->actualizarArea($id, $nombre);
                if ($resultado) {
                    echo json_encode(["status" => "success", "message" => "¡Área específica actualizada con éxito!"]);
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