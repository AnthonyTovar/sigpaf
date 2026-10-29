<?php
// Control de sesiones



require_once 'ConexionModel.php';

class SessionManager
{


    const DIAS_LIMPIEZA = 7;





    const SEGUNDOS_INACTIVIDAD_MAX = 120;



    const INTERVALO_PING_SEGUNDOS = 30;


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
            $sesion = $stmt->fetch(PDO::FETCH_ASSOC);


            if (!$sesion) {
                return false;
            }



            if (!self::esActividadReciente($sesion['ultima_actividad'])) {
                self::eliminarSesion($usuarioId);
                return false;
            }


            return $sesion;

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
                SELECT session_id, ultima_actividad
                FROM sesiones_activas
                WHERE usuario_id = ? AND session_id = ?
            ");
            $stmt->execute([$_SESSION['usuario_id'], $_SESSION['session_id_registrada']]);
            $sesion = $stmt->fetch(PDO::FETCH_ASSOC);


            if (!$sesion) {
                return false;
            }



            if (!self::esActividadReciente($sesion['ultima_actividad'])) {
                self::eliminarSesion($_SESSION['usuario_id']);
                return false;
            }

            return true;

        } catch (PDOException $e) {
            error_log("Error validando sesión: " . $e->getMessage());
            return false;
        }
    }


    private static function esActividadReciente($ultimaActividad)
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT TIMESTAMPDIFF(SECOND, ?, NOW()) AS segundos_inactivo
            ");
            $stmt->execute([$ultimaActividad]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                return false;
            }

            return ((int) $fila['segundos_inactivo']) <= self::SEGUNDOS_INACTIVIDAD_MAX;
        } catch (PDOException $e) {
            error_log("Error en SessionManager::esActividadReciente: " . $e->getMessage());

            return true;
        }
    }


    public static function refrescarConPing()
    {
        if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['session_id_registrada'])) {
            return false;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                UPDATE sesiones_activas
                SET ultima_actividad = NOW()
                WHERE usuario_id = ? AND session_id = ?
            ");
            $stmt->execute([$_SESSION['usuario_id'], $_SESSION['session_id_registrada']]);

            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("Error en SessionManager::refrescarConPing: " . $e->getMessage());
            return false;
        }
    }


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


    public static function limpiarSesionesInactivas()
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                DELETE FROM sesiones_activas 
                WHERE ultima_actividad < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            $stmt->execute([self::DIAS_LIMPIEZA]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error limpiando sesiones: " . $e->getMessage());
            return 0;
        }
    }
}