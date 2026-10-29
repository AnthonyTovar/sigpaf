<?php
// Controlador de grupos etarios



require_once 'model/GrupoEtarioModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class GrupoEtarioController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new GrupoEtarioModel();
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

        $grupos = $this->model->listarGrupos();
        $this->renderizar('view/GrupoEtarioView', ['grupos' => $grupos]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nomGrupoEtareo'] ?? '';
            $edadMin = $_POST['edadMin'] ?? '';
            $edadMax = $_POST['edadMax'] ?? '';
            $descripcion = $_POST['descGrupoEtareo'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($nombre, 'El nombre del grupo etario es obligatorio.');
            if (!(ValidationHelper::enteroMin($edadMin, 0))) {
                ValidationHelper::error('La edad mínima no es válida.');
            }
            if (!(ValidationHelper::enteroMin($edadMax, 0))) {
                ValidationHelper::error('La edad máxima no es válida.');
            }
            if (!($edadMax >= $edadMin)) {
                ValidationHelper::error('La edad máxima no puede ser menor que la edad mínima.');
            }
            ValidationHelper::exigirMaxLongitud($descripcion, 'La descripción es muy larga', 250);
            if (!empty($nombre) && $edadMin !== '' && $edadMax !== '') {

                // Validar duplicados
                if (ValidationHelper::esDuplicado($this->model->listarGrupos(), 'nomGrupoEtareo', $nombre)) {
                    ValidationHelper::errorDuplicado('un grupo etario', 'el nombre', $nombre);
                }

                $nuevoId = $this->model->registrarGrupo($nombre, $edadMin, $edadMax, $descripcion);

                if ($nuevoId) {

                    Logger::crear('Grupo Etario', $nuevoId, $nombre);
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Grupo etario registrado con éxito!",
                        "id" => $nuevoId,
                        "nombre" => $nombre,
                        "edadMin" => $edadMin,
                        "edadMax" => $edadMax,
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
                    "message" => "Los campos obligatorios son: Nombre, Edad Mínima y Edad Máxima."
                ]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idGrupoEtareo'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del grupo"]);
            exit;
        }


        $reg = $this->model->obtenerGrupoPorId($id);

        $resultado = $this->model->eliminarGrupo($id);

        if ($resultado === true) {

            Logger::eliminar('Grupo Etario', $id, $reg ? $reg['nomGrupoEtareo'] : 'desconocido');
            echo json_encode(["status" => "success", "message" => "Grupo etario eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este grupo está asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el grupo."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $grupo = $this->model->obtenerGrupoPorId($id);
        header('Content-Type: application/json');
        echo json_encode($grupo);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idGrupoEtareoEdit'] ?? '';
            $nombre = $_POST['nomGrupoEtareoEdit'] ?? '';
            $edadMin = $_POST['edadMinEdit'] ?? '';
            $edadMax = $_POST['edadMaxEdit'] ?? '';
            $descripcion = $_POST['descGrupoEtareoEdit'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($id, 'No se recibió el grupo etario a editar.');
            ValidationHelper::exigirRequerido($nombre, 'El nombre del grupo etario es obligatorio.');
            if (!(ValidationHelper::enteroMin($edadMin, 0))) {
                ValidationHelper::error('La edad mínima no es válida.');
            }
            if (!(ValidationHelper::enteroMin($edadMax, 0))) {
                ValidationHelper::error('La edad máxima no es válida.');
            }
            if (!($edadMax >= $edadMin)) {
                ValidationHelper::error('La edad máxima no puede ser menor que la edad mínima.');
            }
            ValidationHelper::exigirMaxLongitud($descripcion, 'La descripción es muy larga', 250);
            if (!empty($id) && !empty($nombre) && $edadMin !== '' && $edadMax !== '') {

                // Validar duplicados
                if (ValidationHelper::esDuplicado($this->model->listarGrupos(), 'nomGrupoEtareo', $nombre, 'idGrupoEtareo', $id)) {
                    ValidationHelper::errorDuplicado('un grupo etario', 'el nombre', $nombre);
                }

                $resultado = $this->model->actualizarGrupo($id, $nombre, $edadMin, $edadMax, $descripcion);

                if ($resultado) {

                    Logger::actualizar('Grupo Etario', $id, $nombre);
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Grupo etario actualizado con éxito!"
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

        $registros = $this->model->listarGrupos();


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
        require 'view/public/reportes/GrupoEtarioReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Grupos Etarios');


        $dompdf->stream("reporte_grupos_etarios_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}