<?php
require_once 'ConexionModel.php';

class SessionManager
{
    /**
     * Verifica si el usuario ya tiene una sesión activa en otro dispositivo/navegador
     * Retorna array con datos de la sesión existente, o false si no tiene
     */
    public static function tieneSesionActiva($usuarioId)
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT session_id, ip_address, ultima_actividad 
                FROM sesiones_activas 
                WHERE usuario_id = ?
            ");
            $stmt->execute([$usuarioId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
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
                SELECT session_id 
                FROM sesiones_activas 
                WHERE usuario_id = ? AND session_id = ?
            ");
            $stmt->execute([$_SESSION['usuario_id'], $_SESSION['session_id_registrada']]);
            
            return $stmt->fetch() !== false;
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
     * Limpia sesiones inactivas (ejecutar con cron cada cierto tiempo)
     * @param int $minutos Tiempo de inactividad para considerar sesión muerta
     */
    public static function limpiarSesionesInactivas($minutos = 30)
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                DELETE FROM sesiones_activas 
                WHERE ultima_actividad < DATE_SUB(NOW(), INTERVAL ? MINUTE)
            ");
            $stmt->execute([$minutos]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error limpiando sesiones: " . $e->getMessage());
            return 0;
        }
    }
}