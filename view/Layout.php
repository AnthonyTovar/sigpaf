<?php
require_once 'RolHelper.php';

// Versión de assets: al cambiarla, los navegadores descargan de nuevo los
// CSS/JS del sistema aunque tengan caché (soluciona "cambios que no se ven").
$vAsset = '2026-10-26.1';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGPAF - Sistema de Gestión</title>
    <script src="view/public/js/init.js?v=<?php echo $vAsset; ?>"></script>
    <script src="view/public/js/validaciones.js?v=<?php echo $vAsset; ?>"></script>
    <link rel="icon" href="view/public/img/favicon.ico" sizes="32x32" type="image/png">
    <link rel="stylesheet" href="view/public/css/bootstrap.min.css">
    <link rel="stylesheet" href="view/public/css/bootstrap-icons.css">
    <link rel="stylesheet" href="view/public/css/Style.css?v=<?php echo $vAsset; ?>">
    <link rel="stylesheet" href="view/public/css/actividad.css?v=<?php echo $vAsset; ?>">
    <link rel="stylesheet" href="view/public/css/bitacora.css?v=<?php echo $vAsset; ?>">
    <link rel="stylesheet" href="view/public/css/diseno.css?v=<?php echo $vAsset; ?>">
    <link rel="stylesheet" href="view/public/css/responsive.css?v=<?php echo $vAsset; ?>">
    <link rel="stylesheet" href="view/public/css/modales.css?v=<?php echo $vAsset; ?>">

    <?php if (!isset($_SESSION['usuario_id'])): ?>
        <link rel="stylesheet" href="view/public/css/loginStyle.css?v=<?php echo $vAsset; ?>">
        <link rel="stylesheet" media="screen" href="view/public/css/particles.css?v=<?php echo $vAsset; ?>" />
    <?php endif; ?>

    <script src="view/public/js/jquery.min.js"></script>
</head>

