<?php
require '../../includes/conexion.php';
include '../../includes/header.php';
?>

<main class="auth-container">
    <div class="auth-card">
        <h2 class="auth-title">Recuperar Contraseña</h2>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        
        <form method="post" action="../../procesos/login/procesar_recupera_psw.php">
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required placeholder="tu@mail.com">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Enviar enlace de recuperación</button>
                <a href="login.php" class="auth-link">Volver al inicio de sesión</a>
            </div>
        </form>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>