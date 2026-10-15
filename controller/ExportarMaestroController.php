<?php
require_once 'RolHelper.php';
require_once 'Logger.php';
require_once 'model/AreaEspecificaModel.php';
require_once 'model/CargoModel.php';
require_once 'model/DocenteModel.php';
require_once 'model/EmpleadoModel.php';
require_once 'model/EspacioUtilizarModel.php';
require_once 'model/EstadoModel.php';
require_once 'model/EstatusModel.php';
require_once 'model/EstrategiaDesarrolloModel.php';
require_once 'model/GrupoEtarioModel.php';
require_once 'model/GrupoEtnioModel.php';
require_once 'model/HorarioModel.php';
require_once 'model/LugarActividadModel.php';
require_once 'model/MunicipioModel.php';
require_once 'model/ParroquiaModel.php';
require_once 'model/TipoActividadModel.php';
require_once 'model/TipoEntregaModel.php';
require_once 'model/TipoUsuarioModel.php';
require_once 'model/UnidadEModel.php';
require_once 'model/UnidadMedidaModel.php';
require_once 'model/UsuarioModel.php';
require_once 'model/VerticeModel.php';

/**
 * EXPORTACIÓN DE MAESTROS A PDF (módulos de Configuración)
 * Un solo controlador genérico: cada módulo se describe en el mapa
 * MODULOS (modelo + método + columnas) y se renderiza con Dompdf
 * usando la misma plantilla institucional que el reporte mensual.
 * Acceso: SOLO Super Usuario (los maestros se gestionan en Configuración).
 */
