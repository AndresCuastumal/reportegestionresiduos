<?php
session_start();
require_once '../../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $generador_id = $_POST['generador_id'] ?? '';
        $anio = $_POST['anio'] ?? '';
        
        if (empty($generador_id) || empty($anio)) {
            throw new Exception('Datos incompletos');
        }
        
        // Verificar permisos
        if (!isset($_SESSION['usuario_id'])) {
            throw new Exception('No autenticado');
        }
        
        // Función para procesar archivos
        function procesarArchivo($archivo, $directorio, $prefijo) {
            global $generador_id, $anio;
            
            if ($archivo['error'] !== UPLOAD_ERR_OK) {
                return null;
            }
            
            // Crear directorio si no existe
            if (!is_dir($directorio)) {
                mkdir($directorio, 0755, true);
            }
            
            // Validar que sea PDF
            $tipo_archivo = mime_content_type($archivo['tmp_name']);
            if ($tipo_archivo !== 'application/pdf') {
                throw new Exception("Solo se permiten archivos PDF");
            }
            
            // Validar tamaño (máximo 10MB)
            if ($archivo['size'] > 10 * 1024 * 1024) {
                throw new Exception("El archivo no puede ser mayor a 10MB");
            }
            
            // Generar nombre único
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $nombre_archivo = $prefijo . $generador_id . '_' . $anio . '_' . time() . '.' . $extension;
            $ruta_completa = $directorio . $nombre_archivo;
            
            // Mover archivo
            if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
                throw new Exception("Error al guardar el archivo");
            }
            
            return $nombre_archivo;
        }
        
        // Procesar archivos
        $directorio = '../../procesos/uploads/soportes_anuales/';
        $archivo_cronograma = null;
        $archivo_soportes = null;
        $archivo_resultados_auditorias = null;
        $archivo_plan_mejoramiento = null;
        
        // Procesar cada archivo si se subió
        if (isset($_FILES['archivo_cronograma']) && $_FILES['archivo_cronograma']['error'] === UPLOAD_ERR_OK) {
            $archivo_cronograma = procesarArchivo($_FILES['archivo_cronograma'], $directorio, 'cronograma_');
        }
        
        if (isset($_FILES['archivo_soportes_capacitaciones']) && $_FILES['archivo_soportes_capacitaciones']['error'] === UPLOAD_ERR_OK) {
            $archivo_soportes = procesarArchivo($_FILES['archivo_soportes_capacitaciones'], $directorio, 'soportes_capacitaciones_');
        }
        
        if (isset($_FILES['archivo_resultados_auditorias']) && $_FILES['archivo_resultados_auditorias']['error'] === UPLOAD_ERR_OK) {
            $archivo_resultados_auditorias = procesarArchivo($_FILES['archivo_resultados_auditorias'], $directorio, 'resultados_auditorias_');
        }
        
        if (isset($_FILES['archivo_plan_mejoramiento']) && $_FILES['archivo_plan_mejoramiento']['error'] === UPLOAD_ERR_OK) {
            $archivo_plan_mejoramiento = procesarArchivo($_FILES['archivo_plan_mejoramiento'], $directorio, 'plan_mejoramiento_');
        }
        
        // Convertir acciones a JSON
        $acciones = isset($_POST['acciones_preventivas']) ? 
            json_encode($_POST['acciones_preventivas']) : '[]';
        
        // Obtener otra acción preventiva si existe
        $otra_accion_preventiva = $_POST['otra_accion_preventiva'] ?? null;
        
        // Verificar si ya existe un registro
        $stmt_check = $conn->prepare("SELECT id FROM reporte_anual_adicional WHERE generador_id = ? AND anio = ?");
        $stmt_check->execute([$generador_id, $anio]);
        $existe_registro = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        if ($existe_registro) {
            // Actualizar registro existente
            $sql = "UPDATE reporte_anual_adicional SET 
                num_capacitaciones_programadas = ?,
                num_capacitaciones_ejecutadas = ?,
                tiene_accidentes = ?,
                num_accidentes = ?,
                acciones_preventivas = ?,
                otra_accion_preventiva = ?,
                num_auditorias = ?,
                fecha_actualizacion = NOW()";
            
            $params = [
                $_POST['num_capacitaciones_programadas'],
                $_POST['num_capacitaciones_ejecutadas'],
                $_POST['tiene_accidentes'],
                $_POST['num_accidentes'] ?? 0,
                $acciones,
                $otra_accion_preventiva,
                $_POST['num_auditorias']
            ];
            
            // Agregar archivos si se subieron
            if ($archivo_cronograma) {
                $sql .= ", archivo_cronograma = ?";
                $params[] = $archivo_cronograma;
            }
            
            if ($archivo_soportes) {
                $sql .= ", archivo_soportes_capacitaciones = ?";
                $params[] = $archivo_soportes;
            }
            
            if ($archivo_resultados_auditorias) {
                $sql .= ", archivo_resultados_auditorias = ?";
                $params[] = $archivo_resultados_auditorias;
            }
            
            if ($archivo_plan_mejoramiento) {
                $sql .= ", archivo_plan_mejoramiento = ?";
                $params[] = $archivo_plan_mejoramiento;
            }
            
            $sql .= " WHERE generador_id = ? AND anio = ?";
            $params[] = $generador_id;
            $params[] = $anio;
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
        } else {
            // Insertar nuevo registro
            $stmt = $conn->prepare("INSERT INTO reporte_anual_adicional 
                (generador_id, anio, num_capacitaciones_programadas, archivo_cronograma,
                 num_capacitaciones_ejecutadas, archivo_soportes_capacitaciones,
                 tiene_accidentes, num_accidentes, acciones_preventivas, otra_accion_preventiva,
                 num_auditorias, archivo_resultados_auditorias, archivo_plan_mejoramiento)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $generador_id, 
                $anio,
                $_POST['num_capacitaciones_programadas'],
                $archivo_cronograma,
                $_POST['num_capacitaciones_ejecutadas'],
                $archivo_soportes,
                $_POST['tiene_accidentes'],
                $_POST['num_accidentes'] ?? 0,
                $acciones,
                $otra_accion_preventiva,
                $_POST['num_auditorias'],
                $archivo_resultados_auditorias,
                $archivo_plan_mejoramiento
            ]);
        }
        
        // Actualizar estado en revisiones_anuales
        $stmt_revision = $conn->prepare("UPDATE revisiones_anuales 
                                      SET formulario_adicional = 'pendiente'
                                      WHERE generador_id = ? AND anio = ?");
        $stmt_revision->execute([$generador_id, $anio]);
        
        $response['success'] = true;
        $response['message'] = 'Información adicional guardada correctamente';
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
        error_log("Error en procesar_reporte_adicional_ajax: " . $e->getMessage());
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    $response = ['success' => false, 'message' => 'Método no permitido'];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}