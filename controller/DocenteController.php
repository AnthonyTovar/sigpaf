<?php
require_once 'model/DocenteModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class DocenteController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new DocenteModel();
    }

    private function renderizar($nombreVista, $datos = [])
    {
        extract($datos);
        ob_start();
        require $nombreVista . '.php';
        $content = ob_get_clean();

        require 'view/Layout.php';
    }

    public function listar()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $docentes = $this->model->listarDocentes();
        $this->renderizar('view/DocenteView', [
            'docentes' => $docentes
        ]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $prefijoNacionalidad = $_POST['nacionalidad'] ?? 'V';
            $cedulaCompleta = $_POST['cedDocente'] ?? '';
            $nombres = $_POST['nombreDocente'] ?? '';
            $apellidos = $_POST['apellidoDocente'] ?? '';
            $telefono = $_POST['telfDocente'] ?? '';

            // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
            if (!(ValidationHelper::cedula($cedulaCompleta))) {
                ValidationHelper::error('La cédula debe tener entre 6 y 9 dígitos.');
            }
            ValidationHelper::exigirRequerido($nombres, 'Los nombres del docente son obligatorios.');
            ValidationHelper::exigirRequerido($apellidos, 'Los apellidos del docente son obligatorios.');
            if (!(ValidationHelper::telefono($telefono))) {
                ValidationHelper::error('El teléfono debe tener el formato 04XX-XXXXXXX.');
            }
            if (empty($cedulaCompleta) || empty($nombres) || empty($apellidos)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Los campos obligatorios son: Cédula, Nombres y Apellidos."
                ]);
                exit();
            }

            $prefijo = substr($cedulaCompleta, 0, 1);
            $numeroCedula = substr($cedulaCompleta, 1);

            if (!in_array(strtoupper($prefijo), ['V', 'E'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Formato de cédula inválido. Debe comenzar con V o E."
                ]);
                exit();
            }

            $prefijoFinal = strtoupper($prefijoNacionalidad);
            if (!in_array($prefijoFinal, ['V', 'E'])) {
                $prefijoFinal = strtoupper($prefijo);
            }

            $nuevoId = $this->model->registrarDocente(
                $prefijoFinal,
                $cedulaCompleta,
                $nombres,
                $apellidos,
                $telefono
            );

            if ($nuevoId === 'cedula_duplicada') {
                echo json_encode([
                    "status" => "error",
                    "message" => "Cédula ya existente. No se puede registrar un docente con la misma cédula."
                ]);
            } else if ($nuevoId) {
     // Bitacora: registro creado
     Logger::crear('Docente', $nuevoId, $nombres . ' ' . $apellidos);
                echo json_encode([
                    "status" => "success",
                    "message" => "¡Docente registrado con éxito!",
                    "id" => $nuevoId,
                    "nacionalidad" => $prefijoFinal,
                    "cedula" => $cedulaCompleta,
                    "nombres" => $nombres,
                    "apellidos" => $apellidos,
                    "telefono" => $telefono
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Hubo un error en la base de datos al registrar el docente."
                ]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idDocente'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del docente"]);
            exit;
        }

        // Consulta el nombre antes de eliminar para la bitacora
        $reg = $this->model->obtenerDocentePorId($id);

        $resultado = $this->model->eliminarDocente($id);

        if ($resultado === true) {
            // Bitacora: registro eliminado
            Logger::eliminar('Docente', $id, $reg ? $reg['nombreDocente'] . ' ' . $reg['apellidoDocente'] : 'desconocido');
            echo json_encode(["status" => "success", "message" => "Docente eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este docente está asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el docente."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $docente = $this->model->obtenerDocentePorId($id);
        echo json_encode($docente);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idDocenteEdit'] ?? '';
            $prefijoNacionalidad = $_POST['nacionalidadEdit'] ?? 'V';
            $cedulaCompleta = $_POST['cedDocenteEdit'] ?? '';
            $nombres = $_POST['nombreDocenteEdit'] ?? '';
            $apellidos = $_POST['apellidoDocenteEdit'] ?? '';
            $telefono = $_POST['telfDocenteEdit'] ?? '';

            // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
            ValidationHelper::exigirRequerido($id, 'No se recibió el docente a editar.');
            if (!(ValidationHelper::cedula($cedulaCompleta))) {
                ValidationHelper::error('La cédula debe tener entre 6 y 9 dígitos.');
            }
            ValidationHelper::exigirRequerido($nombres, 'Los nombres del docente son obligatorios.');
            ValidationHelper::exigirRequerido($apellidos, 'Los apellidos del docente son obligatorios.');
            if (!(ValidationHelper::telefono($telefono))) {
                ValidationHelper::error('El teléfono debe tener el formato 04XX-XXXXXXX.');
            }
            if (empty($id) || empty($cedulaCompleta) || empty($nombres) || empty($apellidos)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Datos incompletos."
                ]);
                exit();
            }

            $prefijoFinal = strtoupper($prefijoNacionalidad);
            if (!in_array($prefijoFinal, ['V', 'E'])) {
                $prefijoFinal = 'V';
            }

            $resultado = $this->model->actualizarDocente(
                $id,
                $prefijoFinal,
                $cedulaCompleta,
                $nombres,
                $apellidos,
                $telefono
            );

            if ($resultado === 'cedula_duplicada') {
                echo json_encode([
                    "status" => "error",
                    "message" => "Cédula ya existente. No se puede asignar una cédula que pertenece a otro docente."
                ]);
            } else if ($resultado) {
     // Bitacora: registro actualizado
     Logger::actualizar('Docente', $id, $nombres . ' ' . $apellidos);
                echo json_encode([
                    "status" => "success",
                    "message" => "¡Docente actualizado con éxito!"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se realizaron cambios o hubo un error en la base de datos."
                ]);
            }
            exit();
        }
    }

    // Exporta el listado a PDF con Dompdf (se muestra en el navegador)
    public function exportarPdf()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        require_once 'lib/dompdf/autoload.inc.php';
        require_once 'Logger.php';

        $registros = $this->model->listarDocentes();

        // Filtro de busqueda: el PDF exporta solo lo filtrado en pantalla
        $filtro = trim((string) ($_GET['q'] ?? ''));
        if ($filtro !== '') {
            $registros = array_values(array_filter($registros, function ($r) use ($filtro) {
                foreach ($r as $v) {
                    if (mb_stripos((string) $v, $filtro) !== false) {
                        return true;
                    }
                }
                return false;
            }));
        }

        ob_start();
        require 'view/public/reportes/DocenteReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'landscape');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Docentes');

        // Attachment => false: se MUESTRA en el navegador
        $dompdf->stream("reporte_docentes_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}