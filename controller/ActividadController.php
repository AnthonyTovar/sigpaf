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
            'fechasOcupadas' => $this->model->obtenerFechasOcupadas(),
            'modo' => 'nuevo',
            'model' => $this->model
        ];

        $this->renderizar('view/ActividadNuevoView', $datosMaestros);
    }

    // GUARDAR (POST)
    public function guardar()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(["status" => "error", "message" => "Método no permitido."]);
            exit();
        }

        try {
            $datos = [
                'nombreActividad' => trim($_POST['nombreActividad'] ?? ''),
                'fechainicioActividad' => $_POST['fechainicioActividad'] ?? '',
                'fechafinActividad' => $_POST['fechafinActividad'] ?? '',
                'cantSesionesPlanificada' => intval($_POST['cantSesionesPlanificada'] ?? 1),
                'objetivoActividad' => trim($_POST['objetivoActividad'] ?? ''),
                'descActividad' => trim($_POST['descActividad'] ?? ''),
                'cantPersoAtender' => intval($_POST['cantPersoAtender'] ?? 0),
                'observacion' => trim($_POST['observacion'] ?? ''),
                'idTipoActividad' => $_POST['idTipoActividad'] ?? '',
                'idVertice' => $_POST['idVertice'] ?? '',
                'idAreaE' => $_POST['idAreaE'] ?? '',
                'idEmpleado' => $_POST['idEmpleado'] ?? '',
                'idUnidadMedida' => $_POST['idUnidadMedida'] ?? '',
                'idGrupoEtnio' => $_POST['idGrupoEtnio'] ?? '',
                'idDocente' => $_POST['idDocente'] ?? '',
                'idEstatus' => $_POST['idEstatus'] ?? 'ES0001',
                'idHorario' => $_POST['idHorario'] ?? '',
                'idEstDesarrollo' => $_POST['idEstDesarrollo'] ?? '',
                'idLugarActividad' => $_POST['idLugarActividad'] ?? '',
                'idEspacioUtilizar' => (!empty($_POST['idEspacioUtilizar']) ? $_POST['idEspacioUtilizar'] : null),
                'gruposEtarios' => $_POST['gruposEtarios'] ?? [],
                'fechasSesiones' => $_POST['fechasSesiones'] ?? [],
                'idTipEntrega' => $_POST['idTipEntrega'] ?? 'TE0001'
            ];

            // Validaciones
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
            if ($datos['cantPersoAtender'] <= 0) {
                echo json_encode(["status" => "error", "message" => "La cantidad de personas debe ser mayor a 0."]);
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
                    "message" => "Hubo un error al registrar la actividad en la base de datos."
                ]);
            }
            exit();

        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Error del servidor: " . $e->getMessage()]);
            exit();
        }
    }

    public function obtenerHorariosOcupados()
    {
        if (ob_get_length()) ob_clean();
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



    public function guardarLugarActividad()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["status" => "error", "message" => "Método no permitido"]);
            exit();
        }

        $nombre = trim($_POST['nomLugarActividad'] ?? '');
        $descripcion = trim($_POST['desLugarActividad'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $esSede = isset($_POST['esSede']) && $_POST['esSede'] == '1';
        $idParroquia = $_POST['idParroquia'] ?? '';

        if (empty($nombre) || empty($direccion) || empty($idParroquia)) {
            echo json_encode(["status" => "error", "message" => "Nombre, dirección y parroquia son obligatorios"]);
            exit();
        }

        $nuevoId = $this->model->registrarLugarActividad($nombre, $descripcion, $direccion, $esSede, $idParroquia);

        if ($nuevoId) {
            echo json_encode([
                "status" => "success",
                "message" => "Lugar registrado correctamente",
                "id" => $nuevoId
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al registrar el lugar"]);
        }
        exit();
    }

    

    public function guardarDocente()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["status" => "error", "message" => "Método no permitido"]);
            exit();
        }

        $cedula = trim($_POST['cedDocente'] ?? '');
        $nacionalidad = trim($_POST['nacionalidad'] ?? '');
        $nombres = trim($_POST['nombreDocente'] ?? '');
        $apellidos = trim($_POST['apellidoDocente'] ?? '');
        $telefono = trim($_POST['telfDocente'] ?? '');

        if (empty($cedula) || empty($nacionalidad) || empty($nombres) || empty($apellidos)) {
            echo json_encode(["status" => "error", "message" => "Cédula, nacionalidad, nombres y apellidos son obligatorios"]);
            exit();
        }

        $nuevoId = $this->model->registrarDocente($cedula, $nacionalidad, $nombres, $apellidos, $telefono);

        if ($nuevoId) {
            echo json_encode([
                "status" => "success",
                "message" => "Docente registrado correctamente",
                "id" => $nuevoId
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al registrar el docente. Posiblemente la cédula ya existe."]);
        }
        exit();
    }

    public function eliminar()
    {
        if (ob_get_length()) ob_clean();
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
        if (ob_get_length()) ob_clean();
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

    // EDITAR
    public function editar()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        // POST: Actualizar
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');

            $datos = [
                'idActividad' => $_POST['idActividad'] ?? '',
                'nombreActividad' => trim($_POST['nombreActividad'] ?? ''),
                'fechainicioActividad' => $_POST['fechainicioActividad'] ?? '',
                'fechafinActividad' => $_POST['fechafinActividad'] ?? '',
                'cantSesionesPlanificada' => intval($_POST['cantSesionesPlanificada'] ?? 1),
                'objetivoActividad' => trim($_POST['objetivoActividad'] ?? ''),
                'descActividad' => trim($_POST['descActividad'] ?? ''),
                'cantPersoAtender' => intval($_POST['cantPersoAtender'] ?? 0),
                'observacion' => trim($_POST['observacion'] ?? ''),
                'idTipoActividad' => $_POST['idTipoActividad'] ?? '',
                'idVertice' => $_POST['idVertice'] ?? '',
                'idAreaE' => $_POST['idAreaE'] ?? '',
                'idEmpleado' => $_POST['idEmpleado'] ?? '',
                'idUnidadMedida' => $_POST['idUnidadMedida'] ?? '',
                'idGrupoEtnio' => $_POST['idGrupoEtnio'] ?? '',
                'idDocente' => $_POST['idDocente'] ?? '',
                'idEstatus' => $_POST['idEstatus'] ?? 'ES0001',
                'idHorario' => $_POST['idHorario'] ?? '',
                'idEstDesarrollo' => $_POST['idEstDesarrollo'] ?? '',
                'idLugarActividad' => $_POST['idLugarActividad'] ?? '',
                'idEspacioUtilizar' => (!empty($_POST['idEspacioUtilizar']) ? $_POST['idEspacioUtilizar'] : null),
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
        $id = $_GET['id'] ?? '';
        
        if (empty($id)) {
            header("Location: index.php?action=actividades");
            exit();
        }

        $actividad = $this->model->obtenerActividadPorId($id);
        if (!$actividad) {
            header("Location: index.php?action=actividades");
            exit();
        }

        $lugarAct = $this->model->obtenerLugarActividadPorId($id);
        $gruposEtariosAct = $this->model->obtenerGruposEtariosActividad($id);
        $seguimiento = $this->model->obtenerSeguimientoActividad($id);

        // Extraer IDs de grupos etarios seleccionados
        $gruposEtariosSeleccionados = [];
        foreach ($gruposEtariosAct as $ge) {
            $gruposEtariosSeleccionados[] = $ge['idGrupoEtareo'];
        }

        // Extraer fechas de sesiones
        $fechasSesiones = [];
        foreach ($seguimiento as $seg) {
            $fechasSesiones[] = $seg['fechaSesion'];
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
            'lugarActividad' => $lugarAct,
            'gruposEtariosActividad' => $gruposEtariosSeleccionados,
            'seguimiento' => $seguimiento,
            'fechasSesiones' => $fechasSesiones,
            'modo' => 'editar'
        ];

        $this->renderizar('view/ActividadNuevoView', $datosMaestros);
    }
}