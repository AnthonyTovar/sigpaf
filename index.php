<?php
// Rutas del sistema







date_default_timezone_set('America/Caracas');

require_once 'RolHelper.php';
require_once 'model/SessionManager.php';
require_once 'controller/UsuarioController.php';
require_once 'controller/TipoUsuarioController.php';
require_once 'controller/AuthController.php';
require_once 'controller/ConfiguracionController.php';
require_once 'controller/CargoController.php';
require_once 'controller/UnidadEController.php';
require_once 'controller/VerticeController.php';
require_once 'controller/GrupoEtarioController.php';
require_once 'controller/GrupoEtnioController.php';
require_once 'controller/EstadoController.php';
require_once 'controller/MunicipioController.php';
require_once 'controller/ParroquiaController.php';
require_once 'controller/HorarioController.php';
require_once 'controller/EstatusController.php';
require_once 'controller/TipoActividadController.php';
require_once 'controller/EspacioUtilizarController.php';
require_once 'controller/EmpleadoController.php';
require_once 'controller/AreaEspecificaController.php';
require_once 'controller/LugarActividadController.php';
require_once 'controller/DocenteController.php';
require_once 'controller/TipoEntregaController.php';
require_once 'controller/EstrategiaDesarrolloController.php';
require_once 'controller/ActividadController.php';
require_once 'controller/UnidadMedidaController.php';
require_once 'controller/BitacoraController.php';
require_once 'controller/SeguimientoController.php';
require_once 'controller/ReporteController.php';
require_once 'controller/RespaldoController.php';

$usuarioCtrl = new UsuarioController();
$tipoUsuarioCtrl = new TipoUsuarioController();
$auth = new AuthController();
$configCtrl = new ConfiguracionController();
$cargoCtrl = new CargoController();
$UnidadECtrl = new UnidadEController();
$verticeCtrl = new VerticeController();
$grupoEtarioCtrl = new GrupoEtarioController();
$grupoEtnioCtrl = new GrupoEtnioController();
$estadoCtrl = new EstadoController();
$municipioCtrl = new MunicipioController();
$parroquiaCtrl = new ParroquiaController();
$horarioCtrl = new HorarioController();
$estatusCtrl = new EstatusController();
$tipoActividadCtrl = new TipoActividadController();
$espacioUtilizarCtrl = new EspacioUtilizarController();
$empleadoCtrl = new EmpleadoController();
$areaEspecificaCtrl = new AreaEspecificaController();
$lugarActividadCtrl = new LugarActividadController();
$docenteCtrl = new DocenteController();
$tipoEntregaCtrl = new TipoEntregaController();
$estrategiaDesarrolloCtrl = new EstrategiaDesarrolloController();
$actividadCtrl = new ActividadController();
$unidadMedidaCtrl = new UnidadMedidaController();
$bitacoraCtrl = new BitacoraController();
$seguimientoCtrl = new SeguimientoController();
$reporteCtrl = new ReporteController();
$respaldoCtrl = new RespaldoController();


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['action']) || empty($_GET['action'])) {
    header("Location: index.php?action=login");
    exit();
}

function verificarSesion()
{
    RolHelper::verificarSesion();
}

$action = $_GET['action'] ?? 'login';

