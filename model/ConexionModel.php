<?php
// --- INICIO DE CONFIGURACIÓN ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'sigpaf_DB');
define('DB_USER', 'root');
define('DB_PASS', '');

class Database
{
    private static $instance = null;

    public static function getConnection()
    {
        if (self::$instance === null) {
            try {
                // charset=utf8mb4" caracteres especiales
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    // Sesión de MySQL comunique estrictamente en UTF-8
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ];

                self::$instance = new PDO(
                    $dsn,
                    DB_USER,
                    DB_PASS,
                    $options
                );
            } catch (PDOException $e) {
                die("Error crítico de conexión: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
// --- FIN DE CONFIGURACIÓN ---