<?php
// Conexión a la base de datos



// Inicio de configuración
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

                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,



                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4; SET time_zone = '-04:00'"
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
// Fin de configuración