class ExportarMaestroController
{
    // Catálogo blanco de módulos exportables: nadie puede pedir un módulo
    // que no esté en esta lista (evita exportar tablas sensibles por URL).
    const MODULOS = [
        'cargo' => [
            'titulo' => 'Listado de Cargos',
            'archivo' => 'Cargos',
            'modelo' => 'CargoModel', 'metodo' => 'listarCargos',
            'orientacion' => 'portrait',
            'columnas' => ['Código' => 'idCargo', 'Cargo' => 'nombreCargo', 'Descripción' => 'descripcionCargo'],
        ],
        'unidadejecutora' => [
            'titulo' => 'Listado de Unidades Ejecutoras',
            'archivo' => 'Unidades_Ejecutoras',
            'modelo' => 'UnidadEModel', 'metodo' => 'listarUnidadE',
            'orientacion' => 'portrait',
            'columnas' => ['Código' => 'idUnidadEjecutora', 'Unidad Ejecutora' => 'nomUnidadEjecutora', 'Descripción' => 'desUnidadEjecutora'],
        ],
        'areaespecifica' => [
            'titulo' => 'Listado de Áreas Específicas',
            'archivo' => 'Areas_Especificas',
            'modelo' => 'AreaEspecificaModel', 'metodo' => 'listarAreas',
            'orientacion' => 'portrait',
            'columnas' => ['Código' => 'idAreaE', 'Área Específica' => 'nomAreaE'],
        ],
        'vertice' => [
            'titulo' => 'Listado de Vértices',
            'archivo' => 'Vertices',
            'modelo' => 'VerticeModel', 'metodo' => 'listarVertice',
            'orientacion' => 'portrait',
            'columnas' => ['Código' => 'idVertice', 'Vértice' => 'nombreVertice', 'Descripción' => 'descVertice'],
        ],
        'grupoetario' => [
            'titulo' => 'Listado de Grupos Etarios',
            'archivo' => 'Grupos_Etarios',
            'modelo' => 'GrupoEtarioModel', 'metodo' => 'listarGrupos',
            'orientacion' => 'portrait',
            'columnas' => ['Código' => 'idGrupoEtareo', 'Grupo' => 'nomGrupoEtareo', 'Edad Mín.' => 'edadMin', 'Edad Máx.' => 'edadMax', 'Descripción' => 'descGrupoEtareo'],
        ],
        'grupoetnio' => [
            'titulo' => 'Listado de Grupos Étnicos',
            'archivo' => 'Grupos_Etnicos',
            'modelo' => 'GrupoEtnioModel', 'metodo' => 'listarGrupos',
            'orientacion' => 'portrait',
            'columnas' => ['Código' => 'idGrupoEtnio', 'Grupo Étnico' => 'nomGrupoEtnio', 'Descripción' => 'desGrupoEtnio'],
        ],
        'estado' => [
            'titulo' => 'Listado de Estados',
            'archivo' => 'Estados',
            'modelo' => 'EstadoModel', 'metodo' => 'listarEstados',
            'orientacion' => 'portrait',
            'columnas' => ['Código' => 'idEstado', 'Estado' => 'nombreEstado'],
        ],
        'municipio' => [
            'titulo' => 'Listado de Municipios',
            'archivo' => 'Municipios',
            'modelo' => 'MunicipioModel', 'metodo' => 'listarMunicipios',
            'orientacion' => 'portrait',
            'columnas' => ['Código' => 'idMunicipio', 'Municipio' => 'nombreMunicipio', 'Estado' => 'nombreEstado'],
        ],
        'parroquia' => [
            'titulo' => 'Listado de Parroquias',
            'archivo' => 'Parroquias',
            'modelo' => 'ParroquiaModel', 'metodo' => 'listarParroquias',
            'orientacion' => 'portrait',
            'columnas' => ['Código' => 'idParroquia', 'Parroquia' => 'nombreParroquia', 'Municipio' => 'nombreMunicipio'],
        ],
        'horario' => [
            'titulo' => 'Listado de Horarios',
            'archivo' => 'Horarios',
            'modelo' => 'HorarioModel', 'metodo' => 'listarHorarios',
            'orientacion' => 'portrait',
            'columnas' => ['Código' => 'idHorario', 'Horario' => 'nomHorario'],
        ],
        'estatus' => [
            'titulo' => 'Listado de Estatus',
            'archivo' => 'Estatus',
            'modelo' => 'EstatusModel', 'metodo' => 'listarEstatus',
            'orientacion' => 'portrait',
            'columnas' => ['Código' => 'idEstatus', 'Estatus' => 'nomEstatus', 'Descripción' => 'descEstatus'],
        ],
        'tipoactividad' => [
            'titulo' => 'Listado de Tipos de Actividad',
            'archivo' => 'Tipos_Actividad',
            'modelo' => 'TipoActividadModel', 'metodo' => 'listarTiposActividad',
            'orientacion' => 'portrait',
            'columnas' => ['Código' => 'idTipoActividad', 'Tipo de Actividad' => 'nomTipoActividad', 'Descripción' => 'descTipoActividad'],
        ],
        'lugaractividad' => [
            'titulo' => 'Listado de Lugares de Actividad',
            'archivo' => 'Lugares_Actividad',
            'modelo' => 'LugarActividadModel', 'metodo' => 'listarLugares',
            'orientacion' => 'landscape',
            'columnas' => [
                'Código' => 'idLugarActividad', 'Lugar' => 'nomLugarActividad',
                'Dirección' => 'direccion', 'Sede' => 'esSede',
                'Parroquia' => 'nombreParroquia', 'Municipio' => 'nombreMunicipio', 'Estado' => 'nombreEstado',
            ],
            'formatos' => ['esSede' => 'bool'],
        ],
        'espacioutilizar' => [
            'titulo' => 'Listado de Espacios a Utilizar',
            'archivo' => 'Espacios_Utilizar',
            'modelo' => 'EspacioUtilizarModel', 'metodo' => 'listarEspacios',
            'orientacion' => 'portrait',
            'columnas' => ['Código' => 'idEspacioUtilizar', 'Espacio' => 'nombreEspacioUtilizar', 'Descripción' => 'descEspacio', 'Capacidad' => 'capacidad'],
        ],
        'empleado' => [
            'titulo' => 'Listado de Empleados',
            'archivo' => 'Empleados',
            'modelo' => 'EmpleadoModel', 'metodo' => 'listarEmpleados',
            'orientacion' => 'landscape',
            'columnas' => [
                'Código' => 'idEmpleado', 'Cédula' => 'cedulaEmpleado',
                'Nombres' => 'nombres', 'Apellidos' => 'apellidos',
                'Fecha Nac.' => 'fechaNacimiento', 'Teléfono' => 'telefonoEmpleado',
                'Correo' => 'correoEmpleado', 'Cargo' => 'nombreCargo',
                'Unidad Ejecutora' => 'nomUnidadEjecutora',
            ],
            'formatos' => ['fechaNacimiento' => 'fecha'],
        ],
        'docente' => [
            'titulo' => 'Listado de Docentes',
            'archivo' => 'Docentes',
            'modelo' => 'DocenteModel', 'metodo' => 'listarDocentes',
            'orientacion' => 'landscape',
            'columnas' => [
                'Código' => 'idDocente', 'Cédula' => 'cedDocente', 'Nacionalidad' => 'nacionalidad',
                'Nombre' => 'nombreDocente', 'Apellido' => 'apellidoDocente', 'Teléfono' => 'telfDocente',
            ],
        ],
        'tipousuario' => [
            'titulo' => 'Listado de Tipos de Usuario',
            'archivo' => 'Tipos_Usuario',
            'modelo' => 'TipoUsuarioModel', 'metodo' => 'listarTipoUsuarios',
            'orientacion' => 'portrait',
            'columnas' => ['Código' => 'idTipoUsuario', 'Rol' => 'rolUsuario'],
        ],
        'usuarios' => [
            'titulo' => 'Listado de Usuarios del Sistema',
            'archivo' => 'Usuarios',
            'modelo' => 'UsuarioModel', 'metodo' => 'listarUsuarios',
            'orientacion' => 'landscape',
            // Nota: jamás se exporta la contraseña ni el ID interno de empleado
            'columnas' => [
                'Código' => 'idUsuario', 'Usuario' => 'nombreUsuario', 'Rol' => 'rolUsuario',
                'Cédula' => 'cedulaEmpleado', 'Nombres' => 'nombres', 'Apellidos' => 'apellidos',
                'Registrado' => 'created_at',
            ],
            'formatos' => ['created_at' => 'fecha'],
        ],
        'tipoentrega' => [
            'titulo' => 'Listado de Tipos de Entrega',
            'archivo' => 'Tipos_Entrega',
            'modelo' => 'TipoEntregaModel', 'metodo' => 'listarTiposEntrega',
            'orientacion' => 'portrait',
            'columnas' => ['Código' => 'idTipEntrega', 'Tipo de Entrega' => 'nomTipEntrega'],
        ],
        'estrategiadesarrollo' => [
            'titulo' => 'Listado de Estrategias de Desarrollo',
            'archivo' => 'Estrategias_Desarrollo',
            'modelo' => 'EstrategiaDesarrolloModel', 'metodo' => 'listarEstrategias',
            'orientacion' => 'portrait',
            'columnas' => ['Código' => 'idEstDesarrollo', 'Estrategia' => 'nomEstDesarrollo', 'Descripción' => 'descEstDesarrollo'],
        ],
        'unidadmedida' => [
            'titulo' => 'Listado de Unidades de Medida',
            'archivo' => 'Unidades_Medida',
            'modelo' => 'UnidadMedidaModel', 'metodo' => 'listarUnidades',
            'orientacion' => 'portrait',
            'columnas' => ['Código' => 'idUnidadMedida', 'Unidad de Medida' => 'nomUnidadMedida', 'Descripción' => 'descUnidadMedida'],
        ],
    ];

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Devuelve la configuración de un módulo exportable o null si no existe.
     */
    public static function obtenerConfig($modulo)
    {
        return self::MODULOS[$modulo] ?? null;
    }

