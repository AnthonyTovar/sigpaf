<?php
require_once 'model/SessionManager.php';

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
            header("Location: index.php?action=dashboard&error=no_autorizado");
            exit();
        }
    }

    public static function verificarSesion()
    {
        self::iniciarSesion();

        // Verifica que exista sesión
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        // ========== VALIDAR SESIÓN ÚNICA ==========
        if (!SessionManager::validarSesion()) {
            // La sesión fue invalidada (otro login desde otro lugar)
            SessionManager::cerrarSesionCompleta();
            header("Location: index.php?action=login&error=sesion_invalidada");
            exit();
        }

        // Actualiza última actividad
        SessionManager::actualizarActividad($_SESSION['usuario_id']);
        // ===========================================
    }

    private static function iniciarSesion()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
}