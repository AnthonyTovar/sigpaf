<?php
require_once 'Controller/AuthController.php';
require_once 'Controller/ConfiguracionController.php';
require_once 'Controller/CargoController.php';
require_once 'Controller/UnidadEController.php';
require_once 'Controller/VerticeController.php';
require_once 'Controller/GrupoEtarioController.php';
require_once 'Controller/EstadoController.php';
require_once 'Controller/MunicipioController.php';
require_once 'Controller/NacionalidadController.php';
require_once 'Controller/ParroquiaController.php';
require_once 'Controller/HorarioController.php';
require_once 'Controller/EstatusController.php';
require_once 'Controller/TipoActividadController.php';
require_once 'Controller/EspacioUtilizarController.php';
require_once 'Controller/EmpleadoController.php';
require_once 'Controller/AreaEspecificaController.php';
require_once 'Controller/LugarActividadController.php';
require_once 'Controller/DocenteController.php';

$auth = new AuthController();
$configCtrl = new ConfiguracionController();
$cargoCtrl = new CargoController();
$UnidadECtrl = new UnidadEController();
$verticeCtrl = new VerticeController();
$grupoEtarioCtrl = new GrupoEtarioController();
$estadoCtrl = new EstadoController();
$municipioCtrl = new MunicipioController();
$parroquiaCtrl = new ParroquiaController();
$nacionalidadCtrl = new NacionalidadController();
$horarioCtrl = new HorarioController();
$estatusCtrl = new EstatusController();
$tipoActividadCtrl = new TipoActividadController();
$espacioCtrl = new EspacioUtilizarController();
$empleadoCtrl = new EmpleadoController();
$areaEspecificaCtrl = new AreaEspecificaController();
$lugarActividadCtrl = new LugarActividadController();
$docenteCtrl = new DocenteController();


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['action']) || empty($_GET['action'])) {
    header("Location: index.php?action=login");
    exit();
}

