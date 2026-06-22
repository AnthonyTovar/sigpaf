<?php
// --- INICIO DE CONFIGURACIÓN ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'sigpaf_db');
define('DB_USER', 'root');
define('DB_PASS', '');

class Database
{
    private static $instance = null;

    public static function getConnection()
    {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                    DB_USER,
                    DB_PASS
                );
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Error crítico de conexión: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
// --- FIN DE CONFIGURACIÓN ---