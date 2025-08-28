<?php
session_start();
require_once '../includes/conexion.php';

// Verificar que viene del formulario adicional
if (!isset($_SESSION['generador_id_reportando'])) {
    header("Location: ../vistas/listado_generadores_view.php");
    exit();
}

$generador_id = $_SESSION['generador_id_reportando'];
$anio = $_SESSION['anio_reportando'];

try {
    // Procesar archivos
    function procesarArchivo($archivo, $directorio, $prefijo) {
        if ($archivo['error'] !== UPLOAD_ERR_OK) return null;
        
        if (!is_dir($directorio)) mkdir($directorio, 0755, true);
        
        $tipo = mime_content_type($archivo['tmp_name']);
        if ($tipo !== 'application/pdf') throw new Exception("Solo se permiten PDF");
        if ($archivo['size'] > 10 * 1024 * 1024) throw new Exception("Archivo muy grande");
        
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombre = $prefijo . $generador_id . '_' . $anio . '_' . time() . '.' . $extension;
        $ruta = $directorio . $nombre;
        
        if (!move_uploaded_file($archivo['tmp_name'], $ruta)) {
            throw new Exception("Error al guardar archivo");
        }
        
        return $nombre;
    }
    
    $directorio = '../uploads/soportes_anuales/';
    $archivo_cronograma = procesarArchivo($_FILES['archivo_cronograma'], $directorio, 'cronograma_');
    $archivo_soportes = procesarArchivo($_FILES['archivo_soportes_capacitaciones'], $directorio, 'soportes_capacitaciones_');
    
    // Convertir acciones a JSON
    $acciones = isset($_POST['acciones_preventivas']) ? 
        json_encode($_POST['acciones_preventivas']) : '[]';
    
    // Insertar en la base de datos
    $stmt = $conn->prepare("INSERT INTO reporte_anual_adicional 
        (generador_id, anio, num_capacitaciones_programadas, archivo_cronograma,
         num_capacitaciones_ejecutadas, archivo_soportes_capacitaciones,
         tiene_accidentes, num_accidentes, acciones_preventivas, otra_accion_preventiva)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $generador_id, $anio,
        $_POST['num_capacitaciones_programadas'],
        $archivo_cronograma,
        $_POST['num_capacitaciones_ejecutadas'],
        $archivo_soportes,
        $_POST['tiene_accidentes'],
        $_POST['num_accidentes'] ?? 0,
        $acciones,
        $_POST['otra_accion_preventiva'] ?? null
    ]);
    
    // Limpiar sesión y redirigir
    unset($_SESSION['generador_id_reportando']);
    unset($_SESSION['anio_reportando']);
    
    $_SESSION['mensaje_exito'] = "¡Reporte completo guardado exitosamente!";
    header("Location: ../vistas/listado_generadores_view.php");
    exit();
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: ../vistas/reporte_adicional_view.php?id=" . $generador_id);
    exit();
}
?>