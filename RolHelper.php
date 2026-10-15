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
            // Lógica: no es un "error" llegar al sistema sin sesión (primera
            // visita, enlace guardado, pestaña restaurada): se lleva al login
            // limpio, sin el aviso amarillo de "acceso no permitido"
            header("Location: index.php?action=login");
            exit();
        }

        // ========== VALIDAR SESIÓN ÚNICA ==========
        if (!SessionManager::validarSesion()) {
            // Sesión invalidada: inicio en otro dispositivo/navegador, cierre
            // del navegador sin usar "Salir", o inactividad prolongada (sin pings)
            Logger::registrar('LOGOUT', 'Sesion', null,
                'Sesion invalidada: inicio en otro dispositivo, cierre del navegador o inactividad prolongada');

            SessionManager::cerrarSesionCompleta();
            header("Location: index.php?action=login&error=sesion_invalidada");
            exit();
        }

        // Actualiza última actividad (informativo)
        SessionManager::actualizarActividad($_SESSION['usuario_id']);
        // ==========================================================
    }

    /**
     * Devuelve true si el usuario autenticado es UNICAMENTE Super Usuario.
     * Sirve para ocultar opciones del menú sin redirigir.
     */
    public static function esSuperUsuario()
    {
        self::iniciarSesion();
        return isset($_SESSION['rol']) && $_SESSION['rol'] === self::ROL_SUPER_USUARIO;
    }

    /**
     * Verifica que el usuario sea UNICAMENTE Super Usuario.
     * Cualquier otro rol queda registrado en la bitacora como acceso denegado.
     */
    public static function verificarSuperUsuario()
    {
        self::verificarSesion();

        if (!self::esSuperUsuario()) {
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