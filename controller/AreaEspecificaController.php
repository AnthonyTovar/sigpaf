<?php
// Controlador de áreas específicas



require_once 'model/AreaEspecificaModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class AreaEspecificaController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new AreaEspecificaModel();
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
        $areas = $this->model->listarAreas();
        $this->renderizar('view/AreaEspecificaView', ['areas' => $areas]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nomAreaE'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($nombre, 'El nombre del área es obligatorio.');
            if (!empty($nombre)) {

                // Validar duplicados
                if (ValidationHelper::esDuplicado($this->model->listarAreas(), 'nomAreaE', $nombre)) {
                    ValidationHelper::errorDuplicado('un área específica', 'el nombre', $nombre);
                }

                $nuevoId = $this->model->registrarArea($nombre);
                if ($nuevoId) {

                    Logger::crear('Area Especifica', $nuevoId, $nombre);
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Área específica registrada con éxito!",
                        "id" => $nuevoId,
                        "nombre" => $nombre
                    ]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error al guardar en la base de datos."]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "El nombre del área es obligatorio."]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');
        $id = $_POST['idAreaE'] ?? null;
        if (!$id) {
            echo json_encode(["status" => "error", "message" => "ID no recibido."]);
            exit;
        }

        $reg = $this->model->obtenerAreaPorId($id);

        $resultado = $this->model->eliminarArea($id);
        if ($resultado === true) {

            Logger::eliminar('Area Especifica', $id, $reg ? $reg['nomAreaE'] : 'desconocido');
            echo json_encode(["status" => "success", "message" => "Área específica eliminada correctamente."]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Tiene registros asociados (vértices)."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al eliminar."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $area = $this->model->obtenerAreaPorId($id);
        header('Content-Type: application/json');
        echo json_encode($area);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idAreaEEdit'] ?? '';
            $nombre = $_POST['nomAreaEEdit'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($id, 'No se recibió el área a editar.');
            ValidationHelper::exigirRequerido($nombre, 'El nombre del área es obligatorio.');
            if (!empty($id) && !empty($nombre)) {

                // Validar duplicados
                if (ValidationHelper::esDuplicado($this->model->listarAreas(), 'nomAreaE', $nombre, 'idAreaE', $id)) {
                    ValidationHelper::errorDuplicado('un área específica', 'el nombre', $nombre);
                }

                $resultado = $this->model->actualizarArea($id, $nombre);
                if ($resultado) {

                    Logger::actualizar('Area Especifica', $id, $nombre);
                    echo json_encode(["status" => "success", "message" => "¡Área específica actualizada con éxito!"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se detectaron cambios o hubo un error."]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Datos incompletos."]);
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

        $registros = $this->model->listarAreas();


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
        require 'view/public/reportes/AreaEspecificaReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Áreas Específicas');


        $dompdf->stream("reporte_areas_especificas_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}