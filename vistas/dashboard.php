<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require '../includes/conexion.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['email']); ?></h2>
    <p>Esta es tu área privada.</p>
    <a href="../logout.php">Cerrar sesión</a>
</body>
</html>