<?php
// Controlador de bitácora



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


        $filtros = [
            'accion'  => $this->limpiarTexto($_GET['accion_f'] ?? '', 20),
            'usuario' => $this->limpiarTexto($_GET['usuario'] ?? '', 50),
            'desde'   => $this->limpiarFecha($_GET['desde'] ?? ''),
            'hasta'   => $this->limpiarFecha($_GET['hasta'] ?? ''),
            'buscar'  => $this->limpiarTexto($_GET['buscar'] ?? '', 100)
        ];


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


    private function limpiarTexto($valor, $maxLongitud)
    {
        $valor = trim((string) $valor);
        return mb_substr($valor, 0, $maxLongitud);
    }


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
