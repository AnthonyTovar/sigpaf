<?php
require_once 'model/GrupoEtnioModel.php';

class GrupoEtnioController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new GrupoEtnioModel();
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
        $this->renderizar('view/GrupoEtnioView', ['grupos' => $grupos]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nomGrupoEtnio'] ?? '';
            $descripcion = $_POST['desGrupoEtnio'] ?? '';

            if (!empty($nombre)) {
                $nuevoId = $this->model->registrarGrupo($nombre, $descripcion);

                if ($nuevoId) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Grupo étnico registrado con éxito!",
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
                    "message" => "El nombre del grupo étnico es obligatorio."
                ]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idGrupoEtnio'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del grupo"]);
            exit;
        }

        $resultado = $this->model->eliminarGrupo($id);

        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Grupo étnico eliminado correctamente"]);
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
            $id = $_POST['idGrupoEtnioEdit'] ?? '';
            $nombre = $_POST['nomGrupoEtnioEdit'] ?? '';
            $descripcion = $_POST['desGrupoEtnioEdit'] ?? '';

            if (!empty($id) && !empty($nombre)) {
                $resultado = $this->model->actualizarGrupo($id, $nombre, $descripcion);

                if ($resultado) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Grupo étnico actualizado con éxito!"
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