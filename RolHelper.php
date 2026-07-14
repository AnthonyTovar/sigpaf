<?php
class RolHelper
{
    // IDs de roles con acceso a configuración
    const ROL_SUPER_USUARIO = 'Rol0001';
    const ROL_ADMINISTRADOR = 'Rol0002';

    public static function esAdministrador()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['rol']) && 
               ($_SESSION['rol'] === self::ROL_ADMINISTRADOR || $_SESSION['rol'] === self::ROL_SUPER_USUARIO);
    }

    public static function verificarAdministrador()
    {
        if (!self::esAdministrador()) {
            header("Location: index.php?action=dashboard&error=no_autorizado");
            exit();
        }
    }

    public static function verificarSesion()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }
    }
}