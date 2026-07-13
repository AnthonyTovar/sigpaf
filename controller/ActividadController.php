<?php
require_once 'model/ActividadModel.php';

class ActividadController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new ActividadModel();
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

        $actividades = $this->model->listarActividades();
        $this->renderizar('view/ActividadView', [
            'actividades' => $actividades
        ]);
    }

    public function nuevo()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        // Cargar todos los datos maestros para los selects
        $datosMaestros = [
            'estrategias' => $this->model->obtenerEstrategiasDesarrollo(),
            'tiposActividad' => $this->model->obtenerTiposActividad(),
            'vertices' => $this->model->obtenerVertices(),
            'areasEspecificas' => $this->model->obtenerAreasEspecificas(),
            'gruposEtarios' => $this->model->obtenerGruposEtarios(),
            'gruposEtnicos' => $this->model->obtenerGruposEtnicos(),
            'unidadesMedida' => $this->model->obtenerUnidadesMedida(),
            'lugares' => $this->model->obtenerLugaresActividad(),
            'espacios' => $this->model->obtenerEspaciosUtilizar(),
            'horarios' => $this->model->obtenerHorarios(),
            'empleados' => $this->model->obtenerEmpleados(),
            'docentes' => $this->model->obtenerDocentes(),
            'estatus' => $this->model->obtenerEstatus(),
            'tiposEntrega' => $this->model->obtenerTiposEntrega(),
            'fechasOcupadas' => $this->model->obtenerFechasOcupadas()
        ];

        $this->renderizar('view/ActividadNuevoView', $datosMaestros);
    }

    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $datos = [
                'nombreActividad' => $_POST['nombreActividad'] ?? '',
                'fechainicioActividad' => $_POST['fechainicioActividad'] ?? '',
                'fechafinActividad' => $_POST['fechafinActividad'] ?? '',
                'cantSesionesPlanificada' => $_POST['cantSesionesPlanificada'] ?? 1,
                'objetivoActividad' => $_POST['objetivoActividad'] ?? '',
                'descActividad' => $_POST['descActividad'] ?? '',
                'cantPersoAtender' => $_POST['cantPersoAtender'] ?? 0,
                'observacion' => $_POST['observacion'] ?? '',
                'idTipoActividad' => $_POST['idTipoActividad'] ?? '',
                'idVertice' => $_POST['idVertice'] ?? '',
                'idAreaE' => $_POST['idAreaE'] ?? '',
                'idEmpleado' => $_POST['idEmpleado'] ?? '',
                'idUnidadMedida' => $_POST['idUnidadMedida'] ?? '',
                'idGrupoEtnio' => $_POST['idGrupoEtnio'] ?? '',
                'idDocente' => $_POST['idDocente'] ?? '',
                'idEstatus' => $_POST['idEstatus'] ?? '',
                'idHorario' => $_POST['idHorario'] ?? '',
                'idEstDesarrollo' => $_POST['idEstDesarrollo'] ?? '',
                'idLugarActividad' => $_POST['idLugarActividad'] ?? '',
                'idEspacioUtilizar' => $_POST['idEspacioUtilizar'] ?? null,
                'gruposEtarios' => $_POST['gruposEtarios'] ?? [],
                'fechasSesiones' => $_POST['fechasSesiones'] ?? [],
                'idTipEntrega' => $_POST['idTipEntrega'] ?? 'TE0001'
            ];

            // Validaciones básicas
            if (empty($datos['nombreActividad'])) {
                echo json_encode(["status" => "error", "message" => "El nombre de la actividad es obligatorio."]);
                exit();
            }
            if (empty($datos['fechainicioActividad']) || empty($datos['fechafinActividad'])) {
                echo json_encode(["status" => "error", "message" => "Las fechas son obligatorias."]);
                exit();
            }
            if (empty($datos['idTipoActividad']) || empty($datos['idVertice']) || empty($datos['idAreaE'])) {
                echo json_encode(["status" => "error", "message" => "Debe completar todos los campos de clasificación."]);
                exit();
            }
            if (empty($datos['idEmpleado']) || empty($datos['idDocente'])) {
                echo json_encode(["status" => "error", "message" => "Debe seleccionar responsables."]);
                exit();
            }
            if (empty($datos['idLugarActividad']) || empty($datos['idHorario'])) {
                echo json_encode(["status" => "error", "message" => "Debe seleccionar lugar y horario."]);
                exit();
            }

            $nuevoId = $this->model->registrarActividad($datos);

            if ($nuevoId) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Actividad registrada con éxito!",
                    "id" => $nuevoId
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Hubo un error al registrar la actividad."
                ]);
            }
            exit();
        }
    }

    public function obtenerHorariosOcupados()
    {
        header('Content-Type: application/json');
        $fecha = $_GET['fecha'] ?? '';
        $idEspacio = $_GET['idEspacio'] ?? null;
        $idLugar = $_GET['idLugar'] ?? null;

        if (empty($fecha)) {
            echo json_encode([]);
            exit();
        }

        $horariosOcupados = $this->model->obtenerHorariosOcupados($fecha, $idEspacio, $idLugar);
        echo json_encode($horariosOcupados);
        exit();
    }

    public function eliminar()
    {
        header('Content-Type: application/json');
        $id = $_POST['idActividad'] ?? null;

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "No se recibió el ID"]);
            exit;
        }

        $resultado = $this->model->eliminarActividad($id);

        if ($resultado) {
            echo json_encode(["status" => "success", "message" => "Actividad eliminada correctamente"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al eliminar la actividad."]);
        }
        exit;
    }

    public function consultar()
    {
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? '';
        
        $actividad = $this->model->obtenerActividadPorId($id);
        $lugar = $this->model->obtenerLugarActividadPorId($id);
        $gruposEtarios = $this->model->obtenerGruposEtariosActividad($id);
        $seguimiento = $this->model->obtenerSeguimientoActividad($id);

        echo json_encode([
            'actividad' => $actividad,
            'lugar' => $lugar,
            'gruposEtarios' => $gruposEtarios,
            'seguimiento' => $seguimiento
        ]);
        exit;
    }


    public function editar()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $id = $_GET['id'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            $datos = [
                'idActividad' => $_POST['idActividad'] ?? '',
                'nombreActividad' => $_POST['nombreActividad'] ?? '',
                'fechainicioActividad' => $_POST['fechainicioActividad'] ?? '',
                'fechafinActividad' => $_POST['fechafinActividad'] ?? '',
                'cantSesionesPlanificada' => $_POST['cantSesionesPlanificada'] ?? 1,
                'objetivoActividad' => $_POST['objetivoActividad'] ?? '',
                'descActividad' => $_POST['descActividad'] ?? '',
                'cantPersoAtender' => $_POST['cantPersoAtender'] ?? 0,
                'observacion' => $_POST['observacion'] ?? '',
                'idTipoActividad' => $_POST['idTipoActividad'] ?? '',
                'idVertice' => $_POST['idVertice'] ?? '',
                'idAreaE' => $_POST['idAreaE'] ?? '',
                'idEmpleado' => $_POST['idEmpleado'] ?? '',
                'idUnidadMedida' => $_POST['idUnidadMedida'] ?? '',
                'idGrupoEtnio' => $_POST['idGrupoEtnio'] ?? '',
                'idDocente' => $_POST['idDocente'] ?? '',
                'idEstatus' => $_POST['idEstatus'] ?? '',
                'idHorario' => $_POST['idHorario'] ?? '',
                'idEstDesarrollo' => $_POST['idEstDesarrollo'] ?? '',
                'idLugarActividad' => $_POST['idLugarActividad'] ?? '',
                'idEspacioUtilizar' => $_POST['idEspacioUtilizar'] ?? null,
                'gruposEtarios' => $_POST['gruposEtarios'] ?? [],
                'fechasSesiones' => $_POST['fechasSesiones'] ?? [],
                'idTipEntrega' => $_POST['idTipEntrega'] ?? 'TE0001'
            ];

            $resultado = $this->model->editarActividad($datos);

            if ($resultado) {
                echo json_encode(["status" => "success", "message" => "Actividad actualizada con éxito!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error al actualizar la actividad."]);
            }
            exit();
        }

        // GET: Mostrar formulario de edición
        $actividad = $this->model->obtenerActividadPorId($id);
        if (!$actividad) {
            header("Location: index.php?action=actividades");
            exit();
        }

        $datosMaestros = [
            'actividad' => $actividad,
            'estrategias' => $this->model->obtenerEstrategiasDesarrollo(),
            'tiposActividad' => $this->model->obtenerTiposActividad(),
            'vertices' => $this->model->obtenerVertices(),
            'areasEspecificas' => $this->model->obtenerAreasEspecificas(),
            'gruposEtarios' => $this->model->obtenerGruposEtarios(),
            'gruposEtnicos' => $this->model->obtenerGruposEtnicos(),
            'unidadesMedida' => $this->model->obtenerUnidadesMedida(),
            'lugares' => $this->model->obtenerLugaresActividad(),
            'espacios' => $this->model->obtenerEspaciosUtilizar(),
            'horarios' => $this->model->obtenerHorarios(),
            'empleados' => $this->model->obtenerEmpleados(),
            'docentes' => $this->model->obtenerDocentes(),
            'estatus' => $this->model->obtenerEstatus(),
            'tiposEntrega' => $this->model->obtenerTiposEntrega(),
            'fechasOcupadas' => $this->model->obtenerFechasOcupadas(),
            'lugarActividad' => $this->model->obtenerLugarActividadPorId($id),
            'gruposEtariosActividad' => $this->model->obtenerGruposEtariosActividad($id),
            'seguimiento' => $this->model->obtenerSeguimientoActividad($id),
            'modo' => 'editar'
        ];

        $this->renderizar('view/ActividadNuevoView', $datosMaestros);
    }

}