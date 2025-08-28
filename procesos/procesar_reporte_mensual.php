<?php
session_start();
require_once '../includes/conexion.php';
require_once 'reporte_mensual_controller.php';

error_log("=== INICIANDO PROCESAR_REPORTE_MENSUAL ===");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("ID: " . ($_GET['id'] ?? 'NO_ID'));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $generador_id = $_GET['id'];
    $controller = new ReporteMensualController($conn);
    
    error_log("Entrando al TRY");
    
    try {
        // Guardar en sesión para el siguiente formulario
        $_SESSION['generador_id_reportando'] = $generador_id;
        $_SESSION['anio_reportando'] = $_POST['anio'];
        
        error_log("Procesando reporte...");
        
        // Procesar reporte mensual
        $controller->procesarReporte($generador_id, $_POST, $_FILES['soporte_pdf']);
        
        error_log("Redirigiendo a formulario adicional");
        
        // Redirigir al segundo formulario
        header("Location: ../vistas/reporte_adicional_view.php?id=" . $generador_id);
        exit();
        
    } catch (Exception $e) {
        error_log("ERROR: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../vistas/reporte_mensual_view.php?id=" . $generador_id);
        exit();
    }
} else {
    error_log("Redirigiendo a listado - No POST o sin ID");
    header("Location: ../vistas/listado_generadores_view.php");
    exit();
}
?>