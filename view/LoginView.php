<div class="auth-container">

    <?php 
    // Mostrar mensajes de error de sesión
    if (isset($_GET['error'])): 
        $mensaje = '';
        switch ($_GET['error']) {
            case 'sesion_activa':
                $mensaje = '⚠️ Ya tienes una sesión activa en otro dispositivo o navegador. Cierra la sesión anterior para continuar.';
                break;
            case 'sesion_invalidada':
                $mensaje = '🔒 Tu sesión fue cerrada porque iniciaste sesión en otro lugar.';
                break;
            case 'no_autorizado':
                $mensaje = '⛔ No tienes permisos para acceder a esta sección.';
                break;
            default:
                $mensaje = '❌ Ha ocurrido un error. Intenta nuevamente.';
        }
    ?>
        <div class="error-msg" style="background: #fff3cd; color: #856404; border: 1px solid #ffc107; padding: 12px; border-radius: 6px; margin-bottom: 15px; text-align: center;">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="index.php?action=login" method="POST" id="loginForm">
        <div class="login">
            <p class="bienv">Bienvenido</p>
            <p class="subtitle">Ingresa tus credenciales para continuar</p>
            <div class="photo"></div>
            <div id="user-display-area" class="hidden">
                <p class="name" id="name"></p>
                <span class="edit-user"><i class="bi bi-pencil-square"></i> Cambiar usuario</span>
            </div>
            <div class="error-container">
                <div id="msg-error" class="error-text" style="display:none;"></div>
            </div>
            <div class="username-wrap">
                <input type="text" name="username" class="username" placeholder="Usuario" id="username-input"
                    autocomplete="off" />
            </div>
            <div class="pw-box">
                <span class="flap">
                    <i class="bi bi-person-lock"></i>
                </span>
                <input type="password" name="password" class="password" placeholder="********" id="password-input" />
                <button type="button" class="toggle-password hidden" id="toggle-password" title="Mostrar contraseña">
                    <i class="bi bi-eye-slash" id="eye-icon"></i>
                </button>
            </div>
            <button type="button" id="btn-submit-custom" class="btn-u">Iniciar Sesión</button>
        
        </div>
    </form>
</div>