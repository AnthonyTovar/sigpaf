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

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nomEstatus'] ?? '';
            $desc = $_POST['descEstatus'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($nombre, 'El nombre del estatus es obligatorio.');
            ValidationHelper::exigirMaxLongitud($desc, 'La descripción del estatus es muy larga', 250);
            if (!empty($nombre) && !empty($desc)) {

                // Validar duplicados
                if (ValidationHelper::esDuplicado($this->model->listarEstatus(), 'nomEstatus', $nombre)) {
                    ValidationHelper::errorDuplicado('un estatus', 'el nombre', $nombre);
                }

                $nuevoId = $this->model->registrarEstatus($nombre, $desc);

                if ($nuevoId) {

                    Logger::crear('Estatus', $nuevoId, $nombre);
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Estatus registrado con éxito!",
                        "id" => $nuevoId,
                        "nombre" => $nombre,
                        "descripcion" => $desc
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
                    "message" => "Todos los campos son obligatorios."
                ]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idEstatus'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del estatus"]);
            exit;
        }


        $reg = $this->model->obtenerEstatusPorId($id);

        $resultado = $this->model->eliminarEstatus($id);

        if ($resultado === true) {

            Logger::eliminar('Estatus', $id, $reg ? $reg['nomEstatus'] : 'desconocido');
            echo json_encode(["status" => "success", "message" => "Estatus eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este estatus está asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el estatus."]);
        }
        exit;
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
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idEstatusEdit'] ?? '';
            $nombre = $_POST['nomEstatusEdit'] ?? '';
            $desc = $_POST['descEstatusEdit'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($id, 'No se recibió el estatus a editar.');
            ValidationHelper::exigirRequerido($nombre, 'El nombre del estatus es obligatorio.');
            ValidationHelper::exigirMaxLongitud($desc, 'La descripción del estatus es muy larga', 250);
            if (!empty($id) && !empty($nombre) && !empty($desc)) {

                // Validar duplicados
                if (ValidationHelper::esDuplicado($this->model->listarEstatus(), 'nomEstatus', $nombre, 'idEstatus', $id)) {
                    ValidationHelper::errorDuplicado('un estatus', 'el nombre', $nombre);
                }

                $resultado = $this->model->actualizarEstatus($id, $nombre, $desc);

                if ($resultado) {

                    Logger::actualizar('Estatus', $id, $nombre);
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Estatus actualizado con éxito!"
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