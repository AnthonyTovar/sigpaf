<?php
require_once 'model/BitacoraModel.php';

class BitacoraController
{
    private $model;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new BitacoraModel();
    }

    /**
     * METODO RENDERIZAR (mismo patron que los demas controladores)
     */
    private function renderizar($nombreVista, $datos = [])
    {
        extract($datos);
        ob_start();
        require $nombreVista . '.php';
        $content = ob_get_clean();
        require 'view/Layout.php';
    }

    // Listado de la bitacora - SOLO SUPER USUARIO (validado en index.php)
    public function listar()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        // Limpieza y saneamiento de los filtros que vienen por GET
        $filtros = [
            'accion'  => $this->limpiarTexto($_GET['accion_f'] ?? '', 20),
            'usuario' => $this->limpiarTexto($_GET['usuario'] ?? '', 50),
            'desde'   => $this->limpiarFecha($_GET['desde'] ?? ''),
            'hasta'   => $this->limpiarFecha($_GET['hasta'] ?? ''),
            'buscar'  => $this->limpiarTexto($_GET['buscar'] ?? '', 100)
        ];

        // Coherencia de fechas: si el usuario invierte el rango, se corrige solo
        if (!empty($filtros['desde']) && !empty($filtros['hasta']) && $filtros['desde'] > $filtros['hasta']) {
            $tmp = $filtros['desde'];
            $filtros['desde'] = $filtros['hasta'];
            $filtros['hasta'] = $tmp;
        }

        $pagina    = max(1, intval($_GET['pagina'] ?? 1));
        $porPagina = 15;

        $total   = $this->model->contar($filtros);
        $paginas = max(1, ceil($total / $porPagina));
        if ($pagina > $paginas) {
            $pagina = $paginas;
        }

        $registros = $this->model->listar($filtros, $pagina, $porPagina);

        // Datos extra para el diseno de la vista: resumen por accion y usuarios
        $resumenAcciones = $this->model->contarPorAccion($filtros);
        $usuariosBitacora = $this->model->obtenerUsuariosConRegistros();

        $this->renderizar('view/BitacoraView', [
            'registros'        => $registros,
            'filtros'          => $filtros,
            'pagina'           => $pagina,
            'paginas'          => $paginas,
            'total'            => $total,
            'resumenAcciones'  => $resumenAcciones,
            'usuariosBitacora' => $usuariosBitacora
        ]);
    }

    /**
     * Recorta espacios y limita la longitud de un texto de entrada.
     */
    private function limpiarTexto($valor, $maxLongitud)
    {
        $valor = trim((string) $valor);
        return mb_substr($valor, 0, $maxLongitud);
    }

    /**
     * Valida que el valor tenga formato de fecha YYYY-MM-DD; si no, lo descarta.
     */
    private function limpiarFecha($valor)
    {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return '';
        }
        $dt = DateTime::createFromFormat('Y-m-d', $valor);
        if ($dt && $dt->format('Y-m-d') === $valor) {
            return $valor;
        }
        return '';
    }
}
