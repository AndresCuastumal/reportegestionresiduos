<?php
require_once '../../includes/conexion.php';
require_once '../../procesos/admin/revisiones_controller.php';
require_once '../../procesos/admin/reporte_mensual_controller.php';

// Verificar sesión y permisos de admin
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}

if (!isset($_GET['generador_id']) || !isset($_GET['anio'])) {
    header("Location: ../admin/listado_revisiones_view.php");
    exit();
}

$generador_id = $_GET['generador_id'];
$anio = $_GET['anio'];

$revisionController = new RevisionesController($conn);
$mensualController = new ReporteMensualController($conn);

// Obtener datos
$revision = $revisionController->obtenerRevision($generador_id, $anio);
$generador = $mensualController->obtenerDatosGenerador($generador_id);
$reportes_existentes = $mensualController->obtenerReportesExistentes($generador_id, $anio);

// Procesar formulario de revisión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estado = $_POST['estado'];
    $observaciones = $_POST['observaciones'] ?? '';
    
    $data = [
        'formulario_mensual' => $estado,
        'observaciones_mensual' => $observaciones,
        'revisado_por' => $_SESSION['usuario_id'],
        'estado_general' => 'incompleto', // Cambiar según lógica
        'generador_id' => $generador_id,
        'anio' => $anio
    ];
    
    if ($revisionController->actualizarRevision($data)) {
        $_SESSION['success'] = "Revisión actualizada correctamente";
        
        // Verificar si todos los formularios están aprobados
        if ($revisionController->verificarFormulariosCompletos($generador_id, $anio)) {
            // Aquí iría la lógica para generar y enviar el certificado PDF
            $_SESSION['info'] = "Todos los formularios están aprobados. Se enviará el certificado.";
        }
        
        header("Location: listado_revisiones_view.php");
        exit();
    } else {
        $_SESSION['error'] = "Error al actualizar la revisión";
    }
}

include '../../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-clipboard-check"></i>
                        Revisión - Reporte Mensual de Residuos
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Información del generador -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Información del Generador</h5>
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($generador['nom_generador']) ?></p>
                            <p><strong>NIT:</strong> <?= htmlspecialchars($generador['nit']) ?></p>
                            <p><strong>Dirección:</strong> <?= htmlspecialchars($generador['dir_establecimiento']) ?></p>
                            <p><strong>Nombre de responsable de reporte:</strong> <?= htmlspecialchars($generador['nom_responsable']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Detalles de la Revisión</h5>
                            <p><strong>Año:</strong> <?= $anio ?></p>
                            <p><strong>Estado actual:</strong> 
                                <span class="badge bg-<?= $revision['formulario_mensual'] === 'aprobado' ? 'success' : ($revision['formulario_mensual'] === 'rechazado' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($revision['formulario_mensual']) ?>
                                </span>
                            </p>
                            <?php if ($revision['fecha_revision']): ?>
                                <p><strong>Última revisión:</strong> <?= date('d/m/Y H:i', strtotime($revision['fecha_revision'])) ?></p>
                                <p><strong>Por:</strong> <?= htmlspecialchars($revision['nombre_revisor']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Datos del reporte mensual -->
                    <h5 class="mb-3">Datos del Reporte Mensual</h5>
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
                                               value="<?= $valor_actual ?>"
                                               class="form-control" 
                                               disabled>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Soporte documental -->
                    <?php if (!empty($revision['soporte_pdf'])): ?>
                    <div class="mt-4">
                        <h5>Soporte Documental</h5>
                        <a href="../../procesos/uploads/soportes_anuales/<?= $revision['soporte_pdf'] ?>" 
                           target="_blank" class="btn btn-outline-primary">
                            <i class="bi bi-download"></i> Ver PDF de soporte
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Formulario de revisión -->
                    <form method="POST" class="mt-4">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">Evaluación del Administrador</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Estado del formulario:</label>
                                    <select name="estado" class="form-select" required>
                                        <option value="pendiente" <?= $revision['formulario_mensual'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="aprobado" <?= $revision['formulario_mensual'] === 'aprobado' ? 'selected' : '' ?>>Aprobado</option>
                                        <option value="rechazado" <?= $revision['formulario_mensual'] === 'rechazado' ? 'selected' : '' ?>>Rechazado</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Observaciones:</label>
                                    <textarea name="observaciones" class="form-control" rows="4" 
                                              placeholder="Ingrese observaciones sobre la revisión..."><?= htmlspecialchars($revision['observaciones_mensual'] ?? '') ?></textarea>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="listado_revisiones_view.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Volver
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle"></i> Guardar Revisión
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>