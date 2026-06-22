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
        require 'view/layout.php';
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
            $nombre = $_POST['nomLugarActividad'] ?? '';
            $descripcion = $_POST['desLugarActividad'] ?? '';
            $direccion = $_POST['direccion'] ?? '';
            $esSede = isset($_POST['esSede']) ? true : false;
            $idParroquia = $_POST['idParroquia'] ?? '';

            if (!empty($nombre) && !empty($direccion) && !empty($idParroquia)) {
                $nuevoId = $this->model->registrarLugar($nombre, $descripcion, $direccion, $esSede, $idParroquia);

                if ($nuevoId) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Lugar de actividad registrado con éxito!",
                        "id" => $nuevoId,
                        "nombre" => $nombre,
                        "descripcion" => $descripcion,
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
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Los campos obligatorios son: Nombre, Dirección y Parroquia."
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
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del lugar"]);
            exit;
        }

        $resultado = $this->model->eliminarLugar($id);

        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Lugar eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este lugar está asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el lugar."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $lugar = $this->model->obtenerLugarPorId($id);
        header('Content-Type: application/json');
        echo json_encode($lugar);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idLugarActividadEdit'] ?? '';
            $nombre = $_POST['nomLugarActividadEdit'] ?? '';
            $descripcion = $_POST['desLugarActividadEdit'] ?? '';
            $direccion = $_POST['direccionEdit'] ?? '';
            $esSede = isset($_POST['esSedeEdit']) ? true : false;
            $idParroquia = $_POST['idParroquiaEdit'] ?? '';

            if (!empty($id) && !empty($nombre) && !empty($direccion) && !empty($idParroquia)) {
                $resultado = $this->model->actualizarLugar($id, $nombre, $descripcion, $direccion, $esSede, $idParroquia);

                if ($resultado) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Lugar de actividad actualizado con éxito!"
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