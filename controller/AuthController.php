<?php
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
                // tieneSesionActiva() ignora y limpia las sesiones "fantasma"
                // (navegador cerrado sin usar "Salir"), por lo que solo bloquea
                // cuando realmente hay una sesión viva en otro dispositivo/navegador.
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

                // Registra el inicio de sesion en la bitacora
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
            // Bitacora: sesión invalidada (inicio en otro lugar, cierre del navegador o inactividad)
            Logger::registrar('LOGOUT', 'Sesion', null,
                'Sesion invalidada: inicio de sesion en otro dispositivo, cierre del navegador o inactividad prolongada');

            SessionManager::cerrarSesionCompleta();
            header("Location: index.php?action=login&error=sesion_invalidada");
            exit();
        }

        // Actualiza última actividad (mantiene la sesión viva)
        SessionManager::actualizarActividad($_SESSION['usuario_id']);

        // Datos REALES para el dashboard (ver model/DashboardModel.php)
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

    /**
     * MÉTODO LOGOUT
     */
    public function logout()
    {
        // Registra el cierre de sesion en la bitacora (antes de destruirla)
        Logger::logout();

        SessionManager::cerrarSesionCompleta();

        header("Location: index.php?action=login");
        exit();
    }

    /**
     * PING DE SESIÓN (heartbeat).
     *
     * Lo llama el navegador cada INTERVALO_PING_SEGUNDOS segundos mientras
     * el usuario tiene el sistema abierto (ver view/public/js/heartbeat.js).
     *
     *  - Si la sesión sigue viva, refresca su última actividad y responde 'ok'.
     *  - Si expiró (inactividad o inicio de sesión en otro lugar), destruye
     *    la sesión y responde 'expired' para que el navegador redirija al login.
     *
     * Responde siempre en JSON y nunca redirige con headers, porque es una
     * llamada AJAX en segundo plano.
     */
    public function pingSesion()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['usuario_id']) || !SessionManager::validarSesion()) {
            // Sesión muerta: destruir lo que quede y avisar al navegador
            SessionManager::cerrarSesionCompleta();
            echo json_encode(['status' => 'expired']);
            exit();
        }

        // Sesión viva: refrescar el latido
        SessionManager::refrescarConPing();
        echo json_encode(['status' => 'ok']);
        exit();
    }
}