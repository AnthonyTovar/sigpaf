<?php
require_once 'RolHelper.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGPAF - Sistema de Gestión</title>
    <script src="view/public/js/init.js"></script>
    <link rel="icon" href="view/public/img/favicon.ico" sizes="32x32" type="image/png">
    <link rel="stylesheet" href="view/public/css/bootstrap.min.css">
    <link rel="stylesheet" href="view/public/css/bootstrap-icons.css">
    <link rel="stylesheet" href="view/public/css/Style.css">
    <link rel="stylesheet" href="view/public/css/actividad.css">

    <?php if (!isset($_SESSION['usuario_id'])): ?>
        <link rel="stylesheet" href="view/public/css/loginStyle.css">
        <link rel="stylesheet" media="screen" href="view/public/css/particles.css" />
    <?php endif; ?>

    <script src="view/public/js/jquery.min.js"></script>
</head>

<body class="<?php echo isset($_SESSION['usuario_id']) ? 'dashboard-body' : 'login-body'; ?>">
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <div class="dashboard-wrapper">

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
                            <a href="#" class="nav-link">
                                <i class="bi bi-file-earmark-bar-graph"></i>
                                <span>Reportes</span>
                            </a>
                        </li>
                        <?php if (RolHelper::esAdministrador()): ?>
                        <li class="nav-item">
                            <a href="index.php?action=configuracion"
                                class="nav-link <?php echo ($action == 'configuracion') ? 'active' : ''; ?>">
                                <i class="bi bi-gear"></i>
                                <span>Configuración</span>
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
                        <span class="topbar-title">SIGPAF</span>
                    </div>
                    <div class="topbar-right">
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
            <script src="view/public/js/login.js"></script>
            <script src="view/public/js/particulas/particles.js"></script>
            <script src="view/public/js/particulas/app.js"></script>
        <?php endif; ?>

        <div class="auth-wrapper">
            <?php echo $content; ?>
        </div>

    <?php endif; ?>

    <script src="view/public/js/bootstrap.bundle.min.js"></script>
    <script src="view/public/js/Main.js"></script>
</body>

</html>