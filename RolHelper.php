<?php
require_once 'model/SessionManager.php';
require_once 'Logger.php';

class RolHelper
{
    // IDs de roles con acceso a configuración
    const ROL_SUPER_USUARIO = 'Rol0001';
    const ROL_ADMINISTRADOR = 'Rol0002';

    public static function esAdministrador()
    {
        self::iniciarSesion();
        return isset($_SESSION['rol']) && 
               ($_SESSION['rol'] === self::ROL_ADMINISTRADOR || 
                $_SESSION['rol'] === self::ROL_SUPER_USUARIO);
    }

    public static function verificarAdministrador()
    {
        self::verificarSesion();
        if (!self::esAdministrador()) {
            // Registra en la bitacora el intento de acceso no autorizado
            Logger::accesoDenegado($_GET['action'] ?? 'desconocido');
            header("Location: index.php?action=dashboard&error=no_autorizado");
            exit();
        }
    }

    public static function verificarSesion()
    {
        self::iniciarSesion();

        // Verifica que exista sesión
        if (!isset($_SESSION['usuario_id'])) {
            // Bitacora: usuario desconocido intento entrar sin credenciales
            Logger::registrar('ACCESS_DENIED', $_GET['action'] ?? 'desconocido', null,
                'Usuario desconocido intento acceder al sistema sin credenciales');

            header("Location: index.php?action=login&error=sin_sesion");
            exit();
        }

        // ========== VALIDAR SESIÓN ÚNICA (con expiración) ==========
        if (!SessionManager::validarSesion()) {
            // La sesión fue invalidada (otro login desde otro lugar) o expiró
            // Bitacora: sesion cerrada por exceder el tiempo limite de inactividad
            Logger::registrar('LOGOUT', 'Sesion', null,
                'Sesion cerrada automaticamente por exceder el tiempo limite de inactividad');

            SessionManager::cerrarSesionCompleta();
            header("Location: index.php?action=login&error=sesion_invalidada");
            exit();
        }

        // Actualiza última actividad
        SessionManager::actualizarActividad($_SESSION['usuario_id']);
        // ==========================================================
    }

    /**
     * Verifica que el usuario sea UNICAMENTE Super Usuario.
     * Cualquier otro rol queda registrado en la bitacora como acceso denegado.
     */
    public static function verificarSuperUsuario()
    {
        self::verificarSesion();

        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== self::ROL_SUPER_USUARIO) {
            Logger::accesoDenegado($_GET['action'] ?? 'desconocido');
            header("Location: index.php?action=dashboard&error=no_autorizado");
            exit();
        }
    }

    private static function iniciarSesion()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
}