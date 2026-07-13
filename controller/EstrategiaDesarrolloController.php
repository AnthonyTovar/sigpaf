<?php
require_once 'model/EstrategiaDesarrolloModel.php';

class EstrategiaDesarrolloController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new EstrategiaDesarrolloModel();
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

        $estrategias = $this->model->listarEstrategias();
        $this->renderizar('view/EstrategiaDesarrolloView', [
            'estrategias' => $estrategias
        ]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nomEstDesarrollo = $_POST['nomEstDesarrollo'] ?? '';
            $descEstDesarrollo = $_POST['descEstDesarrollo'] ?? '';

            if (empty($nomEstDesarrollo)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre de la estrategia es obligatorio."
                ]);
                exit();
            }

            $nuevoId = $this->model->registrarEstrategia($nomEstDesarrollo, $descEstDesarrollo);

            if ($nuevoId === 'nombre_duplicado') {
                echo json_encode([
                    "status" => "error",
                    "message" => "La estrategia de desarrollo ya existe. Use un nombre diferente."
                ]);
            } else if ($nuevoId) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Estrategia de desarrollo registrada con exito!",
                    "id" => $nuevoId,
                    "nomEstDesarrollo" => $nomEstDesarrollo,
                    "descEstDesarrollo" => $descEstDesarrollo
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

        $id = $_POST['idEstDesarrollo'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibio el ID"]);
            exit;
        }

        $resultado = $this->model->eliminarEstrategia($id);

        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Estrategia eliminada correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Esta asignada a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $estrategia = $this->model->obtenerEstrategiaPorId($id);
        echo json_encode($estrategia);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idEstDesarrolloEdit'] ?? '';
            $nomEstDesarrollo = $_POST['nomEstDesarrolloEdit'] ?? '';
            $descEstDesarrollo = $_POST['descEstDesarrolloEdit'] ?? '';

            if (empty($id) || empty($nomEstDesarrollo)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre de la estrategia es obligatorio."
                ]);
                exit();
            }

            $resultado = $this->model->actualizarEstrategia($id, $nomEstDesarrollo, $descEstDesarrollo);

            if ($resultado === 'nombre_duplicado') {
                echo json_encode([
                    "status" => "error",
                    "message" => "La estrategia de desarrollo ya esta en uso por otro registro."
                ]);
            } else if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Estrategia de desarrollo actualizada con exito!"
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