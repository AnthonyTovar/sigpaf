<?php
require_once 'model/TipoEntregaModel.php';

class TipoEntregaController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new TipoEntregaModel();
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

        $tiposEntrega = $this->model->listarTiposEntrega();
        $this->renderizar('view/TipoEntregaView', [
            'tiposEntrega' => $tiposEntrega
        ]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nomTipEntrega = $_POST['nomTipEntrega'] ?? '';

            if (empty($nomTipEntrega)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre del tipo de entrega es obligatorio."
                ]);
                exit();
            }

            $nuevoId = $this->model->registrarTipoEntrega($nomTipEntrega);

            if ($nuevoId === 'nombre_duplicado') {
                echo json_encode([
                    "status" => "error",
                    "message" => "El tipo de entrega ya existe. Use un nombre diferente."
                ]);
            } else if ($nuevoId) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Tipo de entrega registrado con exito!",
                    "id" => $nuevoId,
                    "nomTipEntrega" => $nomTipEntrega
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

        $id = $_POST['idTipEntrega'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibio el ID"]);
            exit;
        }

        $resultado = $this->model->eliminarTipoEntrega($id);

        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Tipo de entrega eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Esta asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $tipoEntrega = $this->model->obtenerTipoEntregaPorId($id);
        echo json_encode($tipoEntrega);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idTipEntregaEdit'] ?? '';
            $nomTipEntrega = $_POST['nomTipEntregaEdit'] ?? '';

            if (empty($id) || empty($nomTipEntrega)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre del tipo de entrega es obligatorio."
                ]);
                exit();
            }

            $resultado = $this->model->actualizarTipoEntrega($id, $nomTipEntrega);

            if ($resultado === 'nombre_duplicado') {
                echo json_encode([
                    "status" => "error",
                    "message" => "El tipo de entrega ya esta en uso por otro registro."
                ]);
            } else if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Tipo de entrega actualizado con exito!"
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