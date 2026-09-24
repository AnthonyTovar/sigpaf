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

        $filtros = [
            'accion'  => $_GET['accion_f'] ?? '',
            'usuario' => $_GET['usuario'] ?? '',
            'desde'   => $_GET['desde'] ?? '',
            'hasta'   => $_GET['hasta'] ?? '',
            'buscar'  => $_GET['buscar'] ?? ''
        ];

        $pagina    = max(1, intval($_GET['pagina'] ?? 1));
        $porPagina = 15;

        $total   = $this->model->contar($filtros);
        $paginas = max(1, ceil($total / $porPagina));
        if ($pagina > $paginas) {
            $pagina = $paginas;
        }

        $registros = $this->model->listar($filtros, $pagina, $porPagina);

        $this->renderizar('view/BitacoraView', [
            'registros' => $registros,
            'filtros'   => $filtros,
            'pagina'    => $pagina,
            'paginas'   => $paginas,
            'total'     => $total
        ]);
    }
}
