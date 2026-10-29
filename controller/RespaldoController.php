<?php
// Controlador de respaldos



require_once 'model/ConexionModel.php';
require_once 'model/SessionManager.php';
require_once 'RolHelper.php';
require_once 'Logger.php';


// Respaldo y restauracion de la base de datos




class RespaldoController
{

    const TAMANO_MAXIMO = 32 * 1024 * 1024;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }




    public function exportar()
    {
        RolHelper::verificarSuperUsuario();

        $db = Database::getConnection();
        $dump = $this->generarDump($db);

        $nombreArchivo = 'sigpaf_respaldo_' . date('Y-m-d_H-i') . '.sql';
        $dirRespaldo = __DIR__ . '/../respaldo';


        if (!is_dir($dirRespaldo) && !@mkdir($dirRespaldo, 0755, true)) {
            Logger::registrar('EXPORTAR_BD', 'Configuracion', null,
                'Fallo al respaldar: no se pudo crear la carpeta respaldo/');
            header('Location: index.php?action=configuracion&bd=error&detalle=' . urlencode(
                'No se pudo crear la carpeta respaldo/. Revise los permisos de escritura de la carpeta del sistema.'));
            exit();
        }


        if (@file_put_contents($dirRespaldo . '/' . $nombreArchivo, $dump) === false) {
            Logger::registrar('EXPORTAR_BD', 'Configuracion', null,
                'Fallo al respaldar: no se pudo escribir en respaldo/');
            header('Location: index.php?action=configuracion&bd=error&detalle=' . urlencode(
                'No se pudo guardar el respaldo. Revise los permisos de escritura de la carpeta respaldo/.'));
            exit();
        }
        @chmod($dirRespaldo . '/' . $nombreArchivo, 0644);

        Logger::registrar('EXPORTAR_BD', 'Configuracion', null,
            'Genero un respaldo de la base de datos (' . DB_NAME . '): ' . $nombreArchivo);

        header('Location: index.php?action=configuracion&bd=ok&detalle=' . urlencode(
            'Respaldo guardado correctamente en la carpeta respaldo/ del sistema: ' . $nombreArchivo));
        exit();
    }




    public function importar()
    {
        RolHelper::verificarSuperUsuario();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=configuracion');
            exit();
        }

        $volver = 'index.php?action=configuracion';


        if (($_POST['confirmarImporte'] ?? '') !== '1') {
            header('Location: ' . $volver . '&bd=error&detalle=' .
                urlencode('Debe confirmar que entiende que se reemplazarán todos los datos.'));
            exit();
        }


        if (!isset($_FILES['archivoSql']) || !is_uploaded_file($_FILES['archivoSql']['tmp_name'])) {
            $detalle = 'No se recibió ningún archivo.';
            if (isset($_FILES['archivoSql']) && $_FILES['archivoSql']['error'] === UPLOAD_ERR_INI_SIZE) {
                $detalle = 'El archivo excede el tamaño máximo permitido por el servidor.';
            } elseif (isset($_FILES['archivoSql']) && $_FILES['archivoSql']['error'] !== UPLOAD_ERR_OK) {
                $detalle = 'Error al subir el archivo (código ' . (int) $_FILES['archivoSql']['error'] . ').';
            }
            header('Location: ' . $volver . '&bd=error&detalle=' . urlencode($detalle));
            exit();
        }

        $archivo = $_FILES['archivoSql'];
        if ($archivo['size'] <= 0 || $archivo['size'] > self::TAMANO_MAXIMO) {
            header('Location: ' . $volver . '&bd=error&detalle=' .
                urlencode('El archivo debe ser un .sql de máximo 32 MB.'));
            exit();
        }
        if (strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION)) !== 'sql') {
            header('Location: ' . $volver . '&bd=error&detalle=' .
                urlencode('Solo se permiten archivos .sql'));
            exit();
        }

        $contenido = file_get_contents($archivo['tmp_name']);
        if ($contenido === false || trim($contenido) === '') {
            header('Location: ' . $volver . '&bd=error&detalle=' .
                urlencode('El archivo está vacío o no se pudo leer.'));
            exit();
        }
        if (stripos($contenido, 'CREATE TABLE') === false) {
            header('Location: ' . $volver . '&bd=error&detalle=' .
                urlencode('El archivo no parece un respaldo válido (no contiene CREATE TABLE).'));
            exit();
        }


        $db = Database::getConnection();
        $dirRespaldo = __DIR__ . '/../respaldo';
        if (!is_dir($dirRespaldo) && !@mkdir($dirRespaldo, 0755, true)) {
            header('Location: ' . $volver . '&bd=error&detalle=' . urlencode(
                'No se pudo crear la carpeta respaldo/ (permisos de escritura). '
                . 'La importación se canceló: sin respaldo previo no se modifican los datos.'));
            exit();
        }
        $rutaRespaldo = $dirRespaldo . '/auto_antes_importar_' . date('Ymd_His') . '.sql';
        if (@file_put_contents($rutaRespaldo, $this->generarDump($db)) !== false) {
            @chmod($rutaRespaldo, 0644);
        }


        $sentencias = $this->dividirSentencias($contenido);

        $db->exec('SET FOREIGN_KEY_CHECKS=0');
        $ejecutadas = 0;
        $error = null;
        foreach ($sentencias as $sentencia) {
            try {
                $db->exec($sentencia);
                $ejecutadas++;
            } catch (PDOException $e) {
                $error = 'Falló la sentencia #' . ($ejecutadas + 1) . ': '
                    . mb_substr(preg_replace('/\s+/', ' ', $sentencia), 0, 120) . '... — '
                    . $e->getMessage();
                break;
            }
        }
        $db->exec('SET FOREIGN_KEY_CHECKS=1');

        if ($error !== null) {
            Logger::registrar('IMPORTAR_BD', 'Configuracion', null,
                'Restauracion fallida: ' . mb_substr($error, 0, 200));
            header('Location: ' . $volver . '&bd=error&detalle=' .
                urlencode($error . ' | Antes de intentarlo se guardó un respaldo automático en la carpeta respaldo/.'));
            exit();
        }

        Logger::registrar('IMPORTAR_BD', 'Configuracion', null,
            'Restauro la base de datos desde un archivo .sql (' . $ejecutadas . ' sentencias ejecutadas)');



        $usuarioId = $_SESSION['usuario_id'] ?? null;
        $usuarioSigue = false;
        if ($usuarioId) {
            try {
                $stmt = $db->prepare('SELECT COUNT(*) FROM usuarios WHERE idUsuario = ?');
                $stmt->execute([$usuarioId]);
                $usuarioSigue = ((int) $stmt->fetchColumn()) > 0;
            } catch (PDOException $e) {
                $usuarioSigue = false;
            }
        }

        if ($usuarioSigue) {
            SessionManager::registrarSesion($usuarioId);
            header('Location: ' . $volver . '&bd=ok&detalle=' . urlencode(
                'Base de datos restaurada correctamente (' . $ejecutadas . ' sentencias). '
                . 'Respaldo previo guardado en respaldo/. Tu sesión se mantuvo abierta.'));
            exit();
        }


        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        header('Location: index.php?action=login&error=bd_restaurada');
        exit();
    }




    private function generarDump(PDO $db)
    {
        $salida = "SET NAMES utf8mb4;\n\n"
            . "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;\n"
            . "USE `" . DB_NAME . "`;\n\n"
            . "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tablas = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tablas as $tabla) {

            $create = $db->query("SHOW CREATE TABLE `$tabla`")->fetch(PDO::FETCH_NUM);
            $salida .= "DROP TABLE IF EXISTS `$tabla`;\n"
                . $create[1] . ";\n\n";


            $stmt = $db->query("SELECT * FROM `$tabla`");
            $columnas = null;
            $lote = [];

            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($columnas === null) {
                    $columnas = array_keys($fila);
                }
                $valores = [];
                foreach ($fila as $valor) {
                    $valores[] = $valor === null ? 'NULL' : $db->quote($valor);
                }
                $lote[] = '(' . implode(',', $valores) . ')';

                if (count($lote) >= 100) {
                    $salida .= $this->insertLote($tabla, $columnas, $lote);
                    $lote = [];
                }
            }

            if ($lote) {
                $salida .= $this->insertLote($tabla, $columnas, $lote);
            }
            $salida .= "\n";
        }

        $salida .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $salida;
    }

    private function insertLote($tabla, $columnas, $lote)
    {
        $colsSql = '`' . implode('`, `', $columnas) . '`';
        return "INSERT INTO `$tabla` ($colsSql) VALUES\n" . implode(",\n", $lote) . ";\n\n";
    }





    private function dividirSentencias($sql)
    {
        $sentencias = [];
        $actual = '';
        $longitud = strlen($sql);
        $enComilla = false;
        $comilla = '';
        $enComentarioLinea = false;
        $enComentarioBloque = false;

        for ($i = 0; $i < $longitud; $i++) {
            $c = $sql[$i];
            $siguiente = $i + 1 < $longitud ? $sql[$i + 1] : '';

            if ($enComentarioLinea) {
                if ($c === "\n") {
                    $enComentarioLinea = false;
                }
                continue;
            }

            if ($enComentarioBloque) {
                if ($c === '*' && $siguiente === '/') {
                    $enComentarioBloque = false;
                    $i++;
                }
                continue;
            }

            if ($enComilla) {
                $actual .= $c;
                if ($c === '\\' && $siguiente !== '') {
                    $actual .= $siguiente;
                    $i++;
                } elseif ($c === $comilla) {
                    $enComilla = false;
                }
                continue;
            }

            if ($c === "'" || $c === '"') {
                $enComilla = true;
                $comilla = $c;
                $actual .= $c;
                continue;
            }
            if ($c === '-' && $siguiente === '-') {
                $enComentarioLinea = true;
                $i++;
                continue;
            }
            if ($c === '#') {
                $enComentarioLinea = true;
                continue;
            }
            if ($c === '/' && $siguiente === '*') {
                $enComentarioBloque = true;
                $i++;
                continue;
            }
            if ($c === ';') {
                $limpio = trim($actual);
                if ($limpio !== '') {
                    $sentencias[] = $limpio;
                }
                $actual = '';
                continue;
            }
            $actual .= $c;
        }

        $limpio = trim($actual);
        if ($limpio !== '') {
            $sentencias[] = $limpio;
        }

        return $sentencias;
    }
}
