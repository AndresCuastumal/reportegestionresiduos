<?php
session_start();
require_once '../includes/conexion.php';

// Verificar que viene del formulario de contingencias
if (!isset($_SESSION['generador_id_reportando'])) {
    header("Location: ../vistas/listado_generadores_view.php");
    exit();
}

$generador_id = $_POST['generador_id'];
$anio = $_POST['anio'];

try {
    // Procesar archivo si se subió
    function procesarArchivo($archivo, $directorio, $prefijo) {
        if ($archivo['error'] !== UPLOAD_ERR_OK) return null;
        if ($archivo['size'] === 0) return null;
        
        if (!is_dir($directorio)) mkdir($directorio, 0755, true);
        
        $tipo = mime_content_type($archivo['tmp_name']);
        if ($tipo !== 'application/pdf') throw new Exception("Solo se permiten PDF");
        if ($archivo['size'] > 10 * 1024 * 1024) throw new Exception("Archivo muy grande (máximo 10MB)");
        
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombre = $prefijo . $generador_id . '_' . $anio . '_' . time() . '.' . $extension;
        $ruta = $directorio . $nombre;
        
        if (!move_uploaded_file($archivo['tmp_name'], $ruta)) {
            throw new Exception("Error al guardar archivo");
        }
        
        return $nombre;
    }
    
    $directorio = '../uploads/contingencias/';
    $archivo_soporte = null;
    
    if (isset($_FILES['archivo_soporte']) && $_FILES['archivo_soporte']['error'] === UPLOAD_ERR_OK) {
        $archivo_soporte = procesarArchivo($_FILES['archivo_soporte'], $directorio, 'contingencias_');
    }
    
    // Insertar en la base de datos
    $stmt = $conn->prepare("INSERT INTO contingencias 
        (generador_id, anio, fecha_reporte, persona_reporta, area_localizacion,
         incendios_numero, incendios_acciones,
         inundaciones_numero, inundaciones_acciones,
         agua_numero, agua_acciones,
         energia_numero, energia_acciones,
         derrames_numero, derrames_tipo, derrames_acciones,
         recoleccion_numero, recoleccion_acciones,
         operativas_numero, operativas_acciones,
         medidas_adicionales, efectividad_acciones, archivo_soporte)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $generador_id, $anio,
        $_POST['fecha_reporte'],
        $_POST['persona_reporta'],
        $_POST['area_localizacion'],
        $_POST['incendios_numero'] ?? 0,
        $_POST['incendios_acciones'] ?? '',
        $_POST['inundaciones_numero'] ?? 0,
        $_POST['inundaciones_acciones'] ?? '',
        $_POST['agua_numero'] ?? 0,
        $_POST['agua_acciones'] ?? '',
        $_POST['energia_numero'] ?? 0,
        $_POST['energia_acciones'] ?? '',
        $_POST['derrames_numero'] ?? 0,
        $_POST['derrames_tipo'] ?? '',
        $_POST['derrames_acciones'] ?? '',
        $_POST['recoleccion_numero'] ?? 0,
        $_POST['recoleccion_acciones'] ?? '',
        $_POST['operativas_numero'] ?? 0,
        $_POST['operativas_acciones'] ?? '',
        $_POST['medidas_adicionales'] ?? '',
        $_POST['efectividad_acciones'] ?? '',
        $archivo_soporte
    ]);
    
    // Limpiar sesión y redirigir
    unset($_SESSION['generador_id_reportando']);
    unset($_SESSION['anio_reportando']);
    
    $_SESSION['mensaje_exito'] = "¡Plan de contingencias guardado exitosamente!";
    header("Location: ../vistas/listado_generadores_view.php");
    exit();
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: ../vistas/contingencias_view.php?id=" . $generador_id);
    exit();
}
?>