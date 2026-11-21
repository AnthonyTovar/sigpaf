<?php
// Controlador de lugares de actividad



require_once 'model/LugarActividadModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class LugarActividadController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new LugarActividadModel();
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

        $lugares = $this->model->listarLugares();
        $parroquias = $this->model->listarParroquias();
        $estados = $this->model->listarEstados();
        $this->renderizar('view/LugarActividadView', [
            'lugares' => $lugares,
            'parroquias' => $parroquias,
            'estados' => $estados
        ]);
    }

    // AJAX: municipios de un estado (cascada Estado -> Municipio -> Parroquia)
    public function municipiosPorEstado()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        $idEstado = $_GET['idEstado'] ?? '';
        if ($idEstado === '') {
            echo json_encode(["status" => "error", "message" => "No se recibió el estado."]);
            exit();
        }
        echo json_encode([
            "status" => "success",
            "municipios" => $this->model->listarMunicipiosPorEstado($idEstado)
        ]);
        exit();
    }

    // AJAX: parroquias de un municipio (cascada Estado -> Municipio -> Parroquia)
    public function parroquiasPorMunicipio()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        $idMunicipio = $_GET['idMunicipio'] ?? '';
        if ($idMunicipio === '') {
            echo json_encode(["status" => "error", "message" => "No se recibió el municipio."]);
            exit();
        }
        echo json_encode([
            "status" => "success",
            "parroquias" => $this->model->listarParroquiasPorMunicipio($idMunicipio)
        ]);
        exit();
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nomLugar = $_POST['nomLugarActividad'] ?? '';
            $desLugar = $_POST['desLugarActividad'] ?? '';
            $direccion = $_POST['direccion'] ?? '';
            $esSede = ($_POST['esSede'] ?? '0') === '1';
            $idParroquia = $_POST['idParroquia'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($nomLugar, 'El nombre del lugar es obligatorio.');
            ValidationHelper::exigirMaxLongitud($desLugar, 'La descripción del lugar es muy larga', 255);
            ValidationHelper::exigirRequerido($direccion, 'La dirección es obligatoria.');
            ValidationHelper::exigirRequerido($idParroquia, 'Debe seleccionar la parroquia.');
            if (empty($nomLugar) || empty($direccion) || empty($idParroquia)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Los campos obligatorios son: Nombre del Lugar, Direccion y Parroquia."
                ]);
                exit();
            }

            $nuevoId = $this->model->registrarLugar($nomLugar, $desLugar, $direccion, $esSede, $idParroquia);

            if ($nuevoId === 'nombre_duplicado') {
                echo json_encode([
                    "status" => "error",
                    "message" => "Ya existe un lugar con el nombre '{$nomLugar}' en esa parroquia y municipio. Puede registrarlo si es en otra parroquia o municipio."
                ]);
            } else if ($nuevoId) {

     Logger::crear('Lugar de Actividad', $nuevoId, $nomLugar);
                echo json_encode([
                    "status" => "success",
                    "message" => "Lugar de actividad registrado con exito!",
                    "id" => $nuevoId,
                    "nomLugar" => $nomLugar,
                    "desLugar" => $desLugar,
                    "direccion" => $direccion,
                    "esSede" => $esSede,
                    "idParroquia" => $idParroquia
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

        $id = $_POST['idLugarActividad'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibio el ID del lugar"]);
            exit;
        }


        $reg = $this->model->obtenerLugarPorId($id);

        $resultado = $this->model->eliminarLugar($id);

        if ($resultado === true) {

            Logger::eliminar('Lugar de Actividad', $id, $reg ? $reg['nomLugarActividad'] : 'desconocido');
            echo json_encode(["status" => "success", "message" => "Lugar eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este lugar esta asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el lugar."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $lugar = $this->model->obtenerLugarPorId($id);
        echo json_encode($lugar);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idLugarActividadEdit'] ?? '';
            $nomLugar = $_POST['nomLugarActividadEdit'] ?? '';
            $desLugar = $_POST['desLugarActividadEdit'] ?? '';
            $direccion = $_POST['direccionEdit'] ?? '';
            $esSede = ($_POST['esSedeEdit'] ?? '0') === '1';
            $idParroquia = $_POST['idParroquiaEdit'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($id, 'No se recibió el lugar a editar.');
            ValidationHelper::exigirRequerido($nomLugar, 'El nombre del lugar es obligatorio.');
            ValidationHelper::exigirMaxLongitud($desLugar, 'La descripción del lugar es muy larga', 255);
            ValidationHelper::exigirRequerido($direccion, 'La dirección es obligatoria.');
            ValidationHelper::exigirRequerido($idParroquia, 'Debe seleccionar la parroquia.');
            if (empty($id) || empty($nomLugar) || empty($direccion) || empty($idParroquia)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Los campos obligatorios son: Nombre del Lugar, Direccion y Parroquia."
                ]);
                exit();
            }

            $resultado = $this->model->actualizarLugar($id, $nomLugar, $desLugar, $direccion, $esSede, $idParroquia);

            if ($resultado === 'nombre_duplicado') {
                echo json_encode([
                    "status" => "error",
                    "message" => "Ya existe otro lugar con el nombre '{$nomLugar}' en esa parroquia y municipio. Puede registrarlo si es en otra parroquia o municipio."
                ]);
            } else if ($resultado) {

     Logger::actualizar('Lugar de Actividad', $id, $nomLugar);
                echo json_encode([
                    "status" => "success",
                    "message" => "Lugar de actividad actualizado con exito!"
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

        $registros = $this->model->listarLugares();


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
        require 'view/public/reportes/LugarActividadReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'landscape');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Lugares de Actividad');


        $dompdf->stream("reporte_lugares_actividad_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}