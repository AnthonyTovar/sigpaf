<?php
require_once 'model/EstadoModel.php';

class EstadoController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new EstadoModel();
    }

    private function renderizar($nombreVista, $datos = [])
    {
        extract($datos);
        ob_start();
        require $nombreVista . '.php';
        $content = ob_get_clean();
        require 'view/layout.php';
    }

    // Carga la vista principal con el listado
    public function listar()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }
        $estados = $this->model->listarEstados();
        $this->renderizar('view/EstadoView', ['estados' => $estados]);
    }

    // Procesa el registro de un nuevo estado vía AJAX
    public function guardar()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nombreEstado'] ?? '';

            $nuevoId = $this->model->registrarEstado($nombre);
            
            if ($nuevoId) {
                echo json_encode([
                    "status" => "success",
                    "message" => "¡Estado registrado con éxito!",
                    "id" => $nuevoId,
                    "nombre" => $nombre
                ]);
            } else {
                echo json_encode([
                    "status" => "error", 
                    "message" => "Error al guardar en la base de datos."
                ]);
            }
            exit();
        }
    }

    // Consulta un estado por ID para cargar el modal de edición
    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $estado = $this->model->obtenerEstadoPorId($id);
        header('Content-Type: application/json');
        echo json_encode($estado);
        exit;
    }

    // Procesa la actualización vía AJAX
    public function editar()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idEstadoEdit'] ?? '';
            $nombre = $_POST['nombreEstadoEdit'] ?? '';

            $resultado = $this->model->actualizarEstado($id, $nombre);
            
            if ($resultado) {
                echo json_encode([
                    "status" => "success", 
                    "message" => "¡Estado actualizado con éxito!"
                ]);
            } else {
                echo json_encode([
                    "status" => "error", 
                    "message" => "No se detectaron cambios o hubo un error."
                ]);
            }
            exit();
        }
    }

    // Procesa la eliminación vía AJAX
    public function eliminar()
    {
        header('Content-Type: application/json');
        $id = $_POST['idEstado'] ?? null;
        
        if (!$id) {
            echo json_encode([
                "status" => "error", 
                "message" => "ID no recibido."
            ]);
            exit;
        }
        
        $resultado = $this->model->eliminarEstado($id);
        
        if ($resultado === true) {
            echo json_encode([
                "status" => "success", 
                "message" => "Estado eliminado correctamente."
            ]);
        } else if ($resultado === "link") {
            echo json_encode([
                "status" => "error", 
                "message" => "No se puede eliminar: El estado tiene municipios asociados."
            ]);
        } else {
            echo json_encode([
                "status" => "error", 
                "message" => "Error interno al eliminar."
            ]);
        }
        exit;
    }
}