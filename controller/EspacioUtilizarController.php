<?php
require_once 'model/EspacioUtilizarModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class EspacioUtilizarController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new EspacioUtilizarModel();
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

        $espacios = $this->model->listarEspacios();
        $this->renderizar('view/EspacioUtilizarView', [
            'espacios' => $espacios
        ]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombreEspacio = $_POST['nombreEspacioUtilizar'] ?? '';
            $descEspacio = $_POST['descEspacio'] ?? '';
            $capacidad = $_POST['capacidad'] ?? '';

            // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
            ValidationHelper::exigirRequerido($nombreEspacio, 'El nombre del espacio es obligatorio.');
            ValidationHelper::exigirMaxLongitud($descEspacio, 'La descripción es muy larga', 255);
            if (!(ValidationHelper::enteroMin($capacidad, 1))) {
                ValidationHelper::error('La capacidad debe ser un número mayor a 0.');
            }
            if (empty($nombreEspacio) || $capacidad === '') {
                echo json_encode([
                    "status" => "error",
                    "message" => "Los campos obligatorios son: Nombre del Espacio y Capacidad."
                ]);
                exit();
            }

            $nuevoId = $this->model->registrarEspacio($nombreEspacio, $descEspacio, $capacidad);

            if ($nuevoId) {
                // Bitacora: registro creado
                Logger::crear('Espacio a Utilizar', $nuevoId, $nombreEspacio);
                echo json_encode([
                    "status" => "success",
                    "message" => "¡Espacio registrado con éxito!",
                    "id" => $nuevoId,
                    "nombreEspacio" => $nombreEspacio,
                    "descEspacio" => $descEspacio,
                    "capacidad" => $capacidad
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

        $id = $_POST['idEspacioUtilizar'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del espacio"]);
            exit;
        }

        // Consulta el nombre antes de eliminar para la bitacora
        $reg = $this->model->obtenerEspacioPorId($id);

        $resultado = $this->model->eliminarEspacio($id);

        if ($resultado === true) {
            // Bitacora: registro eliminado
            Logger::eliminar('Espacio a Utilizar', $id, $reg ? $reg['nombreEspacioUtilizar'] : 'desconocido');
            echo json_encode(["status" => "success", "message" => "Espacio eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este espacio está asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el espacio."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $espacio = $this->model->obtenerEspacioPorId($id);
        echo json_encode($espacio);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idEspacioUtilizarEdit'] ?? '';
            $nombreEspacio = $_POST['nombreEspacioUtilizarEdit'] ?? '';
            $descEspacio = $_POST['descEspacioEdit'] ?? '';
            $capacidad = $_POST['capacidadEdit'] ?? '';

            // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
            ValidationHelper::exigirRequerido($id, 'No se recibió el espacio a editar.');
            ValidationHelper::exigirRequerido($nombreEspacio, 'El nombre del espacio es obligatorio.');
            ValidationHelper::exigirMaxLongitud($descEspacio, 'La descripción es muy larga', 255);
            if (!(ValidationHelper::enteroMin($capacidad, 1))) {
                ValidationHelper::error('La capacidad debe ser un número mayor a 0.');
            }
            if (empty($id) || empty($nombreEspacio) || $capacidad === '') {
                echo json_encode([
                    "status" => "error",
                    "message" => "Los campos obligatorios son: Nombre del Espacio y Capacidad."
                ]);
                exit();
            }

            $resultado = $this->model->actualizarEspacio($id, $nombreEspacio, $descEspacio, $capacidad);

            if ($resultado) {
                // Bitacora: registro actualizado
                Logger::actualizar('Espacio a Utilizar', $id, $nombreEspacio);
                echo json_encode([
                    "status" => "success",
                    "message" => "¡Espacio actualizado con éxito!"
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

    // Exporta el listado a PDF con Dompdf (se muestra en el navegador)
    public function exportarPdf()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        require_once 'lib/dompdf/autoload.inc.php';
        require_once 'Logger.php';

        $registros = $this->model->listarEspacios();

        ob_start();
        require 'view/public/reportes/EspacioUtilizarReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Espacios a Utilizar');

        // Attachment => false: se MUESTRA en el navegador
        $dompdf->stream("reporte_espacios_utilizar_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}