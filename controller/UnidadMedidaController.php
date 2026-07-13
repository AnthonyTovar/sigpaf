<?php
require_once 'model/UnidadMedidaModel.php';

class UnidadMedidaController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new UnidadMedidaModel();
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

        $unidades = $this->model->listarUnidades();
        $this->renderizar('view/UnidadMedidaView', ['unidades' => $unidades]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nomUnidadMedida'] ?? '';
            $descripcion = $_POST['descUnidadMedida'] ?? '';

            if (!empty($nombre)) {
                $nuevoId = $this->model->registrarUnidad($nombre, $descripcion);

                if ($nuevoId) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Unidad de medida registrada con éxito!",
                        "id" => $nuevoId,
                        "nombre" => $nombre,
                        "descripcion" => $descripcion
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
                    "message" => "El nombre de la unidad de medida es obligatorio."
                ]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idUnidadMedida'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID de la unidad"]);
            exit;
        }

        $resultado = $this->model->eliminarUnidad($id);

        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Unidad de medida eliminada correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Esta unidad está asignada a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar la unidad."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $unidad = $this->model->obtenerUnidadPorId($id);
        header('Content-Type: application/json');
        echo json_encode($unidad);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idUnidadMedidaEdit'] ?? '';
            $nombre = $_POST['nomUnidadMedidaEdit'] ?? '';
            $descripcion = $_POST['descUnidadMedidaEdit'] ?? '';

            if (!empty($id) && !empty($nombre)) {
                $resultado = $this->model->actualizarUnidad($id, $nombre, $descripcion);

                if ($resultado) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Unidad de medida actualizada con éxito!"
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