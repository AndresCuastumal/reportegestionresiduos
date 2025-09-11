<?php
session_start();
require_once '../../includes/conexion.php';
require_once 'reporte_mensual_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $generador_id = $_POST['generador_id'] ?? '';
        $anio = $_POST['anio'] ?? '';
        $meses = $_POST['meses'] ?? [];
        
        if (empty($generador_id) || empty($anio)) {
            throw new Exception('Datos incompletos');
        }
        
        // Verificar permisos primero
        if (!isset($_SESSION['usuario_id'])) {
            throw new Exception('No autenticado');
        }
        
        // Crear instancia del controlador
        $controller = new ReporteMensualController($conn);
        
        // Preparar datos para el procesamiento
        $postData = [
            'anio' => $anio,
            'meses' => $meses
        ];
        
        // Procesar el archivo si se subió
        $archivo = null;
        if (isset($_FILES['soporte_pdf']) && $_FILES['soporte_pdf']['error'] === UPLOAD_ERR_OK) {
            $archivo = $_FILES['soporte_pdf'];
        } elseif (isset($_FILES['soporte_pdf']) && $_FILES['soporte_pdf']['error'] !== UPLOAD_ERR_NO_FILE) {
            throw new Exception('Error al subir el archivo: ' . $_FILES['soporte_pdf']['error']);
        }
        
        // Procesar reporte mensual
        $controller->procesarReporte($generador_id, $postData, $archivo);
        
        // Guardar en sesión para el siguiente formulario
        $_SESSION['generador_id_reportando'] = $generador_id;
        $_SESSION['anio_reportando'] = $anio;
        
        $response['success'] = true;
        $response['message'] = 'Reporte mensual guardado correctamente';
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
        error_log("Error en procesar_reporte_mensual_ajax: " . $e->getMessage());
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}