switch ($action) {
    case 'login':
        $auth->login();
        break;
    case 'register':
        verificarSesion();
        $auth->register();
        break;
    case 'dashboard':
        verificarSesion();
        $auth->dashboard();
        break;
    case 'logout':

        $auth->logout();
        break;

    case 'pingSesion':


        $auth->pingSesion();
        break;


    // Gestión de usuario propio - todos los roles

    case 'gestionUsuario':
        verificarSesion();
        $usuarioCtrl->gestionUsuario();
        break;

    case 'actualizarPerfil':
        verificarSesion();
        $usuarioCtrl->actualizarPerfil();
        break;


    // Configuración - solo super usuario

    case 'configuracion':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $configCtrl->Mostrar();
        break;

    case 'exportarBd':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $respaldoCtrl->exportar();
        break;

    case 'importarBd':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $respaldoCtrl->importar();
        break;


    // Módulo usuarios - solo administrador y super usuario

    case 'usuarios':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $usuarioCtrl->listar();
        break;

    case 'guardarUsuario':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $usuarioCtrl->guardar();
        break;

    case 'eliminarUsuario':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $usuarioCtrl->eliminar();
        break;

    case 'consultarUsuario':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $usuarioCtrl->consultar();
        break;

    case 'editarUsuario':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $usuarioCtrl->editar();
        break;

    // Buscar empleado por cédula
    case 'buscarEmpleadoPorCedula':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $usuarioCtrl->buscarEmpleadoPorCedula();
        break;


    // Módulo tipos de usuario - solo administrador y super usuario

    case 'tiposUsuario':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $tipoUsuarioCtrl->listar();
        break;

    case 'guardarTipoUsuario':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $tipoUsuarioCtrl->guardar();
        break;

    case 'eliminarTipoUsuario':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $tipoUsuarioCtrl->eliminar();
        break;

    case 'consultarTipoUsuario':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $tipoUsuarioCtrl->consultar();
        break;

    case 'editarTipoUsuario':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $tipoUsuarioCtrl->editar();
        break;


    // Módulo cargos - solo administrador y super usuario

    case 'cargos':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $cargoCtrl->listar();
        break;

    case 'guardarCargo':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $cargoCtrl->guardar();
        break;

    case 'eliminarCargo':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $cargoCtrl->eliminar();
        break;

    case 'consultarCargo':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $cargoCtrl->consultar();
        break;

    case 'editarCargo':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $cargoCtrl->editar();
        break;


    // Módulo unidad ejecutora - solo administrador y super usuario

    case 'unidad':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $UnidadECtrl->listar();
        break;

    case 'guardarUnidadE':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $UnidadECtrl->guardar();
        break;

    case 'eliminarUnidadE':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $UnidadECtrl->eliminar();
        break;

    case 'consultarUnidadE':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $UnidadECtrl->consultar();
        break;

    case 'editarUnidadE':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $UnidadECtrl->editar();
        break;


    // Módulo área específica - solo administrador y super usuario

    case 'areasEspecificas':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $areaEspecificaCtrl->listar();
        break;
    case 'guardarAreaEspecifica':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $areaEspecificaCtrl->guardar();
        break;
    case 'eliminarAreaEspecifica':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $areaEspecificaCtrl->eliminar();
        break;
    case 'consultarAreaEspecifica':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $areaEspecificaCtrl->consultar();
        break;
    case 'editarAreaEspecifica':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $areaEspecificaCtrl->editar();
        break;


    // Módulo vértices - solo administrador y super usuario

    case 'listarVertice':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $verticeCtrl->listar();
        break;
    case 'guardarVertice':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $verticeCtrl->guardar();
        break;
    case 'eliminarVertice':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $verticeCtrl->eliminar();
        break;
    case 'consultarVertice':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $verticeCtrl->consultar();
        break;
    case 'editarVertice':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $verticeCtrl->editar();
        break;


    // Módulo grupo etario - solo administrador y super usuario

    case 'listarGrupoEtario':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $grupoEtarioCtrl->listar();
        break;
    case 'guardarGrupoEtario':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $grupoEtarioCtrl->guardar();
        break;
    case 'eliminarGrupoEtario':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $grupoEtarioCtrl->eliminar();
        break;
    case 'consultarGrupoEtario':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $grupoEtarioCtrl->consultar();
        break;
    case 'editarGrupoEtario':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $grupoEtarioCtrl->editar();
        break;


    // Módulo grupo étnico

    case 'gruposEtnios':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $grupoEtnioCtrl->listar();
        break;

    case 'guardarGrupoEtnio':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $grupoEtnioCtrl->guardar();
        break;

    case 'eliminarGrupoEtnio':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $grupoEtnioCtrl->eliminar();
        break;

    case 'consultarGrupoEtnio':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $grupoEtnioCtrl->consultar();
        break;

    case 'editarGrupoEtnio':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $grupoEtnioCtrl->editar();
        break;


    // Módulo estados - solo administrador y super usuario

    case 'estados':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $estadoCtrl->listar();
        break;
    case 'guardarEstado':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $estadoCtrl->guardar();
        break;
    case 'eliminarEstado':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $estadoCtrl->eliminar();
        break;
    case 'consultarEstado':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $estadoCtrl->consultar();
        break;
    case 'editarEstado':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $estadoCtrl->editar();
        break;


    // Módulo municipios - solo administrador y super usuario

    case 'municipios':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $municipioCtrl->listar();
        break;
    case 'guardarMunicipio':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $municipioCtrl->guardar();
        break;
    case 'eliminarMunicipio':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $municipioCtrl->eliminar();
        break;
    case 'consultarMunicipio':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $municipioCtrl->consultar();
        break;
    case 'editarMunicipio':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $municipioCtrl->editar();
        break;


    // Módulo horarios - solo administrador y super usuario

    case 'horarios':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $horarioCtrl->listar();
        break;

    case 'guardarHorario':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $horarioCtrl->guardar();
        break;

    case 'eliminarHorario':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $horarioCtrl->eliminar();
        break;

    case 'consultarHorario':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $horarioCtrl->consultar();
        break;

    case 'editarHorario':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $horarioCtrl->editar();
        break;


    // Módulo parroquias - solo administrador y super usuario

    case 'parroquias':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $parroquiaCtrl->listar();
        break;

    case 'guardarParroquia':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $parroquiaCtrl->guardar();
        break;

    case 'eliminarParroquia':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $parroquiaCtrl->eliminar();
        break;

    case 'consultarParroquia':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $parroquiaCtrl->consultar();
        break;

    case 'editarParroquia':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $parroquiaCtrl->editar();
        break;


    // Módulo estatus - solo administrador y super usuario

    case 'estatus':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $estatusCtrl->listar();
        break;

    case 'guardarEstatus':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $estatusCtrl->guardar();
        break;

    case 'eliminarEstatus':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $estatusCtrl->eliminar();
        break;

    case 'consultarEstatus':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $estatusCtrl->consultar();
        break;

    case 'editarEstatus':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $estatusCtrl->editar();
        break;


    // Módulo tipos de actividad - solo administrador y super usuario

    case 'tiposActividad':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $tipoActividadCtrl->listar();
        break;

    case 'guardarTipoActividad':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $tipoActividadCtrl->guardar();
        break;

    case 'eliminarTipoActividad':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $tipoActividadCtrl->eliminar();
        break;

    case 'consultarTipoActividad':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $tipoActividadCtrl->consultar();
        break;

    case 'editarTipoActividad':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $tipoActividadCtrl->editar();
        break;


    // Módulo lugar de actividad - solo administrador y super usuario

    case 'lugaresActividad':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $lugarActividadCtrl->listar();
        break;

    case 'guardarLugarActividad':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $lugarActividadCtrl->guardar();
        break;

    case 'eliminarLugarActividad':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $lugarActividadCtrl->eliminar();
        break;

    case 'consultarLugarActividad':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $lugarActividadCtrl->consultar();
        break;

    case 'editarLugarActividad':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $lugarActividadCtrl->editar();
        break;


    // Módulo espacios a utilizar - solo administrador y super usuario

    case 'espaciosUtilizar':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $espacioUtilizarCtrl->listar();
        break;

    case 'guardarEspacioUtilizar':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $espacioUtilizarCtrl->guardar();
        break;

    case 'eliminarEspacioUtilizar':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $espacioUtilizarCtrl->eliminar();
        break;

    case 'consultarEspacioUtilizar':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $espacioUtilizarCtrl->consultar();
        break;

    case 'editarEspacioUtilizar':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $espacioUtilizarCtrl->editar();
        break;


    // Módulo empleados - solo administrador y super usuario

    case 'empleados':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $empleadoCtrl->listar();
        break;

    case 'guardarEmpleado':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $empleadoCtrl->guardar();
        break;

    case 'eliminarEmpleado':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $empleadoCtrl->eliminar();
        break;

    case 'consultarEmpleado':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $empleadoCtrl->consultar();
        break;

    case 'editarEmpleado':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $empleadoCtrl->editar();
        break;


    // Módulo docentes - solo administrador y super usuario

    case 'docentes':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $docenteCtrl->listar();
        break;

    case 'guardarDocente':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $docenteCtrl->guardar();
        break;

    case 'eliminarDocente':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $docenteCtrl->eliminar();
        break;

    case 'consultarDocente':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $docenteCtrl->consultar();
        break;

    case 'editarDocente':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $docenteCtrl->editar();
        break;


    // Módulo tipos de entrega

    case 'tiposEntrega':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $tipoEntregaCtrl->listar();
        break;

    case 'guardarTipoEntrega':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $tipoEntregaCtrl->guardar();
        break;

    case 'eliminarTipoEntrega':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $tipoEntregaCtrl->eliminar();
        break;

    case 'consultarTipoEntrega':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $tipoEntregaCtrl->consultar();
        break;

    case 'editarTipoEntrega':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $tipoEntregaCtrl->editar();
        break;


    // Módulo estrategia desarrollo


        case 'estrategiasDesarrollo':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $estrategiaDesarrolloCtrl->listar();
        break;

    case 'guardarEstrategiaDesarrollo':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $estrategiaDesarrolloCtrl->guardar();
        break;

    case 'eliminarEstrategiaDesarrollo':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $estrategiaDesarrolloCtrl->eliminar();
        break;

    case 'consultarEstrategiaDesarrollo':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $estrategiaDesarrolloCtrl->consultar();
        break;

    case 'editarEstrategiaDesarrollo':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $estrategiaDesarrolloCtrl->editar();
        break;


    // Módulo actividades

    case 'actividades':
        verificarSesion();
        $actividadCtrl->listar();
        break;

    case 'nuevaActividad':
        verificarSesion();
        $actividadCtrl->nuevo();
        break;

    case 'guardarActividad':
        verificarSesion();
        $actividadCtrl->guardar();
        break;

    case 'eliminarActividad':
        verificarSesion();
        $actividadCtrl->eliminar();
        break;

    case 'consultarActividad':
        verificarSesion();
        $actividadCtrl->consultar();
        break;

    case 'editarActividad':
        verificarSesion();
        $actividadCtrl->editar();
        break;

    case 'obtenerHorariosOcupados':
        verificarSesion();
        $actividadCtrl->obtenerHorariosOcupados();
        break;

    case 'guardarLugarActividad':
        verificarSesion();
        $actividadCtrl->guardarLugarActividad();
        break;

    case 'guardarDocenteAjax':
        verificarSesion();
        $actividadCtrl->guardarDocente();
        break;


    // Módulo unidad de medida

    case 'unidadesMedida':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $unidadMedidaCtrl->listar();
        break;

    case 'guardarUnidadMedida':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $unidadMedidaCtrl->guardar();
        break;

    case 'eliminarUnidadMedida':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $unidadMedidaCtrl->eliminar();
        break;

    case 'consultarUnidadMedida':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $unidadMedidaCtrl->consultar();
        break;

    case 'editarUnidadMedida':
        verificarSesion();
        RolHelper::verificarAdministrador();
        $unidadMedidaCtrl->editar();
        break;


    // Modulo bitacora - solo super usuario

    case 'bitacora':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $bitacoraCtrl->listar();
        break;




    case 'seguimiento':
        verificarSesion();
        $seguimientoCtrl->listar();
        break;

    case 'registrarSeguimiento':
        verificarSesion();
        $seguimientoCtrl->nuevo();
        break;

    case 'obtenerSesionesSeguimiento':
        verificarSesion();
        $seguimientoCtrl->obtenerSesiones();
        break;

    case 'guardarSeguimiento':
        verificarSesion();
        $seguimientoCtrl->guardar();
        break;

    case 'guardarBeneficiarioSeguimiento':
        verificarSesion();
        $seguimientoCtrl->guardarBeneficiario();
        break;

    case 'cambiarEstatusActividad':
        verificarSesion();
        $seguimientoCtrl->cambiarEstatus();
        break;



    // Módulo reportes - solo super usuario

    case 'reportes':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $reporteCtrl->listar();
        break;

    case 'exportarPdfReportes':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $reporteCtrl->exportarPdf();
        break;








    case 'exportarPdfActividades':
        verificarSesion();
        $actividadCtrl->exportarPdf();
        break;

    case 'exportarPdfCargos':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $cargoCtrl->exportarPdf();
        break;
    case 'exportarPdfUnidadEjecutora':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $UnidadECtrl->exportarPdf();
        break;
    case 'exportarPdfAreasEspecificas':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $areaEspecificaCtrl->exportarPdf();
        break;
    case 'exportarPdfVertices':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $verticeCtrl->exportarPdf();
        break;
    case 'exportarPdfGruposEtarios':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $grupoEtarioCtrl->exportarPdf();
        break;
    case 'exportarPdfGruposEtnicos':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $grupoEtnioCtrl->exportarPdf();
        break;
    case 'exportarPdfEstados':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $estadoCtrl->exportarPdf();
        break;
    case 'exportarPdfMunicipios':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $municipioCtrl->exportarPdf();
        break;
    case 'exportarPdfParroquias':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $parroquiaCtrl->exportarPdf();
        break;
    case 'exportarPdfHorarios':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $horarioCtrl->exportarPdf();
        break;
    case 'exportarPdfEstatus':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $estatusCtrl->exportarPdf();
        break;
    case 'exportarPdfTiposActividad':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $tipoActividadCtrl->exportarPdf();
        break;
    case 'exportarPdfLugaresActividad':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $lugarActividadCtrl->exportarPdf();
        break;
    case 'exportarPdfEspaciosUtilizar':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $espacioUtilizarCtrl->exportarPdf();
        break;
    case 'exportarPdfEmpleados':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $empleadoCtrl->exportarPdf();
        break;
    case 'exportarPdfDocentes':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $docenteCtrl->exportarPdf();
        break;
    case 'exportarPdfTiposUsuario':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $tipoUsuarioCtrl->exportarPdf();
        break;
    case 'exportarPdfUsuarios':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $usuarioCtrl->exportarPdf();
        break;
    case 'exportarPdfTiposEntrega':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $tipoEntregaCtrl->exportarPdf();
        break;
    case 'exportarPdfEstrategiasDesarrollo':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $estrategiaDesarrolloCtrl->exportarPdf();
        break;
    case 'exportarPdfUnidadesMedida':
        verificarSesion();
        RolHelper::verificarSuperUsuario();
        $unidadMedidaCtrl->exportarPdf();
        break;


    default:
        $auth->login();
        break;

}