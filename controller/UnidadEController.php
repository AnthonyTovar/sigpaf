<?php
require_once 'model/UnidadEModel.php';

class UnidadEController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new UnidadEModel();
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

        $unidad = $this->model->listarUnidadE();

        $this->renderizar('view/UnidadEView', ['unidad' => $unidad]);
    }

    // Guardar nueva Unidad Ejecutora
    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = trim($_POST['nomUnidadEjecutora'] ?? '');
            $desc = trim($_POST['desUnidadEjecutora'] ?? '');

            if (empty($nombre)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre de la Unidad Ejecutora es obligatorio."
                ]);
                exit();
            }

            // 1. Validar no más de 2 caracteres iguales seguidos
            if (preg_match('/(.)\1{2,}/u', $nombre) || preg_match('/(.)\1{2,}/u', $desc)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se permiten 3 o más caracteres iguales consecutivos."
                ]);
                exit();
            }

            // 2. Validar si ya existe en BD
            $duplicado = $this->model->existeNombreODescripcion($nombre, $desc);
            if ($duplicado === 'nombre') {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre de la Unidad Ejecutora ya se encuentra registrado."
                ]);
                exit();
            } else if ($duplicado === 'descripcion') {
                echo json_encode([
                    "status" => "error",
                    "message" => "La descripción ingresada ya pertenece a otra Unidad Ejecutora."
                ]);
                exit();
            }

            // 3. Registrar si supera las validaciones
            $idNuevo = $this->model->registrarUnidadE($nombre, $desc);

            if ($idNuevo) {
                echo json_encode([
                    "status" => "success",
                    "message" => "¡Unidad Ejecutora registrada con éxito!",
                    "id" => $idNuevo,
                    "nombre" => $nombre,
                    "descripcion" => $desc
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

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $unidadE = $this->model->obtenerUnidadEPorId($id);
        echo json_encode($unidadE); 
        exit;
    }

    // Editar Unidad Ejecutora
    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = trim($_POST['idUnidadEjecutoraEdit'] ?? '');
            $nombre = trim($_POST['nomUnidadEjecutoraEdit'] ?? '');
            $desc = trim($_POST['desUnidadEjecutoraEdit'] ?? '');

            if (empty($id) || empty($nombre)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Datos incompletos."
                ]);
                exit();
            }

            // 1. Validar no más de 2 caracteres iguales seguidos
            if (preg_match('/(.)\1{2,}/u', $nombre) || preg_match('/(.)\1{2,}/u', $desc)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se permiten 3 o más caracteres iguales consecutivos."
                ]);
                exit();
            }

            // 2. Validar duplicidad ignorando el registro actual
            $duplicado = $this->model->existeNombreODescripcion($nombre, $desc, $id);
            if ($duplicado === 'nombre') {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre ingresado ya le pertenece a otra Unidad Ejecutora."
                ]);
                exit();
            } else if ($duplicado === 'descripcion') {
                echo json_encode([
                    "status" => "error",
                    "message" => "La descripción ingresada ya pertenece a otra Unidad Ejecutora."
                ]);
                exit();
            }

            // 3. Actualizar
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
            exit();
        }
    }
}