<body class="<?php echo isset($_SESSION['usuario_id']) ? 'dashboard-body' : 'login-body'; ?>">
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <div class="dashboard-wrapper">

            <!-- OVERLAY MÓVIL: cierra el menú lateral al tocar fuera -->
            <div class="sidebar-overlay" id="sidebar-overlay"></div>

            <!-- SIDEBAR PRINCIPAL -->
            <aside class="sidebar" id="sidebar">
                <!-- Logo - link al inicio -->
                <div class="sidebar-brand">
                    <a href="index.php?action=dashboard" title="Ir al inicio">
                        <img src="view/public/img/favicon.ico" alt="SIGPAF">
                        <span class="sidebar-brand-text">SIGPAF</span>
                    </a>
                </div>

                <!-- Navegación principal -->
                <nav class="sidebar-nav">
                    <ul class="nav flex-column w-100">
                        <li class="nav-item">
                            <a href="index.php?action=dashboard"
                                class="nav-link <?php echo ($action == 'dashboard') ? 'active' : ''; ?>">
                                <i class="bi bi-house-door"></i>
                                <span>Inicio</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?action=actividades"
                                class="nav-link <?php echo ($action == 'actividades' || $action == 'nuevaActividad' || $action == 'editarActividad') ? 'active' : ''; ?>">
                                <i class="bi bi-calendar-event"></i>
                                <span>Actividades</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?action=seguimiento"
                                class="nav-link <?php echo ($action == 'seguimiento' || $action == 'registrarSeguimiento') ? 'active' : ''; ?>">
                                <i class="bi bi-clipboard2-check"></i>
                                <span>Seguimiento</span>
                            </a>
                        </li>
                        <?php if (RolHelper::esSuperUsuario()): ?>
                        <li class="nav-item">
                            <a href="index.php?action=reportes"
                                class="nav-link <?php echo ($action == 'reportes' || $action == 'exportarPdfReportes') ? 'active' : ''; ?>">
                                <i class="bi bi-file-earmark-bar-graph"></i>
                                <span>Reportes</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?action=configuracion"
                                class="nav-link <?php echo ($action == 'configuracion') ? 'active' : ''; ?>">
                                <i class="bi bi-gear"></i>
                                <span>Configuración</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?action=bitacora"
                                class="nav-link <?php echo ($action == 'bitacora') ? 'active' : ''; ?>">
                                <i class="bi bi-journal-text"></i>
                                <span>Bitacora</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <!-- Usuario abajo -->
                <div class="sidebar-user">
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li><a class="dropdown-item" href="index.php?action=gestionUsuario">
                                    <i class="bi bi-person me-2"></i>Perfil
                                </a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="index.php?action=logout">
                                    <i class="bi bi-box-arrow-right me-2"></i>Salir
                                </a></li>
                        </ul>
                    </div>
                </div>
            </aside>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="main-layout-container">
                <header class="topbar">
                    <div class="topbar-left">
                        <!-- BOTÓN MENÚ: colapsa/expande el sidebar (escritorio) o abre el menú (móvil) -->
                        <button type="button" id="btn-sidebar-toggle" class="btn-sidebar-toggle" title="Menú">
                            <i class="bi bi-list"></i>
                        </button>
                        <span class="topbar-title">SIGPAF</span>
                    </div>
                    <div class="topbar-right">
                        <!-- MENÚ DE USUARIO (look app de escritorio) -->
                        <?php
                        $nombreUsuarioActual = $_SESSION['username'] ?? 'U';
                        $partesNombre = preg_split('/\s+/', trim($nombreUsuarioActual));
                        $iniciales = strtoupper(mb_substr($partesNombre[0], 0, 1) . (isset($partesNombre[1]) ? mb_substr($partesNombre[1], 0, 1) : ''));
                        ?>
                        <div class="dropdown topbar-user">
                            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="topbar-avatar"><?php echo htmlspecialchars($iniciales); ?></span>
                                <span class="topbar-user-name"><?php echo htmlspecialchars($nombreUsuarioActual); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><a class="dropdown-item" href="index.php?action=gestionUsuario">
                                        <i class="bi bi-person-gear me-2"></i>Mi perfil
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="index.php?action=logout">
                                        <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
                                    </a></li>
                            </ul>
                        </div>
                        <!-- BOTÓN GESTIÓN DE USUARIO -->
                        <a href="index.php?action=gestionUsuario" class="btn-gestion-usuario" title="Gestionar mi perfil" data-bs-toggle="tooltip" data-bs-placement="bottom">
                            <i class="bi bi-person-gear"></i>
                        </a>
                        <a href="index.php?action=logout" class="btn-logout" title="Cerrar sesión" data-bs-toggle="tooltip"
                            data-bs-placement="bottom">
                            <i class="bi bi-box-arrow-right"></i>
                        </a>
                    </div>
                </header>

                <main class="view-container">
                    <?php echo $content; ?>
                </main>

                <footer class="footer-dash">
                    <span>&copy; 2026 — UPTYAB | FUNDACITE Yaracuy</span>
                </footer>
            </div>
        </div>

    <?php else: ?>
        <header class="header-banner"></header>

        <?php if (isset($_GET['action']) && $_GET['action'] === 'login'): ?>
            <div id="particles-js"></div>
            <script src="view/public/js/login.js?v=<?php echo $vAsset; ?>"></script>
            <script src="view/public/js/particulas/particles.js?v=<?php echo $vAsset; ?>"></script>
            <script src="view/public/js/particulas/app.js?v=<?php echo $vAsset; ?>"></script>
        <?php endif; ?>

        <div class="auth-wrapper">
            <?php echo $content; ?>
        </div>

    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'no_autorizado'): ?>
        <div class="modal fade" id="modalAccesoDenegado" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-shield-lock-fill me-2"></i> Acceso denegado</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="mb-0">No tienes permisos para acceder a esta seccion. Este intento ha quedado registrado en la bitacora del sistema.</p>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Entendido</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function () {
                var modal = new bootstrap.Modal(document.getElementById('modalAccesoDenegado'));
                modal.show();
            });
        </script>
    <?php endif; ?>

    <script src="view/public/js/bootstrap.bundle.min.js"></script>
    <script src="view/public/js/Main.js?v=<?php echo $vAsset; ?>"></script>
    <script src="view/public/js/filtroTabla.js?v=<?php echo $vAsset; ?>"></script>

    <?php if (isset($_SESSION['usuario_id'])): ?>
        <!-- Latido de sesión: avisa al servidor que el navegador sigue abierto.
             Si el usuario cierra el navegador, el servidor lo detecta por la
             ausencia de pings y libera la sesión. -->
        <script>
            window.SIGPAF_PING_INTERVAL = <?php echo (int) SessionManager::INTERVALO_PING_SEGUNDOS * 1000; ?>;
        </script>
        <script src="view/public/js/heartbeat.js?v=<?php echo $vAsset; ?>"></script>
    <?php endif; ?>
</body>

</html>