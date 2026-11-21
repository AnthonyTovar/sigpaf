<?php
// Controlador de reportes



require_once 'model/ReporteModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';
require_once 'RolHelper.php';


class ReporteController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new ReporteModel();
    }

    private function renderizar($nombreVista, $datos = [])
    {
        extract($datos);
        ob_start();
        require $nombreVista . '.php';
        $content = ob_get_clean();
        require 'view/Layout.php';
    }




    private function leerFiltros()
    {
        $mes = (int) ($_GET['mes'] ?? date('n'));
        $anio = (int) ($_GET['anio'] ?? date('Y'));


        if ($mes < 1 || $mes > 12) {
            $mes = (int) date('n');
        }
        if ($anio < 2000 || $anio > 2100) {
            $anio = (int) date('Y');
        }

        return [
            'mes'        => $mes,
            'anio'       => $anio,
            'idDocente'  => trim($_GET['idDocente'] ?? '')
        ];
    }


    private function nombreMes($mes)
    {
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        return $meses[(int) $mes] ?? '';
    }



    private function imagenBase64($ruta)
    {
        $contenido = @file_get_contents($ruta);
        if ($contenido === false) {
            return '';
        }
        $info = @getimagesizefromstring($contenido);
        $mime = $info['mime'] ?? 'image/png';
        return "data:$mime;base64," . base64_encode($contenido);
    }




    private function describirFiltros($filtros)
    {
        $partes = ['Mes: ' . $this->nombreMes($filtros['mes']) . ' de ' . $filtros['anio']];
        $partes[] = 'Actividades finalizadas en este mes';
        if ($filtros['idDocente'] !== '') {
            foreach ($this->model->obtenerDocentes() as $d) {
                if ($d['idDocente'] === $filtros['idDocente']) {
                    $partes[] = 'Docente: ' . $d['nombreDocente'] . ' ' . $d['apellidoDocente'];
                    break;
                }
            }
        }
        return implode('  |  ', $partes);
    }



    public function listar()
    {
        RolHelper::verificarSuperUsuario();

        $filtros = $this->leerFiltros();
        $actividades = $this->model->obtenerActividadesReporte($filtros);
        $resumen = $this->model->obtenerResumen($filtros);

        $this->renderizar('view/ReporteView', [
            'filtros' => $filtros,
            'filtrosTexto' => $this->describirFiltros($filtros),
            'actividades' => $actividades,
            'resumen' => $resumen,
            'docentes' => $this->model->obtenerDocentes()
        ]);
    }






    public function exportarPdf()
    {
        RolHelper::verificarSuperUsuario();

        $filtros = $this->leerFiltros();
        $actividades = $this->model->obtenerActividadesReporte($filtros);
        $resumen = $this->model->obtenerResumen($filtros);
        $nombreMes = $this->nombreMes($filtros['mes']);
        $filtrosTexto = $this->describirFiltros($filtros);



        ini_set('display_errors', '0');
        while (ob_get_level() > 0) {
            ob_end_clean();
        }



        $fondoBase64 = $this->imagenBase64('view/public/img/background_science_sutil.jpg');



        if ($fondoBase64 !== '' && !extension_loaded('gd')) {
            $fondoBase64 = '';
        }


        ob_start();
        require 'view/public/reportes/ReporteActividadesView.php';
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

        Logger::registrar('GENERAR', 'Reportes', null,
            'Genero reporte PDF de actividades de ' . $nombreMes . ' ' . $filtros['anio'] .
            ' (' . count($actividades) . ' actividades)');

        $dompdf->stream("reporte_actividades_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}
