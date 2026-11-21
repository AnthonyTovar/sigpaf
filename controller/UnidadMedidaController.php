<?php
// Controlador de unidades de medida



require_once 'model/UnidadMedidaModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class UnidadMedidaController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new UnidadMedidaModel();
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

        $unidades = $this->model->listarUnidades();
        $this->renderizar('view/UnidadMedidaView', ['unidades' => $unidades]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nomUnidadMedida'] ?? '';
            $descripcion = $_POST['descUnidadMedida'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($nombre, 'El nombre de la unidad de medida es obligatorio.');
            ValidationHelper::exigirMaxLongitud($descripcion, 'La descripción es muy larga', 250);
            if (!empty($nombre)) {

                // Validar duplicados
                if (ValidationHelper::esDuplicado($this->model->listarUnidades(), 'nomUnidadMedida', $nombre)) {
                    ValidationHelper::errorDuplicado('una unidad de medida', 'el nombre', $nombre);
                }

                $nuevoId = $this->model->registrarUnidad($nombre, $descripcion);

                if ($nuevoId) {

                    Logger::crear('Unidad de Medida', $nuevoId, $nombre);
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Unidad de medida registrada con éxito!",
                        "id" => $nuevoId,
                        "nombre" => $nombre,
                        "descripcion" => $descripcion
                    ]);
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Hubo un error en la base de datos."
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre de la unidad de medida es obligatorio."
                ]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idUnidadMedida'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID de la unidad"]);
            exit;
        }


        $reg = $this->model->obtenerUnidadPorId($id);

        $resultado = $this->model->eliminarUnidad($id);

        if ($resultado === true) {

            Logger::eliminar('Unidad de Medida', $id, $reg ? $reg['nomUnidadMedida'] : 'desconocido');
            echo json_encode(["status" => "success", "message" => "Unidad de medida eliminada correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Esta unidad está asignada a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar la unidad."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $unidad = $this->model->obtenerUnidadPorId($id);
        header('Content-Type: application/json');
        echo json_encode($unidad);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idUnidadMedidaEdit'] ?? '';
            $nombre = $_POST['nomUnidadMedidaEdit'] ?? '';
            $descripcion = $_POST['descUnidadMedidaEdit'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($id, 'No se recibió la unidad de medida a editar.');
            ValidationHelper::exigirRequerido($nombre, 'El nombre de la unidad de medida es obligatorio.');
            ValidationHelper::exigirMaxLongitud($descripcion, 'La descripción es muy larga', 250);
            if (!empty($id) && !empty($nombre)) {

                // Validar duplicados
                if (ValidationHelper::esDuplicado($this->model->listarUnidades(), 'nomUnidadMedida', $nombre, 'idUnidadMedida', $id)) {
                    ValidationHelper::errorDuplicado('una unidad de medida', 'el nombre', $nombre);
                }

                $resultado = $this->model->actualizarUnidad($id, $nombre, $descripcion);

                if ($resultado) {

                    Logger::actualizar('Unidad de Medida', $id, $nombre);
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Unidad de medida actualizada con éxito!"
                    ]);
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "No se realizaron cambios o hubo un error."
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Datos incompletos."
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

        $registros = $this->model->listarUnidades();


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
        require 'view/public/reportes/UnidadMedidaReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Unidades de Medida');


        $dompdf->stream("reporte_unidades_medida_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}