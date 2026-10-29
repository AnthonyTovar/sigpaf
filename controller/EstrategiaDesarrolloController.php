<?php
// Controlador de estrategias de desarrollo



require_once 'model/EstrategiaDesarrolloModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class EstrategiaDesarrolloController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new EstrategiaDesarrolloModel();
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

        $estrategias = $this->model->listarEstrategias();
        $this->renderizar('view/EstrategiaDesarrolloView', [
            'estrategias' => $estrategias
        ]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nomEstDesarrollo = $_POST['nomEstDesarrollo'] ?? '';
            $descEstDesarrollo = $_POST['descEstDesarrollo'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($nomEstDesarrollo, 'El nombre de la estrategia es obligatorio.');
            ValidationHelper::exigirMaxLongitud($descEstDesarrollo, 'La descripción es muy larga', 500);
            if (empty($nomEstDesarrollo)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre de la estrategia es obligatorio."
                ]);
                exit();
            }

            $nuevoId = $this->model->registrarEstrategia($nomEstDesarrollo, $descEstDesarrollo);

            if ($nuevoId === 'nombre_duplicado') {
                echo json_encode([
                    "status" => "error",
                    "message" => "Ya existe una estrategia de desarrollo con el nombre '{$nomEstDesarrollo}'. Verifique e inténtelo con otro."
                ]);
            } else if ($nuevoId) {

     Logger::crear('Estrategia de Desarrollo', $nuevoId, $nomEstDesarrollo);
                echo json_encode([
                    "status" => "success",
                    "message" => "Estrategia de desarrollo registrada con exito!",
                    "id" => $nuevoId,
                    "nomEstDesarrollo" => $nomEstDesarrollo,
                    "descEstDesarrollo" => $descEstDesarrollo
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

        $id = $_POST['idEstDesarrollo'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibio el ID"]);
            exit;
        }


        $reg = $this->model->obtenerEstrategiaPorId($id);

        $resultado = $this->model->eliminarEstrategia($id);

        if ($resultado === true) {

            Logger::eliminar('Estrategia de Desarrollo', $id, $reg ? $reg['nomEstDesarrollo'] : 'desconocido');
            echo json_encode(["status" => "success", "message" => "Estrategia eliminada correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Esta asignada a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $estrategia = $this->model->obtenerEstrategiaPorId($id);
        echo json_encode($estrategia);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idEstDesarrolloEdit'] ?? '';
            $nomEstDesarrollo = $_POST['nomEstDesarrolloEdit'] ?? '';
            $descEstDesarrollo = $_POST['descEstDesarrolloEdit'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($id, 'No se recibió la estrategia a editar.');
            ValidationHelper::exigirRequerido($nomEstDesarrollo, 'El nombre de la estrategia es obligatorio.');
            ValidationHelper::exigirMaxLongitud($descEstDesarrollo, 'La descripción es muy larga', 500);
            if (empty($id) || empty($nomEstDesarrollo)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre de la estrategia es obligatorio."
                ]);
                exit();
            }

            $resultado = $this->model->actualizarEstrategia($id, $nomEstDesarrollo, $descEstDesarrollo);

            if ($resultado === 'nombre_duplicado') {
                echo json_encode([
                    "status" => "error",
                    "message" => "Ya existe otra estrategia de desarrollo con el nombre '{$nomEstDesarrollo}'. Verifique e inténtelo con otro."
                ]);
            } else if ($resultado) {

     Logger::actualizar('Estrategia de Desarrollo', $id, $nomEstDesarrollo);
                echo json_encode([
                    "status" => "success",
                    "message" => "Estrategia de desarrollo actualizada con exito!"
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


    public function exportarPdf()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        require_once 'lib/dompdf/autoload.inc.php';
        require_once 'Logger.php';

        $registros = $this->model->listarEstrategias();


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
        require 'view/public/reportes/EstrategiaDesarrolloReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Estrategias de Desarrollo');


        $dompdf->stream("reporte_estrategias_desarrollo_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}