<?php
session_start();
require '../../includes/conexion.php';

// Verificar si el usuario ya está logueado
if (isset($_SESSION['usuario_id'])) {
    header("Location: ../dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_email'] = $user['email'];
        $_SESSION['usuario_rol'] = $user['rol'];
        header("Location: ../dashboard.php");
        exit();
    } else {
        $error = "Email o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Gestión de Residuos</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">    
    
    <style>
        body {
            background: linear-gradient(135deg, #f8f4ceff 0%, #eed296ff 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .login-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            width: 100%;
            max-width: 450px;
        }
        
        .login-title {
            color: #333;
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            border-color: #4a6ad1;
            outline: none;
            box-shadow: 0 0 0 3px rgba(74, 106, 209, 0.1);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #4a6ad1 0%, #3949ab 100%);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            width: 100%;
            margin-bottom: 1rem;
            color: white;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #3949ab 0%, #303f9f 100%);
            color: white;
        }
        
        .auth-link {
            color: #4a6ad1;
            text-decoration: none;
        }
        
        .auth-link:hover {
            text-decoration: underline;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 0.75rem 1.25rem;
            border: 1px solid #f5c6cb;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 0.75rem 1.25rem;
            border: 1px solid #c3e6cb;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Sección hero con logo (estilo del dashboard) -->
    <div class="hero-section text-center">
        <div style="background-color: #eed296ff;" class="p-4">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-center mb-4">
                <div class="d-flex align-items-center mb-3 mb-md-0">
                    <img src="/reportegestionresiduos/assets/css/logoNuevoSMS2024.png" alt="Logo SMS" 
                        class="me-3 img-fluid d-none d-md-block" 
                        style="max-height: 100px; width: auto;">
                    <img src="/reportegestionresiduos/assets/css/logoNuevoSMS2024.png" alt="Logo SMS" 
                        class="me-2 img-fluid d-md-none" 
                        style="max-height: 40px; width: auto;">
                    <h1 class="h3 mb-0 text-center">Sistema de Gestión de Residuos Peligrosos</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="login-container">
        <div class="login-card">
            <h2 class="login-title">Iniciar Sesión</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required placeholder="tu@email.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-login">Ingresar</button>
                    <div class="text-center">
                        <a href="recuperar.php" class="auth-link">¿Olvidaste tu contraseña?</a>
                    </div>
                </div>
            </form>
            
            <div class="auth-footer text-center mt-3">
                ¿No tienes una cuenta? <a href="registro.php" class="auth-link">Regístrate aquí</a>
            </div>
        </div>
    </div>

    <!-- Footer simplificado -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <div class="container">
            <p class="mb-0">Sistema de Gestión de Residuos Peligrosos &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>