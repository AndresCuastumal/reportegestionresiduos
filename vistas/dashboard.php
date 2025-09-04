<?php
session_start();
require_once '../includes/conexion.php'; // Archivo con la conexión a tu BD

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login/login.php");
    exit();
}

// Obtener rol del usuario para permisos
$rol = $_SESSION['usuario_rol'];

include '../includes/header.php'; // Incluye el encabezado HTML
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Residuos Peligrosos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">    
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-biohazard me-2"></i>Secretaría de Salud - Pasto
            </a>
            <span class="navbar-text ms-auto">
                <?php echo $_SESSION['usuario_email']; ?>
                <a href="../logout.php" class="btn btn-sm btn-outline-light ms-3">Cerrar sesión</a>
            </span>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container my-5">
        <!-- Sección hero con explicación -->
        <div class="hero-section text-center">
            <h1><i class="fas fa-biohazard me-2"></i>Sistema de Gestión de Residuos Peligrosos</h1>
            <p class="lead mt-3">
                Plataforma de reporte según Resolución 591 de 2024. De acuerdo con la normatividad vigente, 
                todos los generadores de residuos peligrosos deben reportar anualmente información relacionada con 
                la gestión de estos residuos. Para ello se deben diligenciar tres formularios:
            </p>
            <p class="text-start">
                <li>Reporte mensual de residuos peligrosos</li>
                <li>Capacitaciones, accidentes y auditorías realizadas</li>
                <li>Contingencias</li>
            </p>
            
            <!-- Botones principales -->
            <div class="d-flex justify-content-center gap-3 mt-4">
                <?php if (in_array($rol, ['generador'])): ?>
                    <a href="generador/listado_generadores_view.php" class="btn btn-info btn-lg">
                        <i class="fas fa-building me-2"></i>Mis Establecimientos
                    </a>
                <?php endif; ?>               
            </div>
        </div>

        <!-- Tarjetas informativas -->
        <div class="row mt-5">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-alt fa-3x mb-3 text-primary"></i>
                        <h5 class="card-title">Reporte Año <?= date('Y', strtotime('-1 year') ) ?></h5>
                        <p class="card-text">Reporte de información relacionada a residuos peligrosos para el año <?= date('Y', strtotime('-1 year') ) ?>.</p>
                        <?php if (in_array($rol, ['generador'])): ?>
                            <a href="generador/reporte_mensual_view.php" class="btn btn-sm btn-outline-primary mt-2">Acceder</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-bar fa-3x mb-3 text-success"></i>
                        <h5 class="card-title">Revisión Salud Ambiental</h5>
                        <p class="card-text">Módulo de revisión y gestión de validación de la información reportada.</p>
                        <?php if (in_array($rol, ['admin'])): ?>
                            <a href="admin/listado_revisiones_view.php" class="btn btn-sm btn-outline-success mt-2">Revisión de reportes</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-file-pdf fa-3x mb-3 text-danger"></i>
                        <h5 class="card-title">Certificados</h5>
                        <p class="card-text">Descargue certificados oficiales una vez sus reportes sean aprobados.</p>
                        <?php if (in_array($rol, ['generador'])): ?>
                            <a href="certificados.php" class="btn btn-sm btn-outline-danger mt-2">Descargar</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <?php include '../includes/footer.php'; // Incluye el pie de página ?>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>