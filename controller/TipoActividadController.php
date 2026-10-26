<?php
require_once 'model/TipoActividadModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class TipoActividadController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new TipoActividadModel();
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

        $tiposActividad = $this->model->listarTiposActividad();
        $this->renderizar('view/TipoActividadView', ['tiposActividad' => $tiposActividad]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nomTipoActividad'] ?? '';
            $desc = $_POST['descTipoActividad'] ?? '';

            // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
            ValidationHelper::exigirRequerido($nombre, 'El nombre del tipo de actividad es obligatorio.');
            ValidationHelper::exigirMaxLongitud($desc, 'La descripción es muy larga', 250);
            if (!empty($nombre) && !empty($desc)) {

                // ===== VALIDACIÓN DE DUPLICADO (mensaje estándar del sistema) =====
                if (ValidationHelper::esDuplicado($this->model->listarTiposActividad(), 'nomTipoActividad', $nombre)) {
                    ValidationHelper::errorDuplicado('un tipo de actividad', 'el nombre', $nombre);
                }

                $nuevoId = $this->model->registrarTipoActividad($nombre, $desc);

                if ($nuevoId) {
                    // Bitacora: registro creado
                    Logger::crear('Tipo de Actividad', $nuevoId, $nombre);
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Tipo de actividad registrado con éxito!",
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

        $id = $_POST['idTipoActividad'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del tipo de actividad"]);
            exit;
        }

        // Consulta el nombre antes de eliminar para la bitacora
        $reg = $this->model->obtenerTipoActividadPorId($id);

        $resultado = $this->model->eliminarTipoActividad($id);

        if ($resultado === true) {
            // Bitacora: registro eliminado
            Logger::eliminar('Tipo de Actividad', $id, $reg ? $reg['nomTipoActividad'] : 'desconocido');
            echo json_encode(["status" => "success", "message" => "Tipo de actividad eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este tipo de actividad está asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el tipo de actividad."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $tipoActividad = $this->model->obtenerTipoActividadPorId($id);
        echo json_encode($tipoActividad);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idTipoActividadEdit'] ?? '';
            $nombre = $_POST['nomTipoActividadEdit'] ?? '';
            $desc = $_POST['descTipoActividadEdit'] ?? '';

            // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
            ValidationHelper::exigirRequerido($id, 'No se recibió el tipo de actividad a editar.');
            ValidationHelper::exigirRequerido($nombre, 'El nombre del tipo de actividad es obligatorio.');
            ValidationHelper::exigirMaxLongitud($desc, 'La descripción es muy larga', 250);
            if (!empty($id) && !empty($nombre) && !empty($desc)) {

                // ===== VALIDACIÓN DE DUPLICADO (mensaje estándar del sistema) =====
                if (ValidationHelper::esDuplicado($this->model->listarTiposActividad(), 'nomTipoActividad', $nombre, 'idTipoActividad', $id)) {
                    ValidationHelper::errorDuplicado('un tipo de actividad', 'el nombre', $nombre);
                }

                $resultado = $this->model->actualizarTipoActividad($id, $nombre, $desc);

                if ($resultado) {
                    // Bitacora: registro actualizado
                    Logger::actualizar('Tipo de Actividad', $id, $nombre);
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Tipo de actividad actualizado con éxito!"
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

        $registros = $this->model->listarTiposActividad();

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
        require 'view/public/reportes/TipoActividadReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Tipos de Actividad');

        // Attachment => false: se MUESTRA en el navegador
        $dompdf->stream("reporte_tipos_actividad_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}