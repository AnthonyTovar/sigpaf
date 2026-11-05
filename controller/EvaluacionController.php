<?php
// Controlador del módulo de evaluación de actividades

require_once 'model/EvaluacionModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';
require_once 'RolHelper.php';

class EvaluacionController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new EvaluacionModel();
    }

    private function renderizar($nombreVista, $datos = [])
    {
        extract($datos);
        ob_start();
        require $nombreVista . '.php';
        $content = ob_get_clean();
        require 'view/Layout.php';
    }

    private function leerFiltros()
    {
        $fecha = trim($_GET['fecha'] ?? '');
        if ($fecha !== '' && !ValidationHelper::fecha($fecha)) {
            $fecha = '';
        }
        $anio = trim($_GET['anio'] ?? '');
        if ($anio !== '' && (!ctype_digit($anio) || (int) $anio < 2000 || (int) $anio > 2100)) {
            $anio = '';
        }
        return [
            'fecha'     => $fecha,
            'anio'      => $anio,
            'idEstatus' => trim($_GET['idEstatus'] ?? '')
        ];
    }

    // Listado de actividades para evaluar - solo super usuario
    public function listar()
    {
        $filtros = $this->leerFiltros();

        $this->renderizar('view/EvaluacionView', [
            'actividades' => $this->model->listarActividadesEvaluacion($filtros),
            'filtros'     => $filtros,
            'estatus'     => $this->model->obtenerEstatus(),
            'anios'       => $this->model->obtenerAnios()
        ]);
    }

    // Registrar evaluación (nota + marca de cambios) - solo super usuario
    public function guardar()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["status" => "error", "message" => "Método no permitido."]);
            exit();
        }

        try {
            $idActividad = trim($_POST['idActividad'] ?? '');
            $nota = trim($_POST['notaEvaluacion'] ?? '');
            $requiereCambio = isset($_POST['requiereCambio']) && $_POST['requiereCambio'] === '1';

            ValidationHelper::exigirRequerido($idActividad, 'No se recibió la actividad.');

            $actividad = $this->model->obtenerActividad($idActividad);
            if (!$actividad) {
                ValidationHelper::error('La actividad indicada no existe.');
            }

            if ($requiereCambio && $nota === '') {
                ValidationHelper::error('Si la actividad requiere cambios, debe escribir la nota con las observaciones para el planificador.');
            }
            ValidationHelper::exigirMaxLongitud($nota, 'La nota de evaluación no puede exceder los 500 caracteres.', 500);

            if ($nota === '') {
                $nota = 'Evaluada sin observaciones.';
            }

            $nuevoId = $this->model->registrarEvaluacion(
                $idActividad, $nota, $requiereCambio, $_SESSION['usuario_id'] ?? null
            );

            if ($nuevoId) {
                Logger::crear('Evaluación', $nuevoId, $actividad['nombreActividad']);
                echo json_encode([
                    "status" => "success",
                    "message" => $requiereCambio
                        ? "Evaluación registrada. El planificador verá la nota con los cambios requeridos."
                        : "Evaluación registrada sin cambios requeridos.",
                    "id" => $nuevoId
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error al registrar la evaluación."]);
            }
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Error del servidor: " . $e->getMessage()]);
        }
        exit();
    }

    // Historial de evaluaciones de una actividad - solo super usuario
    public function historial()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $idActividad = trim($_GET['id'] ?? '');
        $actividad = $this->model->obtenerActividad($idActividad);

        if (!$actividad) {
            echo json_encode(["status" => "error", "message" => "La actividad indicada no existe."]);
            exit();
        }

        echo json_encode([
            "status" => "success",
            "nombreActividad" => $actividad['nombreActividad'],
            "evaluaciones" => $this->model->obtenerEvaluacionesActividad($idActividad)
        ]);
        exit();
    }

    // El planificador confirma que hizo los cambios pedidos en la evaluación.
    // Solo puede confirmarlo quien planificó la actividad.
    public function confirmarCambios()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["status" => "error", "message" => "Método no permitido."]);
            exit();
        }

        $idEvaluacion = trim($_POST['idEvaluacion'] ?? '');
        $evaluacion = $idEvaluacion !== '' ? $this->model->obtenerEvaluacionPorId($idEvaluacion) : null;

        if (!$evaluacion) {
            echo json_encode(["status" => "error", "message" => "La evaluación indicada no existe."]);
            exit();
        }

        $creador = $evaluacion['idUsuarioCreador'] ?? null;
        $esPropietario = ($creador !== null && $creador !== '')
            ? ($creador === ($_SESSION['usuario_id'] ?? null))
            : RolHelper::esSuperUsuario();

        if (!$esPropietario) {
            Logger::registrar('ACCESS_DENIED', 'Evaluación', $idEvaluacion,
                "Intento confirmar cambios de la evaluación $idEvaluacion sin ser quien planificó la actividad");
            echo json_encode(["status" => "error", "message" => "Acción no permitida: solo quien planificó la actividad puede confirmar los cambios."]);
            exit();
        }

        if ((int) $evaluacion['requiereCambio'] !== 1) {
            echo json_encode(["status" => "error", "message" => "Esta evaluación no requiere cambios."]);
            exit();
        }

        if ((int) $evaluacion['confirmadoCambios'] === 1) {
            echo json_encode(["status" => "success", "message" => "Los cambios ya habían sido confirmados."]);
            exit();
        }

        if ($this->model->confirmarCambios($idEvaluacion)) {
            Logger::registrar('UPDATE', 'Evaluación', $idEvaluacion,
                "Confirmo que realizo los cambios pedidos en la evaluación $idEvaluacion (" . $evaluacion['nombreActividad'] . ")");
            echo json_encode(["status" => "success", "message" => "Cambios confirmados. El super usuario verá que ya realizaste los ajustes."]);
        } else {
            echo json_encode(["status" => "error", "message" => "No se pudo registrar la confirmación."]);
        }
        exit();
    }

    // Notas de evaluación para el planificador de la actividad.
    // Solo las ve quien planificó la actividad (o el super usuario si la actividad
    // es antigua y no tiene creador registrado).
    public function notasActividad()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $idActividad = trim($_GET['id'] ?? '');
        $actividad = $this->model->obtenerActividad($idActividad);

        if (!$actividad) {
            echo json_encode(["status" => "error", "message" => "La actividad indicada no existe."]);
            exit();
        }

        $creador = $actividad['idUsuarioCreador'] ?? null;
        $esPropietario = ($creador !== null && $creador !== '')
            ? ($creador === ($_SESSION['usuario_id'] ?? null))
            : RolHelper::esSuperUsuario();

        if (!$esPropietario) {
            Logger::registrar('ACCESS_DENIED', 'Evaluación', $idActividad,
                "Intento ver las notas de evaluación de la actividad $idActividad sin ser quien la planificó");
            echo json_encode(["status" => "error", "message" => "Acción no permitida: las notas de evaluación solo las ve quien planificó la actividad."]);
            exit();
        }

        echo json_encode([
            "status" => "success",
            "nombreActividad" => $actividad['nombreActividad'],
            "notas" => $this->model->obtenerEvaluacionesActividad($idActividad)
        ]);
        exit();
    }
}
