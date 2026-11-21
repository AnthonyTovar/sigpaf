<?php
// Control de sesiones



require_once 'ConexionModel.php';

class SessionManager
{


    public static function tieneSesionActiva($usuarioId)
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT session_id, ip_address, user_agent, fecha_inicio
                FROM sesiones_activas
                WHERE usuario_id = ?
            ");
            $stmt->execute([$usuarioId]);
            $sesion = $stmt->fetch(PDO::FETCH_ASSOC);

            return $sesion ?: false;

        } catch (PDOException $e) {
            error_log("Error en SessionManager::tieneSesionActiva: " . $e->getMessage());
            return false;
        }
    }


    public static function registrarSesion($usuarioId)
    {
        try {
            $db = Database::getConnection();
            $sessionId = session_id();
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';


            $stmt = $db->prepare("DELETE FROM sesiones_activas WHERE usuario_id = ?");
            $stmt->execute([$usuarioId]);


            $stmt = $db->prepare("
                INSERT INTO sesiones_activas (usuario_id, session_id, ip_address, user_agent, fecha_inicio)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$usuarioId, $sessionId, $ip, $userAgent]);


            $_SESSION['session_id_registrada'] = $sessionId;

            return true;
        } catch (PDOException $e) {
            error_log("Error registrando sesión: " . $e->getMessage());
            return false;
        }
    }


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
            $sesion = $stmt->fetch(PDO::FETCH_ASSOC);

            return (bool) $sesion;

        } catch (PDOException $e) {
            error_log("Error validando sesión: " . $e->getMessage());
            return false;
        }
    }


    public static function eliminarSesion($usuarioId, $sessionId = null)
    {
        try {
            $db = Database::getConnection();
            $sql = "DELETE FROM sesiones_activas WHERE usuario_id = ?";
            $parametros = [$usuarioId];

            if ($sessionId !== null) {
                $sql .= " AND session_id = ?";
                $parametros[] = $sessionId;
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($parametros);
            return true;
        } catch (PDOException $e) {
            error_log("Error eliminando sesión: " . $e->getMessage());
            return false;
        }
    }


    public static function cerrarSesionCompleta()
    {
        if (isset($_SESSION['usuario_id'], $_SESSION['session_id_registrada'])) {
            self::eliminarSesion($_SESSION['usuario_id'], $_SESSION['session_id_registrada']);
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
}
