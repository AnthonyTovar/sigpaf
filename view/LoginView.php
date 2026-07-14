<div class="auth-container">

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