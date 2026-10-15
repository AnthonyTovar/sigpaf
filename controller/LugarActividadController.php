<?php
require_once 'model/LugarActividadModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class LugarActividadController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new LugarActividadModel();
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

        $lugares = $this->model->listarLugares();
        $parroquias = $this->model->listarParroquias();
        $this->renderizar('view/LugarActividadView', [
            'lugares' => $lugares,
            'parroquias' => $parroquias
        ]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nomLugar = $_POST['nomLugarActividad'] ?? '';
            $desLugar = $_POST['desLugarActividad'] ?? '';
            $direccion = $_POST['direccion'] ?? '';
            $esSede = ($_POST['esSede'] ?? '0') === '1';
            $idParroquia = $_POST['idParroquia'] ?? '';

            // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
            ValidationHelper::exigirRequerido($nomLugar, 'El nombre del lugar es obligatorio.');
            ValidationHelper::exigirMaxLongitud($desLugar, 'La descripción del lugar es muy larga', 255);
            ValidationHelper::exigirRequerido($direccion, 'La dirección es obligatoria.');
            ValidationHelper::exigirRequerido($idParroquia, 'Debe seleccionar la parroquia.');
            if (empty($nomLugar) || empty($direccion) || empty($idParroquia)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Los campos obligatorios son: Nombre del Lugar, Direccion y Parroquia."
                ]);
                exit();
            }

            $nuevoId = $this->model->registrarLugar($nomLugar, $desLugar, $direccion, $esSede, $idParroquia);

            if ($nuevoId === 'nombre_duplicado') {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre del lugar ya existe. Use un nombre diferente."
                ]);
            } else if ($nuevoId) {
     // Bitacora: registro creado
     Logger::crear('Lugar de Actividad', $nuevoId, $nomLugar);
                echo json_encode([
                    "status" => "success",
                    "message" => "Lugar de actividad registrado con exito!",
                    "id" => $nuevoId,
                    "nomLugar" => $nomLugar,
                    "desLugar" => $desLugar,
                    "direccion" => $direccion,
                    "esSede" => $esSede,
                    "idParroquia" => $idParroquia
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Hubo un error en la base de datos."
                ]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idLugarActividad'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibio el ID del lugar"]);
            exit;
        }

        // Consulta el nombre antes de eliminar para la bitacora
        $reg = $this->model->obtenerLugarPorId($id);

        $resultado = $this->model->eliminarLugar($id);

        if ($resultado === true) {
            // Bitacora: registro eliminado
            Logger::eliminar('Lugar de Actividad', $id, $reg ? $reg['nomLugarActividad'] : 'desconocido');
            echo json_encode(["status" => "success", "message" => "Lugar eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este lugar esta asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el lugar."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $lugar = $this->model->obtenerLugarPorId($id);
        echo json_encode($lugar);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idLugarActividadEdit'] ?? '';
            $nomLugar = $_POST['nomLugarActividadEdit'] ?? '';
            $desLugar = $_POST['desLugarActividadEdit'] ?? '';
            $direccion = $_POST['direccionEdit'] ?? '';
            $esSede = ($_POST['esSedeEdit'] ?? '0') === '1';
            $idParroquia = $_POST['idParroquiaEdit'] ?? '';

            // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
            ValidationHelper::exigirRequerido($id, 'No se recibió el lugar a editar.');
            ValidationHelper::exigirRequerido($nomLugar, 'El nombre del lugar es obligatorio.');
            ValidationHelper::exigirMaxLongitud($desLugar, 'La descripción del lugar es muy larga', 255);
            ValidationHelper::exigirRequerido($direccion, 'La dirección es obligatoria.');
            ValidationHelper::exigirRequerido($idParroquia, 'Debe seleccionar la parroquia.');
            if (empty($id) || empty($nomLugar) || empty($direccion) || empty($idParroquia)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Los campos obligatorios son: Nombre del Lugar, Direccion y Parroquia."
                ]);
                exit();
            }

            $resultado = $this->model->actualizarLugar($id, $nomLugar, $desLugar, $direccion, $esSede, $idParroquia);

            if ($resultado === 'nombre_duplicado') {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre del lugar ya esta en uso por otro registro."
                ]);
            } else if ($resultado) {
     // Bitacora: registro actualizado
     Logger::actualizar('Lugar de Actividad', $id, $nomLugar);
                echo json_encode([
                    "status" => "success",
                    "message" => "Lugar de actividad actualizado con exito!"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se realizaron cambios o hubo un error."
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

        $registros = $this->model->listarLugares();

        ob_start();
        require 'view/public/reportes/LugarActividadReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'landscape');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Lugares de Actividad');

        // Attachment => false: se MUESTRA en el navegador
        $dompdf->stream("reporte_lugares_actividad_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}