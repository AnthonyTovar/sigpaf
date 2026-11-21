<?php
// Controlador de tipos de usuario



require_once 'model/TipoUsuarioModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

class TipoUsuarioController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new TipoUsuarioModel();
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

        $tipoUsuarios = $this->model->listarTipoUsuarios();
        $this->renderizar('view/TipoUsuarioView', [
            'tipoUsuarios' => $tipoUsuarios
        ]);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $rolUsuario = $_POST['rolUsuario'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($rolUsuario, 'El nombre del rol es obligatorio.');
            if (!empty($rolUsuario)) {

                // Validar duplicados
                if (ValidationHelper::esDuplicado($this->model->listarTipoUsuarios(), 'rolUsuario', $rolUsuario)) {
                    ValidationHelper::errorDuplicado('un tipo de usuario', 'el nombre', $rolUsuario);
                }

                $nuevoId = $this->model->registrarTipoUsuario($rolUsuario);

                if ($nuevoId) {

                    Logger::crear('Tipo de Usuario', $nuevoId, $rolUsuario);
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Tipo de usuario registrado con éxito!",
                        "id" => $nuevoId,
                        "rol" => $rolUsuario
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
                    "message" => "El campo Rol de Usuario es obligatorio."
                ]);
            }
            exit();
        }
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = $_POST['idTipoUsuario'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID del tipo de usuario"]);
            exit;
        }


        $reg = $this->model->obtenerTipoUsuarioPorId($id);

        $resultado = $this->model->eliminarTipoUsuario($id);

        if ($resultado === true) {

            Logger::eliminar('Tipo de Usuario', $id, $reg ? $reg['rolUsuario'] : 'desconocido');
            echo json_encode(["status" => "success", "message" => "Tipo de usuario eliminado correctamente"]);
        } else if ($resultado === "link") {
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: Este tipo de usuario está asignado a registros activos."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error interno al intentar eliminar el tipo de usuario."]);
        }
        exit;
    }

    public function consultar()
    {
        $id = $_GET['id'] ?? '';
        $tipoUsuario = $this->model->obtenerTipoUsuarioPorId($id);
        echo json_encode($tipoUsuario);
        exit;
    }

    public function editar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['idTipoUsuarioEdit'] ?? '';
            $rolUsuario = $_POST['rolUsuarioEdit'] ?? '';

            // Validación unificada (validationhelper)
            ValidationHelper::exigirRequerido($id, 'No se recibió el tipo de usuario a editar.');
            ValidationHelper::exigirRequerido($rolUsuario, 'El nombre del rol es obligatorio.');
            if (!empty($id) && !empty($rolUsuario)) {

                // Validar duplicados
                if (ValidationHelper::esDuplicado($this->model->listarTipoUsuarios(), 'rolUsuario', $rolUsuario, 'idTipoUsuario', $id)) {
                    ValidationHelper::errorDuplicado('un tipo de usuario', 'el nombre', $rolUsuario);
                }

                $resultado = $this->model->actualizarTipoUsuario($id, $rolUsuario);

                if ($resultado) {

                    Logger::actualizar('Tipo de Usuario', $id, $rolUsuario);
                    echo json_encode([
                        "status" => "success",
                        "message" => "¡Tipo de usuario actualizado con éxito!"
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

        $registros = $this->model->listarTipoUsuarios();


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
        require 'view/public/reportes/TipoUsuarioReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Configuracion', null, 'Generó PDF de Tipos de Usuario');


        $dompdf->stream("reporte_tipos_usuario_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}