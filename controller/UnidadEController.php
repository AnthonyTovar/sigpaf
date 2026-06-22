<?php
require_once 'model/UnidadEModel.php';

class UnidadEController
{
    private $model;

    public function __construct()
    {
        // Iniciamos sesión si no existe
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new UnidadEModel();
    }

    /**
     * MÉTODO RENDERIZAR (se encarga de montar las vistas en el Layout)
     */
    private function renderizar($nombreVista, $datos = [])
    {
        extract($datos);
        ob_start();
        require $nombreVista . '.php';
        $content = ob_get_clean();

        require 'view/layout.php';
    }

    // Listado de Cargos
    public function listar()
    {
        // Verificamos usuario_id
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $unidad = $this->model->listarUnidadE();

        // IMPORTANTE: Solo enviamos el nombre de la ruta SIN el .php
        $this->renderizar('view/UnidadEView', ['unidad' => $unidad]);
    }

    // Guardar nuevo cargo
    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nomUnidadEjecutora'] ?? '';
            $desc = $_POST['desUnidadEjecutora'] ?? '';

            if (!empty($nombre)) {
                // CAMBIO AQUÍ: El modelo ahora debe devolver el ID generado (o false si falla)
                $idNuevo = $this->model->registrarUnidadE($nombre, $desc);

                if ($idNuevo) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Unidad Ejecutora registrada con éxito!",
                        "id" => $idNuevo, // Enviamos el ID al JavaScript
                        "nombre" => $nombre, // Enviamos el nombre para la tabla
                        "descripcion" => $desc // Enviamos la descripción para la tabla
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
                    "message" => "El nombre de la Unidad Ejecutora es obligatorio."
                ]);
            }
            exit();
        }
    }
    // Eliminar cargo
    public function eliminar()
    {
        header('Content-Type: application/json');

        // Validamos que el ID llegue por POST (enviado por AJAX)
        $id = $_POST['idUnidadEjecutora'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID de la Unidad Ejecutora"]);
            exit;
        }

        $resultado = $this->model->eliminarUnidadE($id);

        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Unidad Ejecutora eliminada correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Esta Unidad Ejecutora está asignada a empleados activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar la Unidad Ejecutora."]);
        }
        exit;
    }

    // Este método lo usamos con AJAX
    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $unidadE = $this->model->obtenerUnidadEPorId($id);
        echo json_encode($unidadE); // Enviamos los datos al navegador en formato JSON
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json'); // Misma lógica que guardar

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idUnidadEjecutoraEdit'] ?? '';
            $nombre = $_POST['nomUnidadEjecutoraEdit'] ?? '';
            $desc = $_POST['desUnidadEjecutoraEdit'] ?? '';

            if (!empty($id) && !empty($nombre)) {
                $resultado = $this->model->actualizarUnidadE($id, $nombre, $desc);

                if ($resultado) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Unidad Ejecutora actualizada con éxito!"
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