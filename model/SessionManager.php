<?php
require_once 'ConexionModel.php';

class SessionManager
{
    // Días para la limpieza manual de registros antiguos de sesiones.
    // NOTA: esto NO cierra sesiones de usuarios, solo limpia registros viejos de la tabla.
    const DIAS_LIMPIEZA = 7;

    // Tiempo máximo (en segundos) sin recibir "latido" del navegador antes de
    // considerar que la sesión quedó abandonada (usuario cerró el navegador).
    // Debe ser MAYOR que INTERVALO_PING_SEGUNDOS con un margen amplio,
    // para soportar que el navegador retrase los timers en pestañas en segundo plano.
    const SEGUNDOS_INACTIVIDAD_MAX = 120; // 2 minutos

    // Cada cuánto el navegador envía el ping (latido) al servidor.
    // Este valor se expone al JavaScript (heartbeat.js) desde el Layout.
    const INTERVALO_PING_SEGUNDOS = 30; // 30 segundos

    /**
     * Verifica si el usuario ya tiene una sesión activa en otro dispositivo/navegador.
     *
     * Control de sesión única: si hay un registro VIVO (con latido reciente),
     * bloquea un nuevo login. Si el registro es un "fantasma" (el usuario cerró
     * el navegador sin usar el botón Salir y los pings dejaron de llegar),
     * lo elimina y permite el acceso.
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
            $sesion = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si NO hay sesión registrada, todo bien
            if (!$sesion) {
                return false;
            }

            // Sesión fantasma: el navegador fue cerrado sin "Salir".
            // Se libera para que el usuario pueda entrar de nuevo.
            if (!self::esActividadReciente($sesion['ultima_actividad'])) {
                self::eliminarSesion($usuarioId);
                return false;
            }

            // Hay sesión VIVA registrada: bloquea un nuevo login (sesión única)
            return $sesion;

        } catch (PDOException $e) {
            error_log("Error en SessionManager::tieneSesionActiva: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra una nueva sesión activa para el usuario.
     * Elimina cualquier sesión anterior del mismo usuario (viva o fantasma).
     */
    public static function registrarSesion($usuarioId)
    {
        try {
            $db = Database::getConnection();
            $sessionId = session_id();
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

            // Elimina sesión anterior del mismo usuario (si existe).
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
     * Valida que la sesión actual siga siendo la registrada en la base de datos.
     *
     * La sesión se invalida cuando:
     *  - El usuario inició sesión en otro dispositivo/navegador, o
     *  - El usuario cerró sesión, o
     *  - El navegador fue cerrado/abandonado y dejó de enviar latidos por más
     *    de SEGUNDOS_INACTIVIDAD_MAX segundos (cierre por inactividad).
     *
     * En el caso de inactividad, el registro "fantasma" se elimina de la BD
     * para liberar el acceso del usuario.
     */
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

            // No existe la sesión en BD (el usuario inició sesión en otro lugar o cerró sesión)
            if (!$sesion) {
                return false;
            }

            // La sesión existe pero el navegador dejó de enviar latidos:
            // se considera cerrada y se libera el registro.
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

    /**
     * Indica si una marca de última actividad está dentro del tiempo
     * de inactividad permitido (recibió latido recientemente).
     */
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
            // Ante la duda, se trata como reciente para no cerrar sesiones por error.
            return true;
        }
    }

    /**
     * Refresca la última actividad de la sesión SIEMPRE que la sesión siga viva.
     * Lo usa el ping periódico (heartbeat) del navegador para mantener la
     * sesión abierta mientras el usuario tenga el sistema abierto, aunque
     * no navegue entre páginas.
     *
     * Devuelve true si la sesión estaba viva y se refrescó, false si no.
     */
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
     * Actualiza la última actividad de la sesión.
     * Se llama en cada carga de página (ver RolHelper::verificarSesion);
     * junto con el ping del navegador, mantiene viva la sesión.
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
     * Limpia registros de sesiones con más de X días de antigüedad.
     * Es una limpieza de tabla (opcional, no cierra sesiones activas de usuarios).
     */
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