<?php
require_once '../../includes/conexion.php';
require_once '../../procesos/admin/revisiones_controller.php';
require_once '../../procesos/admin/reporte_accidentes_controller.php';
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
$accidentesController = new ReporteAccidentesController($conn);
$mensualController = new ReporteMensualController($conn);

// Obtener datos
$revision = $revisionController->obtenerRevision($generador_id, $anio);
$generador = $mensualController->obtenerDatosGenerador($generador_id);
$datosReporte = $accidentesController->obtenerDatosReporteAdicional($generador_id, $anio);
$accionesPreventivas = $accidentesController->obtenerAccionesPreventivas($datosReporte);

// Lista de acciones preventivas posibles
$listaAcciones = [
    'refuerzo_capacitacion' => 'Refuerzo en capacitación',
    'mejora_procedimientos' => 'Mejora de procedimientos',
    'actualizacion_equipos' => 'Actualización de equipos',
    'revision_protocolos' => 'Revisión de protocolos',
    'otra' => 'Otra acción'
];

// Procesar formulario de revisión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estado = $_POST['estado'];
    $observaciones = $_POST['observaciones'] ?? '';
    
    $data = [
        'formulario_accidentes' => $estado,
        'observaciones_accidentes' => $observaciones,
        'revisado_por' => $_SESSION['usuario_id'],
        'estado_general' => 'incompleto',
        'generador_id' => $generador_id,
        'anio' => $anio
    ];
    
    if ($revisionController->actualizarRevisionAccidentes($data)) {
        $_SESSION['success'] = "Revisión de capacitaciones y accidentes actualizada correctamente";
        
        // Verificar si todos los formularios están aprobados
        if ($revisionController->verificarFormulariosCompletos($generador_id, $anio)) {
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
                        Revisión - Capacitaciones, Accidentes y Auditorías
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Información del generador -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Información del Generador</h5>
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($generador['nom_generador']) ?></p>
                            <p><strong>NIT:</strong> <?= htmlspecialchars($generador['nit']) ?></p>
                            <p><strong>Responsable:</strong> <?= htmlspecialchars($generador['nom_responsable']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Detalles de la Revisión</h5>
                            <p><strong>Año:</strong> <?= $anio ?></p>
                            <p><strong>Estado actual:</strong> 
                                <span class="badge bg-<?= $revision['formulario_accidentes'] === 'aprobado' ? 'success' : ($revision['formulario_accidentes'] === 'rechazado' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($revision['formulario_accidentes']) ?>
                                </span>
                            </p>
                            <?php if ($revision['fecha_revision']): ?>
                                <p><strong>Última revisión:</strong> <?= date('d/m/Y H:i', strtotime($revision['fecha_revision'])) ?></p>
                                <p><strong>Por:</strong> <?= htmlspecialchars($revision['nombre_revisor']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($datosReporte): ?>
                    <!-- Datos de capacitaciones -->
                    <h5 class="mb-3">Capacitaciones</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Capacitaciones programadas:</strong> <?= $datosReporte['num_capacitaciones_programadas'] ?></p>
                            <?php if ($datosReporte['archivo_cronograma']): ?>
                            <p><strong>Cronograma:</strong> 
                                <a href="../../procesos/uploads/soportes_anuales/<?= $datosReporte['archivo_cronograma'] ?>" 
                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i> Ver archivo
                                </a>
                            </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Capacitaciones ejecutadas:</strong> <?= $datosReporte['num_capacitaciones_ejecutadas'] ?></p>
                            <?php if ($datosReporte['archivo_soportes_capacitaciones']): ?>
                            <p><strong>Soportes:</strong> 
                                <a href="../../procesos/uploads/soportes_anuales/<?= $datosReporte['archivo_soportes_capacitaciones'] ?>" 
                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i> Ver archivos
                                </a>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Datos de accidentes -->
                    <h5 class="mb-3">Accidentes</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>¿Tuvo accidentes?:</strong> <?= ucfirst($datosReporte['tiene_accidentes']) ?></p>
                            <?php if ($datosReporte['tiene_accidentes'] === 'si'): ?>
                            <p><strong>Número de accidentes:</strong> <?= $datosReporte['num_accidentes'] ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if (!empty($accionesPreventivas)): ?>
                            <p><strong>Acciones preventivas implementadas:</strong></p>
                            <ul>
                                <?php foreach ($accionesPreventivas as $accionKey): ?>
                                    <?php if (isset($listaAcciones[$accionKey])): ?>
                                    <li><?= $listaAcciones[$accionKey] ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                            
                            <?php if (!empty($datosReporte['otra_accion_preventiva'])): ?>
                            <p><strong>Otra acción preventiva:</strong> <?= htmlspecialchars($datosReporte['otra_accion_preventiva']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Datos de auditorías -->
                    <h5 class="mb-3">Auditorías</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Número de auditorías:</strong> <?= $datosReporte['num_auditorias'] ?></p>
                            <?php if ($datosReporte['archivo_resultados_auditorias']): ?>
                            <p><strong>Resultados de auditorías:</strong> 
                                <a href="../../procesos/uploads/soportes_anuales/<?= $datosReporte['archivo_resultados_auditorias'] ?>" 
                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i> Ver archivo
                                </a>
                            </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if ($datosReporte['archivo_plan_mejoramiento']): ?>
                            <p><strong>Plan de mejoramiento:</strong> 
                                <a href="../../procesos/uploads/soportes_anuales/<?= $datosReporte['archivo_plan_mejoramiento'] ?>" 
                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i> Ver archivo
                                </a>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        No se ha encontrado información para este año.
                    </div>
                    <?php endif; ?>

                    <!-- Formulario de revisión -->
                    <form method="POST" class="mt-4">
                        <input type="hidden" name="generador_id" value="<?= $generador_id ?>">
                        <input type="hidden" name="anio" value="<?= $anio ?>">
                        
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">Evaluación del Administrador</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Estado del formulario:</label>
                                    <select name="estado" class="form-select" required>
                                        <option value="pendiente" <?= $revision['formulario_accidentes'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="aprobado" <?= $revision['formulario_accidentes'] === 'aprobado' ? 'selected' : '' ?>>Aprobado</option>
                                        <option value="rechazado" <?= $revision['formulario_accidentes'] === 'rechazado' ? 'selected' : '' ?>>Rechazado</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Observaciones:</label>
                                    <textarea name="observaciones" class="form-control" rows="4" 
                                              placeholder="Ingrese observaciones sobre la revisión..."><?= htmlspecialchars($revision['observaciones_accidentes'] ?? '') ?></textarea>
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