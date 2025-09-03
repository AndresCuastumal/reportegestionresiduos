<?php
session_start();
require_once '../../includes/conexion.php';
require_once '../../includes/enviar_correo.php';

// Verificar que viene del formulario de contingencias
if (!isset($_SESSION['generador_id_reportando'])) {
    // Limpiar buffer antes de redireccionar
    header("Location: ../../vistas/generador/listado_generadores_view.php");
    exit();
}

$generador_id = $_POST['generador_id'];
$anio = $_POST['anio'];
$fecha_reporte = date('Y-m-d');
$persona_reporta = $_SESSION['usuario_id'];

try {
    // Convertir arrays de acciones a JSON
    $incendios_acciones = isset($_POST['incendios_acciones']) ? json_encode($_POST['incendios_acciones']) : '[]';
    $agua_acciones = isset($_POST['agua_acciones']) ? json_encode($_POST['agua_acciones']) : '[]';
    $energia_acciones = isset($_POST['energia_acciones']) ? json_encode($_POST['energia_acciones']) : '[]';
    $derrames_acciones = isset($_POST['derrames_acciones']) ? json_encode($_POST['derrames_acciones']) : '[]';
    $recoleccion_acciones = isset($_POST['recoleccion_acciones']) ? json_encode($_POST['recoleccion_acciones']) : '[]';
    $operativas_acciones = isset($_POST['operativas_acciones']) ? json_encode($_POST['operativas_acciones']) : '[]';
    
    // Verificar si ya existe un registro para este generador y año
    $stmt_check = $conn->prepare("SELECT id FROM contingencias WHERE generador_id = ? AND anio = ?");
    $stmt_check->execute([$generador_id, $anio]);
    $existe_registro = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if ($existe_registro) {
        error_log("Actualizando registro existente para generador_id: $generador_id, anio: $anio");
        // Actualizar registro existente
        $stmt = $conn->prepare("UPDATE contingencias SET 
            fecha_reporte = ?, persona_reporta = ?, 
            incendios_numero = ?, incendios_acciones = ?, incendios_otra_accion = ?,
            inundaciones_numero = ?, inundaciones_acciones = ?,
            agua_numero = ?, agua_acciones = ?, agua_otra_accion = ?,
            energia_numero = ?, energia_acciones = ?, energia_otra_accion = ?,
            derrames_numero = ?, derrames_tipo = ?, derrames_acciones = ?, derrames_otra_accion = ?,
            recoleccion_numero = ?, recoleccion_acciones = ?, recoleccion_otra_accion = ?,
            operativas_numero = ?, operativas_acciones = ?, operativas_otra_accion = ?,
            fecha_creacion = CURRENT_TIMESTAMP
            WHERE generador_id = ? AND anio = ?");
        
        $stmt->execute([
            $fecha_reporte,
            $persona_reporta,
            $_POST['incendios_numero'] ?? 0,
            $incendios_acciones,
            $_POST['incendios_otra_accion'] ?? '',
            $_POST['inundaciones_numero'] ?? 0,
            $_POST['inundaciones_acciones'] ?? '',
            $_POST['agua_numero'] ?? 0,
            $agua_acciones,
            $_POST['agua_otra_accion'] ?? '',
            $_POST['energia_numero'] ?? 0,
            $energia_acciones,
            $_POST['energia_otra_accion'] ?? '',
            $_POST['derrames_numero'] ?? 0,
            $_POST['derrames_tipo'] ?? '',
            $derrames_acciones,
            $_POST['derrames_otra_accion'] ?? '',
            $_POST['recoleccion_numero'] ?? 0,
            $recoleccion_acciones,
            $_POST['recoleccion_otra_accion'] ?? '',
            $_POST['operativas_numero'] ?? 0,
            $operativas_acciones,
            $_POST['operativas_otra_accion'] ?? '',
            $generador_id,  // Para el WHERE
            $anio           // Para el WHERE
        ]);
        
        $_SESSION['mensaje_exito'] = "¡El reporte se ha actualizado exitosamente!.  Recibirá un mensaje de confirmación al correo registrado en nuestro sistema";
    } else {
        // Insertar nuevo registro            
        $stmt = $conn->prepare("INSERT INTO contingencias 
            (generador_id, anio, fecha_reporte, persona_reporta, 
            incendios_numero, incendios_acciones, incendios_otra_accion,
            inundaciones_numero, inundaciones_acciones,
            agua_numero, agua_acciones, agua_otra_accion,
            energia_numero, energia_acciones, energia_otra_accion,
            derrames_numero, derrames_tipo, derrames_acciones, derrames_otra_accion,
            recoleccion_numero, recoleccion_acciones, recoleccion_otra_accion,
            operativas_numero, operativas_acciones, operativas_otra_accion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $generador_id, $anio,
            $fecha_reporte,
            $persona_reporta,
            $_POST['incendios_numero'] ?? 0,
            $incendios_acciones,
            $_POST['incendios_otra_accion'] ?? '',
            $_POST['inundaciones_numero'] ?? 0,
            $_POST['inundaciones_acciones'] ?? '',
            $_POST['agua_numero'] ?? 0,
            $agua_acciones,
            $_POST['agua_otra_accion'] ?? '',
            $_POST['energia_numero'] ?? 0,
            $energia_acciones,
            $_POST['energia_otra_accion'] ?? '',
            $_POST['derrames_numero'] ?? 0,
            $_POST['derrames_tipo'] ?? '',
            $derrames_acciones,
            $_POST['derrames_otra_accion'] ?? '',
            $_POST['recoleccion_numero'] ?? 0,
            $recoleccion_acciones,
            $_POST['recoleccion_otra_accion'] ?? '',
            $_POST['operativas_numero'] ?? 0,
            $operativas_acciones,
            $_POST['operativas_otra_accion'] ?? ''                
        ]);
        
        $_SESSION['mensaje_exito'] = "¡El reporte se ha actualizado exitosamente!.  Recibirá un mensaje de confirmación al correo registrado en nuestro sistema";
    }
    
    // Enviar correo de notificación
    $stmt_usuario = $conn->prepare("SELECT u.email, g.nom_responsable, g.nom_generador 
                               FROM usuarios u 
                               JOIN usuario_generador ug ON ug.usuario_id = u.id
                               JOIN generador g ON g.id = ug.generador_id 
                               WHERE g.id = :generador_id and u.id = :usuario_id");
    $stmt_usuario->bindParam(':generador_id', $generador_id);
    $stmt_usuario->bindParam(':usuario_id', $persona_reporta);
    $stmt_usuario->execute();
    $info_usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);
    
    if ($info_usuario) {
        $destinatario = $info_usuario['email'];
        $nombre_usuario = $info_usuario['nom_responsable'];
        $nombre_generador = $info_usuario['nom_generador'];
        
        // Configurar y enviar el correo
        $mail = configurarMailer();
        $mail->addAddress($destinatario);
        $mail->Subject = htmlentities('Confirmación de Reporte Completo - Sistema de Gestión de Residuos', ENT_QUOTES, 'UTF-8');
        
        // Cuerpo del mensaje en HTML
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Confirmación de Reporte</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2c3e50; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 20px; border-radius: 5px; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Sistema de Gestión de Residuos</h1>
                </div>
                <div class='content'>
                    <h2>Confirmación de Recepción</h2>
                    <p>Estimado(a) <strong>$nombre_usuario</strong>,</p>
                    <p>Hemos recibido exitosamente todos sus reportes para el generador <strong>$nombre_generador</strong> correspondiente al año <strong>$anio</strong>.</p>
                    <p>Los siguientes formularios han sido completados:</p>
                    <ul>
                        <li>Reporte Mensual de Residuos</li>
                        <li>Información Adicional y Capacitaciones</li>
                        <li>Plan de Contingencias</li>
                    </ul>
                    <p>El reporte completo ha sido registrado en nuestro sistema y se encuentra en estado: <strong>Pendiente de revisión</strong>.</p>
                    <p>Recibirá una notificación una vez que el técnico asignado haya revisado la información.</p>
                    <p>Gracias por utilizar nuestro sistema.</p>
                </div>
                <div class='footer'>
                    <p>Este es un mensaje automático, por favor no responda a este correo.</p>
                    <p>&copy; " . date('Y') . " Sistema de Gestión de Residuos. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Versión alternativa en texto plano
        $mail->AltBody = "Confirmación de Reporte Completo\n\n" .
                        "Estimado(a) $nombre_usuario,\n\n" .
                        "Hemos recibido exitosamente todos sus reportes para el generador $nombre_generador correspondiente al año $anio.\n\n" .
                        "Los siguientes formularios han sido completados:\n" .
                        "- Reporte Mensual de Residuos\n" .
                        "- Información Adicional y Capacitaciones\n" .
                        "- Plan de Contingencias\n\n" .
                        "El reporte completo ha sido registrado en nuestro sistema y se encuentra en estado: Pendiente de revisión.\n\n" .
                        "Recibirá una notificación una vez que el técnico asignado haya revisado la información.\n\n" .
                        "Gracias por utilizar nuestro sistema.\n\n" .
                        "Este es un mensaje automático, por favor no responda a este correo.";
        
        // Intentar enviar el correo
        if ($mail->send()) {
            error_log("Correo de confirmación enviado a: " . $destinatario);
        } else {
            error_log("Error al enviar correo de confirmación: " . $mail->ErrorInfo);
            // No redirigimos con error para no interrumpir el flujo principal
        }
    }
    
    // Limpiar sesión y redirigir
    unset($_SESSION['generador_id_reportando']);
    unset($_SESSION['anio_reportando']);
    
    // Limpiar buffer y redirigir
    header("Location: ../../vistas/generador/listado_generadores_view.php");
    exit();    
} catch (Exception $e) {
    // Limpiar buffer y redirigir con error
    $_SESSION['error'] = $e->getMessage();
    header("Location: ../../vistas/generador/reporte_contingencias_view.php?id=".$generador_id);    
    exit();
}