<?php
require_once 'model/TipoUsuarioModel.php';

class TipoUsuarioController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new TipoUsuarioModel();
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

        $tipoUsuarios = $this->model->listarTipoUsuarios();
        $this->renderizar('view/TipoUsuarioView', [
            'tipoUsuarios' => $tipoUsuarios
        ]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $rolUsuario = $_POST['rolUsuario'] ?? '';

            if (!empty($rolUsuario)) {
                $nuevoId = $this->model->registrarTipoUsuario($rolUsuario);

                if ($nuevoId) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Tipo de usuario registrado con éxito!",
                        "id" => $nuevoId,
                        "rol" => $rolUsuario
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
                    "message" => "El campo Rol de Usuario es obligatorio."
                ]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idTipoUsuario'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del tipo de usuario"]);
            exit;
        }

        $resultado = $this->model->eliminarTipoUsuario($id);

        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Tipo de usuario eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este tipo de usuario está asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el tipo de usuario."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $tipoUsuario = $this->model->obtenerTipoUsuarioPorId($id);
        echo json_encode($tipoUsuario);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idTipoUsuarioEdit'] ?? '';
            $rolUsuario = $_POST['rolUsuarioEdit'] ?? '';

            if (!empty($id) && !empty($rolUsuario)) {
                $resultado = $this->model->actualizarTipoUsuario($id, $rolUsuario);

                if ($resultado) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Tipo de usuario actualizado con éxito!"
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