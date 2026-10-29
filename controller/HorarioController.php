<?php
// Controlador de horarios



require_once 'model/HorarioModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class HorarioController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new HorarioModel();
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

        $horarios = $this->model->listarHorarios();
        $this->renderizar('view/HorarioView', ['horarios' => $horarios]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nomHorario'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($nombre, 'El nombre del horario es obligatorio.');
            if (!empty($nombre)) {

                // Validar duplicados
                if (ValidationHelper::esDuplicado($this->model->listarHorarios(), 'nomHorario', $nombre)) {
                    ValidationHelper::errorDuplicado('un horario', 'el nombre', $nombre);
                }

                $nuevoId = $this->model->registrarHorario($nombre);

                if ($nuevoId) {

                    Logger::crear('Horario', $nuevoId, $nombre);
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Horario registrado con éxito!",
                        "id" => $nuevoId,
                        "nombre" => $nombre
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
                    "message" => "El nombre del horario es obligatorio."
                ]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idHorario'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del horario"]);
            exit;
        }


        $reg = $this->model->obtenerHorarioPorId($id);

        $resultado = $this->model->eliminarHorario($id);

        if ($resultado === true) {

            Logger::eliminar('Horario', $id, $reg ? $reg['nomHorario'] : 'desconocido');
            echo json_encode(["status" => "success", "message" => "Horario eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este horario está asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el horario."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $horario = $this->model->obtenerHorarioPorId($id);
        echo json_encode($horario);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idHorarioEdit'] ?? '';
            $nombre = $_POST['nomHorarioEdit'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($id, 'No se recibió el horario a editar.');
            ValidationHelper::exigirRequerido($nombre, 'El nombre del horario es obligatorio.');
            if (!empty($id) && !empty($nombre)) {

                // Validar duplicados
                if (ValidationHelper::esDuplicado($this->model->listarHorarios(), 'nomHorario', $nombre, 'idHorario', $id)) {
                    ValidationHelper::errorDuplicado('un horario', 'el nombre', $nombre);
                }

                $resultado = $this->model->actualizarHorario($id, $nombre);

                if ($resultado) {

                    Logger::actualizar('Horario', $id, $nombre);
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Horario actualizado con éxito!"
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

        $registros = $this->model->listarHorarios();


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
        require 'view/public/reportes/HorarioReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Horarios');


        $dompdf->stream("reporte_horarios_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}