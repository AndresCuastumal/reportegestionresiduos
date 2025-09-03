<?php
session_start();
require_once '../../includes/conexion.php';

// Verificar que viene del formulario adicional
if (!isset($_SESSION['generador_id_reportando'])) {
    header("Location: ../../vistas/generador/listado_generadores_view.php");
    exit();
}

$generador_id = $_SESSION['generador_id_reportando'];
$anio = $_SESSION['anio_reportando'];

try {
    // Procesar archivos
    function procesarArchivo($archivo, $directorio, $prefijo) {
        global $generador_id, $anio;
        
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
    $archivo_resultados_auditorias = procesarArchivo($_FILES['archivo_resultados_auditorias'], $directorio, 'resultados_auditorias_');
    $archivo_plan_mejoramiento = procesarArchivo($_FILES['archivo_plan_mejoramiento'], $directorio, 'plan_mejoramiento_');
    
    // Convertir acciones a JSON
    $acciones = isset($_POST['acciones_preventivas']) ? 
        json_encode($_POST['acciones_preventivas']) : '[]';
    
    // Verificar si ya existe un registro para este generador y año
    $stmt_check = $conn->prepare("SELECT id FROM reporte_anual_adicional WHERE generador_id = ? AND anio = ?");
    $stmt_check->execute([$generador_id, $anio]);
    $existe_registro = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if ($existe_registro) {
        // Actualizar registro existente
        $stmt = $conn->prepare("UPDATE reporte_anual_adicional SET 
            num_capacitaciones_programadas = ?,
            archivo_cronograma = ?,
            num_capacitaciones_ejecutadas = ?,
            archivo_soportes_capacitaciones = ?,
            tiene_accidentes = ?,
            num_accidentes = ?,
            acciones_preventivas = ?,
            otra_accion_preventiva = ?,
            num_auditorias = ?,
            archivo_resultados_auditorias = ?,
            archivo_plan_mejoramiento = ?,
            fecha_creacion = CURRENT_TIMESTAMP
            WHERE generador_id = ? AND anio = ?");
        
        $stmt->execute([
            $_POST['num_capacitaciones_programadas'],
            $archivo_cronograma,
            $_POST['num_capacitaciones_ejecutadas'],
            $archivo_soportes,
            $_POST['tiene_accidentes'],
            $_POST['num_accidentes'] ?? 0,
            $acciones,
            $_POST['otra_accion_preventiva'] ?? null,
            $_POST['num_auditorias'],
            $archivo_resultados_auditorias,
            $archivo_plan_mejoramiento,
            $generador_id,
            $anio
        ]);
        
        $_SESSION['mensaje_exito'] = "¡Información adicional actualizada! Complete ahora el plan de contingencias.";
    } else {
        // Insertar nuevo registro
        $stmt = $conn->prepare("INSERT INTO reporte_anual_adicional 
            (generador_id, anio, num_capacitaciones_programadas, archivo_cronograma,
             num_capacitaciones_ejecutadas, archivo_soportes_capacitaciones,
             tiene_accidentes, num_accidentes, acciones_preventivas, otra_accion_preventiva,
             num_auditorias, archivo_resultados_auditorias, archivo_plan_mejoramiento)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $generador_id, $anio,
            $_POST['num_capacitaciones_programadas'],
            $archivo_cronograma,
            $_POST['num_capacitaciones_ejecutadas'],
            $archivo_soportes,
            $_POST['tiene_accidentes'],
            $_POST['num_accidentes'] ?? 0,
            $acciones,
            $_POST['otra_accion_preventiva'] ?? null,
            $_POST['num_auditorias'],
            $archivo_resultados_auditorias,
            $archivo_plan_mejoramiento
        ]);
        
        $_SESSION['mensaje_exito'] = "¡Información adicional guardada! Complete ahora el plan de contingencias.";
    }
    
    // Mantener los datos de sesión y redirigir al formulario de contingencias    
    header("Location: ../../vistas/generador/reporte_contingencias_view.php?id=" . $generador_id);
    exit();
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: ../../vistas/generador/reporte_adicional_view.php?id=" . $generador_id);
    exit();
}
?>