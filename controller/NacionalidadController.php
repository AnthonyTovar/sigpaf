<?php
require_once 'model/NacionalidadModel.php';

class NacionalidadController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new NacionalidadModel();
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

        $nacionalidades = $this->model->listarNacionalidades();
        $this->renderizar('view/NacionalidadView', ['nacionalidades' => $nacionalidades]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nomNacionalidad'] ?? '';

            if (!empty($nombre)) {
                $nuevoId = $this->model->registrarNacionalidad($nombre);

                if ($nuevoId) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Nacionalidad registrada con éxito!",
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
                    "message" => "El nombre de la nacionalidad es obligatorio."
                ]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idNacionalidad'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID de la nacionalidad"]);
            exit;
        }

        $resultado = $this->model->eliminarNacionalidad($id);

        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Nacionalidad eliminada correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Esta nacionalidad está asignada a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar la nacionalidad."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $nacionalidad = $this->model->obtenerNacionalidadPorId($id);
        echo json_encode($nacionalidad);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idNacionalidadEdit'] ?? '';
            $nombre = $_POST['nomNacionalidadEdit'] ?? '';

            if (!empty($id) && !empty($nombre)) {
                $resultado = $this->model->actualizarNacionalidad($id, $nombre);

                if ($resultado) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Nacionalidad actualizada con éxito!"
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