<?php
require_once 'model/LugarActividadModel.php';

class LugarActividadController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new LugarActividadModel();
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

        $lugares = $this->model->listarLugares();
        $parroquias = $this->model->listarParroquias();
        $this->renderizar('view/LugarActividadView', [
            'lugares' => $lugares,
            'parroquias' => $parroquias
        ]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nomLugar = $_POST['nomLugarActividad'] ?? '';
            $desLugar = $_POST['desLugarActividad'] ?? '';
            $direccion = $_POST['direccion'] ?? '';
            $esSede = ($_POST['esSede'] ?? '0') === '1';
            $idParroquia = $_POST['idParroquia'] ?? '';

            if (empty($nomLugar) || empty($direccion) || empty($idParroquia)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Los campos obligatorios son: Nombre del Lugar, Direccion y Parroquia."
                ]);
                exit();
            }

            $nuevoId = $this->model->registrarLugar($nomLugar, $desLugar, $direccion, $esSede, $idParroquia);

            if ($nuevoId === 'nombre_duplicado') {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre del lugar ya existe. Use un nombre diferente."
                ]);
            } else if ($nuevoId) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Lugar de actividad registrado con exito!",
                    "id" => $nuevoId,
                    "nomLugar" => $nomLugar,
                    "desLugar" => $desLugar,
                    "direccion" => $direccion,
                    "esSede" => $esSede,
                    "idParroquia" => $idParroquia
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

        $id = $_POST['idLugarActividad'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibio el ID del lugar"]);
            exit;
        }

        $resultado = $this->model->eliminarLugar($id);

        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Lugar eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este lugar esta asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el lugar."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $lugar = $this->model->obtenerLugarPorId($id);
        echo json_encode($lugar);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idLugarActividadEdit'] ?? '';
            $nomLugar = $_POST['nomLugarActividadEdit'] ?? '';
            $desLugar = $_POST['desLugarActividadEdit'] ?? '';
            $direccion = $_POST['direccionEdit'] ?? '';
            $esSede = ($_POST['esSedeEdit'] ?? '0') === '1';
            $idParroquia = $_POST['idParroquiaEdit'] ?? '';

            if (empty($id) || empty($nomLugar) || empty($direccion) || empty($idParroquia)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Los campos obligatorios son: Nombre del Lugar, Direccion y Parroquia."
                ]);
                exit();
            }

            $resultado = $this->model->actualizarLugar($id, $nomLugar, $desLugar, $direccion, $esSede, $idParroquia);

            if ($resultado === 'nombre_duplicado') {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre del lugar ya esta en uso por otro registro."
                ]);
            } else if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Lugar de actividad actualizado con exito!"
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