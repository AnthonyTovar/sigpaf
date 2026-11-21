<?php
// Controlador de listados del panel principal

require_once 'model/DashboardModel.php';
require_once 'RolHelper.php';
require_once 'SecurityHelper.php';
require_once 'Logger.php';
require_once 'lib/BeneficiariosExcel.php';

class DashboardDetalleController
{
    private $model;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new DashboardModel();
    }

    private function renderizar($tipo, $registros)
    {
        SecurityHelper::preventBackAfterLogout();
        $puedeExportar = RolHelper::esAdministrador();
        ob_start();
        require 'view/DashboardDetalleView.php';
        $content = ob_get_clean();
        require 'view/Layout.php';
    }

    public function actividades()
    {
        $this->renderizar('actividades', $this->model->listarActividadesEjecucion());
    }

    public function beneficiarios()
    {
        $this->renderizar('beneficiarios', $this->model->listarBeneficiariosRegistrados());
    }

    public function sesionesHoy()
    {
        $this->renderizar('sesiones', $this->model->listarSesionesHoy());
    }

    public function lugares()
    {
        $this->renderizar('lugares', $this->model->listarLugaresRegistrados());
    }

    private function beneficiariosParaExportar()
    {
        $ids = $_POST['ids'] ?? [];
        if (!is_array($ids)) {
            $ids = [];
        }
        return $this->model->listarBeneficiariosRegistrados($ids);
    }

    public function exportarBeneficiariosPdf()
    {
        RolHelper::verificarAdministrador();
        $registros = $this->beneficiariosParaExportar();
        $filtro = !empty($_POST['ids']) && is_array($_POST['ids'])
            ? 'Beneficiarios seleccionados: ' . count($registros)
            : '';

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
        require 'view/public/reportes/BeneficiarioReporteView.php';
        $html = ob_get_clean();

        require_once 'lib/dompdf/autoload.inc.php';
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'landscape');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        $canvas = $dompdf->getCanvas();
        $fuente = $dompdf->getFontMetrics()->getFont('Helvetica');
        $canvas->page_text(
            $canvas->get_width() - 90,
            $canvas->get_height() - 32,
            'Página {PAGE_NUM} de {PAGE_COUNT}',
            $fuente,
            8,
            [0.42, 0.45, 0.49]
        );

        Logger::registrar('GENERAR', 'Beneficiarios', null,
            'Genero reporte PDF de beneficiarios (' . count($registros) . ' registros)');
        $dompdf->stream('reporte_beneficiarios_sigpaf.pdf', ['Attachment' => false]);
        exit();
    }

    public function exportarBeneficiariosExcel()
    {
        RolHelper::verificarAdministrador();
        $registros = $this->beneficiariosParaExportar();
        $excel = new BeneficiariosExcel();
        $contenido = $excel->generar($registros);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="beneficiarios_sigpaf.xlsx"');
        header('Content-Length: ' . strlen($contenido));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');

        Logger::registrar('GENERAR', 'Beneficiarios', null,
            'Genero reporte Excel de beneficiarios (' . count($registros) . ' registros)');
        echo $contenido;
        exit();
    }
}
