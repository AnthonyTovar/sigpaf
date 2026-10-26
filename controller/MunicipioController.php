<?php
require_once 'model/MunicipioModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class MunicipioController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new MunicipioModel();
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

        $municipios = $this->model->listarMunicipios();
        $estados = $this->model->listarEstados();

        $this->renderizar('view/MunicipioView', [
            'municipios' => $municipios,
            'estados' => $estados
        ]);
    }

    // GUARDAR (POST)
    public function guardar()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $nombre = trim($_POST['nombreMunicipio'] ?? '');
                $idEstado = $_POST['idEstado'] ?? '';

                // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
                ValidationHelper::exigirRequerido($nombre, 'El nombre del municipio es obligatorio.');
                ValidationHelper::exigirRequerido($idEstado, 'Debe seleccionar el estado.');
                if (!empty($nombre) && !empty($idEstado)) {

                    // ===== VALIDACIÓN DE DUPLICADO (mensaje estándar del sistema) =====
                    if (ValidationHelper::esDuplicado($this->model->listarMunicipios(), 'nombreMunicipio', $nombre, null, null, ['idEstado' => $idEstado])) {
                        ValidationHelper::errorDuplicado('un municipio', 'el nombre en este estado', $nombre);
                    }

                    $nuevoId = $this->model->registrarMunicipio($nombre, $idEstado);

                    if ($nuevoId) {
                        // Bitacora: registro creado
                        Logger::crear('Municipio', $nuevoId, $nombre);
                        $nombreEstado = $this->model->obtenerNombreEstado($idEstado);
                        
                        echo json_encode([
                            "status" => "success",
                            "message" => "¡Municipio registrado con éxito!",
                            "id" => $nuevoId,
                            "nombre" => $nombre,
                            "estado" => $nombreEstado
                        ]);
                    } else {
                        echo json_encode(["status" => "error", "message" => "Error al insertar en la base de datos."]);
                    }
                } else {
                    echo json_encode(["status" => "error", "message" => "Todos los campos son obligatorios."]);
                }
            }
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Excepción del servidor: " . $e->getMessage()]);
        }
        exit();
    }

    // CONSULTAR PARA MODAL (GET)
    public function consultar()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        $id = $_GET['id'] ?? '';
        
        if (empty($id)) {
            echo json_encode(["error" => "ID no proporcionado"]);
            exit;
        }

        $municipio = $this->model->obtenerMunicipioPorId($id);
        
        if ($municipio) {
            echo json_encode($municipio);
        } else {
            echo json_encode(["error" => "Municipio no encontrado"]);
        }
        exit;
    }

    // EDITAR / ACTUALIZAR (POST)
    public function editar()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                $id = $_POST['idMunicipioEdit'] ?? '';
                $nombre = trim($_POST['nombreMunicipioEdit'] ?? '');
                $idEstado = $_POST['idEstadoEdit'] ?? '';

                // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
                ValidationHelper::exigirRequerido($id, 'No se recibió el municipio a editar.');
                ValidationHelper::exigirRequerido($nombre, 'El nombre del municipio es obligatorio.');
                ValidationHelper::exigirRequerido($idEstado, 'Debe seleccionar el estado.');
                if (!empty($id) && !empty($nombre) && !empty($idEstado)) {

                    // ===== VALIDACIÓN DE DUPLICADO (mensaje estándar del sistema) =====
                    if (ValidationHelper::esDuplicado($this->model->listarMunicipios(), 'nombreMunicipio', $nombre, 'idMunicipio', $id, ['idEstado' => $idEstado])) {
                        ValidationHelper::errorDuplicado('un municipio', 'el nombre en este estado', $nombre);
                    }

                    $resultado = $this->model->actualizarMunicipio($id, $nombre, $idEstado);

                    if ($resultado !== false) {
                        // Bitacora: registro actualizado
                        Logger::actualizar('Municipio', $id, $nombre);

                        echo json_encode(["status" => "success", "message" => "¡Municipio actualizado correctamente!"]);
                    } else {
                        echo json_encode(["status" => "error", "message" => "Error al intentar actualizar en la base de datos."]);
                    }
                } else {
                    echo json_encode(["status" => "error", "message" => "Datos incompletos para procesar la edición."]);
                }
            }
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Error técnico: " . $e->getMessage()]);
        }
        exit();
    }

    // ELIMINAR (POST)
    public function eliminar()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        $id = $_POST['idMunicipio'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del municipio."]);
            exit;
        }

        try {
            // Consulta el nombre antes de eliminar para la bitacora
            $reg = $this->model->obtenerMunicipioPorId($id);

            $resultado = $this->model->eliminarMunicipio($id);

            if ($resultado === true) {
                // Bitacora: registro eliminado
                Logger::eliminar('Municipio', $id, $reg ? $reg['nombreMunicipio'] : 'desconocido');
                echo json_encode(["status" => "success", "message" => "Municipio eliminado correctamente."]);
            } else if ($resultado === "link") {
                echo json_encode(["status" => "error", "message" => "No se puede eliminar: Existen datos vinculados a este municipio."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo eliminar el registro."]);
            }
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Error técnico: " . $e->getMessage()]);
        }
        exit;
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

        $registros = $this->model->listarMunicipios();

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
        require 'view/public/reportes/MunicipioReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Municipios');

        // Attachment => false: se MUESTRA en el navegador
        $dompdf->stream("reporte_municipios_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}