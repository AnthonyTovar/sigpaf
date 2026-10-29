<?php
// Permisos y roles



require_once 'model/SessionManager.php';
require_once 'Logger.php';

class RolHelper
{

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

            Logger::accesoDenegado($_GET['action'] ?? 'desconocido');
            header("Location: index.php?action=dashboard&error=no_autorizado");
            exit();
        }
    }

    public static function verificarSesion()
    {
        self::iniciarSesion();


        if (!isset($_SESSION['usuario_id'])) {



            header("Location: index.php?action=login");
            exit();
        }

        // Validar sesión única
        if (!SessionManager::validarSesion()) {


            Logger::registrar('LOGOUT', 'Sesion', null,
                'Sesion invalidada: inicio en otro dispositivo, cierre del navegador o inactividad prolongada');

            SessionManager::cerrarSesionCompleta();
            header("Location: index.php?action=login&error=sesion_invalidada");
            exit();
        }


        SessionManager::actualizarActividad($_SESSION['usuario_id']);

    }


    public static function esSuperUsuario()
    {
        self::iniciarSesion();
        return isset($_SESSION['rol']) && $_SESSION['rol'] === self::ROL_SUPER_USUARIO;
    }


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