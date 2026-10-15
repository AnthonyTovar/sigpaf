<?php
require_once 'RolHelper.php';

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
        require 'view/Layout.php';
    }

    public function Mostrar()
    {
        // SOLO SUPER USUARIO (ademas de la validacion de la ruta en index.php)
        RolHelper::verificarSuperUsuario();
        $this->renderizar('view/ConfiguracionView');
    }
}