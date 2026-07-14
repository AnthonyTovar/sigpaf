<?php
require_once 'model/HorarioModel.php';

class HorarioController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new HorarioModel();
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

        $horarios = $this->model->listarHorarios();
        $this->renderizar('view/HorarioView', ['horarios' => $horarios]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nomHorario'] ?? '';

            if (!empty($nombre)) {
                $nuevoId = $this->model->registrarHorario($nombre);

                if ($nuevoId) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Horario registrado con éxito!",
                        "id" => $nuevoId,
                        "nombre" => $nombre
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
                    "message" => "El nombre del horario es obligatorio."
                ]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idHorario'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del horario"]);
            exit;
        }

        $resultado = $this->model->eliminarHorario($id);

        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Horario eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este horario está asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el horario."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $horario = $this->model->obtenerHorarioPorId($id);
        echo json_encode($horario);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idHorarioEdit'] ?? '';
            $nombre = $_POST['nomHorarioEdit'] ?? '';

            if (!empty($id) && !empty($nombre)) {
                $resultado = $this->model->actualizarHorario($id, $nombre);

                if ($resultado) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Horario actualizado con éxito!"
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