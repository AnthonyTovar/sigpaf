<?php
require_once 'model/ConexionModel.php';
require_once 'RolHelper.php';
require_once 'Logger.php';

/**
 * RESPALDO / RESTAURACIÓN DE LA BASE DE DATOS
 * Solo super usuario (además de la validación de la ruta en index.php).
 *
 * - exportar(): genera un dump SQL completo (estructura + datos), lo
 *   guarda en la carpeta respaldo/ del sistema y además lo descarga
 *   como archivo .sql.
 * - importar(): recibe un archivo .sql y lo ejecuta contra la base de
 *   datos, reemplazando TODO el contenido actual. Antes de ejecutar
 *   guarda automáticamente un respaldo del estado actual en la carpeta
 *   respaldo/ del proyecto, por si se necesita revertir.
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

        // Guardar una copia del respaldo en la carpeta respaldo/ del sistema.
        // La ruta usa __DIR__ con '/', que funciona igual en Windows y Linux.
        $nombreArchivo = 'sigpaf_respaldo_' . date('Y-m-d_H-i') . '.sql';
        $dirRespaldo = __DIR__ . '/../respaldo';
        if (!is_dir($dirRespaldo) && !@mkdir($dirRespaldo, 0755, true)) {
            // Sin permiso de escritura (típico en Linux si la carpeta del
            // sistema pertenece a root): se avisa en la bitácora y se sigue
            // con la descarga normal.
            Logger::registrar('EXPORTAR_BD', 'Configuracion', null,
                'ADVERTENCIA: no se pudo crear la carpeta respaldo/ (revisar permisos de escritura). '
                . 'El respaldo solo se descargo, sin copia local.');
            $guardadoLocal = false;
        } else {
            $guardadoLocal = @file_put_contents($dirRespaldo . '/' . $nombreArchivo, $dump);
            if ($guardadoLocal !== false) {
                @chmod($dirRespaldo . '/' . $nombreArchivo, 0644);
            } else {
                Logger::registrar('EXPORTAR_BD', 'Configuracion', null,
                    'ADVERTENCIA: no se pudo guardar la copia en respaldo/ (revisar permisos de escritura). '
                    . 'El respaldo solo se descargo, sin copia local.');
            }
        }

        Logger::registrar('EXPORTAR_BD', 'Configuracion', null,
            'Descargo un respaldo de la base de datos (' . DB_NAME . ')'
            . ($guardadoLocal !== false ? ' y se guardo una copia en respaldo/' : ''));

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
        $dirRespaldos = __DIR__ . '/../respaldo';
        if (!is_dir($dirRespaldos) && !@mkdir($dirRespaldos, 0755, true)) {
            // Sin permisos de escritura: se aborta la importación para no
            // arriesgar los datos sin haber podido guardar el respaldo previo.
            header('Location: ' . $volver . '&bd=error&detalle=' . urlencode(
                'No se pudo crear la carpeta respaldo/ (permisos de escritura). '
                . 'Por seguridad la importación se canceló: sin respaldo previo no se tocan los datos.'));
            exit();
        }
        $rutaRespaldo = $dirRespaldos . '/auto_antes_importar_' . date('Ymd_His') . '.sql';
        if (@file_put_contents($rutaRespaldo, $this->generarDump($db)) !== false) {
            @chmod($rutaRespaldo, 0644);
        }

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
            // puede deshacer; queda el respaldo automático en respaldo/
            Logger::registrar('IMPORTAR_BD', 'Configuracion', null,
                'Intento de restauracion FALLIDO: ' . mb_substr($error, 0, 200));
            header('Location: ' . $volver . '&bd=error&detalle=' .
                urlencode($error . ' | Antes de intentarlo se guardó un respaldo automático en la carpeta respaldo/.'));
            exit();
        }

        Logger::registrar('IMPORTAR_BD', 'Configuracion', null,
            'Restauro la base de datos desde un archivo .sql (' . $ejecutadas . ' sentencias ejecutadas)');

        // ===== Mantener la sesión viva tras la importación =====
        // La importación reemplazó TODAS las tablas, incluida
        // sesiones_activas: sin re-registrar la sesión aquí, la próxima
        // validación la consideraría cerrada y expulsaría al usuario al
        // login (era el "me saca del sistema al restaurar").
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
            require_once 'model/SessionManager.php';
            SessionManager::registrarSesion($usuarioId);
            $detalle = 'Se ejecutaron ' . $ejecutadas . ' sentencias correctamente. Respaldo previo guardado en respaldo/. '
                . 'Tu sesión se mantuvo abierta.';
            header('Location: ' . $volver . '&bd=ok&detalle=' . urlencode($detalle));
            exit();
        }

        // El usuario actual no existe en la base importada: se cierra la
        // sesión limpio y se avisa para que entre con las credenciales
        // de la base restaurada.
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
