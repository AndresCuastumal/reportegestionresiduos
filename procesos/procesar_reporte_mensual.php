<?php
session_start();
require_once '../includes/conexion.php';
require_once 'reporte_mensual_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $generador_id = $_GET['id'];
    $controller = new ReporteMensualController($conn);
    
    try {
        // Guardar en sesión para el siguiente formulario
        $_SESSION['generador_id_reportando'] = $generador_id;
        $_SESSION['anio_reportando'] = $_POST['anio'];
        
        // Procesar reporte mensual
        $controller->procesarReporte($generador_id, $_POST, $_FILES['soporte_pdf']);
                       
        // Redirigir al segundo formulario
        header("Location: ../vistas/reporte_adicional_view.php?id=" . $generador_id);
        exit();
        
    } catch (Exception $e) {       
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../vistas/reporte_mensual_view.php?id=" . $generador_id);
        exit();
    }
} else {    
    header("Location: ../vistas/listado_generadores_view.php");
    exit();
}
?>