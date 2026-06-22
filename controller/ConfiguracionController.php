<?php
require_once 'SecurityHelper.php';

class ConfiguracionController
{
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function renderizar($nombreVista, $datos = [])
    {
        extract($datos);
        ob_start();
        require $nombreVista . '.php';
        $content = ob_get_clean();
        require 'view/layout.php';
    }

    public function Mostrar()
    {
        SecurityHelper::preventBackAfterLogout();
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }
        $this->renderizar('view/ConfiguracionView');
    }
}