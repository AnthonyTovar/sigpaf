<?php
// Controlador de autenticación



require_once 'model/UsuarioModel.php';
require_once 'model/SessionManager.php';
require_once 'model/DashboardModel.php';
require_once 'SecurityHelper.php';
require_once 'Logger.php';

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


    private function renderizar($nombreVista, $datos = [])
    {
        extract($datos);

        ob_start();
        require $nombreVista . '.php';
        $content = ob_get_clean();

        require 'view/Layout.php';
    }


    public function login()
    {

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

                // Control de sesión única



                $sesionExistente = SessionManager::tieneSesionActiva($usuarioId);

                if ($sesionExistente) {
                    $respuesta = [
                        'status' => 'error',
                        'message' => 'Ya tienes una sesión activa en otro dispositivo o navegador. Ciérrala para poder entrar.'
                    ];

                    header('Content-Type: application/json');
                    echo json_encode($respuesta);
                    exit();
                }



                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }


                SessionManager::registrarSesion($usuarioId);

                $_SESSION['usuario_id'] = $usuarioId;
                $_SESSION['username'] = $usuario['nombreUsuario'];
                $_SESSION['rol'] = $usuario['idTipoUsuario'];
                $_SESSION['idEmpleado'] = $usuario['idEmpleado'];


                Logger::login();

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


    public function dashboard()
    {
        SecurityHelper::preventBackAfterLogout();
        

        if (!isset($_SESSION['usuario_id']) || !SessionManager::validarSesion()) {

            Logger::registrar('LOGOUT', 'Sesion', null,
                'Sesion invalidada: inicio de sesion en otro dispositivo, cierre del navegador o inactividad prolongada');

            SessionManager::cerrarSesionCompleta();
            header("Location: index.php?action=login&error=sesion_invalidada");
            exit();
        }


        SessionManager::actualizarActividad($_SESSION['usuario_id']);


        $dashboardModel = new DashboardModel();
        $resumen        = $dashboardModel->obtenerResumen();
        $proximas       = $dashboardModel->obtenerProximasActividades(5);
        $recientes      = $dashboardModel->obtenerActividadesRecientes(6);

        $this->renderizar('view/DashboardView', [
            'resumen'   => $resumen,
            'proximas'  => $proximas,
            'recientes' => $recientes
        ]);
    }


    public function logout()
    {

        Logger::logout();

        SessionManager::cerrarSesionCompleta();

        header("Location: index.php?action=login");
        exit();
    }


    public function pingSesion()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['usuario_id']) || !SessionManager::validarSesion()) {

            SessionManager::cerrarSesionCompleta();
            echo json_encode(['status' => 'expired']);
            exit();
        }


        SessionManager::refrescarConPing();
        echo json_encode(['status' => 'ok']);
        exit();
    }
}