<?php
require_once 'model/ConexionModel.php';
require_once 'RolHelper.php';
require_once 'Logger.php';

/**
 * RESPALDO / RESTAURACIÓN DE LA BASE DE DATOS
 * Solo super usuario (además de la validación de la ruta en index.php).
 *
 * - exportar(): genera un dump SQL completo (estructura + datos) y lo
 *   descarga como archivo .sql.
 * - importar(): recibe un archivo .sql y lo ejecuta contra la base de
 *   datos, reemplazando TODO el contenido actual. Antes de ejecutar
 *   guarda automáticamente un respaldo del estado actual en la carpeta
 *   respaldos/ del proyecto, por si se necesita revertir.
 */
class RespaldoController
{
    // Tamaño máximo de archivo .sql a importar (32 MB)
    const TAMANO_MAXIMO = 32 * 1024 * 1024;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    // ============================================================
    // EXPORTAR: descarga un .sql con la base completa
    // ============================================================
    public function exportar()
    {
        RolHelper::verificarSuperUsuario();

        $db = Database::getConnection();
        $dump = $this->generarDump($db);

        Logger::registrar('EXPORTAR_BD', 'Configuracion', null,
            'Descargo un respaldo de la base de datos (' . DB_NAME . ')');

        $nombreArchivo = 'sigpaf_respaldo_' . date('Y-m-d_H-i') . '.sql';
        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Content-Length: ' . strlen($dump));
        echo $dump;
        exit;
    }

    // ============================================================
    // IMPORTAR: ejecuta un .sql subido, reemplazando los datos
    // ============================================================
    public function importar()
    {
        RolHelper::verificarSuperUsuario();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=configuracion');
            exit();
        }

        $volver = 'index.php?action=configuracion';

        // Debe venir marcada la casilla de confirmación (doble seguridad,
        // además del checkbox que habilita el botón en la vista)
        if (($_POST['confirmarImporte'] ?? '') !== '1') {
            header('Location: ' . $volver . '&bd=error&detalle=' .
                urlencode('Debe confirmar que entiende que se reemplazarán todos los datos.'));
            exit();
        }

        // ===== Validar el archivo subido =====
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

        // ===== Respaldo automático del estado actual (por si acaso) =====
        $db = Database::getConnection();
        $dirRespaldos = __DIR__ . '/../respaldos';
        if (!is_dir($dirRespaldos)) {
            mkdir($dirRespaldos, 0755, true);
        }
        $rutaRespaldo = $dirRespaldos . '/auto_antes_importar_' . date('Ymd_His') . '.sql';
        @file_put_contents($rutaRespaldo, $this->generarDump($db));

        // ===== Ejecutar el archivo =====
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
            // Nota: el DDL de MySQL confirma automáticamente, por eso no se
            // puede deshacer; queda el respaldo automático en respaldos/
            Logger::registrar('IMPORTAR_BD', 'Configuracion', null,
                'Intento de restauracion FALLIDO: ' . mb_substr($error, 0, 200));
            header('Location: ' . $volver . '&bd=error&detalle=' .
                urlencode($error . ' | Antes de intentarlo se guardó un respaldo automático en la carpeta respaldos/.'));
            exit();
        }

        Logger::registrar('IMPORTAR_BD', 'Configuracion', null,
            'Restauro la base de datos desde un archivo .sql (' . $ejecutadas . ' sentencias ejecutadas)');

        $detalle = 'Se ejecutaron ' . $ejecutadas . ' sentencias correctamente. Respaldo previo guardado en respaldos/. '
            . 'Si los usuarios cambiaron, inicia sesión de nuevo.';
        header('Location: ' . $volver . '&bd=ok&detalle=' . urlencode($detalle));
        exit();
    }

    // ============================================================
    // GENERAR DUMP: estructura + datos de todas las tablas
    // ============================================================
    private function generarDump(PDO $db)
    {
        // El archivo se descarga SIN comentarios: solo sentencias SQL puras,
        // listas para importar en cualquier phpMyAdmin
        $salida = "SET NAMES utf8mb4;\n\n"
            // Base de datos: se crea si no existe (así el archivo funciona tal
            // cual en otra PC, sin crear la base de datos a mano antes)
            . "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;\n"
            . "USE `" . DB_NAME . "`;\n\n"
            . "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tablas = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tablas as $tabla) {
            // Estructura
            $create = $db->query("SHOW CREATE TABLE `$tabla`")->fetch(PDO::FETCH_NUM);
            $salida .= "DROP TABLE IF EXISTS `$tabla`;\n"
                . $create[1] . ";\n\n";

            // Datos (por lotes de 100 INSERT)
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

    // ============================================================
    // DIVIDIR SENTENCIAS: separa un .sql en sentencias individuales
    // respetando comillas (' "), comentarios (-- , #, /* */) y escapes
    // ============================================================
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

            // Dentro de un comentario de línea: se ignora hasta el salto de línea
            if ($enComentarioLinea) {
                if ($c === "\n") {
                    $enComentarioLinea = false;
                }
                continue;
            }

            // Dentro de un comentario de bloque: se ignora hasta */
            if ($enComentarioBloque) {
                if ($c === '*' && $siguiente === '/') {
                    $enComentarioBloque = false;
                    $i++;
                }
                continue;
            }

            // Dentro de una cadena: copiar tal cual (con escapes)
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