    /**
     * Punto de entrada de la ruta index.php?action=exportar&modulo=X
     * SOLO Super Usuario; cualquier otro rol queda en la bitácora.
     */
    public function generar()
    {
        RolHelper::verificarSuperUsuario();

        $modulo = $_GET['modulo'] ?? '';
        $cfg = self::obtenerConfig($modulo);
        if ($cfg === null) {
            header("Location: index.php?action=configuracion");
            exit();
        }

        $this->exportarPdf($cfg);
    }

    /**
     * Obtiene los datos del módulo y construye el PDF con Dompdf.
     * Separado de generar() para poder probarlo sin sesión HTTP.
     */
    public function exportarPdf(array $cfg)
    {
        $modelo = $cfg['modelo'];
        $metodo = $cfg['metodo'];
        $model = new $modelo();
        $registros = $model->$metodo();

        // Prepara encabezados y filas ya formateadas para la plantilla
        $encabezados = array_keys($cfg['columnas']);
        $filas = [];
        foreach ($registros as $reg) {
            $fila = [];
            foreach ($cfg['columnas'] as $campo) {
                $valor = $reg[$campo] ?? '';
                $tipo = $cfg['formatos'][$campo] ?? null;
                if ($tipo === 'bool') {
                    $valor = ((int) $valor === 1) ? 'Sí' : 'No';
                } elseif ($tipo === 'fecha' && !empty($valor)) {
                    $valor = date('d/m/Y', strtotime($valor));
                }
                $fila[] = ($valor === null || $valor === '') ? '—' : $valor;
            }
            $filas[] = $fila;
        }

        // El PDF es un archivo binario: ningún mensaje de PHP puede mezclarse
        ini_set('display_errors', '0');
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $bannerBase64 = $this->imagenBase64('view/public/img/banner_institucional.png');
        $fondoBase64 = $this->imagenBase64('view/public/img/background_science_sutil.jpg');
        // Sin GD (extensión de PHP) dompdf no puede pintar fondos: se omite
        if ($fondoBase64 !== '' && !extension_loaded('gd')) {
            $fondoBase64 = '';
        }

        $tituloModulo = $cfg['titulo'];
        $total = count($filas);

        ob_start();
        require 'view/ExportarMaestroPDFView.php';
        $html = ob_get_clean();

        require_once 'lib/dompdf/autoload.inc.php';
        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', $cfg['orientacion']);
        $dompdf->render();

        // Número de página en cada hoja (canvas de dompdf)
        $canvas = $dompdf->getCanvas();
        $fuente = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $canvas->page_text(
            $canvas->get_width() - 90,
            $canvas->get_height() - 32,
            'Página {PAGE_NUM} de {PAGE_COUNT}',
            $fuente,
            8,
            [0.42, 0.45, 0.49]
        );

        Logger::registrar('GENERAR', 'Configuracion', null,
            'Exportó PDF del módulo ' . $cfg['titulo'] . ' (' . $total . ' registros)');

        $nombre = sprintf('SIGPAF_%s_%s.pdf', $cfg['archivo'], date('Y-m-d'));
        $dompdf->stream($nombre, ['Attachment' => true]);
        exit();
    }

    // Imagen del proyecto embebida en base64 (dompdf no resuelve rutas relativas)
    private function imagenBase64($ruta)
    {
        $contenido = @file_get_contents($ruta);
        if ($contenido === false) {
            return '';
        }
        $info = @getimagesizefromstring($contenido);
        $mime = $info['mime'] ?? 'image/png';
        return "data:$mime;base64," . base64_encode($contenido);
    }
}
