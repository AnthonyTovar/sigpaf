<?php
require_once 'model/ParroquiaModel.php';

class ParroquiaController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new ParroquiaModel();
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

        $parroquias = $this->model->listarParroquias();
        $municipios = $this->model->listarMunicipios();
        $this->renderizar('view/ParroquiaView', [
            'parroquias' => $parroquias,
            'municipios' => $municipios
        ]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nombreParroquia'] ?? '';
            $idMunicipio = $_POST['idMunicipio'] ?? '';

            if (!empty($nombre) && !empty($idMunicipio)) {
                $nuevoId = $this->model->registrarParroquia($nombre, $idMunicipio);

                if ($nuevoId) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Parroquia registrada con éxito!",
                        "id" => $nuevoId,
                        "nombre" => $nombre,
                        "idMunicipio" => $idMunicipio
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
                    "message" => "El nombre de la parroquia y el municipio son obligatorios."
                ]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idParroquia'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID de la parroquia"]);
            exit;
        }

        $resultado = $this->model->eliminarParroquia($id);

        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Parroquia eliminada correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Esta parroquia está asignada a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar la parroquia."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $parroquia = $this->model->obtenerParroquiaPorId($id);
        echo json_encode($parroquia);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idParroquiaEdit'] ?? '';
            $nombre = $_POST['nombreParroquiaEdit'] ?? '';
            $idMunicipio = $_POST['idMunicipioEdit'] ?? '';

            if (!empty($id) && !empty($nombre) && !empty($idMunicipio)) {
                $resultado = $this->model->actualizarParroquia($id, $nombre, $idMunicipio);

                if ($resultado) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Parroquia actualizada con éxito!"
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