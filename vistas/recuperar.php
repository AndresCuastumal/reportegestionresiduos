<?php
require '../includes/conexion.php';
include '../includes/header.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    
    // Verificar si el email existe
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Generar token y fecha de expiración
        $token = bin2hex(random_bytes(32));
        $expiracion = date("Y-m-d H:i:s", strtotime("+1 hour"));
        
        // Guardar token en la base de datos
        $stmt = $conn->prepare("UPDATE usuarios SET token_recuperacion = :token, expiracion_token = :expiracion WHERE email = :email");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expiracion', $expiracion);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        // Enviar email con el enlace (simulado aquí)
        $enlace = "http://localhost/reportegestionresiduos/vistas/reset.php?token=$token";
        echo "<div cass = 'alert alert-error'> <p>Se ha enviado un enlace de recuperación a tu email. <a href='$enlace'>Haz clic aquí</a> para simular el proceso.</p> </div>";
    } else {
        echo "No existe una cuenta con ese email.";
    }
}
?>
<main class="auth-container">
    <div class="auth-card">
        <h2 class="auth-title">Recuperar Contraseña</h2>
        <form method="post" action="">
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" name="email" required placeholder="tu@mail.com">
            </div>
            <div class="form-actions">
                <button type="submit">Enviar enlace de recuperación</button>
                <a href="login.php" class="auth-link">Volver al inicio de sesión</a>
            </div>
        </form>
    </div>
</main>
<?php include '../includes/footer.php'; ?>