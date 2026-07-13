<?php
require_once 'model/UsuarioModel.php';
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
     *FUNCIÓN CLAVE (MÉTODO PRIVADO)
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
        if (isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=dashboard");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user = $_POST['username'] ?? '';
            $pass = $_POST['password'] ?? '';

            $usuario = $this->model->validarUsuario($user, $pass);

            $respuesta = [];

            if ($usuario) {
                $_SESSION['usuario_id'] = $usuario['idUsuario'];
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
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }
        $this->renderizar('view/DashboardView');
    }

    /**
     * MÉTODO LOGOUT
     * Borra variables de servidor, elimina cookies del navegador y destruye la sesión.
     */
    public function logout()
    {
        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        header("Location: index.php?action=login");
        exit();
    }
}