<?php
// Controlador de seguimiento



require_once 'model/SeguimientoModel.php';
require_once 'ValidationHelper.php';
require_once 'Logger.php';
require_once 'RolHelper.php';


class SeguimientoController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new SeguimientoModel();
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
            'desde'     => trim($_GET['desde'] ?? ''),
            'hasta'     => trim($_GET['hasta'] ?? ''),
            'idEstatus' => trim($_GET['idEstatus'] ?? ''),
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
        return $descripcion;
    }

    public function listar()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $filtros = $this->obtenerFiltros();
        $actividades = $this->model->listarActividadesProgreso($filtros);
        $tiposEntrega = $this->obtenerTiposEntrega();

        $this->renderizar('view/SeguimientoView', [
            'actividades' => $actividades,
            'tiposEntrega' => $tiposEntrega,
            'filtros' => $filtros,
            'estatus' => $this->model->obtenerEstatus(),
            'idEmpleadoUsuario' => $_SESSION['idEmpleado'] ?? '',
            'esAdmin' => RolHelper::esAdministrador()
        ]);
    }



    public function nuevo()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $idActividad = $_GET['id'] ?? '';
        $actividad = $idActividad ? $this->model->obtenerActividad($idActividad) : null;
        if (!$actividad) {
            header("Location: index.php?action=seguimiento");
            exit();
        }

        $idEmpleadoUsuario = $_SESSION['idEmpleado'] ?? '';
        $esAdmin = RolHelper::esAdministrador();

        $this->renderizar('view/SeguimientoNuevoView', [
            'actividad' => $actividad,
            'tiposEntrega' => $this->obtenerTiposEntrega(),
            'estatus' => $this->model->obtenerEstatus(),
            'idEmpleadoUsuario' => $idEmpleadoUsuario,
            'esAdmin' => $esAdmin,

            'puedeEditarEstatus' => $esAdmin || $actividad['idEmpleado'] === $idEmpleadoUsuario
        ]);
    }

    private function obtenerTiposEntrega()
    {
        $db = Database::getConnection();
        return $db->query("SELECT idTipEntrega, nomTipEntrega FROM tipoEntrega ORDER BY nomTipEntrega")
                  ->fetchAll(PDO::FETCH_ASSOC);
    }


    public function obtenerSesiones()
    {
        header('Content-Type: application/json');

        $idActividad = $_GET['idActividad'] ?? '';
        if (empty($idActividad)) {
            echo json_encode(['status' => 'error', 'message' => 'No se recibió la actividad']);
            exit;
        }





        $sesiones = $this->model->ensureSesiones($idActividad);
        $actividad = $this->model->obtenerActividad($idActividad);
        $beneficiarios = $this->model->listarBeneficiarios();
        $asistentesPorSesion = [];
        foreach ($sesiones as $s) {
            $asistentesPorSesion[$s['idSegActividad']] = $this->model->obtenerAsistentes($s['idSegActividad']);
        }

        echo json_encode([
            'status' => 'success',
            'actividad' => $actividad,
            'sesiones' => $sesiones,
            'beneficiarios' => $beneficiarios,
            'asistentes' => $asistentesPorSesion,
            'hoy' => date('Y-m-d')
        ]);
        exit;
    }


    public function guardar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["status" => "error", "message" => "Método no permitido"]);
            exit();
        }

        $idSeg = trim($_POST['idSegActividad'] ?? '');
        $logro = trim($_POST['logroActividad'] ?? '');
        $obstaculo = trim($_POST['observObstaculo'] ?? '');
        $idTipEntrega = trim($_POST['idTipEntrega'] ?? '');
        $asistentes = $_POST['asistentes'] ?? [];

        // Validación unificada (validationhelper)
        ValidationHelper::exigirRequerido($idSeg, 'No se recibió la sesión a registrar.');
        ValidationHelper::exigirRequerido($logro, 'Debe describir el logro de la sesión.');
        ValidationHelper::exigirMaxLongitud($logro, 'El logro no puede exceder los 500 caracteres.', 500);
        ValidationHelper::exigirMaxLongitud($obstaculo, 'Las observaciones no pueden exceder los 500 caracteres.', 500);
        ValidationHelper::exigirRequerido($idTipEntrega, 'Debe seleccionar el tipo de entrega de la sesión.');


        $sesion = $this->buscarSesion($idSeg);
        if (!$sesion) {
            ValidationHelper::error('La sesión indicada no existe.');
        }
        if ($sesion['fechaSesion'] > date('Y-m-d')) {
            ValidationHelper::error('No se puede registrar una sesión con fecha futura (' .
                date('d/m/Y', strtotime($sesion['fechaSesion'])) . ').');
        }

        $asistentesLimpios = [];
        if (is_array($asistentes)) {
            foreach ($asistentes as $idBen) {
                $idBen = trim($idBen);
                if ($idBen !== '') {
                    $asistentesLimpios[] = $idBen;
                }
            }
        }

        $resultado = $this->model->guardarSeguimiento($idSeg, $logro, $obstaculo, $idTipEntrega, $asistentesLimpios);

        if ($resultado) {
            Logger::actualizar('Seguimiento', $idSeg, 'Sesión ' . $sesion['nroSesionPlanificada']);

            echo json_encode([
                "status" => "success",
                "message" => "Seguimiento de la sesión " . $sesion['nroSesionPlanificada'] . " registrado correctamente."
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al registrar el seguimiento."]);
        }
        exit();
    }

    // Sesiones de una actividad para el modal del listado (sin crear nuevas)
    public function obtenerSesionesDeActividad()
    {
        header('Content-Type: application/json');

        $idActividad = trim($_GET['idActividad'] ?? '');
        if ($idActividad === '') {
            echo json_encode(['status' => 'error', 'message' => 'No se recibió la actividad']);
            exit();
        }

        $actividad = $this->model->obtenerActividadDetalle($idActividad);
        if (!$actividad) {
            echo json_encode(['status' => 'error', 'message' => 'La actividad no existe']);
            exit();
        }

        echo json_encode([
            'status' => 'success',
            'actividad' => $actividad,
            'sesiones' => $this->model->obtenerSesiones($idActividad)
        ]);
        exit();
    }

    // Ficha completa de una sesión: datos + beneficiarios
    public function obtenerFichaSesion()
    {
        header('Content-Type: application/json');

        $idSeg = trim($_GET['id'] ?? '');
        $sesion = $idSeg !== '' ? $this->model->obtenerSesionDetalle($idSeg) : null;
        if (!$sesion) {
            echo json_encode(['status' => 'error', 'message' => 'La sesión indicada no existe']);
            exit();
        }

        echo json_encode([
            'status' => 'success',
            'actividad' => $this->model->obtenerActividadDetalle($sesion['idActividad']),
            'sesion' => $sesion,
            'beneficiarios' => $this->model->obtenerBeneficiariosSesion($idSeg)
        ]);
        exit();
    }

    // Exportar PDF del listado de seguimiento con los filtros aplicados
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
        $actividades = $this->model->listarActividadesProgreso($filtros);
        $filtrosDesc = $this->describirFiltros($filtros);

        ob_start();
        require 'view/public/reportes/SeguimientoReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'landscape');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Seguimiento', null,
            'Generó PDF de seguimiento (' . count($actividades) . ' actividades)');

        $dompdf->stream("reporte_seguimiento_sigpaf.pdf", ["Attachment" => false]);
        exit();
    }

    // Exportar PDF de la ficha de una sesión (con su lista de beneficiarios)
    public function exportarPdfSesion()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $idSeg = trim($_GET['id'] ?? '');
        $sesion = $idSeg !== '' ? $this->model->obtenerSesionDetalle($idSeg) : null;
        if (!$sesion) {
            header("Location: index.php?action=seguimiento");
            exit();
        }

        ini_set('display_errors', '0');
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        require_once 'lib/dompdf/autoload.inc.php';

        $actividad = $this->model->obtenerActividadDetalle($sesion['idActividad']);
        $beneficiarios = $this->model->obtenerBeneficiariosSesion($idSeg);

        ob_start();
        require 'view/public/reportes/SesionReporteView.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        Logger::registrar('GENERAR', 'Seguimiento', $idSeg,
            'Generó PDF de la sesión ' . $sesion['nroSesionPlanificada'] . ' de ' . ($actividad['nombreActividad'] ?? ''));

        $dompdf->stream("sesion_" . $sesion['nroSesionPlanificada'] . "_" . $sesion['idActividad'] . ".pdf", ["Attachment" => false]);
        exit();
    }


    private function buscarSesion($idSeg)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT idSegActividad, nroSesionPlanificada, fechaSesion
                              FROM seguimientoActividad WHERE idSegActividad = ?");
        $stmt->execute([$idSeg]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function guardarBeneficiario()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["status" => "error", "message" => "Método no permitido"]);
            exit();
        }

        $cedula = trim($_POST['cedulaBeneficiario'] ?? '');
        $nombres = trim($_POST['nombresBeneficiario'] ?? '');
        $apellidos = trim($_POST['apellidosBeneficiario'] ?? '');
        $nacionalidad = trim($_POST['nacionalidad'] ?? '');
        $fechaNac = trim($_POST['fechaNacBeneficiario'] ?? '');
        $sexo = trim($_POST['sexoBeneficiario'] ?? '');

        // Validación unificada (validationhelper)
        ValidationHelper::exigirRequerido($cedula, 'La cédula del beneficiario es obligatoria.');
        if (!ValidationHelper::cedula($cedula)) {
            ValidationHelper::error('La cédula no tiene un formato válido (V12345678).');
        }
        ValidationHelper::exigirRequerido($nombres, 'Los nombres del beneficiario son obligatorios.');
        ValidationHelper::exigirRequerido($apellidos, 'Los apellidos del beneficiario son obligatorios.');
        ValidationHelper::exigirRequerido($nacionalidad, 'Debe seleccionar la nacionalidad.');
        ValidationHelper::exigirMaxLongitud($nombres, 'Los nombres no pueden exceder los 250 caracteres.', 250);
        ValidationHelper::exigirMaxLongitud($apellidos, 'Los apellidos no pueden exceder los 250 caracteres.', 250);
        if ($fechaNac !== '' && !ValidationHelper::fecha($fechaNac)) {
            ValidationHelper::error('La fecha de nacimiento no es válida.');
        }
        if (!in_array($sexo, ['M', 'F'])) {
            ValidationHelper::error('Debe seleccionar el sexo del beneficiario.');
        }


        $existente = $this->model->obtenerBeneficiarioPorCedula($cedula);
        if ($existente) {
            echo json_encode([
                "status" => "error",
                "message" => "Ya existe un beneficiario con esa cédula.",
                "idBeneficiario" => $existente['idBeneficiario']
            ]);
            exit();
        }

        $nuevoId = $this->model->registrarBeneficiario($cedula, $nombres, $apellidos, $nacionalidad, $fechaNac, $sexo);

        if ($nuevoId) {
            Logger::crear('Beneficiario', $nuevoId, $nombres . ' ' . $apellidos);

            echo json_encode([
                "status" => "success",
                "message" => "Beneficiario registrado correctamente.",
                "id" => $nuevoId,
                "cedula" => $cedula,
                "nombres" => $nombres,
                "apellidos" => $apellidos
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al registrar el beneficiario."]);
        }
        exit();
    }

    // Registro por lotes: una página de beneficiarios por petición (guardado modular)
    public function guardarBeneficiariosLote()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["status" => "error", "message" => "Método no permitido"]);
            exit();
        }

        $lista = $_POST['beneficiarios'] ?? [];
        if (!is_array($lista)) {
            $lista = [];
        }

        $validos = [];
        $errores = [];
        foreach ($lista as $indice => $b) {
            $cedula = strtoupper(trim($b['cedula'] ?? ''));
            $nombres = trim($b['nombres'] ?? '');
            $apellidos = trim($b['apellidos'] ?? '');
            $nacionalidad = trim($b['nacionalidad'] ?? '');
            $fechaNac = trim($b['fechaNac'] ?? '');
            $sexo = trim($b['sexo'] ?? '');
            $fila = $indice + 1;

            if (!ValidationHelper::cedula($cedula)) {
                $errores[] = "Fila $fila: cédula inválida.";
                continue;
            }
            if ($nombres === '' || $apellidos === '') {
                $errores[] = "Fila $fila: nombres y apellidos son obligatorios.";
                continue;
            }
            if (!ValidationHelper::soloLetras($nombres) || !ValidationHelper::soloLetras($apellidos)) {
                $errores[] = "Fila $fila: nombres y apellidos no pueden contener números.";
                continue;
            }
            if (!in_array($nacionalidad, ['Venezolano', 'Extranjero'])) {
                $errores[] = "Fila $fila: nacionalidad inválida.";
                continue;
            }
            if (!in_array($sexo, ['M', 'F'])) {
                $errores[] = "Fila $fila: sexo inválido.";
                continue;
            }
            if ($fechaNac !== '' && !ValidationHelper::fecha($fechaNac)) {
                $errores[] = "Fila $fila: fecha de nacimiento inválida.";
                continue;
            }

            $validos[] = [
                'cedula' => $cedula,
                'nombres' => mb_substr($nombres, 0, 250),
                'apellidos' => mb_substr($apellidos, 0, 250),
                'nacionalidad' => $nacionalidad,
                'fechaNac' => $fechaNac,
                'sexo' => $sexo
            ];
        }

        // Omitir duplicados (en BD o dentro del mismo lote)
        $duplicados = [];
        $vistos = [];
        $porGuardar = [];
        foreach ($validos as $b) {
            if (isset($vistos[$b['cedula']]) || $this->model->obtenerBeneficiarioPorCedula($b['cedula'])) {
                $duplicados[] = $b['cedula'];
                continue;
            }
            $vistos[$b['cedula']] = true;
            $porGuardar[] = $b;
        }

        if (empty($porGuardar) && empty($errores) && empty($duplicados)) {
            echo json_encode(["status" => "error", "message" => "No se recibieron beneficiarios para guardar."]);
            exit();
        }

        $guardados = $this->model->registrarBeneficiariosLote($porGuardar);

        if ($guardados === false) {
            echo json_encode(["status" => "error", "message" => "Error al registrar la página de beneficiarios. No se guardó ninguna fila."]);
            exit();
        }

        foreach ($guardados as $g) {
            Logger::crear('Beneficiario', $g['idBeneficiario'], $g['nombresBeneficiario'] . ' ' . $g['apellidosBeneficiario']);
        }

        $mensaje = count($guardados) . ' beneficiario(s) registrado(s) correctamente.';
        if (!empty($errores)) {
            $mensaje .= ' Filas con error: ' . implode(' ', $errores);
        }

        echo json_encode([
            "status" => "success",
            "message" => $mensaje,
            "guardados" => $guardados,
            "cedulasGuardadas" => array_column($guardados, 'cedulaBeneficiario'),
            "duplicados" => $duplicados,
            "errores" => $errores
        ]);
        exit();
    }


    public function cambiarEstatus()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["status" => "error", "message" => "Método no permitido"]);
            exit();
        }

        $idActividad = trim($_POST['idActividad'] ?? '');
        $idEstatus = trim($_POST['idEstatus'] ?? '');

        // Validación unificada (validationhelper)
        ValidationHelper::exigirRequerido($idActividad, 'No se recibió la actividad.');
        ValidationHelper::exigirRequerido($idEstatus, 'Debe seleccionar el estatus.');

        if (!ValidationHelper::maxLongitud($idActividad, 10)) {
            ValidationHelper::error('El identificador de la actividad no es válido.');
        }

        $actividad = $this->model->obtenerActividad($idActividad);
        if (!$actividad) {
            ValidationHelper::error('La actividad indicada no existe.');
        }

        if (!$this->model->existeEstatus($idEstatus)) {
            ValidationHelper::error('El estatus seleccionado no existe.');
        }

        // Permisos
        $idEmpleadoUsuario = $_SESSION['idEmpleado'] ?? '';
        if (!RolHelper::esAdministrador() && $actividad['idEmpleado'] !== $idEmpleadoUsuario) {
            ValidationHelper::error('Solo el responsable de la actividad puede cambiar su estatus.');
        }

        // Validar ejecución
        if ($idEstatus === 'ES0006') {
            $registradas = $this->model->contarSesionesRegistradas($idActividad);
            if ($registradas < 1) {
                ValidationHelper::error(
                    'No se puede marcar como Ejecutada: la actividad no tiene ninguna sesión con seguimiento registrado. ' .
                    'Registre el seguimiento de al menos una sesión.'
                );
            }
        }

        $resultado = $this->model->actualizarEstatusActividad($idActividad, $idEstatus);

        if ($resultado) {
            $nombreEstatus = '';
            foreach ($this->model->obtenerEstatus() as $e) {
                if ($e['idEstatus'] === $idEstatus) {
                    $nombreEstatus = $e['nomEstatus'];
                    break;
                }
            }

            Logger::actualizar('Actividad', $idActividad,
                $actividad['nombreActividad'] . ' → estatus: ' . $nombreEstatus);

            echo json_encode([
                "status" => "success",
                "message" => 'Estatus actualizado a "' . $nombreEstatus . '".'
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al actualizar el estatus."]);
        }
        exit();
    }
}
