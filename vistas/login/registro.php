<?php include '../../includes/header.php'; ?>
<main class="auth-container">
    <div class="auth-card">
        <h2 class = "auth-title">Registro de usuario</h2>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>
        <form  class = "auth-form" id="registroForm" method="post" action="../../procesos/login/registrar.php" onsubmit="return validarFormulario()">
            <div class = "form-group">
                <label for="email">Correo electrónico:</label>
                <input type="email" name="email" required placeholder="tu@mail.com">
            </div>
            
            <div class = "form-group">
                <label>Contraseña (mínimo 6 caracteres):</label>
                <input type="password" name="password" id="password" required minlength="6" placeholder="••••••••">
            </div>
            
            <div class = "form-group">
                <label>Confirmar Contraseña:</label>
                <input type="password" name="confirm_password" id="confirm_password" required placeholder="••••••••">
                <span id="mensajeError" style="color:red;"></span>
            </div>
            <div class = "form-actions">
                <button type="submit">Registrarse</button>
                <a href="login.php" class="auth-link">¿Ya tienes cuenta? Inicia sesión</a>
            </div>
        </form>
    </div>
</main>


<!-- Incluir JavaScript externo -->
<script src="../../assets/js/validacion-registro.js"></script>
<?php include '../../includes/footer.php'; ?>