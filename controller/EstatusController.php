<?php
// Controlador de estatus



require_once 'model/EstatusModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class EstatusController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new EstatusModel();
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

        $estatus = $this->model->listarEstatus();
        $this->renderizar('view/EstatusView', ['estatus' => $estatus]);
    }

    private function denegarSoloLectura()
    {
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "error",
            "message" => "El módulo de estatus es de solo lectura. No se pueden agregar, modificar ni eliminar registros."
        ]);
        exit();
    }

    public function guardar()
    {
        $this->denegarSoloLectura();
    }

    public function eliminar()
    {
        $this->denegarSoloLectura();
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $estatus = $this->model->obtenerEstatusPorId($id);
        echo json_encode($estatus);
        exit;
    }

    public function editar()
    {
        $this->denegarSoloLectura();
    }


    public function exportarPdf()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        require_once 'lib/dompdf/autoload.inc.php';
        require_once 'Logger.php';

        $registros = $this->model->listarEstatus();


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
        require 'view/public/reportes/EstatusReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Estatus');


        $dompdf->stream("reporte_estatus_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}