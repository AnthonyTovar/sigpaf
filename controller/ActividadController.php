<?php
// Controlador de actividades



require_once 'model/ActividadModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';
require_once 'RolHelper.php';

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
            'idLugar'         => trim($_GET['idLugar'] ?? ''),
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
        if (!empty($filtros['idLugar'])) {
            foreach ($this->model->obtenerLugaresActividad() as $l) {
                if ($l['idLugarActividad'] === $filtros['idLugar']) {
                    $descripcion[] = 'Lugar: ' . $l['nomLugarActividad'];
                    break;
                }
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

        // Validacion de sesiones multiples
        $fechasSesiones = $origen['fechasSesiones'] ?? [];
        if (!is_array($fechasSesiones)) {
            $fechasSesiones = $fechasSesiones === '' || $fechasSesiones === null ? [] : [$fechasSesiones];
        }
        $fechasSesiones = array_map('trim', $fechasSesiones);

        // Sesiones: fecha + horario + lugar + docentes
        $horariosSesion = is_array($origen['sesionesHorario'] ?? null) ? $origen['sesionesHorario'] : [];
        $lugaresSesion = is_array($origen['sesionesLugar'] ?? null) ? $origen['sesionesLugar'] : [];
        $docentesSesion = is_array($origen['sesionesDocentes'] ?? null) ? $origen['sesionesDocentes'] : null;

        $sesiones = [];
        foreach ($fechasSesiones as $indice => $fechaSesion) {
            if ($fechaSesion === '') continue;
            $docentesManual = null;
            if ($docentesSesion !== null) {
                $docentesManual = array_values(array_filter(array_map('trim', explode(',', (string) ($docentesSesion[$indice] ?? '')))));
            }
            $idLugarSesion = trim((string) ($lugaresSesion[$indice] ?? ''));
            $sesiones[] = [
                'fecha' => $fechaSesion,
                'idHorario' => trim((string) ($horariosSesion[$indice] ?? '')),
                'idLugar' => $idLugarSesion !== '' ? $idLugarSesion : null,
                'docentes' => $docentesManual,
            ];
        }
        usort($sesiones, function ($a, $b) {
            return strcmp($a['fecha'], $b['fecha']);
        });
        $fechasSesiones = array_column($sesiones, 'fecha');

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
            'sesiones' => $sesiones,
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
        $this->model->sincronizarEstatusAutomatico();
        $actividades = $this->model->listarActividades($filtros);

        $this->renderizar('view/ActividadView', [
            'actividades'    => $actividades,
            'filtros'        => $filtros,
            'estatus'        => $this->model->obtenerEstatus(),
            'tiposActividad' => $this->model->obtenerTiposActividad(),
            'empleados'      => $this->model->obtenerEmpleados(),
            'docentes'       => $this->model->obtenerDocentes(),
            'lugares'        => $this->model->obtenerLugaresActividad(),
            'usuarioActual'  => $_SESSION['usuario_id'] ?? null,
            'esSuperUsuario' => RolHelper::esSuperUsuario(),
            'esAdministrador' => RolHelper::esAdministrador(),
            'esSoloLectura'  => RolHelper::esSoloLectura()
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

        if (count($datos['fechasSesiones']) !== $datos['cantSesionesPlanificada']) {
            ValidationHelper::error('Debe generar una fecha disponible para cada sesión planificada.');
        }

        foreach ($datos['fechasSesiones'] as $fechaSesion) {
            if (!ValidationHelper::fecha($fechaSesion) || $fechaSesion < $datos['fechainicioActividad'] || $fechaSesion > $datos['fechafinActividad']) {
                ValidationHelper::error('Las fechas de las sesiones deben ser válidas y estar dentro del rango de la actividad.');
            }
        }

        // Sesiones simultaneas en distintos lugares
        $horariosPermitidos = array_flip($datos['idHorarios']);
        $docentesPermitidos = array_flip($datos['idDocentes']);
        foreach ($datos['sesiones'] as $indice => $sesion) {
            $nroSesion = $indice + 1;
            if (empty($sesion['idHorario']) || !isset($horariosPermitidos[$sesion['idHorario']])) {
                ValidationHelper::error("Debe asignar un horario válido a la sesión $nroSesion.");
            }
            if (!empty($sesion['idLugar']) && !in_array($sesion['idLugar'], $datos['idLugares'], true)) {
                ValidationHelper::error("El lugar de la sesión $nroSesion debe ser uno de los lugares seleccionados para la actividad.");
            }
            if (is_array($sesion['docentes'])) {
                foreach ($sesion['docentes'] as $idDocenteSesion) {
                    if (!isset($docentesPermitidos[$idDocenteSesion])) {
                        ValidationHelper::error("Los docentes de la sesión $nroSesion deben estar entre los docentes seleccionados para la actividad.");
                    }
                }
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
                $sedeEspacio = $espaciosCatalogo[$idEspacio]['idLugarActividad'] ?? null;
                if (!empty($sedeEspacio) && $sedeEspacio !== $idLugar) {
                    ValidationHelper::error('El espacio "' . $espaciosCatalogo[$idEspacio]['nombreEspacioUtilizar'] . '" no pertenece a la sede ' . $lugar['nomLugarActividad'] . '.');
                }
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
    }
 
    private function validarSesiones($datos, $idExcluir = null)
    {
        $sesiones = $datos['sesiones'];
        if (!$sesiones) return;

        $mapaHorarios = [];
        foreach ($this->model->obtenerHorarios() as $h) {
            $mapaHorarios[$h['idHorario']] = $h['nomHorario'];
        }
        $mapaLugares = [];
        foreach ($this->model->obtenerLugaresActividad() as $l) {
            $mapaLugares[$l['idLugarActividad']] = $l['nomLugarActividad'];
        }
        $mapaDocentes = [];
        foreach ($this->model->obtenerDocentes() as $d) {
            $mapaDocentes[$d['idDocente']] = $d['nombreCompleto'];
        }

        // Máximo 3 sesiones el mismo día
        $porFecha = [];
        foreach ($sesiones as $sesion) {
            $porFecha[$sesion['fecha']] = ($porFecha[$sesion['fecha']] ?? 0) + 1;
        }
        foreach ($porFecha as $fecha => $cantidad) {
            if ($cantidad > 3) {
                ValidationHelper::error('El día ' . date('d/m/Y', strtotime($fecha)) . " tiene $cantidad sesiones. Solo se permiten máximo 3 sesiones por día.");
            }
        }

        // Toda sesión debe tener al menos un docente
        foreach ($sesiones as $indice => $sesion) {
            if (empty($sesion['docentes'])) {
                ValidationHelper::error('La sesión ' . ($indice + 1) . ' (' . date('d/m/Y', strtotime($sesion['fecha'])) . ') no tiene docente asignado. Cada sesión debe tener al menos un docente; si todos están ocupados en ese horario, cambie la fecha u horario de la sesión.');
            }
        }

        // Choques de lugar dentro de la misma actividad (mismo día, horario igual o solapado)
        $total = count($sesiones);
        for ($i = 0; $i < $total; $i++) {
            for ($j = $i + 1; $j < $total; $j++) {
                $a = $sesiones[$i];
                $b = $sesiones[$j];
                if ($a['fecha'] !== $b['fecha'] || empty($a['idLugar']) || $a['idLugar'] !== $b['idLugar']) continue;
                $horarioA = $mapaHorarios[$a['idHorario']] ?? '';
                $horarioB = $mapaHorarios[$b['idHorario']] ?? '';
                if ($a['idHorario'] === $b['idHorario'] || $this->model->horariosSeSolapan($horarioA, $horarioB)) {
                    ValidationHelper::error('Las sesiones ' . ($i + 1) . ' y ' . ($j + 1) . ' usan el mismo lugar (' . ($mapaLugares[$a['idLugar']] ?? $a['idLugar']) . ') el ' . date('d/m/Y', strtotime($a['fecha'])) . ' en horarios que se solapan. En distinto horario sí es posible.');
                }
            }
        }

        // Lugar y docentes disponibles frente a otras actividades
        $ocupacionLugaresPorFecha = [];
        $ocupadosPorSesion = $this->model->obtenerDocentesOcupadosPorSesiones($sesiones, $idExcluir);
        foreach ($sesiones as $indice => $sesion) {
            $nroSesion = $indice + 1;
            $fecha = $sesion['fecha'];
            $nomHorario = $mapaHorarios[$sesion['idHorario']] ?? '';

            if (!empty($sesion['idLugar']) && $nomHorario !== '') {
                if (!isset($ocupacionLugaresPorFecha[$fecha])) {
                    $ocupacionLugaresPorFecha[$fecha] = $this->model->obtenerOcupacionLugaresPorFecha($fecha, $idExcluir);
                }
                foreach ($ocupacionLugaresPorFecha[$fecha] as $ocupacion) {
                    if ($ocupacion['idLugarActividad'] === $sesion['idLugar']
                        && $this->model->horariosSeSolapan($nomHorario, $ocupacion['nomHorario'])) {
                        ValidationHelper::error('El lugar ' . ($mapaLugares[$sesion['idLugar']] ?? $sesion['idLugar']) . ' ya está ocupado el ' . date('d/m/Y', strtotime($fecha)) . ' en ese horario por la actividad "' . $ocupacion['nombreActividad'] . '" (sesión ' . $nroSesion . '). En distinto horario sí es posible.');
                    }
                }
            }

            foreach ($sesion['docentes'] as $idDocente) {
                if (in_array($idDocente, $ocupadosPorSesion[$indice] ?? [], true)) {
                    ValidationHelper::error('El docente ' . ($mapaDocentes[$idDocente] ?? $idDocente) . ' ya tiene otra actividad el ' . date('d/m/Y', strtotime($fecha)) . ' en ese horario, por lo que no puede asignarse a la sesión ' . $nroSesion . '.');
                }
            }
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

            $datos['idUsuarioCreador'] = $_SESSION['usuario_id'] ?? null;

            $this->validarDatosActividad($datos);

            $advertencias = $this->prepararSesiones($datos);

            $this->validarSesiones($datos);

            $nuevoId = $this->model->registrarActividad($datos);

            if ($nuevoId) {

                Logger::crear('Actividad', $nuevoId, $datos['nombreActividad']);
                $mensaje = "Actividad registrada con éxito!";
                if ($advertencias) {
                    $mensaje .= ' Atención: ' . count($advertencias) . ' sesión(es) quedaron sin docente disponible.';
                }
                echo json_encode([
                    "status" => "success",
                    "message" => $mensaje,
                    "id" => $nuevoId,
                    "advertencias" => $advertencias
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

    // Prepara las sesiones antes de guardar: lugar general por defecto y, si el usuario
    // no eligió docentes por sesión, asignación automática de los disponibles.
    // Devuelve la lista de advertencias (sesiones sin docente disponible en modo automático).
    private function prepararSesiones(&$datos, $idExcluir = null)
    {
        $lugarGeneral = $datos['idLugares'][0] ?? null;
        $faltanDocentes = false;
        foreach ($datos['sesiones'] as $indice => $sesion) {
            if (empty($sesion['idLugar'])) {
                $datos['sesiones'][$indice]['idLugar'] = $lugarGeneral;
            }
            if ($sesion['docentes'] === null) {
                $faltanDocentes = true;
            }
        }

        if (!$faltanDocentes) {
            return [];
        }

        // Modo automático
        $resultado = $this->model->asignarDocentesSesiones(
            $datos['idDocentes'], $datos['sesiones'], $idExcluir
        );
        foreach ($resultado['sesiones'] as $indice => $sesion) {
            if ($datos['sesiones'][$indice]['docentes'] === null) {
                $datos['sesiones'][$indice]['docentes'] = $sesion['idDocente'] ? [$sesion['idDocente']] : [];
            }
        }
        return $resultado['advertencias'];
    }

    // Previsualización: docentes ocupados (por otras actividades) en cada sesión.
    public function previsualizarSesiones()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        // Validacion de fechas que se repiten, mientras no sean en el mismo horario
        $fechasSesiones = $_GET['fechasSesiones'] ?? [];
        if (!is_array($fechasSesiones)) {
            $fechasSesiones = $fechasSesiones === '' || $fechasSesiones === null ? [] : [$fechasSesiones];
        }
        $fechasSesiones = array_map('trim', $fechasSesiones);
        $horariosSesion = is_array($_GET['sesionesHorario'] ?? null) ? $_GET['sesionesHorario'] : [];
        $excluir = $_GET['idExcluir'] ?? null;

        $sesiones = [];
        foreach ($fechasSesiones as $indice => $fecha) {
            if (!ValidationHelper::fecha($fecha)) continue;
            $sesiones[] = [
                'fecha' => $fecha,
                'idHorario' => trim((string) ($horariosSesion[$indice] ?? '')),
            ];
        }

        echo json_encode([
            'sesiones' => $sesiones,
            'ocupadosPorSesion' => $this->model->obtenerDocentesOcupadosPorSesiones($sesiones, $excluir)
        ]);
        exit();
    }

    public function obtenerHorariosOcupados()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $fechaInicio = $_GET['fechaInicio'] ?? '';
        $fechaFin = $_GET['fechaFin'] ?? '';
        if (!$fechaInicio || !$fechaFin) {
            echo json_encode(['horariosOcupados' => []]);
            exit();
        }

        $fechasSesiones = $this->seleccionMultiple($_GET['fechasSesiones'] ?? []);
        $lugares = $this->seleccionMultiple($_GET['idLugares'] ?? []);
        $espacios = $this->seleccionMultiple($_GET['idEspacios'] ?? []);
        $excluir = $_GET['idExcluir'] ?? null;

        echo json_encode([
            'horariosOcupados' => $this->model->obtenerHorariosOcupados(
                $fechaInicio, $fechaFin, $fechasSesiones, $lugares, $espacios, $excluir
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

        if ($nuevoId === 'nombre_duplicado') {
            echo json_encode([
                "status" => "error",
                "message" => "Ya existe un lugar con el nombre '{$nombre}' en esa parroquia y municipio. Puede registrarlo si es en otra parroquia o municipio."
            ]);
        } else if ($nuevoId) {

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

        $cedula = strtoupper(trim($_POST['cedDocente'] ?? ''));
        $nacionalidad = trim($_POST['nacionalidad'] ?? '');
        $nombres = trim($_POST['nombreDocente'] ?? '');
        $apellidos = trim($_POST['apellidoDocente'] ?? '');
        $telefono = trim($_POST['telfDocente'] ?? '');

        // prefijo de cedula
        if ($nacionalidad === '' && $cedula !== '') {
            $letraCedula = substr($cedula, 0, 1);
            $nacionalidad = $letraCedula === 'E' ? 'Extranjero' : 'Venezolano';
        }

        if (empty($cedula) || empty($nacionalidad) || empty($nombres) || empty($apellidos) || empty($telefono)) {
            echo json_encode(["status" => "error", "message" => "Cédula, nacionalidad, nombres, apellidos y teléfono son obligatorios"]);
            exit();
        }

        // Validaciones
        if (!ValidationHelper::cedula($cedula)) {
            ValidationHelper::error('La cédula del docente no tiene un formato válido (V12345678), sin letras en el número.');
        }
        if (!ValidationHelper::soloLetras($nombres) || !ValidationHelper::soloLetras($apellidos)) {
            ValidationHelper::error('Los nombres y apellidos solo pueden contener letras, sin números.');
        }
        if (preg_match('/[A-Za-z]/', $telefono)) {
            ValidationHelper::error('El teléfono no puede contener letras.');
        }
        if (strlen(preg_replace('/\D/', '', $telefono)) < 10) {
            ValidationHelper::error('Ingrese un teléfono válido (mínimo 10 dígitos, Ej: 0412-1234567).');
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

    // Modificar la actividad: quien la planificó, el administrador o el super usuario.
    public function puedeModificar($actividad)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (RolHelper::esAdministrador()) {
            return true;
        }
        $creador = $actividad['idUsuarioCreador'] ?? null;
        if ($creador === null || $creador === '') {
            return false;
        }
        return $creador === ($_SESSION['usuario_id'] ?? null);
    }

    // Eliminar actividades: solo el super usuario.
    public function puedeEliminar($actividad)
    {
        return RolHelper::esSuperUsuario();
    }

    // Verifica el permiso según la acción.
    private function verificarPropiedad($idActividad, $accionTexto)
    {
        $actividad = $this->model->obtenerActividadPorId($idActividad);
        if (!$actividad) {
            return null;
        }
        $permiso = ($accionTexto === 'eliminar')
            ? $this->puedeEliminar($actividad)
            : $this->puedeModificar($actividad);
        if ($permiso) {
            return $actividad;
        }

        $detalle = ($accionTexto === 'eliminar')
            ? "Intento eliminar la actividad $idActividad (" . $actividad['nombreActividad'] . ") sin ser super usuario"
            : "Intento $accionTexto la actividad $idActividad (" . $actividad['nombreActividad'] .
              ") planificada por otro usuario (" . ($actividad['nomCreador'] ?? 'sin registrar') . ")";
        Logger::registrar('ACCESS_DENIED', 'Actividad', $idActividad, $detalle);
        return false;
    }

    // Regitro en la bitacora de un usuario sin permiso
    public function registrarIntentoModificacion()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $id = trim($_POST['idActividad'] ?? '');
        $accion = trim($_POST['accion'] ?? 'modificar');
        $accion = in_array($accion, ['editar', 'eliminar'], true) ? $accion : 'modificar';

        $actividad = $id !== '' ? $this->model->obtenerActividadPorId($id) : null;
        if ($actividad) {
            $tienePermiso = ($accion === 'eliminar')
                ? $this->puedeEliminar($actividad)
                : $this->puedeModificar($actividad);
            if (!$tienePermiso) {
                $detalle = ($accion === 'eliminar')
                    ? "Intento eliminar la actividad $id (" . $actividad['nombreActividad'] . ") sin ser super usuario"
                    : "Intento $accion la actividad $id (" . $actividad['nombreActividad'] .
                      ") planificada por otro usuario (" . ($actividad['nomCreador'] ?? 'sin registrar') . ")";
                Logger::registrar('ACCESS_DENIED', 'Actividad', $id, $detalle);
            }
        }

        $mensaje = ($accion === 'eliminar')
            ? "Acción no permitida: solo el super usuario puede eliminar actividades."
            : "Acción no permitida: solo quien planificó la actividad o un administrador pueden modificarla.";
        echo json_encode(["status" => "error", "message" => $mensaje]);
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


        $reg = $this->verificarPropiedad($id, 'eliminar');

        if ($reg === null) {
            echo json_encode(["status" => "error", "message" => "La actividad indicada no existe."]);
            exit;
        }

        if ($reg === false) {
            echo json_encode(["status" => "error", "message" => "Acción no permitida: solo el super usuario puede eliminar actividades."]);
            exit;
        }

        // Solo se puede eliminar una actividad sin seguimiento.
        if ($this->model->tieneSeguimientoRegistrado($id)) {
            Logger::accesoDenegado('eliminarActividad (la actividad ya tiene sesiones con seguimiento registrado)');
            echo json_encode(["status" => "error", "message" => "No se puede eliminar: esta actividad ya tiene sesiones con seguimiento registrado."]);
            exit;
        }

        $resultado = $this->model->eliminarActividad($id);

        if ($resultado) {

            Logger::eliminar('Actividad', $id, $reg ? $reg['nombreActividad'] : 'desconocido');
            echo json_encode(["status" => "success", "message" => "Actividad eliminada correctamente"]);
        } else {
            $detalle = $this->model->errorEliminar !== ''
                ? " No se pudo eliminar porque la actividad está vinculada a otros registros del sistema."
                : " Error al eliminar la actividad.";
            echo json_encode(["status" => "error", "message" => $detalle]);
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

            $propiedad = $this->verificarPropiedad($datos['idActividad'], 'editar');
            if ($propiedad === null) {
                echo json_encode(["status" => "error", "message" => "La actividad indicada no existe."]);
                exit();
            }
            if ($propiedad === false) {
                echo json_encode(["status" => "error", "message" => "Acción no permitida: solo quien planificó la actividad o un administrador pueden modificarla."]);
                exit();
            }

            $this->validarDatosActividad($datos, $datos['idActividad']);

            $advertencias = $this->prepararSesiones($datos, $datos['idActividad']);

            $this->validarSesiones($datos, $datos['idActividad']);

            $resultado = $this->model->editarActividad($datos);

            if ($resultado) {

                Logger::actualizar('Actividad', $datos['idActividad'], $datos['nombreActividad']);

                $mensaje = "Actividad actualizada con éxito!";
                if ($advertencias) {
                    $mensaje .= ' Atención: ' . count($advertencias) . ' sesión(es) quedaron sin docente disponible.';
                }
                echo json_encode(["status" => "success", "message" => $mensaje, "advertencias" => $advertencias]);
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

        if (!$this->puedeModificar($actividad)) {
            Logger::registrar('ACCESS_DENIED', 'Actividad', $id,
                "Intento editar la actividad $id (" . $actividad['nombreActividad'] .
                ") planificada por otro usuario (" . ($actividad['nomCreador'] ?? 'sin registrar') . ")");
            header("Location: index.php?action=actividades&error=no_propietario");
            exit();
        }

        $lugaresAct = $this->model->obtenerLugaresActividadPorId($id);
        $gruposEtariosAct = $this->model->obtenerGruposEtariosActividad($id);
        $horariosAct = $this->model->obtenerHorariosActividad($id);
        $empleadosAct = $this->model->obtenerEmpleadosActividad($id);
        $docentesAct = $this->model->obtenerDocentesActividad($id);
        $seguimiento = $this->model->obtenerSeguimientoActividad($id);
        $docentesSesiones = $this->model->obtenerDocentesSesiones($id);

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
            'sesionesIniciales' => array_values(array_map(function ($s) use ($actividad, $docentesSesiones) {
                return [
                    'fecha' => $s['fechaSesion'],
                    'idHorario' => $s['idHorario'] ?? ($actividad['idHorario'] ?? null),
                    'idLugar' => $s['idLugarActividad'] ?? null,
                    'docentes' => $docentesSesiones[$s['idSegActividad']] ?? [],
                ];
            }, $seguimiento)),
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
        $this->model->sincronizarEstatusAutomatico();
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

    // Exportar Excel de actividades

    public function exportarExcel()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        ini_set('display_errors', '0');
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        require_once 'lib/AgendaExcel.php';

        $filtros = $this->obtenerFiltros();
        $this->model->sincronizarEstatusAutomatico();
        $actividades = $this->model->listarActividades($filtros);
        foreach ($actividades as &$actividad) {
            $actividad['ubicacion'] = $this->model->obtenerUbicacionActividad($actividad['idActividad']);
        }
        unset($actividad);

        $agenda = new AgendaExcel();
        $contenido = $agenda->generar($actividades);

        Logger::registrar('GENERAR', 'Actividades', null,
            'Genero Excel AGENDA FUNDACITE YARACUY (' . count($actividades) . ' actividades)');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="AGENDA FUNDACITE YARACUY.xlsx"');
        header('Content-Length: ' . strlen($contenido));
        header('Cache-Control: no-cache, must-revalidate');
        echo $contenido;
        exit();
    }
}