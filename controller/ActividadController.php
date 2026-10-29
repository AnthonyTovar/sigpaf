<?php
// Controlador de actividades



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


    private function obtenerFiltros()
    {
        return [
            'desde'           => trim($_GET['desde'] ?? ''),
            'hasta'           => trim($_GET['hasta'] ?? ''),
            'idEstatus'       => trim($_GET['idEstatus'] ?? ''),
            'idTipoActividad' => trim($_GET['idTipoActividad'] ?? ''),
            'responsable'     => trim($_GET['responsable'] ?? ''),
        ];
    }


    private function describirFiltros($filtros)
    {
        $descripcion = [];

        if (!empty($filtros['desde'])) {
            $descripcion[] = 'Desde ' . date('d/m/Y', strtotime($filtros['desde']));
        }
        if (!empty($filtros['hasta'])) {
            $descripcion[] = 'Hasta ' . date('d/m/Y', strtotime($filtros['hasta']));
        }
        if (!empty($filtros['idEstatus'])) {
            foreach ($this->model->obtenerEstatus() as $e) {
                if ($e['idEstatus'] === $filtros['idEstatus']) {
                    $descripcion[] = 'Estatus: ' . $e['nomEstatus'];
                    break;
                }
            }
        }
        if (!empty($filtros['idTipoActividad'])) {
            foreach ($this->model->obtenerTiposActividad() as $t) {
                if ($t['idTipoActividad'] === $filtros['idTipoActividad']) {
                    $descripcion[] = 'Tipo: ' . $t['nomTipoActividad'];
                    break;
                }
            }
        }
        if (!empty($filtros['responsable'])) {
            $nombreResp = null;
            foreach ($this->model->obtenerEmpleados() as $em) {
                if ($em['idEmpleado'] === $filtros['responsable']) {
                    $nombreResp = $em['nombreCompleto'];
                    break;
                }
            }
            if (!$nombreResp) {
                foreach ($this->model->obtenerDocentes() as $dc) {
                    if ($dc['idDocente'] === $filtros['responsable']) {
                        $nombreResp = $dc['nombreCompleto'];
                        break;
                    }
                }
            }
            if ($nombreResp) {
                $descripcion[] = 'Responsable: ' . $nombreResp;
            }
        }

        return $descripcion;
    }

    private function seleccionMultiple($valor)
    {
        if (!is_array($valor)) {
            $valor = $valor === '' || $valor === null ? [] : [$valor];
        }
        return array_values(array_unique(array_filter(array_map('trim', $valor), function ($id) {
            return $id !== '';
        })));
    }

    private function obtenerDatosActividad($origen)
    {
        $lugares = $this->seleccionMultiple($origen['idLugarActividad'] ?? []);
        $horarios = $this->seleccionMultiple($origen['idHorario'] ?? []);
        $empleados = $this->seleccionMultiple($origen['idEmpleado'] ?? []);
        $docentes = $this->seleccionMultiple($origen['idDocente'] ?? []);
        $fechasSesiones = $this->seleccionMultiple($origen['fechasSesiones'] ?? []);
        sort($fechasSesiones);

        return [
            'idActividad' => $origen['idActividad'] ?? '',
            'nombreActividad' => trim($origen['nombreActividad'] ?? ''),
            'fechainicioActividad' => $origen['fechainicioActividad'] ?? '',
            'fechafinActividad' => $origen['fechafinActividad'] ?? '',
            'cantSesionesPlanificada' => intval($origen['cantSesionesPlanificada'] ?? 1),
            'objetivoActividad' => trim($origen['objetivoActividad'] ?? ''),
            'descActividad' => trim($origen['descActividad'] ?? ''),
            'cantPersoAtender' => intval($origen['cantPersoAtender'] ?? 0),
            'observacion' => trim($origen['observacion'] ?? ''),
            'idTipoActividad' => $origen['idTipoActividad'] ?? '',
            'idVertice' => $origen['idVertice'] ?? '',
            'idAreaE' => $origen['idAreaE'] ?? '',
            'idEmpleado' => $empleados[0] ?? '',
            'idEmpleados' => $empleados,
            'idUnidadMedida' => $origen['idUnidadMedida'] ?? '',
            'idGrupoEtnio' => $origen['idGrupoEtnio'] ?? '',
            'idDocente' => $docentes[0] ?? '',
            'idDocentes' => $docentes,
            'idEstatus' => $origen['idEstatus'] ?? 'ES0001',
            'idHorario' => $horarios[0] ?? '',
            'idHorarios' => $horarios,
            'idEstDesarrollo' => $origen['idEstDesarrollo'] ?? '',
            'idLugarActividad' => $lugares[0] ?? '',
            'idLugares' => $lugares,
            'idEspacioUtilizar' => null,
            'espaciosPorLugar' => is_array($origen['idEspacioUtilizar'] ?? null) ? $origen['idEspacioUtilizar'] : [],
            'gruposEtarios' => $this->seleccionMultiple($origen['gruposEtarios'] ?? []),
            'fechasSesiones' => $fechasSesiones,
            'idTipEntrega' => !empty($origen['idTipEntrega']) ? $origen['idTipEntrega'] : 'TE0001'
        ];
    }

    public function listar()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $filtros = $this->obtenerFiltros();
        $actividades = $this->model->listarActividades($filtros);

        $this->renderizar('view/ActividadView', [
            'actividades'    => $actividades,
            'filtros'        => $filtros,
            'estatus'        => $this->model->obtenerEstatus(),
            'tiposActividad' => $this->model->obtenerTiposActividad(),
            'empleados'      => $this->model->obtenerEmpleados(),
            'docentes'       => $this->model->obtenerDocentes()
        ]);
    }

    public function nuevo()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }


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


    private function validarDatosActividad(&$datos, $idExcluir = null)
    {
        ValidationHelper::exigirRequerido($datos['nombreActividad'], 'El nombre de la actividad es obligatorio.');
        ValidationHelper::exigirRequerido($datos['objetivoActividad'], 'El objetivo de la actividad es obligatorio.');
        ValidationHelper::exigirRequerido($datos['descActividad'], 'La descripción de la actividad es obligatoria.');
        ValidationHelper::exigirMaxLongitud($datos['nombreActividad'], 'El nombre de la actividad es muy largo', 250);
        ValidationHelper::exigirMaxLongitud($datos['objetivoActividad'], 'El objetivo es muy largo');
        ValidationHelper::exigirMaxLongitud($datos['descActividad'], 'La descripción es muy larga');
        ValidationHelper::exigirMaxLongitud($datos['observacion'], 'La observación es muy larga');

        if (!ValidationHelper::rangoFechas($datos['fechainicioActividad'], $datos['fechafinActividad'])) {
            ValidationHelper::error('Las fechas no son válidas o el inicio es posterior al fin.');
        }
        $fechaMinima = (new DateTime('today', new DateTimeZone('America/Caracas')))
            ->modify('-15 days')->format('Y-m-d');
        if ($datos['fechainicioActividad'] < $fechaMinima) {
            ValidationHelper::error(
                'La fecha de inicio no puede ser anterior al ' . date('d/m/Y', strtotime($fechaMinima)) .
                '. Solo se permiten hasta 15 días antes de la fecha actual.'
            );
        }

        ValidationHelper::exigirRequerido($datos['idTipoActividad'], 'Debe seleccionar el tipo de actividad.');
        ValidationHelper::exigirRequerido($datos['idVertice'], 'Debe seleccionar el vértice.');
        ValidationHelper::exigirRequerido($datos['idAreaE'], 'Debe seleccionar el área específica.');
        if (!$datos['idEmpleados']) ValidationHelper::error('Debe seleccionar al menos un responsable.');
        if (!$datos['idDocentes']) ValidationHelper::error('Debe seleccionar al menos un docente.');
        if (!$datos['idLugares']) ValidationHelper::error('Debe seleccionar al menos un lugar.');
        if (!$datos['idHorarios']) ValidationHelper::error('Debe seleccionar al menos un horario.');
        ValidationHelper::exigirRequerido($datos['idUnidadMedida'], 'Debe seleccionar la unidad de medida.');
        ValidationHelper::exigirRequerido($datos['idGrupoEtnio'], 'Debe seleccionar el grupo étnico.');
        ValidationHelper::exigirRequerido($datos['idEstDesarrollo'], 'Debe seleccionar la estrategia de desarrollo.');
        if (!$datos['gruposEtarios']) ValidationHelper::error('Debe seleccionar al menos un grupo etario.');
        if (!ValidationHelper::enteroMin($datos['cantPersoAtender'], 1)) {
            ValidationHelper::error('La cantidad de personas a atender debe ser mayor a 0.');
        }
        if ($datos['cantSesionesPlanificada'] < 1 || $datos['cantSesionesPlanificada'] > 100) {
            ValidationHelper::error('La cantidad de sesiones planificadas debe estar entre 1 y 100.');
        }

        $diasTotales = (int) ((strtotime($datos['fechafinActividad']) - strtotime($datos['fechainicioActividad'])) / 86400) + 1;
        if ($datos['cantSesionesPlanificada'] > $diasTotales) {
            ValidationHelper::error('No puede haber más sesiones que días en el rango de fechas.');
        }
        if (count($datos['fechasSesiones']) !== $datos['cantSesionesPlanificada']) {
            ValidationHelper::error('Debe generar una fecha disponible para cada sesión planificada.');
        }

        foreach ($datos['fechasSesiones'] as $fechaSesion) {
            if (!ValidationHelper::fecha($fechaSesion) || $fechaSesion < $datos['fechainicioActividad'] || $fechaSesion > $datos['fechafinActividad']) {
                ValidationHelper::error('Las fechas de las sesiones deben ser válidas y estar dentro del rango de la actividad.');
            }
        }



        $lugaresCatalogo = [];
        foreach ($this->model->obtenerLugaresActividad() as $lugar) {
            $lugaresCatalogo[$lugar['idLugarActividad']] = $lugar;
        }
        $espaciosCatalogo = [];
        foreach ($this->model->obtenerEspaciosUtilizar() as $espacio) {
            $espaciosCatalogo[$espacio['idEspacioUtilizar']] = $espacio;
        }
        $capacidadMinima = null;
        foreach ($datos['idLugares'] as $idLugar) {
            if (!isset($lugaresCatalogo[$idLugar])) ValidationHelper::error('Uno de los lugares seleccionados no es válido.');
            $lugar = $lugaresCatalogo[$idLugar];
            $idEspacio = trim((string) ($datos['espaciosPorLugar'][$idLugar] ?? ''));
            if (!empty($lugar['esSede'])) {
                if ($idEspacio === '') ValidationHelper::error('Seleccione un espacio para cada sede elegida.');
                if (!isset($espaciosCatalogo[$idEspacio])) ValidationHelper::error('Uno de los espacios seleccionados no es válido.');
                $capacidad = (int) $espaciosCatalogo[$idEspacio]['capacidad'];
                $capacidadMinima = $capacidadMinima === null ? $capacidad : min($capacidadMinima, $capacidad);
            } else {
                $idEspacio = null;
            }
            $datos['espaciosPorLugar'][$idLugar] = $idEspacio;
        }
        if ($capacidadMinima !== null && $datos['cantPersoAtender'] > $capacidadMinima) {
            ValidationHelper::error('La cantidad de personas excede la capacidad disponible (' . $capacidadMinima . ').');
        }

        $espacios = array_values(array_filter($datos['espaciosPorLugar']));
        $horariosOcupados = $this->model->obtenerHorariosOcupados(
            $datos['fechainicioActividad'], $datos['fechafinActividad'], $datos['fechasSesiones'],
            $datos['idLugares'], $espacios, $idExcluir, $datos['idHorarios']
        );
        if ($horariosOcupados) {
            ValidationHelper::error('Uno de los horarios seleccionados está ocupado o se solapa con otra actividad en el lugar y fecha elegidos.');
        }


        $conflictos = $this->model->obtenerConflictosDocentes(
            $datos['idDocentes'], $datos['idHorarios'],
            $datos['fechainicioActividad'], $datos['fechafinActividad'],
            $datos['fechasSesiones'], $idExcluir
        );
        if ($conflictos) {
            $conflicto = $conflictos[0];
            $detalleFecha = $conflicto['fechaSesion']
                ? 'el ' . date('d/m/Y', strtotime($conflicto['fechaSesion']))
                : 'en las fechas seleccionadas';
            ValidationHelper::error(
                "El docente {$conflicto['nomDocente']} ya tiene la actividad \"{$conflicto['nombreActividad']}\" " .
                "$detalleFecha en el horario \"{$conflicto['nomHorario']}\"."
            );
        }
    }

    // Guardar (POST)
    public function guardar()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo json_encode(["status" => "error", "message" => "Método no permitido."]);
            exit();
        }

        try {
            $datos = $this->obtenerDatosActividad($_POST);


            $this->validarDatosActividad($datos);

            $nuevoId = $this->model->registrarActividad($datos);

            if ($nuevoId) {

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

        $fechaInicio = $_GET['fechaInicio'] ?? '';
        $fechaFin = $_GET['fechaFin'] ?? '';
        if (!$fechaInicio || !$fechaFin) {
            echo json_encode(['horariosOcupados' => [], 'docentesOcupados' => []]);
            exit();
        }

        $fechasSesiones = $this->seleccionMultiple($_GET['fechasSesiones'] ?? []);
        $lugares = $this->seleccionMultiple($_GET['idLugares'] ?? []);
        $espacios = $this->seleccionMultiple($_GET['idEspacios'] ?? []);
        $horarios = $this->seleccionMultiple($_GET['idHorarios'] ?? []);
        $excluir = $_GET['idExcluir'] ?? null;

        echo json_encode([
            'horariosOcupados' => $this->model->obtenerHorariosOcupados(
                $fechaInicio, $fechaFin, $fechasSesiones, $lugares, $espacios, $excluir
            ),
            'docentesOcupados' => $this->model->obtenerDocentesOcupados(
                $fechaInicio, $fechaFin, $fechasSesiones, $horarios, $excluir
            )
        ]);
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

        // Validaciones
        ValidationHelper::exigirMaxLongitud($nombre, 'El nombre del lugar no puede exceder los 100 caracteres.', 100);
        ValidationHelper::exigirMaxLongitud($descripcion, 'La descripción del lugar no puede exceder los 255 caracteres.', 255);
        ValidationHelper::exigirMaxLongitud($direccion, 'La dirección no puede exceder los 255 caracteres.', 255);

        $nuevoId = $this->model->registrarLugarActividad($nombre, $descripcion, $direccion, $esSede, $idParroquia);

        if ($nuevoId) {

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

        // Validaciones
        if (!ValidationHelper::cedula($cedula)) {
            ValidationHelper::error('La cédula del docente no tiene un formato válido (V12345678).');
        }
        ValidationHelper::exigirMaxLongitud($nombres, 'Los nombres no pueden exceder los 50 caracteres.', 50);
        ValidationHelper::exigirMaxLongitud($apellidos, 'Los apellidos no pueden exceder los 50 caracteres.', 50);
        ValidationHelper::exigirMaxLongitud($telefono, 'El teléfono no puede exceder los 20 caracteres.', 20);

        $nuevoId = $this->model->registrarDocente($cedula, $nacionalidad, $nombres, $apellidos, $telefono);

        if ($nuevoId) {

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


        $reg = $this->model->obtenerActividadPorId($id);

        $resultado = $this->model->eliminarActividad($id);

        if ($resultado) {

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
        $lugares = $this->model->obtenerLugaresActividadPorId($id);

        echo json_encode([
            'actividad' => $actividad,
            'lugar' => $lugares ? $lugares[0] : [],
            'lugares' => $lugares,
            'horarios' => $this->model->obtenerHorariosActividad($id),
            'empleados' => $this->model->obtenerEmpleadosActividad($id),
            'docentes' => $this->model->obtenerDocentesActividad($id),
            'gruposEtarios' => $this->model->obtenerGruposEtariosActividad($id),
            'seguimiento' => $this->model->obtenerSeguimientoActividad($id)
        ]);
        exit;
    }

    // Editar
    public function editar()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');

            $datos = $this->obtenerDatosActividad($_POST);


            $this->validarDatosActividad($datos, $datos['idActividad']);

            $resultado = $this->model->editarActividad($datos);

            if ($resultado) {

                Logger::actualizar('Actividad', $datos['idActividad'], $datos['nombreActividad']);

                echo json_encode(["status" => "success", "message" => "Actividad actualizada con éxito!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error al actualizar la actividad."]);
            }
            exit();
        }


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

        $lugaresAct = $this->model->obtenerLugaresActividadPorId($id);
        $gruposEtariosAct = $this->model->obtenerGruposEtariosActividad($id);
        $horariosAct = $this->model->obtenerHorariosActividad($id);
        $empleadosAct = $this->model->obtenerEmpleadosActividad($id);
        $docentesAct = $this->model->obtenerDocentesActividad($id);
        $seguimiento = $this->model->obtenerSeguimientoActividad($id);

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
            'fechasOcupadas' => $this->model->obtenerFechasOcupadas($id),
            'lugarActividad' => $lugaresAct ? $lugaresAct[0] : [],
            'lugaresActividad' => $lugaresAct,
            'horariosActividad' => array_column($horariosAct, 'idHorario'),
            'empleadosActividad' => array_column($empleadosAct, 'idEmpleado'),
            'docentesActividad' => array_column($docentesAct, 'idDocente'),
            'gruposEtariosActividad' => array_column($gruposEtariosAct, 'idGrupoEtareo'),
            'seguimiento' => $seguimiento,
            'fechasSesiones' => array_column($seguimiento, 'fechaSesion'),
            'modo' => 'editar',
            'model' => $this->model
        ];

        $this->renderizar('view/ActividadNuevoView', $datosMaestros);
    }

    // Exportar PDF de actividades





    public function exportarPdf()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }



        ini_set('display_errors', '0');
        while (ob_get_level() > 0) {
            ob_end_clean();
        }


        require_once 'lib/dompdf/autoload.inc.php';


        $filtros = $this->obtenerFiltros();
        $actividades = $this->model->listarActividades($filtros);
        $filtrosDesc = $this->describirFiltros($filtros);


        ob_start();
        require 'view/public/reportes/ActividadReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'landscape');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Actividades', null,
            'Genero PDF de actividades (' . count($actividades) . ' actividades)');


        $dompdf->stream("reporte_actividades_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }
}