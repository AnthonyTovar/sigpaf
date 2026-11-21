<?php
// Controlador de docentes



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
            $nombres = trim($_POST['nombreDocente'] ?? '');
            $apellidos = trim($_POST['apellidoDocente'] ?? '');
            $telefono = $_POST['telfDocente'] ?? '';
            $estatus = (($_POST['estatus'] ?? 'Activo') === 'Inactivo') ? 'Inactivo' : 'Activo';

            // Validación unificada (validationhelper)
            if (!(ValidationHelper::cedula($cedulaCompleta))) {
                ValidationHelper::error('La cédula debe tener entre 6 y 9 dígitos, sin letras (el prefijo V/E se toma del selector).');
            }
            ValidationHelper::exigirRequerido($nombres, 'Los nombres del docente son obligatorios.');
            ValidationHelper::exigirRequerido($apellidos, 'Los apellidos del docente son obligatorios.');
            if (!ValidationHelper::soloLetras($nombres)) {
                ValidationHelper::error('Los nombres solo pueden contener letras, sin números.');
            }
            if (!ValidationHelper::soloLetras($apellidos)) {
                ValidationHelper::error('Los apellidos solo pueden contener letras, sin números.');
            }
            if (!(ValidationHelper::telefono($telefono))) {
                ValidationHelper::error('El teléfono debe tener el formato 04XX-XXXXXXX, sin letras.');
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
                $telefono,
                $estatus
            );

            if ($nuevoId === 'cedula_duplicada') {
                echo json_encode([
                    "status" => "error",
                    "message" => "Ya existe un docente con la cédula '{$cedulaCompleta}'. Verifique e inténtelo con otro."
                ]);
            } else if ($nuevoId) {

     Logger::crear('Docente', $nuevoId, $nombres . ' ' . $apellidos);
                echo json_encode([
                    "status" => "success",
                    "message" => "¡Docente registrado con éxito!",
                    "id" => $nuevoId,
                    "nacionalidad" => $prefijoFinal,
                    "cedula" => $cedulaCompleta,
                    "nombres" => $nombres,
                    "apellidos" => $apellidos,
                    "telefono" => $telefono,
                    "estatus" => $estatus
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


        $reg = $this->model->obtenerDocentePorId($id);

        $resultado = $this->model->eliminarDocente($id);

        if ($resultado === true) {

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

    // Verificación en vivo de cédula duplicada (AJAX desde los formularios)
    public function verificarCedula()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $cedula = strtoupper(trim($_GET['cedula'] ?? ''));
        $excluir = trim($_GET['excluir'] ?? '');

        echo json_encode([
            'status' => 'success',
            'existe' => $cedula !== '' ? $this->model->existeCedula($cedula, $excluir !== '' ? $excluir : null) : false
        ]);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idDocenteEdit'] ?? '';
            $prefijoNacionalidad = $_POST['nacionalidadEdit'] ?? 'V';
            $cedulaCompleta = $_POST['cedDocenteEdit'] ?? '';
            $nombres = trim($_POST['nombreDocenteEdit'] ?? '');
            $apellidos = trim($_POST['apellidoDocenteEdit'] ?? '');
            $telefono = $_POST['telfDocenteEdit'] ?? '';
            $estatus = (($_POST['estatusEdit'] ?? 'Activo') === 'Inactivo') ? 'Inactivo' : 'Activo';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($id, 'No se recibió el docente a editar.');
            if (!(ValidationHelper::cedula($cedulaCompleta))) {
                ValidationHelper::error('La cédula debe tener entre 6 y 9 dígitos, sin letras (el prefijo V/E se toma del selector).');
            }
            ValidationHelper::exigirRequerido($nombres, 'Los nombres del docente son obligatorios.');
            ValidationHelper::exigirRequerido($apellidos, 'Los apellidos del docente son obligatorios.');
            if (!ValidationHelper::soloLetras($nombres)) {
                ValidationHelper::error('Los nombres solo pueden contener letras, sin números.');
            }
            if (!ValidationHelper::soloLetras($apellidos)) {
                ValidationHelper::error('Los apellidos solo pueden contener letras, sin números.');
            }
            if (!(ValidationHelper::telefono($telefono))) {
                ValidationHelper::error('El teléfono debe tener el formato 04XX-XXXXXXX, sin letras.');
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
                $telefono,
                $estatus
            );

            if ($resultado === 'cedula_duplicada') {
                echo json_encode([
                    "status" => "error",
                    "message" => "Ya existe otro docente con la cédula '{$cedulaCompleta}'. Verifique e inténtelo con otro."
                ]);
            } else if ($resultado) {

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


    public function exportarPdf()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        require_once 'lib/dompdf/autoload.inc.php';
        require_once 'Logger.php';

        $registros = $this->model->listarDocentes();


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


        $dompdf->stream("reporte_docentes_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}