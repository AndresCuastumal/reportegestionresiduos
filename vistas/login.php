<?php
session_start();
require '../includes/conexion.php';
include '../includes/header.php'; 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Email o contraseña incorrectos";
    }
}
?>

<main class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Iniciar Sesión</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <form class="auth-form" method="POST">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required placeholder="tu@email.com">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Ingresar</button>
                <a href="recuperar.php" class="auth-link">¿Olvidaste tu contraseña?</a>
            </div>
        </form>
        
        <div class="auth-footer">
            ¿No tienes una cuenta? <a href="registro.php" class="auth-link">Regístrate aquí</a>
        </div>
    </div>
</main>











<?php include '../includes/footer.php'; ?>