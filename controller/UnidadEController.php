<?php
// Controlador de unidades ejecutoras



require_once 'model/UnidadEModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class UnidadEController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new UnidadEModel();
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

        $unidad = $this->model->listarUnidadE();

        $this->renderizar('view/UnidadEView', ['unidad' => $unidad]);
    }


    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = trim($_POST['nomUnidadEjecutora'] ?? '');
            $desc = trim($_POST['desUnidadEjecutora'] ?? '');

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($nombre, 'El nombre de la unidad ejecutora es obligatorio.');
            ValidationHelper::exigirMaxLongitud($desc, 'La descripción es muy larga', 500);
            if (empty($nombre)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "El nombre de la Unidad Ejecutora es obligatorio."
                ]);
                exit();
            }


            if (preg_match('/(.)\1{2,}/u', $nombre) || preg_match('/(.)\1{2,}/u', $desc)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se permiten 3 o más caracteres iguales consecutivos."
                ]);
                exit();
            }


            $duplicado = $this->model->existeNombreODescripcion($nombre, $desc);
            if ($duplicado === 'nombre') {
                echo json_encode([
                    "status" => "error",
                    "message" => "Ya existe una unidad ejecutora con el nombre '{$nombre}'. Verifique e inténtelo con otro."
                ]);
                exit();
            } else if ($duplicado === 'descripcion') {
                echo json_encode([
                    "status" => "error",
                    "message" => "Ya existe una unidad ejecutora con la descripción '{$desc}'. Verifique e inténtelo con otro."
                ]);
                exit();
            }


            $idNuevo = $this->model->registrarUnidadE($nombre, $desc);

            if ($idNuevo) {

                Logger::crear('Unidad Ejecutora', $idNuevo, $nombre);

                echo json_encode([
                    "status" => "success",
                    "message" => "¡Unidad Ejecutora registrada con éxito!",
                    "id" => $idNuevo,
                    "nombre" => $nombre,
                    "descripcion" => $desc
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

        $id = $_POST['idUnidadEjecutora'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID de la Unidad Ejecutora"]);
            exit;
        }


        $reg = $this->model->obtenerUnidadEPorId($id);

        $resultado = $this->model->eliminarUnidadE($id);

        if ($resultado === true) {

            Logger::eliminar('Unidad Ejecutora', $id, $reg ? $reg['nomUnidadEjecutora'] : 'desconocido');
            echo json_encode(["status" => "success", "message" => "Unidad Ejecutora eliminada correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Esta Unidad Ejecutora está asignada a empleados activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar la Unidad Ejecutora."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $unidadE = $this->model->obtenerUnidadEPorId($id);
        echo json_encode($unidadE); 
        exit;
    }


    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = trim($_POST['idUnidadEjecutoraEdit'] ?? '');
            $nombre = trim($_POST['nomUnidadEjecutoraEdit'] ?? '');
            $desc = trim($_POST['desUnidadEjecutoraEdit'] ?? '');

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($id, 'No se recibió la unidad ejecutora a editar.');
            ValidationHelper::exigirRequerido($nombre, 'El nombre de la unidad ejecutora es obligatorio.');
            ValidationHelper::exigirMaxLongitud($desc, 'La descripción es muy larga', 500);
            if (empty($id) || empty($nombre)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Datos incompletos."
                ]);
                exit();
            }


            if (preg_match('/(.)\1{2,}/u', $nombre) || preg_match('/(.)\1{2,}/u', $desc)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se permiten 3 o más caracteres iguales consecutivos."
                ]);
                exit();
            }


            $duplicado = $this->model->existeNombreODescripcion($nombre, $desc, $id);
            if ($duplicado === 'nombre') {
                echo json_encode([
                    "status" => "error",
                    "message" => "Ya existe otra unidad ejecutora con el nombre '{$nombre}'. Verifique e inténtelo con otro."
                ]);
                exit();
            } else if ($duplicado === 'descripcion') {
                echo json_encode([
                    "status" => "error",
                    "message" => "Ya existe una unidad ejecutora con la descripción '{$desc}'. Verifique e inténtelo con otro."
                ]);
                exit();
            }


            $resultado = $this->model->actualizarUnidadE($id, $nombre, $desc);

            if ($resultado) {

                Logger::actualizar('Unidad Ejecutora', $id, $nombre);
                echo json_encode([
                    "status" => "success",
                    "message" => "¡Unidad Ejecutora actualizada con éxito!"
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

        $registros = $this->model->listarUnidadE();


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
        require 'view/public/reportes/UnidadEReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Unidades Ejecutoras');


        $dompdf->stream("reporte_unidades_ejecutoras_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}