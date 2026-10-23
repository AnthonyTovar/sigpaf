<?php
require_once 'model/CargoModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class CargoController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new CargoModel();
    }

    /**
     * MÉTODO RENDERIZAR
     */
    private function renderizar($nombreVista, $datos = [])
    {
        extract($datos);
        ob_start();
        require $nombreVista . '.php';
        $content = ob_get_clean();
        require 'view/Layout.php';
    }

    // Listado de Cargos
    public function listar()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $cargos = $this->model->listarCargos();

        $this->renderizar('view/CargoView', ['cargos' => $cargos]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nombreCargo'] ?? '';
            $desc = $_POST['descripcionCargo'] ?? '';

            // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
            ValidationHelper::exigirRequerido($nombre, 'El nombre del cargo es obligatorio.');
            ValidationHelper::exigirMaxLongitud($nombre, 'El nombre del cargo no puede exceder los 15 caracteres.', 15);
            ValidationHelper::exigirMaxLongitud($desc, 'La descripción del cargo es muy larga', 300);
            if (!empty($nombre)) {
                $nuevoId = $this->model->registrarCargo($nombre, $desc);

                if ($nuevoId) {
                    // Bitacora: registro creado
                    Logger::crear('Cargo', $nuevoId, $nombre);

                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Cargo registrado con éxito!",
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
                    "message" => "El nombre del cargo es obligatorio."
                ]);
            }
            exit();
        }
    }
    // Eliminar cargo
    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idCargo'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del cargo"]);
            exit;
        }

        // Consulta el nombre antes de eliminar para la bitacora
        $cargo = $this->model->obtenerCargoPorId($id);

        $resultado = $this->model->eliminarCargo($id);

        if ($resultado === true) {
            // Bitacora: registro eliminado
            Logger::eliminar('Cargo', $id, $cargo ? $cargo['nombreCargo'] : 'desconocido');

            echo json_encode(["status" => "success", "message" => "Cargo eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este cargo está asignado a empleados activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el cargo."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $cargo = $this->model->obtenerCargoPorId($id);
        echo json_encode($cargo);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json'); // Misma lógica que guardar

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idCargoEdit'] ?? '';
            $nombre = $_POST['nombreCargoEdit'] ?? '';
            $desc = $_POST['descripcionCargoEdit'] ?? '';

            // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
            ValidationHelper::exigirRequerido($id, 'No se recibió el cargo a editar.');
            ValidationHelper::exigirRequerido($nombre, 'El nombre del cargo es obligatorio.');
            ValidationHelper::exigirMaxLongitud($nombre, 'El nombre del cargo no puede exceder los 15 caracteres.', 15);
            ValidationHelper::exigirMaxLongitud($desc, 'La descripción del cargo es muy larga', 300);
            if (!empty($id) && !empty($nombre)) {
                $resultado = $this->model->actualizarCargo($id, $nombre, $desc);

                if ($resultado) {
                    // Bitacora: registro actualizado
                    Logger::actualizar('Cargo', $id, $nombre);

                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Cargo actualizado con éxito!"
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

    // Exporta el listado a PDF con Dompdf (se muestra en el navegador)
    public function exportarPdf()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        require_once 'lib/dompdf/autoload.inc.php';
        require_once 'Logger.php';

        $registros = $this->model->listarCargos();

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
        require 'view/public/reportes/CargoReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Cargos');

        // Attachment => false: se MUESTRA en el navegador
        $dompdf->stream("reporte_cargos_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}