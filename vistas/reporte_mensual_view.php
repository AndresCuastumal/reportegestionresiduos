<?php
require_once '../includes/conexion.php'; // Primero la conexión
require_once '../procesos/reporte_mensual_controller.php';

// Obtener datos del generador
if (isset($_GET['id'])) {
    $generador_id = $_GET['id'];
    
    // Verificar permisos de sesión
    /*session_start();
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }*/
    
    // Crear controlador y obtener datos
    $controller = new ReporteMensualController($conn);
    
    // Verificar permisos
    if ($_SESSION['usuario_rol'] !== 'admin') {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM usuario_generador 
                               WHERE usuario_id = ? AND generador_id = ?");
        $stmt->execute([$_SESSION['usuario_id'], $generador_id]);
        $tiene_acceso = $stmt->fetchColumn();
        
        if (!$tiene_acceso) {
            header("Location: acceso_denegado.php");
            exit();
        }
    }
    
    // Obtener datos del generador
    $generador = $controller->obtenerDatosGenerador($generador_id);
    $anio_actual = date('Y', strtotime('-1 year'));
    $reportes_existentes = $controller->obtenerReportesExistentes($generador_id, $anio_actual);
    
} else {
    header("Location: listado_generadores_view.php");
    exit();
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-clipboard-data"></i>
                        Reporte Mensual de Residuos - <?= htmlspecialchars($generador['nom_generador']) ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" action="../procesos/procesar_reporte_mensual.php?id=<?= $generador_id ?>">
                        <input type="hidden" name="anio" value="<?= $anio_actual ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Año de reporte:</label>
                            <input type="number" class="form-control" value="<?= $anio_actual ?>" disabled>
                            <small class="form-text text-muted">Sistema de reporte anual según Resolución 591 de 2024</small>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Mes</th>
                                        <th>Cantidad (kg)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $meses = [
                                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                                    ];
                                    
                                    foreach ($meses as $id_mes => $nombre_mes):
                                        $valor_actual = '';
                                        foreach ($reportes_existentes as $reporte) {
                                            if ($reporte['id_mes'] == $id_mes) {
                                                $valor_actual = $reporte['total_kg'];
                                                break;
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td><?= $nombre_mes ?></td>
                                        <td>
                                            <input type="number" step="0.01" min="0" 
                                                   name="meses[<?= $id_mes ?>]" 
                                                   value="<?= $valor_actual ?>"
                                                   class="form-control" 
                                                   placeholder="0.00">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-file-pdf"></i>
                                    Soporte Documental Anual
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="soporte_pdf" class="form-label">
                                        Cargar PDF con soportes de los 12 meses
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" class="form-control" id="soporte_pdf" name="soporte_pdf" 
                                        accept=".pdf" required>
                                    <div class="form-text">
                                        Suba un solo archivo PDF que incluya todos los certificados, actas o soportes 
                                        de la empresa recolectora para los 12 meses del año. Tamaño máximo: 10MB.
                                    </div>
                                </div>

                                <?php
                                // Obtener revisión actual
                                $stmt = $conn->prepare("SELECT soporte_pdf FROM revisiones_anuales 
                                                    WHERE generador_id = ? AND anio = ?");
                                $stmt->execute([$generador_id, $anio_actual]);
                                $revision = $stmt->fetch(PDO::FETCH_ASSOC);
                                ?>

                                <?php if (!empty($revision['soporte_pdf'])): ?>
                                <div class="alert alert-info">
                                    <strong>Soporte actual:</strong> 
                                    <a href="../uploads/soportes_anuales/<?= $revision['soporte_pdf'] ?>" 
                                    target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                        <i class="bi bi-download"></i> Ver PDF actual
                                    </a>
                                </div>
                                <?php endif; ?>                              
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="listado_generadores_view.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-cloud-upload"></i> Guardar Reporte y Soporte
                            </button>
                        </div> 
                    </form>
                </div>
            </div>
            
            <div class="mt-4">
                <h5>Instrucciones:</h5>
                <ul>
                    <li>Ingrese la cantidad total de residuos peligrosos generados cada mes en kilogramos (kg)</li>
                    <li>El sistema calculará automáticamente su categoría basado en el promedio móvil de los últimos 6 meses</li>
                    <li><strong>Nuevos rangos:</strong>
                        <ul>
                            <li>Micro generador: &lt; 10 kg</li>
                            <li>Pequeño generador: 10 - 99.99 kg</li>
                            <li>Mediano generador: 100 - 999.99 kg</li>
                            <li>Gran generador: ≥ 1000 kg</li>
                        </ul>
                    </li>
                    <li>Puede dejar en blanco los meses sin generación de residuos</li>
                </ul>
            </div>
            <div class="alert alert-warning mt-4">
                <h6><i class="bi bi-exclamation-triangle"></i> Importante:</h6>
                <ul class="mb-0">
                    <li>El PDF debe incluir <strong>todos los soportes mensuales</strong> del año <?= $anio_actual ?></li>
                    <li>El archivo será revisado por un administrador para validar la información</li>
                    <li>El estado de su reporte cambiará a "Pendiente de revisión"</li>
                    <li>Recibirá una notificación cuando sea aprobado o rechazado</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>