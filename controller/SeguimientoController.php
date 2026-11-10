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
        $beneficiarios = $this->model->listarBeneficiariosActividad($idActividad);
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
            'gruposEtarios' => $this->model->obtenerRangosEtariosActividad($idActividad),
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
        $asistentesLimpios = array_values(array_unique($asistentesLimpios));

        if (empty($asistentesLimpios)) {
            ValidationHelper::error(
                'Debe seleccionar al menos un beneficiario para registrar el seguimiento de la sesión.'
            );
        }

        if (!empty($asistentesLimpios)) {
            $rangos = $this->model->obtenerRangosEtariosActividad($sesion['idActividad']);
            $beneficiarios = $this->model->obtenerBeneficiariosPorIds($asistentesLimpios);
            if (count($beneficiarios) !== count($asistentesLimpios)) {
                ValidationHelper::error('Uno o más beneficiarios seleccionados no existen.');
            }

            $erroresEdad = [];
            foreach ($beneficiarios as $beneficiario) {
                $errorEdad = $this->errorEdadBeneficiario(
                    $beneficiario['fechaNacBeneficiario'] ?? '',
                    $sesion['fechaSesion'],
                    $rangos
                );
                if ($errorEdad !== '') {
                    $nombre = $beneficiario['nombresBeneficiario'] . ' ' . $beneficiario['apellidosBeneficiario'];
                    $erroresEdad[] = $nombre . ' ' . $errorEdad;
                }
            }
            if (!empty($erroresEdad)) {
                ValidationHelper::error('No se puede guardar la asistencia. ' . implode(' ', $erroresEdad));
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
        $stmt = $db->prepare("SELECT idSegActividad, nroSesionPlanificada, fechaSesion, idActividad
                              FROM seguimientoActividad WHERE idSegActividad = ?");
        $stmt->execute([$idSeg]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function calcularEdad($fechaNac, $fechaReferencia)
    {
        if (!ValidationHelper::fecha($fechaNac) || !ValidationHelper::fecha($fechaReferencia)) {
            return null;
        }

        $zona = new DateTimeZone('America/Caracas');
        $nacimiento = DateTime::createFromFormat('!Y-m-d', $fechaNac, $zona);
        $referencia = DateTime::createFromFormat('!Y-m-d', $fechaReferencia, $zona);
        if ($nacimiento > $referencia) {
            return null;
        }
        return (int) $nacimiento->diff($referencia)->y;
    }

    private function edadEnRangos($edad, $rangos)
    {
        foreach ($rangos as $rango) {
            if ($edad >= (int) $rango['edadMin'] && $edad <= (int) $rango['edadMax']) {
                return true;
            }
        }
        return false;
    }

    private function textoRangosEtarios($rangos)
    {
        $textos = [];
        foreach ($rangos as $rango) {
            $clave = $rango['edadMin'] . '-' . $rango['edadMax'];
            $textos[$clave] = $rango['nomGrupoEtareo'] . ' (' .
                (int) $rango['edadMin'] . ' a ' . (int) $rango['edadMax'] . ' años)';
        }
        return implode(', ', array_values($textos));
    }

    private function errorEdadBeneficiario($fechaNac, $fechaReferencia, $rangos)
    {
        if ($fechaNac === '') {
            return 'debe indicar su fecha de nacimiento para comprobar la edad.';
        }

        $edad = $this->calcularEdad($fechaNac, $fechaReferencia);
        if ($edad === null) {
            return 'tiene una fecha de nacimiento inválida o posterior a la sesión.';
        }
        if (empty($rangos)) {
            return 'no se puede validar porque la actividad no tiene grupos de edad configurados.';
        }
        if (!$this->edadEnRangos($edad, $rangos)) {
            return 'tiene ' . $edad . ' años en la fecha de la sesión y está fuera de los rangos permitidos: ' .
                $this->textoRangosEtarios($rangos) . '.';
        }
        return '';
    }

    private function dividirNombreCompleto($nombreCompleto)
    {
        $partes = preg_split('/\s+/u', trim($nombreCompleto));
        if (count($partes) < 2) {
            return ['', ''];
        }
        $corte = (int) ceil(count($partes) / 2);
        return [
            implode(' ', array_slice($partes, 0, $corte)),
            implode(' ', array_slice($partes, $corte))
        ];
    }

    private function normalizarCedulaFicha($valor)
    {
        $valor = strtoupper(trim((string) $valor));
        $valor = preg_replace('/\.0+$/', '', $valor);
        $valor = str_replace([' ', '.', '-'], '', $valor);
        if (ctype_digit($valor)) {
            $valor = 'V' . $valor;
        }
        return $valor;
    }

    private function fechaFichaExcel($valor)
    {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return '';
        }
        if (is_numeric($valor)) {
            $dias = (int) floor((float) $valor);
            if ($dias < 1) {
                return '';
            }
            $fecha = new DateTimeImmutable('1899-12-30', new DateTimeZone('UTC'));
            return $fecha->modify('+' . $dias . ' days')->format('Y-m-d');
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $formato) {
            $fecha = DateTime::createFromFormat('!' . $formato, $valor);
            if ($fecha && $fecha->format($formato) === $valor) {
                return $fecha->format('Y-m-d');
            }
        }
        return '';
    }


    public function guardarBeneficiario()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["status" => "error", "message" => "Método no permitido"]);
            exit();
        }

        $idSeg = trim($_POST['idSegActividad'] ?? '');
        $cedula = trim($_POST['cedulaBeneficiario'] ?? '');
        $nombres = trim($_POST['nombresBeneficiario'] ?? '');
        $apellidos = trim($_POST['apellidosBeneficiario'] ?? '');
        $nacionalidad = trim($_POST['nacionalidad'] ?? '');
        $fechaNac = trim($_POST['fechaNacBeneficiario'] ?? '');
        $sexo = trim($_POST['sexoBeneficiario'] ?? '');

        // Validación unificada (validationhelper)
        ValidationHelper::exigirRequerido($idSeg, 'Debe seleccionar la sesión de la actividad.');
        ValidationHelper::exigirRequerido($cedula, 'La cédula del beneficiario es obligatoria.');
        if (!ValidationHelper::cedula($cedula)) {
            ValidationHelper::error('La cédula no tiene un formato válido (V12345678).');
        }
        ValidationHelper::exigirRequerido($nombres, 'Los nombres del beneficiario son obligatorios.');
        ValidationHelper::exigirRequerido($apellidos, 'Los apellidos del beneficiario son obligatorios.');
        ValidationHelper::exigirRequerido($nacionalidad, 'Debe seleccionar la nacionalidad.');
        ValidationHelper::exigirMaxLongitud($nombres, 'Los nombres no pueden exceder los 250 caracteres.', 250);
        ValidationHelper::exigirMaxLongitud($apellidos, 'Los apellidos no pueden exceder los 250 caracteres.', 250);
        ValidationHelper::exigirRequerido($fechaNac, 'La fecha de nacimiento es obligatoria para comprobar la edad.');
        if (!in_array($sexo, ['M', 'F'])) {
            ValidationHelper::error('Debe seleccionar el sexo del beneficiario.');
        }

        $sesion = $this->buscarSesion($idSeg);
        if (!$sesion) {
            ValidationHelper::error('La sesión indicada no existe.');
        }
        $rangos = $this->model->obtenerRangosEtariosActividad($sesion['idActividad']);
        $errorEdad = $this->errorEdadBeneficiario($fechaNac, $sesion['fechaSesion'], $rangos);
        if ($errorEdad !== '') {
            ValidationHelper::error('No se puede registrar al beneficiario: ' . $errorEdad);
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
                "apellidos" => $apellidos,
                "fechaNacBeneficiario" => $fechaNac
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

        $idSeg = trim($_POST['idSegActividad'] ?? '');
        $lista = $_POST['beneficiarios'] ?? [];
        if (!is_array($lista)) {
            $lista = [];
        }

        ValidationHelper::exigirRequerido($idSeg, 'Debe seleccionar la sesión de la actividad.');
        $sesion = $this->buscarSesion($idSeg);
        if (!$sesion) {
            ValidationHelper::error('La sesión indicada no existe.');
        }
        $rangos = $this->model->obtenerRangosEtariosActividad($sesion['idActividad']);

        $validos = [];
        $errores = [];
        foreach ($lista as $indice => $b) {
            $cedula = $this->normalizarCedulaFicha($b['cedula'] ?? '');
            $fechaNac = trim($b['fechaNac'] ?? '');
            $sexo = strtoupper(trim($b['sexo'] ?? ''));
            $fila = $indice + 1;

            if (isset($b['nombreCompleto'])) {
                $nombreCompleto = trim($b['nombreCompleto']);
                list($nombres, $apellidos) = $this->dividirNombreCompleto($nombreCompleto);
                $nacionalidad = strpos($cedula, 'E') === 0 ? 'Extranjero' : 'Venezolano';
            } else {
                $nombres = trim($b['nombres'] ?? '');
                $apellidos = trim($b['apellidos'] ?? '');
                $nacionalidad = trim($b['nacionalidad'] ?? '');
            }

            if (!ValidationHelper::cedula($cedula) || mb_strlen($cedula) > 9) {
                $errores[] = "Fila $fila: cédula inválida.";
                continue;
            }
            if ($nombres === '' || $apellidos === '') {
                $errores[] = "Fila $fila: debe indicar nombre y apellido.";
                continue;
            }
            if (!ValidationHelper::soloLetras($nombres) || !ValidationHelper::soloLetras($apellidos)) {
                $errores[] = "Fila $fila: el nombre y apellido no pueden contener números.";
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
            $errorEdad = $this->errorEdadBeneficiario($fechaNac, $sesion['fechaSesion'], $rangos);
            if ($errorEdad !== '') {
                $errores[] = "Fila $fila: " . $errorEdad;
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

        // Reutilizar personas existentes y omitir repeticiones del lote
        $duplicados = [];
        $reutilizados = [];
        $vistos = [];
        $porGuardar = [];
        foreach ($validos as $b) {
            if (isset($vistos[$b['cedula']])) {
                $duplicados[] = $b['cedula'];
                continue;
            }
            $vistos[$b['cedula']] = true;
            $existente = $this->model->obtenerBeneficiarioPorCedula($b['cedula']);
            if ($existente) {
                $reutilizados[] = $existente;
                continue;
            }
            $porGuardar[] = $b;
        }

        if (empty($porGuardar) && empty($reutilizados) && empty($errores) && empty($duplicados)) {
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

        $mensaje = count($guardados) . ' beneficiario(s) nuevo(s) y ' .
            count($reutilizados) . ' existente(s) seleccionado(s) correctamente.';
        if (!empty($errores)) {
            $mensaje .= ' Filas con error: ' . implode(' ', $errores);
        }

        $procesados = array_merge($guardados, $reutilizados);
        echo json_encode([
            "status" => "success",
            "message" => $mensaje,
            "guardados" => $guardados,
            "reutilizados" => $reutilizados,
            "cedulasGuardadas" => array_column($procesados, 'cedulaBeneficiario'),
            "duplicados" => $duplicados,
            "errores" => $errores
        ]);
        exit();
    }

    // Importación de asistencia desde la ficha original
    public function importarBeneficiariosExcel()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ValidationHelper::error('Método no permitido.');
        }

        $idSeg = trim($_POST['idSegActividad'] ?? '');
        ValidationHelper::exigirRequerido($idSeg, 'Debe seleccionar la sesión antes de importar.');
        $sesion = $this->buscarSesion($idSeg);
        if (!$sesion) {
            ValidationHelper::error('La sesión indicada no existe.');
        }

        if (!isset($_FILES['archivoExcel'])) {
            $limite = ini_get('post_max_size');
            $mensaje = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0
                ? 'El servidor no recibió el archivo. Puede superar el límite de ' . $limite . ' configurado en PHP.'
                : 'Debe seleccionar un archivo Excel para importar.';
            ValidationHelper::error($mensaje);
        }
        $archivo = $_FILES['archivoExcel'];
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $erroresSubida = [
                UPLOAD_ERR_INI_SIZE => 'El archivo supera el límite de ' . ini_get('upload_max_filesize') . ' configurado en PHP.',
                UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tamaño permitido por el formulario.',
                UPLOAD_ERR_PARTIAL => 'El archivo se cargó incompletamente. Vuelva a intentarlo.',
                UPLOAD_ERR_NO_FILE => 'Debe seleccionar un archivo Excel para importar.',
                UPLOAD_ERR_NO_TMP_DIR => 'El servidor no tiene una carpeta temporal para recibir archivos.',
                UPLOAD_ERR_CANT_WRITE => 'El servidor no pudo escribir el archivo temporal. Revise los permisos.',
                UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la carga del archivo.'
            ];
            ValidationHelper::error($erroresSubida[$archivo['error']] ?? 'El servidor rechazó la carga del archivo.');
        }
        if (!is_uploaded_file($archivo['tmp_name'])) {
            ValidationHelper::error('El servidor no reconoció el archivo cargado. Vuelva a seleccionarlo e inténtelo de nuevo.');
        }
        if ($archivo['size'] <= 0 || $archivo['size'] > 10 * 1024 * 1024) {
            ValidationHelper::error('El archivo Excel debe ser mayor que 0 bytes y no superar los 10 MB.');
        }
        if (strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION)) !== 'xlsx') {
            ValidationHelper::error('El archivo debe tener extensión .xlsx. Los formatos .xls y .csv no son compatibles con esta ficha.');
        }

        if (!is_file('lib/FichaSeguimientoExcel.php')) {
            ValidationHelper::error('La actualización está incompleta: falta lib/FichaSeguimientoExcel.php. Copie ese archivo respetando exactamente su nombre.');
        }
        if (!class_exists('ZipArchive')) {
            ValidationHelper::error('El servidor no tiene habilitada la extensión ZIP de PHP. En Linux debe instalarse o habilitarse php_zip para leer archivos .xlsx.');
        }
        if (!class_exists('SimpleXMLElement')) {
            ValidationHelper::error('El servidor no tiene habilitada la extensión XML de PHP. Debe habilitarse php_xml para leer la ficha.');
        }

        require_once 'lib/FichaSeguimientoExcel.php';
        try {
            $filas = (new FichaSeguimientoExcel($archivo['tmp_name']))->leer();
        } catch (Throwable $e) {
            ValidationHelper::error('No se pudo leer la ficha Excel. Detalle: ' . $e->getMessage());
        }
        if (empty($filas)) {
            ValidationHelper::error('La hoja BEN-AE1 no contiene beneficiarios para importar.');
        }

        $rangos = $this->model->obtenerRangosEtariosActividad($sesion['idActividad']);
        $existentes = [];
        foreach ($this->model->listarBeneficiarios() as $beneficiario) {
            $cedulaExistente = $this->normalizarCedulaFicha($beneficiario['cedulaBeneficiario']);
            $existentes[$cedulaExistente] = $beneficiario;
        }

        $validos = [];
        $errores = [];
        $duplicadosArchivo = [];
        $vistos = [];
        foreach ($filas as $fila) {
            $numeroFila = $fila['fila'];
            $nombreCompleto = preg_replace('/\s+/u', ' ', trim($fila['nombreCompleto']));
            $cedula = $this->normalizarCedulaFicha($fila['cedula']);
            $fechaNac = $this->fechaFichaExcel($fila['fechaNac']);
            $fechaActividad = $this->fechaFichaExcel($fila['fechaActividad']);
            $nroActividad = is_numeric($fila['nroActividad']) ? (int) $fila['nroActividad'] : 0;
            $nroEsEntero = is_numeric($fila['nroActividad']) &&
                (float) $fila['nroActividad'] === (float) $nroActividad;
            $sexo = strtoupper(trim($fila['sexo']));
            $erroresFila = [];

            list($nombres, $apellidos) = $this->dividirNombreCompleto($nombreCompleto);
            if ($nombres === '' || !ValidationHelper::soloLetras($nombreCompleto)) {
                $erroresFila[] = 'debe indicar un nombre y apellido válido';
            }
            if (!ValidationHelper::cedula($cedula) || mb_strlen($cedula) > 9) {
                $erroresFila[] = 'la cédula es inválida';
            } elseif (isset($vistos[$cedula])) {
                $duplicadosArchivo[] = $numeroFila;
                continue;
            }
            if ($fechaActividad === '') {
                $erroresFila[] = 'la fecha de la actividad está vacía o es inválida';
            } elseif ($fechaActividad !== $sesion['fechaSesion']) {
                $erroresFila[] = 'la fecha de actividad debe ser ' .
                    date('d/m/Y', strtotime($sesion['fechaSesion']));
            }
            if (!$nroEsEntero || $nroActividad !== (int) $sesion['nroSesionPlanificada']) {
                $erroresFila[] = 'el número de actividad debe ser ' . (int) $sesion['nroSesionPlanificada'];
            }
            if (!in_array($sexo, ['M', 'F'])) {
                $erroresFila[] = 'el sexo debe ser M o F';
            }

            $existente = $existentes[$cedula] ?? null;
            $fechaValidar = $existente ? $existente['fechaNacBeneficiario'] : $fechaNac;
            $errorEdad = $this->errorEdadBeneficiario($fechaValidar, $sesion['fechaSesion'], $rangos);
            if ($errorEdad !== '') {
                $erroresFila[] = $errorEdad;
            }

            if (!empty($erroresFila)) {
                $errores[] = "Fila $numeroFila: " . implode('; ', $erroresFila) . '.';
                continue;
            }
            $vistos[$cedula] = true;

            if ($existente) {
                $validos[] = [
                    'idBeneficiario' => $existente['idBeneficiario'],
                    'cedula' => $existente['cedulaBeneficiario'],
                    'nombres' => $existente['nombresBeneficiario'],
                    'apellidos' => $existente['apellidosBeneficiario'],
                    'nacionalidad' => $existente['nacionalidad'],
                    'fechaNac' => $existente['fechaNacBeneficiario'],
                    'sexo' => $existente['sexoBeneficiario']
                ];
            } else {
                $validos[] = [
                    'cedula' => $cedula,
                    'nombres' => mb_substr($nombres, 0, 250),
                    'apellidos' => mb_substr($apellidos, 0, 250),
                    'nacionalidad' => strpos($cedula, 'E') === 0 ? 'Extranjero' : 'Venezolano',
                    'fechaNac' => $fechaNac,
                    'sexo' => $sexo
                ];
            }
        }

        if (!empty($errores)) {
            $detalle = implode(' ', array_slice($errores, 0, 12));
            if (count($errores) > 12) {
                $detalle .= ' Hay ' . (count($errores) - 12) . ' error(es) adicional(es).';
            }
            ValidationHelper::error('No se importó ningún beneficiario. Corrija el archivo: ' . $detalle);
        }
        if (empty($validos)) {
            ValidationHelper::error('No hay beneficiarios nuevos para procesar.');
        }

        $resultado = $this->model->importarAsistenciaDesdeFicha($idSeg, $validos);
        if ($resultado === false) {
            $detalle = $this->model->errorImportacion !== ''
                ? ' Detalle: ' . $this->model->errorImportacion
                : '';
            ValidationHelper::error('No se pudo completar la importación. No se guardó ningún cambio.' . $detalle);
        }

        Logger::registrar('CREATE', 'Seguimiento', $idSeg,
            'Importó ' . count($validos) . ' beneficiario(s) desde la ficha Excel');
        $mensaje = 'Importación completada: ' . count($validos) . ' beneficiario(s); ' .
            $resultado['nuevos'] . ' nuevo(s) y ' . $resultado['existentes'] . ' ya registrado(s). ' .
            'Asistencias añadidas: ' . $resultado['asistenciasNuevas'] . '.';
        if (!empty($duplicadosArchivo)) {
            $mensaje .= ' Filas duplicadas omitidas: ' . implode(', ', $duplicadosArchivo) . '.';
        }

        echo json_encode([
            'status' => 'success',
            'message' => $mensaje,
            'beneficiarios' => $resultado['beneficiarios'],
            'asistentes' => $this->model->obtenerAsistentes($idSeg)
        ]);
        exit();
    }


    // Exportar todas las actividades en la ficha por área específica
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

        try {
            require_once 'lib/FichaSeguimientoAEExcel.php';
            $actividades = $this->model->obtenerDatosExportacionExcel();
            $plantilla = 'lib/plantillas/Ficha SeguimientoAE.xlsx';
            $resultado = (new FichaSeguimientoAEExcel($plantilla))->generar($actividades);
            $contenido = $resultado['contenido'];

            Logger::registrar('GENERAR', 'Seguimiento', null,
                'Generó Ficha SeguimientoAE.xlsx (' . count($actividades) . ' actividades)');

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="Ficha SeguimientoAE.xlsx"');
            header('Content-Length: ' . strlen($contenido));
            header('Cache-Control: no-cache, must-revalidate');
            echo $contenido;
        } catch (Throwable $e) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'No se pudo exportar la ficha. ' . $e->getMessage();
        }
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
