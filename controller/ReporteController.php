<?php
require_once 'model/ReporteModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';
require_once 'RolHelper.php';

/**
 * MÓDULO REPORTES - Controlador
 * Pantalla de filtros + vista previa, y generación del PDF de
 * actividades realizadas por FUNDACITI con Dompdf (lib/dompdf).
 */
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

    // Lee y depura los filtros recibidos por GET (compartidos por la
    // pantalla y el PDF, para que ambos muestren exactamente lo mismo).
    // El reporte es por MES: mes (1-12) + año; por defecto el mes actual.
    private function leerFiltros()
    {
        $mes = (int) ($_GET['mes'] ?? date('n'));
        $anio = (int) ($_GET['anio'] ?? date('Y'));

        // Lógica: valores fuera de rango caen al mes/año actual
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

    // Nombre del mes en español (para pantalla, PDF y nombre de archivo)
    private function nombreMes($mes)
    {
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        return $meses[(int) $mes] ?? '';
    }

    // Imagen del proyecto embebida en base64 para el PDF (dompdf no
    // resuelve rutas relativas fuera del servidor web)
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

    // Descripción legible de los filtros aplicados (para pantalla y PDF).
    // El reporte siempre trae solo actividades Finalizada: es la regla del
    // sistema, no un filtro que el usuario combine.
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

    // Pantalla principal: filtros + vista previa del reporte
    // SOLO SUPER USUARIO (validado tambien en la ruta de index.php)
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

    // Genera y descarga el PDF con Dompdf
    // SOLO SUPER USUARIO (validado tambien en la ruta de index.php)
    public function generarPDF()
    {
        RolHelper::verificarSuperUsuario();

        $filtros = $this->leerFiltros();
        $actividades = $this->model->obtenerActividadesReporte($filtros);
        $resumen = $this->model->obtenerResumen($filtros);
        $nombreMes = $this->nombreMes($filtros['mes']);

        // El PDF es un archivo binario: ningún mensaje de PHP (avisos,
        // warnings, notices) puede mezclarse con la salida
        ini_set('display_errors', '0');
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Imágenes institucionales embebidas (banner de encabezado y
        // fondo sutil difuminado) para la plantilla del PDF
        $bannerBase64 = $this->imagenBase64('view/public/img/banner_institucional.png');
        $fondoBase64 = $this->imagenBase64('view/public/img/background_science_sutil.jpg');

        // Sin GD (extensión de PHP) dompdf no puede pintar fondos: se omite
        // el fondo para no tumbar la generación del PDF
        if ($fondoBase64 !== '' && !extension_loaded('gd')) {
            $fondoBase64 = '';
        }

        // Plantilla HTML del PDF (sin Layout: es un documento independiente)
        ob_start();
        require 'view/ReportePDFView.php';
        $html = ob_get_clean();

        require_once 'lib/dompdf/autoload.inc.php';
        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', 'landscape');
        $dompdf->render();

        // Número de página en cada hoja (counter(pages) de CSS falla en
        // elementos fijos de dompdf; el canvas sí lo resuelve por hoja)
        $canvas = $dompdf->getCanvas();
        $fuente = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
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

        $nombre = sprintf('Reporte_Actividades_FUNDACITI_%04d-%02d.pdf', $filtros['anio'], $filtros['mes']);
        $dompdf->stream($nombre, ['Attachment' => true]);
        exit();
    }
}
