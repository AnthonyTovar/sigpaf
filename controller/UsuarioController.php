<?php
require_once 'model/UsuarioModel.php';

class UsuarioController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new UsuarioModel();
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

        $usuarios = $this->model->listarUsuarios();
        $tiposUsuario = $this->model->listarTiposUsuario();
        $empleados = $this->model->listarEmpleadosSinUsuario();
        $this->renderizar('view/UsuarioView', [
            'usuarios' => $usuarios,
            'tiposUsuario' => $tiposUsuario,
            'empleados' => $empleados
        ]);
    }

    // ============================================
    // NUEVO: BUSCAR EMPLEADO POR CÉDULA (AJAX)
    // ============================================
    public function buscarEmpleadoPorCedula()
    {
        header('Content-Type: application/json');

        $cedula = $_GET['cedula'] ?? '';

        if (empty($cedula)) {
            echo json_encode(["status" => "error", "message" => "Cédula no proporcionada."]);
            exit;
        }

        $empleado = $this->model->buscarEmpleadoPorCedula($cedula);

        if (!$empleado) {
            echo json_encode(["status" => "no_existe", "message" => "No se encontró un empleado con esa cédula."]);
            exit;
        }

        $idUsuario = $this->model->empleadoTieneUsuario($empleado['idEmpleado']);

        if ($idUsuario) {
            echo json_encode([
                "status" => "ya_tiene_usuario",
                "message" => "Este empleado ya tiene un usuario asignado.",
                "idUsuario" => $idUsuario,
                "nombres" => $empleado['nombres'],
                "apellidos" => $empleado['apellidos']
            ]);
            exit;
        }

        echo json_encode([
            "status" => "success",
            "message" => "Empleado encontrado y disponible.",
            "idEmpleado" => $empleado['idEmpleado'],
            "cedulaEmpleado" => $empleado['cedulaEmpleado'],
            "nombres" => $empleado['nombres'],
            "apellidos" => $empleado['apellidos']
        ]);
        exit;
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombreUsuario = $_POST['nombreUsuario'] ?? '';
            $contrasena = $_POST['contrasena'] ?? '';
            $confirmarContrasena = $_POST['confirmarContrasena'] ?? '';
            $idTipoUsuario = $_POST['idTipoUsuario'] ?? '';
            $idEmpleado = $_POST['idEmpleado'] ?? '';

            if (empty($nombreUsuario) || empty($contrasena) || empty($idTipoUsuario) || empty($idEmpleado)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Todos los campos son obligatorios."
                ]);
                exit();
            }

            if ($contrasena !== $confirmarContrasena) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Las contraseñas no coinciden."
                ]);
                exit();
            }

            if ($this->model->verificarNombreUsuario($nombreUsuario)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre de usuario ya existe."
                ]);
                exit();
            }

            $nuevoId = $this->model->registrarUsuarioMaestro($nombreUsuario, $contrasena, $idTipoUsuario, $idEmpleado);

            if ($nuevoId) {
                echo json_encode([
                    "status" => "success",
                    "message" => "¡Usuario registrado con éxito!",
                    "id" => $nuevoId,
                    "nombreUsuario" => $nombreUsuario,
                    "idTipoUsuario" => $idTipoUsuario,
                    "idEmpleado" => $idEmpleado
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

        $id = $_POST['idUsuario'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del usuario"]);
            exit;
        }

        $resultado = $this->model->eliminarUsuario($id);

        if ($resultado === true) {
            echo json_encode(["status" => "success", "message" => "Usuario eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este usuario tiene registros vinculados."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el usuario."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $usuario = $this->model->obtenerUsuarioPorId($id);
        echo json_encode($usuario);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idUsuarioEdit'] ?? '';
            $nombreUsuario = $_POST['nombreUsuarioEdit'] ?? '';
            $contrasena = $_POST['contrasenaEdit'] ?? '';
            $idTipoUsuario = $_POST['idTipoUsuarioEdit'] ?? '';
            $idEmpleado = $_POST['idEmpleadoEdit'] ?? '';

            if (empty($id) || empty($nombreUsuario) || empty($idTipoUsuario) || empty($idEmpleado)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Los campos obligatorios son: Nombre de Usuario, Tipo de Usuario y Empleado."
                ]);
                exit();
            }

            if ($this->model->verificarNombreUsuario($nombreUsuario, $id)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre de usuario ya está en uso por otro registro."
                ]);
                exit();
            }

            $resultado = $this->model->actualizarUsuario($id, $nombreUsuario, $contrasena, $idTipoUsuario, $idEmpleado);

            if ($resultado) {
                echo json_encode([
                    "status" => "success",
                    "message" => "¡Usuario actualizado con éxito!"
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

    // ============================================
    // GESTIÓN DE USUARIO PROPIO - TODOS LOS ROLES
    // ============================================

    public function gestionUsuario()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $usuario = $this->model->obtenerUsuarioPorId($_SESSION['usuario_id']);
        $this->renderizar('view/GestionUsuarioView', [
            'usuario' => $usuario
        ]);
    }

    public function actualizarPerfil()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_SESSION['usuario_id'];
            $nombreUsuario = $_POST['nombreUsuarioPerfil'] ?? '';
            $contrasena = $_POST['contrasenaPerfil'] ?? '';

            if (empty($nombreUsuario)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre de usuario es obligatorio."
                ]);
                exit();
            }

            if ($this->model->verificarNombreUsuario($nombreUsuario, $id)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre de usuario ya está en uso por otro registro."
                ]);
                exit();
            }

            $resultado = $this->model->actualizarPerfil($id, $nombreUsuario, $contrasena);

            if ($resultado) {
                $_SESSION['username'] = $nombreUsuario;
                echo json_encode([
                    "status" => "success",
                    "message" => "¡Perfil actualizado con éxito!"
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