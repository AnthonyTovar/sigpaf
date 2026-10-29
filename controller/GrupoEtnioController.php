<?php
// Controlador de grupos étnicos



require_once 'model/GrupoEtnioModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class GrupoEtnioController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new GrupoEtnioModel();
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
        $this->renderizar('view/GrupoEtnioView', ['grupos' => $grupos]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nomGrupoEtnio'] ?? '';
            $descripcion = $_POST['desGrupoEtnio'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($nombre, 'El nombre del grupo étnico es obligatorio.');
            ValidationHelper::exigirMaxLongitud($descripcion, 'La descripción es muy larga', 500);
            if (!empty($nombre)) {

                // Validar duplicados
                if (ValidationHelper::esDuplicado($this->model->listarGrupos(), 'nomGrupoEtnio', $nombre)) {
                    ValidationHelper::errorDuplicado('un grupo étnico', 'el nombre', $nombre);
                }

                $nuevoId = $this->model->registrarGrupo($nombre, $descripcion);

                if ($nuevoId) {

                    Logger::crear('Grupo Etnio', $nuevoId, $nombre);
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Grupo étnico registrado con éxito!",
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
                    "message" => "El nombre del grupo étnico es obligatorio."
                ]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idGrupoEtnio'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del grupo"]);
            exit;
        }


        $reg = $this->model->obtenerGrupoPorId($id);

        $resultado = $this->model->eliminarGrupo($id);

        if ($resultado === true) {

            Logger::eliminar('Grupo Etnio', $id, $reg ? $reg['nomGrupoEtnio'] : 'desconocido');
            echo json_encode(["status" => "success", "message" => "Grupo étnico eliminado correctamente"]);
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
            $id = $_POST['idGrupoEtnioEdit'] ?? '';
            $nombre = $_POST['nomGrupoEtnioEdit'] ?? '';
            $descripcion = $_POST['desGrupoEtnioEdit'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($id, 'No se recibió el grupo étnico a editar.');
            ValidationHelper::exigirRequerido($nombre, 'El nombre del grupo étnico es obligatorio.');
            ValidationHelper::exigirMaxLongitud($descripcion, 'La descripción es muy larga', 500);
            if (!empty($id) && !empty($nombre)) {

                // Validar duplicados
                if (ValidationHelper::esDuplicado($this->model->listarGrupos(), 'nomGrupoEtnio', $nombre, 'idGrupoEtnio', $id)) {
                    ValidationHelper::errorDuplicado('un grupo étnico', 'el nombre', $nombre);
                }

                $resultado = $this->model->actualizarGrupo($id, $nombre, $descripcion);

                if ($resultado) {

                    Logger::actualizar('Grupo Etnio', $id, $nombre);
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Grupo étnico actualizado con éxito!"
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
        require 'view/public/reportes/GrupoEtnioReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Grupos Étnicos');


        $dompdf->stream("reporte_grupos_etnicos_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}