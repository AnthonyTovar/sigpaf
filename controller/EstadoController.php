<?php
// Controlador de estados



require_once 'model/EstadoModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class EstadoController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new EstadoModel();
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
        $estados = $this->model->listarEstados();
        $this->renderizar('view/EstadoView', ['estados' => $estados]);
    }


    public function guardar()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nombreEstado'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($nombre, 'El nombre del estado es obligatorio.');

            // Validar duplicados
            if (ValidationHelper::esDuplicado($this->model->listarEstados(), 'nombreEstado', $nombre)) {
                ValidationHelper::errorDuplicado('un estado', 'el nombre', $nombre);
            }

            $nuevoId = $this->model->registrarEstado($nombre);
            
            if ($nuevoId) {

                Logger::crear('Estado', $nuevoId, $nombre);
                echo json_encode([
                    "status" => "success",
                    "message" => "¡Estado registrado con éxito!",
                    "id" => $nuevoId,
                    "nombre" => $nombre
                ]);
            } else {
                echo json_encode([
                    "status" => "error", 
                    "message" => "Error al guardar en la base de datos."
                ]);
            }
            exit();
        }
    }


    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $estado = $this->model->obtenerEstadoPorId($id);
        header('Content-Type: application/json');
        echo json_encode($estado);
        exit;
    }


    public function editar()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idEstadoEdit'] ?? '';
            $nombre = $_POST['nombreEstadoEdit'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($id, 'No se recibió el estado a editar.');
            ValidationHelper::exigirRequerido($nombre, 'El nombre del estado es obligatorio.');

            // Validar duplicados
            if (ValidationHelper::esDuplicado($this->model->listarEstados(), 'nombreEstado', $nombre, 'idEstado', $id)) {
                ValidationHelper::errorDuplicado('un estado', 'el nombre', $nombre);
            }

            $resultado = $this->model->actualizarEstado($id, $nombre);
            
            if ($resultado) {

                Logger::actualizar('Estado', $id, $nombre);
                echo json_encode([
                    "status" => "success", 
                    "message" => "¡Estado actualizado con éxito!"
                ]);
            } else {
                echo json_encode([
                    "status" => "error", 
                    "message" => "No se detectaron cambios o hubo un error."
                ]);
            }
            exit();
        }
    }


    public function eliminar()
    {
        header('Content-Type: application/json');
        $id = $_POST['idEstado'] ?? null;
        
        if (!$id) {
            echo json_encode([
                "status" => "error", 
                "message" => "ID no recibido."
            ]);
            exit;
        }
        

        $reg = $this->model->obtenerEstadoPorId($id);

        $resultado = $this->model->eliminarEstado($id);
        
        if ($resultado === true) {

            Logger::eliminar('Estado', $id, $reg ? $reg['nombreEstado'] : 'desconocido');
            echo json_encode([
                "status" => "success", 
                "message" => "Estado eliminado correctamente."
            ]);
        } else if ($resultado === "link") {
            echo json_encode([
                "status" => "error", 
                "message" => "No se puede eliminar: El estado tiene municipios asociados."
            ]);
        } else {
            echo json_encode([
                "status" => "error", 
                "message" => "Error interno al eliminar."
            ]);
        }
        exit;
    }


    public function exportarPdf()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        require_once 'lib/dompdf/autoload.inc.php';
        require_once 'Logger.php';

        $registros = $this->model->listarEstados();


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
        require 'view/public/reportes/EstadoReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Estados');


        $dompdf->stream("reporte_estados_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}