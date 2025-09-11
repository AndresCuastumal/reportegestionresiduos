<?php
session_start();
require_once '../../includes/conexion.php';
require_once 'reporte_mensual_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $generador_id = $_GET['id'];
    $controller = new ReporteMensualController($conn);
    
    try {
        // Guardar en sesión para el siguiente formulario
        $_SESSION['generador_id_reportando'] = $generador_id;
        $_SESSION['anio_reportando'] = $_POST['anio'];
        
        // Verificar si se subió un archivo
        $archivo_subido = false;
        $nombre_archivo = null;
        
        if (isset($_FILES['soporte_pdf']) && $_FILES['soporte_pdf']['error'] === UPLOAD_ERR_OK) {
            $archivo_subido = true;
        }
        
        // Procesar reporte mensual
        $controller->procesarReporte($generador_id, $_POST, $archivo_subido ? $_FILES['soporte_pdf'] : null);
                       
        // Redirigir al segundo formulario
        header("Location: ../../vistas/generador/reporte_adicional_view.php?id=" . $generador_id);
        exit();
        
    } catch (Exception $e) {       
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../../vistas/generador/reporte_mensual_view.php?id=" . $generador_id);
        exit();
    }
} else {    
    header("Location: ../../vistas/generador/listado_generadores_view.php");
    exit();
}
?>