<?php
require_once 'model/ActividadModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';

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

    /**
     * Validación UNIFICADA de los datos de una actividad.
     * La usan tanto guardar() como editar() para que ambas validen
     * exactamente igual (mismo orden, mismos mensajes, mismo formato).
     *
     * @param array $datos Datos ya limpios del formulario.
     * @param string|null $idExcluir ID de la actividad que se está editando
     *                              (para no chocar contra sí misma).
     */
    private function validarDatosActividad($datos, $idExcluir = null)
    {
        // Obligatorios
        ValidationHelper::exigirRequerido($datos['nombreActividad'], 'El nombre de la actividad es obligatorio.');
        ValidationHelper::exigirRequerido($datos['objetivoActividad'], 'El objetivo de la actividad es obligatorio.');
        ValidationHelper::exigirRequerido($datos['descActividad'], 'La descripción de la actividad es obligatoria.');

        // Longitudes máximas (objetivo, descripción y observación: 300 caracteres)
        ValidationHelper::exigirMaxLongitud($datos['nombreActividad'], 'El nombre de la actividad es muy largo', 250);
        ValidationHelper::exigirMaxLongitud($datos['objetivoActividad'], 'El objetivo es muy largo');
        ValidationHelper::exigirMaxLongitud($datos['descActividad'], 'La descripción es muy larga');
        ValidationHelper::exigirMaxLongitud($datos['observacion'], 'La observación es muy larga');

        // Fechas coherentes
        if (!ValidationHelper::rangoFechas($datos['fechainicioActividad'], $datos['fechafinActividad'])) {
            ValidationHelper::error('La fecha de inicio no puede ser posterior a la fecha de fin.');
        }

        // Clasificación y responsables
        ValidationHelper::exigirRequerido($datos['idTipoActividad'], 'Debe seleccionar el tipo de actividad.');
        ValidationHelper::exigirRequerido($datos['idVertice'], 'Debe seleccionar el vértice.');
        ValidationHelper::exigirRequerido($datos['idAreaE'], 'Debe seleccionar el área específica.');
        ValidationHelper::exigirRequerido($datos['idEmpleado'], 'Debe seleccionar el responsable (empleado).');
        ValidationHelper::exigirRequerido($datos['idDocente'], 'Debe seleccionar el docente.');

        // Lugar y horario
        ValidationHelper::exigirRequerido($datos['idLugarActividad'], 'Debe seleccionar el lugar de la actividad.');
        ValidationHelper::exigirRequerido($datos['idHorario'], 'Debe seleccionar el horario.');

        // Clasificación adicional (también obligatoria)
        ValidationHelper::exigirRequerido($datos['idUnidadMedida'], 'Debe seleccionar la unidad de medida.');
        ValidationHelper::exigirRequerido($datos['idGrupoEtnio'], 'Debe seleccionar el grupo étnico.');
        ValidationHelper::exigirRequerido($datos['idEstDesarrollo'], 'Debe seleccionar la estrategia de desarrollo.');

        // Al menos un grupo etario
        if (empty($datos['gruposEtarios']) || !is_array($datos['gruposEtarios'])) {
            ValidationHelper::error('Debe seleccionar al menos un grupo etario.');
        }

        // Cantidades
        if (!ValidationHelper::enteroMin($datos['cantPersoAtender'], 1)) {
            ValidationHelper::error('La cantidad de personas a atender debe ser mayor a 0.');
        }
        if (!ValidationHelper::enteroMin($datos['cantSesionesPlanificada'], 1)) {
            ValidationHelper::error('La cantidad de sesiones planificadas debe ser al menos 1.');
        }

        // Lógica: no puede haber más sesiones que días en el rango de fechas
        $diasTotales = (int) ((strtotime($datos['fechafinActividad']) - strtotime($datos['fechainicioActividad'])) / 86400) + 1;
        if ($datos['cantSesionesPlanificada'] > $diasTotales) {
            ValidationHelper::error(
                'No puede haber más sesiones (' . $datos['cantSesionesPlanificada'] .
                ') que días en el rango de fechas (' . $diasTotales . ').'
            );
        }

        // ===== DISPONIBILIDAD DEL DOCENTE =====
        // Un docente no puede estar en dos actividades el mismo día a la
        // misma hora: si comparte horario y las fechas se traslapan, se rechaza.
        $conflicto = $this->model->obtenerConflictoDocente(
            $datos['idDocente'],
            $datos['idHorario'],
            $datos['fechainicioActividad'],
            $datos['fechafinActividad'],
            $idExcluir
        );

        if ($conflicto) {
            ValidationHelper::error(
                "El docente {$conflicto['nomDocente']} no está disponible en el horario " .
                "\"{$conflicto['nomHorario']}\": ya tiene asignada la actividad " .
                "\"{$conflicto['nombreActividad']}\" (del " .
                date('d/m/Y', strtotime($conflicto['fechainicioActividad'])) . " al " .
                date('d/m/Y', strtotime($conflicto['fechafinActividad'])) . "). " .
                "Cambia el docente, el horario o las fechas."
            );
        }
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

            // Validaciones (mismo criterio que la edición)
            $this->validarDatosActividad($datos);

            $nuevoId = $this->model->registrarActividad($datos);

            if ($nuevoId) {
                // Bitacora: registro creado
                Logger::crear('Actividad', $nuevoId, $datos['nombreActividad']);
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

        // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
        ValidationHelper::exigirMaxLongitud($nombre, 'El nombre del lugar no puede exceder los 100 caracteres.', 100);
        ValidationHelper::exigirMaxLongitud($descripcion, 'La descripción del lugar no puede exceder los 255 caracteres.', 255);
        ValidationHelper::exigirMaxLongitud($direccion, 'La dirección no puede exceder los 255 caracteres.', 255);

        $nuevoId = $this->model->registrarLugarActividad($nombre, $descripcion, $direccion, $esSede, $idParroquia);

        if ($nuevoId) {
            // Bitacora: registro creado (desde modulo Actividades)
            Logger::crear('Lugar de Actividad', $nuevoId, $nombre);

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

        // ===== VALIDACIÓN UNIFICADA (ValidationHelper) =====
        if (!ValidationHelper::cedula($cedula)) {
            ValidationHelper::error('La cédula del docente no tiene un formato válido (V12345678).');
        }
        ValidationHelper::exigirMaxLongitud($nombres, 'Los nombres no pueden exceder los 50 caracteres.', 50);
        ValidationHelper::exigirMaxLongitud($apellidos, 'Los apellidos no pueden exceder los 50 caracteres.', 50);
        ValidationHelper::exigirMaxLongitud($telefono, 'El teléfono no puede exceder los 20 caracteres.', 20);

        $nuevoId = $this->model->registrarDocente($cedula, $nacionalidad, $nombres, $apellidos, $telefono);

        if ($nuevoId) {
            // Bitacora: registro creado (desde modulo Actividades)
            Logger::crear('Docente', $nuevoId, $nombres . ' ' . $apellidos);

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

        // Consulta el nombre antes de eliminar para la bitacora
        $reg = $this->model->obtenerActividadPorId($id);

        $resultado = $this->model->eliminarActividad($id);

        if ($resultado) {
            // Bitacora: registro eliminado
            Logger::eliminar('Actividad', $id, $reg ? $reg['nombreActividad'] : 'desconocido');
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

            // Validaciones: MISMOS criterios que al registrar (antes no validaba nada)
            $this->validarDatosActividad($datos, $datos['idActividad']);

            $resultado = $this->model->editarActividad($datos);

            if ($resultado) {
                // Bitacora: registro actualizado
                Logger::actualizar('Actividad', $datos['idActividad'], $datos['nombreActividad']);

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