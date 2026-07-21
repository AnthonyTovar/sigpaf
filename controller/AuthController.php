<?php
require_once 'model/UsuarioModel.php';
require_once 'model/SessionManager.php';
require_once 'SecurityHelper.php';

class AuthController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new UsuarioModel();
    }

    /**
     * FUNCIÓN CLAVE (MÉTODO PRIVADO)
     */
    private function renderizar($nombreVista, $datos = [])
    {
        extract($datos);

        ob_start();
        require $nombreVista . '.php';
        $content = ob_get_clean();

        require 'view/Layout.php';
    }

    // Método para iniciar sesión
    public function login()
    {
        // Si ya tiene sesión válida, redirige al dashboard
        if (isset($_SESSION['usuario_id']) && SessionManager::validarSesion()) {
            header("Location: index.php?action=dashboard");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user = $_POST['username'] ?? '';
            $pass = $_POST['password'] ?? '';

            $usuario = $this->model->validarUsuario($user, $pass);

            $respuesta = [];

            if ($usuario) {
                $usuarioId = $usuario['idUsuario'];

                // ========== CONTROL DE SESIÓN ÚNICA ==========
                $sesionExistente = SessionManager::tieneSesionActiva($usuarioId);

                if ($sesionExistente) {
                    // Sesión realmente activa (no expirada)
                    $respuesta = [
                        'status' => 'error',
                        'message' => 'Sesión activa'
                    ];
                    
                    header('Content-Type: application/json');
                    echo json_encode($respuesta);
                    exit();
                }
                // ==============================================

                // Inicia sesión PHP
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }

                // Registra la sesión en la base de datos
                SessionManager::registrarSesion($usuarioId);

                $_SESSION['usuario_id'] = $usuarioId;
                $_SESSION['username'] = $usuario['nombreUsuario'];
                $_SESSION['rol'] = $usuario['idTipoUsuario'];
                $_SESSION['idEmpleado'] = $usuario['idEmpleado'];

                $respuesta = [
                    'status' => 'success',
                    'redirect' => 'index.php?action=dashboard'
                ];
            } else {
                $respuesta = [
                    'status' => 'error',
                    'message' => 'Usuario o clave incorrectos'
                ];
            }

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit();

        } else {
            $this->renderizar('view/LoginView');
        }
    }

    // Método para registro de nuevos usuarios
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $resultado = $this->model->registrar($_POST['username'], $_POST['password']);
            if ($resultado === true) {
                header("Location: index.php?action=login&success=1");
                exit();
            } else {
                $this->renderizar('view/RegisterView', ['error' => $resultado]);
            }
        } else {
            $this->renderizar('view/RegisterView');
        }
    }

    // Método para la página principal protegida
    public function dashboard()
    {
        SecurityHelper::preventBackAfterLogout();
        
        // Verifica sesión y validez de sesión única
        if (!isset($_SESSION['usuario_id']) || !SessionManager::validarSesion()) {
            SessionManager::cerrarSesionCompleta();
            header("Location: index.php?action=login&error=sesion_invalidada");
            exit();
        }

        // Actualiza última actividad
        SessionManager::actualizarActividad($_SESSION['usuario_id']);

        $this->renderizar('view/DashboardView');
    }

    /**
     * MÉTODO LOGOUT
     */
    public function logout()
    {
        SessionManager::cerrarSesionCompleta();

        header("Location: index.php?action=login");
        exit();
    }
}