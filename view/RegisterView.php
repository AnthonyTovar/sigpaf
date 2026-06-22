<h2>Crear Cuenta</h2>

<?php if(isset($error)): ?>
    <div class="error-msg"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" action="index.php?action=register">
    
    <div class="form-group">
        <label>Nombre de Usuario</label>
        <input type="text" name="username" required placeholder="Elige un nombre de usuario">
    </div>
    
    <div class="form-group">
        <label>Contraseña Segura</label>
        <input type="password" name="password" required placeholder="Crea una contraseña fuerte">
    </div>
    
    <button type="submit" class="btn-u">
        Registrarme ahora
    </button>
</form>

<p class="link-u">
    ¿Ya tienes una cuenta? <a href="index.php?action=login">Inicia sesión aquí</a>
</p>