function verificarSesion()
{
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: index.php?action=login");
        exit();
    }
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
    case 'configuracion':
        verificarSesion();
        $configCtrl->Mostrar();
        break;
    case 'logout':
        verificarSesion();
        $auth->logout();
        break;

    // MÓDULO CARGOS
    case 'cargos':
        verificarSesion();
        $cargoCtrl->listar();
        break;

    case 'guardarCargo':
        verificarSesion();
        $cargoCtrl->guardar();
        break;

    case 'eliminarCargo':
        verificarSesion();
        $cargoCtrl->eliminar();
        break;

    case 'consultarCargo':
        verificarSesion();
        $cargoCtrl->consultar();
        break;

    case 'editarCargo':
        verificarSesion();
        $cargoCtrl->editar();
        break;

    // MÓDULO UNIDAD EJECUTORA
    case 'unidad':
        verificarSesion();
        $UnidadECtrl->listar();
        break;

    case 'guardarUnidadE':
        verificarSesion();
        $UnidadECtrl->guardar();
        break;

    case 'eliminarUnidadE':
        verificarSesion();
        $UnidadECtrl->eliminar();
        break;

    case 'consultarUnidadE':
        verificarSesion();
        $UnidadECtrl->consultar();
        break;

    case 'editarUnidadE':
        verificarSesion();
        $UnidadECtrl->editar();
        break;

    // MÓDULO ÁREA ESPECÍFICA
    case 'areasEspecificas':
        verificarSesion();
        $areaEspecificaCtrl->listar();
        break;
    case 'guardarAreaEspecifica':
        verificarSesion();
        $areaEspecificaCtrl->guardar();
        break;
    case 'eliminarAreaEspecifica':
        verificarSesion();
        $areaEspecificaCtrl->eliminar();
        break;
    case 'consultarAreaEspecifica':
        verificarSesion();
        $areaEspecificaCtrl->consultar();
        break;
    case 'editarAreaEspecifica':
        verificarSesion();
        $areaEspecificaCtrl->editar();
        break;


    // MÓDULO VÉRTICES
    case 'listarVertice':
        verificarSesion();
        $verticeCtrl->listar();
        break;
    case 'guardarVertice':
        verificarSesion();
        $verticeCtrl->guardar();
        break;
    case 'eliminarVertice':
        verificarSesion();
        $verticeCtrl->eliminar();
        break;
    case 'consultarVertice':
        verificarSesion();
        $verticeCtrl->consultar();
        break;
    case 'editarVertice':
        verificarSesion();
        $verticeCtrl->editar();
        break;

    // MÓDULO GRUPO ETARIO
    case 'listarGrupoEtario':
        verificarSesion();
        $grupoEtarioCtrl->listar();
        break;
    case 'guardarGrupoEtario':
        verificarSesion();
        $grupoEtarioCtrl->guardar();
        break;
    case 'eliminarGrupoEtario':
        verificarSesion();
        $grupoEtarioCtrl->eliminar();
        break;
    case 'consultarGrupoEtario':
        verificarSesion();
        $grupoEtarioCtrl->consultar();
        break;
    case 'editarGrupoEtario':
        verificarSesion();
        $grupoEtarioCtrl->editar();
        break;

    // MÓDULO ESTADOS
    case 'estados':
        verificarSesion();
        $estadoCtrl->listar();
        break;
    case 'guardarEstado':
        verificarSesion();
        $estadoCtrl->guardar();
        break;
    case 'eliminarEstado':
        verificarSesion();
        $estadoCtrl->eliminar();
        break;
    case 'consultarEstado':
        verificarSesion();
        $estadoCtrl->consultar();
        break;
    case 'editarEstado':
        verificarSesion();
        $estadoCtrl->editar();
        break;

    // MÓDULO MUNICIPIOS
    case 'municipios':
        verificarSesion();
        $municipioCtrl->listar();
        break;
    case 'guardarMunicipio':
        verificarSesion();
        $municipioCtrl->guardar();
        break;
    case 'eliminarMunicipio':
        verificarSesion();
        $municipioCtrl->eliminar();
        break;
    case 'consultarMunicipio':
        verificarSesion();
        $municipioCtrl->consultar();
        break;
    case 'editarMunicipio':
        verificarSesion();
        $municipioCtrl->editar();
        break;

    // MÓDULO HORARIOS
    case 'horarios':
        verificarSesion();
        $horarioCtrl->listar();
        break;

    case 'guardarHorario':
        verificarSesion();
        $horarioCtrl->guardar();
        break;

    case 'eliminarHorario':
        verificarSesion();
        $horarioCtrl->eliminar();
        break;

    case 'consultarHorario':
        verificarSesion();
        $horarioCtrl->consultar();
        break;

    case 'editarHorario':
        verificarSesion();
        $horarioCtrl->editar();
        break;

    // MÓDULO PARROQUIAS
    case 'parroquias':
        verificarSesion();
        $parroquiaCtrl->listar();
        break;

    case 'guardarParroquia':
        verificarSesion();
        $parroquiaCtrl->guardar();
        break;

    case 'eliminarParroquia':
        verificarSesion();
        $parroquiaCtrl->eliminar();
        break;

    case 'consultarParroquia':
        verificarSesion();
        $parroquiaCtrl->consultar();
        break;

    case 'editarParroquia':
        verificarSesion();
        $parroquiaCtrl->editar();
        break;

    // MÓDULO ESTATUS
    case 'estatus':
        verificarSesion();
        $estatusCtrl->listar();
        break;

    case 'guardarEstatus':
        verificarSesion();
        $estatusCtrl->guardar();
        break;

    case 'eliminarEstatus':
        verificarSesion();
        $estatusCtrl->eliminar();
        break;

    case 'consultarEstatus':
        verificarSesion();
        $estatusCtrl->consultar();
        break;

    case 'editarEstatus':
        verificarSesion();
        $estatusCtrl->editar();
        break;

    // MÓDULO TIPOS DE ACTIVIDAD
    case 'tiposActividad':
        verificarSesion();
        $tipoActividadCtrl->listar();
        break;

    case 'guardarTipoActividad':
        verificarSesion();
        $tipoActividadCtrl->guardar();
        break;

    case 'eliminarTipoActividad':
        verificarSesion();
        $tipoActividadCtrl->eliminar();
        break;

    case 'consultarTipoActividad':
        verificarSesion();
        $tipoActividadCtrl->consultar();
        break;

    case 'editarTipoActividad':
        verificarSesion();
        $tipoActividadCtrl->editar();
        break;

    // MÓDULO LUGAR DE ACTIVIDAD
    case 'lugaresActividad':
        verificarSesion();
        $lugarActividadCtrl->listar();
        break;
    case 'guardarLugarActividad':
        verificarSesion();
        $lugarActividadCtrl->guardar();
        break;
    case 'eliminarLugarActividad':
        verificarSesion();
        $lugarActividadCtrl->eliminar();
        break;
    case 'consultarLugarActividad':
        verificarSesion();
        $lugarActividadCtrl->consultar();
        break;
    case 'editarLugarActividad':
        verificarSesion();
        $lugarActividadCtrl->editar();
        break;

    // MÓDULO ESPACIOS
    case 'espacios':
        verificarSesion();
        $espacioCtrl->listar();
        break;

    case 'guardarEspacio':
        verificarSesion();
        $espacioCtrl->guardar();
        break;

    case 'eliminarEspacio':
        verificarSesion();
        $espacioCtrl->eliminar();
        break;

    case 'consultarEspacio':
        verificarSesion();
        $espacioCtrl->consultar();
        break;

    case 'editarEspacio':
        verificarSesion();
        $espacioCtrl->editar();
        break;

    // MÓDULO EMPLEADOS
    case 'empleados':
        verificarSesion();
        $empleadoCtrl->listar();
        break;

    case 'guardarEmpleado':
        verificarSesion();
        $empleadoCtrl->guardar();
        break;

    case 'eliminarEmpleado':
        verificarSesion();
        $empleadoCtrl->eliminar();
        break;

    case 'consultarEmpleado':
        verificarSesion();
        $empleadoCtrl->consultar();
        break;

    case 'editarEmpleado':
        verificarSesion();
        $empleadoCtrl->editar();
        break;

    // MÓDULO DOCENTES
    case 'docentes':
        verificarSesion();
        $docenteCtrl->listar();
        break;

    case 'guardarDocente':
        verificarSesion();
        $docenteCtrl->guardar();
        break;

    case 'eliminarDocente':
        verificarSesion();
        $docenteCtrl->eliminar();
        break;

    case 'consultarDocente':
        verificarSesion();
        $docenteCtrl->consultar();
        break;

    case 'editarDocente':
        verificarSesion();
        $docenteCtrl->editar();
        break;

    // MÓDULO NACIONALIDADES
    case 'nacionalidades':
        verificarSesion();
        $nacionalidadCtrl->listar();
        break;

    case 'guardarNacionalidad':
        verificarSesion();
        $nacionalidadCtrl->guardar();
        break;

    case 'eliminarNacionalidad':
        verificarSesion();
        $nacionalidadCtrl->eliminar();
        break;

    case 'consultarNacionalidad':
        verificarSesion();
        $nacionalidadCtrl->consultar();
        break;

    case 'editarNacionalidad':
        verificarSesion();
        $nacionalidadCtrl->editar();
        break;

    default:
        $auth->login();
        break;

}