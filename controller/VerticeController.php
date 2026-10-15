<?php
require_once 'model/VerticeModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class VerticeController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new VerticeModel();
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
        $Vertice = $this->model->listarVertice();
        $this->renderizar('view/VerticeView', [
            'Vertice' => $Vertice
        ]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nombreVertice'] ?? '';
            $desc = $_POST['descripcionVertice'] ?? '';

            // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
            ValidationHelper::exigirRequerido($nombre, 'El nombre del vértice es obligatorio.');
            ValidationHelper::exigirMaxLongitud($desc, 'La descripción del vértice es muy larga', 250);
            if (!empty($nombre)) {
                $nuevoId = $this->model->registrarVertice($nombre, $desc);
                if ($nuevoId) {
                    // Bitacora: registro creado
                    Logger::crear('Vertice', $nuevoId, $nombre);
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Vértice registrado con éxito!",
                        "id" => $nuevoId,
                        "nombre" => $nombre,
                        "descripcion" => $desc
                    ]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error al guardar en la base de datos."]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "El nombre del vértice es obligatorio."]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');
        $id = $_POST['idVertice'] ?? null;
        if (!$id) {
            echo json_encode(["status" => "error", "message" => "ID no recibido."]);
            exit;
        }
        // Consulta el nombre antes de eliminar para la bitacora
        $reg = $this->model->obtenerVerticePorId($id);

        $resultado = $this->model->eliminarVertice($id);
        if ($resultado === true) {
            // Bitacora: registro eliminado
            Logger::eliminar('Vertice', $id, $reg ? $reg['nombreVertice'] : 'desconocido');
            echo json_encode(["status" => "success", "message" => "Vértice eliminado correctamente."]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Tiene registros asociados."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al eliminar."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $vertice = $this->model->obtenerVerticePorId($id);
        header('Content-Type: application/json');
        echo json_encode($vertice);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idVerticeEdit'] ?? '';
            $nombre = $_POST['nombreVerticeEdit'] ?? '';
            $desc = $_POST['descripcionVerticeEdit'] ?? '';

            // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
            ValidationHelper::exigirRequerido($id, 'No se recibió el vértice a editar.');
            ValidationHelper::exigirRequerido($nombre, 'El nombre del vértice es obligatorio.');
            ValidationHelper::exigirMaxLongitud($desc, 'La descripción del vértice es muy larga', 250);
            if (!empty($id) && !empty($nombre)) {
                $resultado = $this->model->actualizarVertice($id, $nombre, $desc);
                if ($resultado) {
                    // Bitacora: registro actualizado
                    Logger::actualizar('Vertice', $id, $nombre);
                    echo json_encode(["status" => "success", "message" => "¡Vértice actualizado con éxito!"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se detectaron cambios o hubo un error."]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Datos incompletos."]);
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

        $registros = $this->model->listarVertice();

        ob_start();
        require 'view/public/reportes/VerticeReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Vértices');

        // Attachment => false: se MUESTRA en el navegador
        $dompdf->stream("reporte_vertices_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}