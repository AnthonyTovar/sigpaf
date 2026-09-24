<?php
require_once 'model/BitacoraModel.php';

class Logger
{
    /**
     * Registra un evento en la bitacora.
     * Lee los datos del usuario directamente de la sesion activa.
     */
    public static function registrar($accion, $modulo, $registroId, $descripcion)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $datos = [
            'usuario_id'     => $_SESSION['usuario_id'] ?? 'ANON',
            'usuario_nombre' => $_SESSION['username'] ?? 'Anonimo',
            'accion'         => $accion,
            'modulo'         => $modulo,
            'registro_id'    => $registroId,
            'descripcion'    => $descripcion,
            'ip'             => $_SERVER['REMOTE_ADDR'] ?? 'desconocida'
        ];

        try {
            $modelo = new BitacoraModel();
            $modelo->registrar($datos);
        } catch (Exception $e) {
            // La bitacora nunca debe tumbar el sistema
            error_log("Error en Logger: " . $e->getMessage());
        }
    }

    // ---- Eventos de sesion ----
    public static function login()
    {
        self::registrar('LOGIN', 'Sesion', null, 'Inicio sesion en el sistema');
    }

    public static function logout()
    {
        self::registrar('LOGOUT', 'Sesion', null, 'Cerro sesion en el sistema');
    }

    // ---- Eventos CRUD en maestros ----
    public static function crear($modulo, $id, $nombre)
    {
        self::registrar('CREATE', $modulo, $id, "Creo el registro $id ($nombre)");
    }

    public static function actualizar($modulo, $id, $nombre)
    {
        self::registrar('UPDATE', $modulo, $id, "Actualizo el registro $id ($nombre)");
    }

    public static function eliminar($modulo, $id, $nombre)
    {
        self::registrar('DELETE', $modulo, $id, "Elimino el registro $id ($nombre)");
    }

    // ---- Acceso no autorizado ----
    public static function accesoDenegado($modulo)
    {
        $rol = $_SESSION['rol'] ?? 'desconocido';
        self::registrar('ACCESS_DENIED', $modulo, null,
            "Intento acceder al modulo '$modulo' sin permisos suficientes (Rol: $rol)");
    }
}
