<?php
require_once 'model/GrupoEtarioModel.php';

class GrupoEtarioController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new GrupoEtarioModel();
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

        $grupos = $this->model->listarGrupos();
        $this->renderizar('view/GrupoEtarioView', ['grupos' => $grupos]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nomGrupoEtareo'] ?? '';
            $edadMin = $_POST['edadMin'] ?? '';
            $edadMax = $_POST['edadMax'] ?? '';
            $descripcion = $_POST['descGrupoEtareo'] ?? '';

            if (!empty($nombre) && $edadMin !== '' && $edadMax !== '') {
                $nuevoId = $this->model->registrarGrupo($nombre, $edadMin, $edadMax, $descripcion);

                if ($nuevoId) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Grupo etario registrado con éxito!",
                        "id" => $nuevoId,
                        "nombre" => $nombre,
                        "edadMin" => $edadMin,
                        "edadMax" => $edadMax,
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
                    "message" => "Los campos obligatorios son: Nombre, Edad Mínima y Edad Máxima."
                ]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idGrupoEtareo'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del grupo"]);
            exit;
        }

        $resultado = $this->model->eliminarGrupo($id);

        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Grupo etario eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este grupo está asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el grupo."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $grupo = $this->model->obtenerGrupoPorId($id);
        header('Content-Type: application/json');
        echo json_encode($grupo);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idGrupoEtareoEdit'] ?? '';
            $nombre = $_POST['nomGrupoEtareoEdit'] ?? '';
            $edadMin = $_POST['edadMinEdit'] ?? '';
            $edadMax = $_POST['edadMaxEdit'] ?? '';
            $descripcion = $_POST['descGrupoEtareoEdit'] ?? '';

            if (!empty($id) && !empty($nombre) && $edadMin !== '' && $edadMax !== '') {
                $resultado = $this->model->actualizarGrupo($id, $nombre, $edadMin, $edadMax, $descripcion);

                if ($resultado) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Grupo etario actualizado con éxito!"
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