<?php
require_once 'model/TipoEntregaModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class TipoEntregaController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new TipoEntregaModel();
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

        $tiposEntrega = $this->model->listarTiposEntrega();
        $this->renderizar('view/TipoEntregaView', [
            'tiposEntrega' => $tiposEntrega
        ]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nomTipEntrega = $_POST['nomTipEntrega'] ?? '';

            // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
            ValidationHelper::exigirRequerido($nomTipEntrega, 'El nombre del tipo de entrega es obligatorio.');
            if (empty($nomTipEntrega)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre del tipo de entrega es obligatorio."
                ]);
                exit();
            }

            $nuevoId = $this->model->registrarTipoEntrega($nomTipEntrega);

            if ($nuevoId === 'nombre_duplicado') {
                echo json_encode([
                    "status" => "error",
                    "message" => "El tipo de entrega ya existe. Use un nombre diferente."
                ]);
            } else if ($nuevoId) {
     // Bitacora: registro creado
     Logger::crear('Tipo de Entrega', $nuevoId, $nomTipEntrega);
                echo json_encode([
                    "status" => "success",
                    "message" => "Tipo de entrega registrado con exito!",
                    "id" => $nuevoId,
                    "nomTipEntrega" => $nomTipEntrega
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

        $id = $_POST['idTipEntrega'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibio el ID"]);
            exit;
        }

        // Consulta el nombre antes de eliminar para la bitacora
        $reg = $this->model->obtenerTipoEntregaPorId($id);

        $resultado = $this->model->eliminarTipoEntrega($id);

        if ($resultado === true) {
            // Bitacora: registro eliminado
            Logger::eliminar('Tipo de Entrega', $id, $reg ? $reg['nomTipEntrega'] : 'desconocido');
            echo json_encode(["status" => "success", "message" => "Tipo de entrega eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Esta asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $tipoEntrega = $this->model->obtenerTipoEntregaPorId($id);
        echo json_encode($tipoEntrega);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idTipEntregaEdit'] ?? '';
            $nomTipEntrega = $_POST['nomTipEntregaEdit'] ?? '';

            // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
            ValidationHelper::exigirRequerido($id, 'No se recibió el tipo de entrega a editar.');
            ValidationHelper::exigirRequerido($nomTipEntrega, 'El nombre del tipo de entrega es obligatorio.');
            if (empty($id) || empty($nomTipEntrega)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre del tipo de entrega es obligatorio."
                ]);
                exit();
            }

            $resultado = $this->model->actualizarTipoEntrega($id, $nomTipEntrega);

            if ($resultado === 'nombre_duplicado') {
                echo json_encode([
                    "status" => "error",
                    "message" => "El tipo de entrega ya esta en uso por otro registro."
                ]);
            } else if ($resultado) {
     // Bitacora: registro actualizado
     Logger::actualizar('Tipo de Entrega', $id, $nomTipEntrega);
                echo json_encode([
                    "status" => "success",
                    "message" => "Tipo de entrega actualizado con exito!"
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

        $registros = $this->model->listarTiposEntrega();

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
        require 'view/public/reportes/TipoEntregaReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Tipos de Entrega');

        // Attachment => false: se MUESTRA en el navegador
        $dompdf->stream("reporte_tipos_entrega_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}