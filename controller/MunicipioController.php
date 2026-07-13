<?php
require_once 'model/MunicipioModel.php';

class MunicipioController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new MunicipioModel();
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

        $municipios = $this->model->listarMunicipios();
        $estados = $this->model->listarEstados();

        $this->renderizar('view/MunicipioView', [
            'municipios' => $municipios,
            'estados' => $estados
        ]);
    }

    // GUARDAR (POST)
    public function guardar()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $nombre = trim($_POST['nombreMunicipio'] ?? '');
                $idEstado = $_POST['idEstado'] ?? '';

                if (!empty($nombre) && !empty($idEstado)) {
                    $nuevoId = $this->model->registrarMunicipio($nombre, $idEstado);

                    if ($nuevoId) {
                        echo json_encode([
                            "status" => "success",
                            "message" => "¡Municipio registrado con éxito!",
                            "id" => $nuevoId
                        ]);
                    } else {
                        echo json_encode(["status" => "error", "message" => "Error al insertar en la base de datos."]);
                    }
                } else {
                    echo json_encode(["status" => "error", "message" => "Todos los campos son obligatorios."]);
                }
            }
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Excepción del servidor: " . $e->getMessage()]);
        }
        exit();
    }

    // CONSULTAR PARA MODAL (GET)
    public function consultar()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        $id = $_GET['id'] ?? '';
        
        if (empty($id)) {
            echo json_encode(["error" => "ID no proporcionado"]);
            exit;
        }

        $municipio = $this->model->obtenerMunicipioPorId($id);
        
        if ($municipio) {
            echo json_encode($municipio);
        } else {
            echo json_encode(["error" => "Municipio no encontrado"]);
        }
        exit;
    }

    // EDITAR / ACTUALIZAR (POST)
    public function editar()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                $id = $_POST['idMunicipioEdit'] ?? '';
                $nombre = trim($_POST['nombreMunicipioEdit'] ?? '');
                $idEstado = $_POST['idEstadoEdit'] ?? '';

                if (!empty($id) && !empty($nombre) && !empty($idEstado)) {
                    $resultado = $this->model->actualizarMunicipio($id, $nombre, $idEstado);

                    if ($resultado !== false) {
                        echo json_encode(["status" => "success", "message" => "¡Municipio actualizado correctamente!"]);
                    } else {
                        echo json_encode(["status" => "error", "message" => "Error al intentar actualizar en la base de datos."]);
                    }
                } else {
                    echo json_encode(["status" => "error", "message" => "Datos incompletos para procesar la edición."]);
                }
            }
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Error técnico: " . $e->getMessage()]);
        }
        exit();
    }

    // ELIMINAR (POST)
    public function eliminar()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        $id = $_POST['idMunicipio'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del municipio."]);
            exit;
        }

        try {
            $resultado = $this->model->eliminarMunicipio($id);

            if ($resultado === true) {
                echo json_encode(["status" => "success", "message" => "Municipio eliminado correctamente."]);
            } else if ($resultado === "link") {
                echo json_encode(["status" => "error", "message" => "No se puede eliminar: Existen datos vinculados a este municipio."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo eliminar el registro."]);
            }
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Error técnico: " . $e->getMessage()]);
        }
        exit;
    }
}