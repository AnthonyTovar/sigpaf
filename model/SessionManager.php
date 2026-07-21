<?php
require_once 'ConexionModel.php';

class SessionManager
{
    // Tiempo de inactividad para considerar sesión muerta (en minutos)
    const TIEMPO_INACTIVIDAD = 5;

    /**
     * Verifica si el usuario ya tiene una sesión activa en otro dispositivo/navegador
     * Si la sesión está "muerta" (inactiva por mucho tiempo), la elimina
     */
    public static function tieneSesionActiva($usuarioId)
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT session_id, ip_address, ultima_actividad,
                       TIMESTAMPDIFF(MINUTE, ultima_actividad, NOW()) as minutos_inactivo
                FROM sesiones_activas 
                WHERE usuario_id = ?
            ");
            $stmt->execute([$usuarioId]);
            $sesion = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si NO hay sesión registrada, todo bien
            if (!$sesion) {
                return false;
            }

            // Si la sesión está inactiva por mucho tiempo, considerarla muerta y eliminarla
            if ($sesion['minutos_inactivo'] > self::TIEMPO_INACTIVIDAD) {
                self::eliminarSesion($usuarioId);
                return false; // Sesión muerta, permite nuevo login
            }

            // Sesión realmente activa
            return $sesion;

        } catch (PDOException $e) {
            error_log("Error en SessionManager::tieneSesionActiva: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra una nueva sesión activa para el usuario
     * Elimina cualquier sesión anterior del mismo usuario
     */
    public static function registrarSesion($usuarioId)
    {
        try {
            $db = Database::getConnection();
            $sessionId = session_id();
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

            // Elimina sesión anterior del mismo usuario (si existe)
            $stmt = $db->prepare("DELETE FROM sesiones_activas WHERE usuario_id = ?");
            $stmt->execute([$usuarioId]);

            // Inserta nueva sesión
            $stmt = $db->prepare("
                INSERT INTO sesiones_activas (usuario_id, session_id, ip_address, user_agent, fecha_inicio)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$usuarioId, $sessionId, $ip, $userAgent]);

            // Guarda en sesión PHP para validaciones posteriores
            $_SESSION['session_id_registrada'] = $sessionId;

            return true;
        } catch (PDOException $e) {
            error_log("Error registrando sesión: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Valida que la sesión actual sea la válida registrada
     * Si otro usuario inició sesión, esta sesión queda invalidada
     */
    public static function validarSesion()
    {
        if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['session_id_registrada'])) {
            return false;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT session_id, 
                       TIMESTAMPDIFF(MINUTE, ultima_actividad, NOW()) as minutos_inactivo
                FROM sesiones_activas 
                WHERE usuario_id = ? AND session_id = ?
            ");
            $stmt->execute([$_SESSION['usuario_id'], $_SESSION['session_id_registrada']]);
            $sesion = $stmt->fetch(PDO::FETCH_ASSOC);

            // No existe la sesión en BD
            if (!$sesion) {
                return false;
            }

            // La sesión expiró por inactividad
            if ($sesion['minutos_inactivo'] > self::TIEMPO_INACTIVIDAD) {
                self::eliminarSesion($_SESSION['usuario_id']);
                return false;
            }

            return true;

        } catch (PDOException $e) {
            error_log("Error validando sesión: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina la sesión activa del usuario de la base de datos
     */
    public static function eliminarSesion($usuarioId)
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM sesiones_activas WHERE usuario_id = ?");
            $stmt->execute([$usuarioId]);
            return true;
        } catch (PDOException $e) {
            error_log("Error eliminando sesión: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cierra sesión completamente: limpia BD, destruye sesión PHP y cookies
     */
    public static function cerrarSesionCompleta()
    {
        if (isset($_SESSION['usuario_id'])) {
            self::eliminarSesion($_SESSION['usuario_id']);
        }
        
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
    }

    /**
     * Actualiza la última actividad de la sesión
     */
    public static function actualizarActividad($usuarioId)
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                UPDATE sesiones_activas 
                SET ultima_actividad = NOW() 
                WHERE usuario_id = ?
            ");
            $stmt->execute([$usuarioId]);
        } catch (PDOException $e) {
            error_log("Error actualizando actividad: " . $e->getMessage());
        }
    }

    /**
     * Limpia TODAS las sesiones inactivas (ejecutar con cron cada cierto tiempo)
     */
    public static function limpiarSesionesInactivas()
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                DELETE FROM sesiones_activas 
                WHERE ultima_actividad < DATE_SUB(NOW(), INTERVAL ? MINUTE)
            ");
            $stmt->execute([self::TIEMPO_INACTIVIDAD]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error limpiando sesiones: " . $e->getMessage());
            return 0;
        }
    }
}