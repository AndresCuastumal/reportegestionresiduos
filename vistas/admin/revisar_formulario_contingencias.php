<?php
require_once '../../includes/conexion.php';
require_once '../../procesos/admin/revisiones_controller.php';
require_once '../../procesos/admin/reporte_contingencias_controller.php';
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
$contingenciasController = new ReporteContingenciasController($conn);
$mensualController = new ReporteMensualController($conn);

// Obtener datos
$revision = $revisionController->obtenerRevision($generador_id, $anio);
$generador = $mensualController->obtenerDatosGenerador($generador_id);
$datosContingencias = $contingenciasController->obtenerDatosContingencias($generador_id, $anio);

// Obtener listas de acciones
$accionesIncendios = $contingenciasController->obtenerAccionesIncendios();
$accionesAgua = $contingenciasController->obtenerAccionesAgua();
$accionesEnergia = $contingenciasController->obtenerAccionesEnergia();
$accionesDerrames = $contingenciasController->obtenerAccionesDerrames();
$accionesRecoleccion = $contingenciasController->obtenerAccionesRecoleccion();
$accionesOperativas = $contingenciasController->obtenerAccionesOperativas();
$tiposDerrames = $contingenciasController->obtenerTiposDerrames();

// Procesar acciones JSON
$accionesIncendiosData = $contingenciasController->obtenerAccionesJSON($datosContingencias['incendios_acciones'] ?? '');
$accionesAguaData = $contingenciasController->obtenerAccionesJSON($datosContingencias['agua_acciones'] ?? '');
$accionesEnergiaData = $contingenciasController->obtenerAccionesJSON($datosContingencias['energia_acciones'] ?? '');
$accionesDerramesData = $contingenciasController->obtenerAccionesJSON($datosContingencias['derrames_acciones'] ?? '');
$accionesRecoleccionData = $contingenciasController->obtenerAccionesJSON($datosContingencias['recoleccion_acciones'] ?? '');
$accionesOperativasData = $contingenciasController->obtenerAccionesJSON($datosContingencias['operativas_acciones'] ?? '');

// Procesar formulario de revisión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estado = $_POST['estado'];
    $observaciones = $_POST['observaciones'] ?? '';
    
    $data = [
        'formulario_contingencias' => $estado,
        'observaciones_contingencias' => $observaciones,
        'revisado_por' => $_SESSION['usuario_id'],
        'estado_general' => 'incompleto',
        'generador_id' => $generador_id,
        'anio' => $anio
    ];
    
    if ($revisionController->actualizarRevisionContingencias($data)) {
        $_SESSION['success'] = "Revisión del plan de contingencias actualizada correctamente";
        
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
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-shield-exclamation"></i>
                        Revisión - Plan de Contingencias
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
                                <span class="badge bg-<?= $revision['formulario_contingencias'] === 'aprobado' ? 'success' : ($revision['formulario_contingencias'] === 'rechazado' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($revision['formulario_contingencias']) ?>
                                </span>
                            </p>
                            <?php if ($revision['fecha_revision']): ?>
                                <p><strong>Última revisión:</strong> <?= date('d/m/Y H:i', strtotime($revision['fecha_revision'])) ?></p>
                                <p><strong>Por:</strong> <?= htmlspecialchars($revision['nombre_revisor']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($datosContingencias): ?>
                    <!-- Información general -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Fecha de reporte:</strong> <?= date('d/m/Y', strtotime($datosContingencias['fecha_reporte'])) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Persona que reporta:</strong> <?= htmlspecialchars($datosContingencias['nombre_persona_reporta'] ?? 'No especificado') ?></p>
                        </div>
                    </div>

                    <!-- Incendios -->
                    <div class="card mb-4">
                        <div class="card-header bg-warning">
                            <h5 class="mb-0">Incendios</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Número de incidentes:</strong> <?= $datosContingencias['incendios_numero'] ?></p>
                                </div>
                                <div class="col-md-6">
                                    <?php if (!empty($accionesIncendiosData)): ?>
                                    <p><strong>Acciones tomadas:</strong></p>
                                    <ul>
                                        <?php foreach ($accionesIncendiosData as $accionKey): ?>
                                            <?php if (isset($accionesIncendios[$accionKey])): ?>
                                            <li><?= $accionesIncendios[$accionKey] ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                    <?php if (!empty($datosContingencias['incendios_otra_accion'])): ?>
                                    <p><strong>Otra acción:</strong> <?= htmlspecialchars($datosContingencias['incendios_otra_accion']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inundaciones -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Inundaciones</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Número de incidentes:</strong> <?= $datosContingencias['inundaciones_numero'] ?></p>
                                </div>
                                <div class="col-md-6">
                                    <?php if (!empty($datosContingencias['inundaciones_acciones'])): ?>
                                    <p><strong>Acciones tomadas:</strong> <?= htmlspecialchars($datosContingencias['inundaciones_acciones']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Agua -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Falta de Agua</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Número de incidentes:</strong> <?= $datosContingencias['agua_numero'] ?></p>
                                </div>
                                <div class="col-md-6">
                                    <?php if (!empty($accionesAguaData)): ?>
                                    <p><strong>Acciones tomadas:</strong></p>
                                    <ul>
                                        <?php foreach ($accionesAguaData as $accionKey): ?>
                                            <?php if (isset($accionesAgua[$accionKey])): ?>
                                            <li><?= $accionesAgua[$accionKey] ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                    <?php if (!empty($datosContingencias['agua_otra_accion'])): ?>
                                    <p><strong>Otra acción:</strong> <?= htmlspecialchars($datosContingencias['agua_otra_accion']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Energía -->
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Falta de Energía</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Número de incidentes:</strong> <?= $datosContingencias['energia_numero'] ?></p>
                                </div>
                                <div class="col-md-6">
                                    <?php if (!empty($accionesEnergiaData)): ?>
                                    <p><strong>Acciones tomadas:</strong></p>
                                    <ul>
                                        <?php foreach ($accionesEnergiaData as $accionKey): ?>
                                            <?php if (isset($accionesEnergia[$accionKey])): ?>
                                            <li><?= $accionesEnergia[$accionKey] ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                    <?php if (!empty($datosContingencias['energia_otra_accion'])): ?>
                                    <p><strong>Otra acción:</strong> <?= htmlspecialchars($datosContingencias['energia_otra_accion']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Derrames -->
                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Derrames</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>Número de incidentes:</strong> <?= $datosContingencias['derrames_numero'] ?></p>
                                </div>
                                <div class="col-md-4">
                                    <?php if (!empty($datosContingencias['derrames_tipo'])): ?>
                                    <p><strong>Tipo de derrame:</strong> 
                                        <?= isset($tiposDerrames[$datosContingencias['derrames_tipo']]) ? $tiposDerrames[$datosContingencias['derrames_tipo']] : $datosContingencias['derrames_tipo'] ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <?php if (!empty($accionesDerramesData)): ?>
                                    <p><strong>Acciones tomadas:</strong></p>
                                    <ul>
                                        <?php foreach ($accionesDerramesData as $accionKey): ?>
                                            <?php if (isset($accionesDerrames[$accionKey])): ?>
                                            <li><?= $accionesDerrames[$accionKey] ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                    <?php if (!empty($datosContingencias['derrames_otra_accion'])): ?>
                                    <p><strong>Otra acción:</strong> <?= htmlspecialchars($datosContingencias['derrames_otra_accion']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recolección -->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Fallas en Recolección</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Número de incidentes:</strong> <?= $datosContingencias['recoleccion_numero'] ?></p>
                                </div>
                                <div class="col-md-6">
                                    <?php if (!empty($accionesRecoleccionData)): ?>
                                    <p><strong>Acciones tomadas:</strong></p>
                                    <ul>
                                        <?php foreach ($accionesRecoleccionData as $accionKey): ?>
                                            <?php if (isset($accionesRecoleccion[$accionKey])): ?>
                                            <li><?= $accionesRecoleccion[$accionKey] ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                    <?php if (!empty($datosContingencias['recoleccion_otra_accion'])): ?>
                                    <p><strong>Otra acción:</strong> <?= htmlspecialchars($datosContingencias['recoleccion_otra_accion']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Operativas -->
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Fallas Operativas</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Número de incidentes:</strong> <?= $datosContingencias['operativas_numero'] ?></p>
                                </div>
                                <div class="col-md-6">
                                    <?php if (!empty($accionesOperativasData)): ?>
                                    <p><strong>Acciones tomadas:</strong></p>
                                    <ul>
                                        <?php foreach ($accionesOperativasData as $accionKey): ?>
                                            <?php if (isset($accionesOperativas[$accionKey])): ?>
                                            <li><?= $accionesOperativas[$accionKey] ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php endif; ?>
                                    <?php if (!empty($datosContingencias['operativas_otra_accion'])): ?>
                                    <p><strong>Otra acción:</strong> <?= htmlspecialchars($datosContingencias['operativas_otra_accion']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        No se ha encontrado información de contingencias para este año.
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
                                        <option value="pendiente" <?= $revision['formulario_contingencias'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="aprobado" <?= $revision['formulario_contingencias'] === 'aprobado' ? 'selected' : '' ?>>Aprobado</option>
                                        <option value="rechazado" <?= $revision['formulario_contingencias'] === 'rechazado' ? 'selected' : '' ?>>Rechazado</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Observaciones:</label>
                                    <textarea name="observaciones" class="form-control" rows="4" 
                                              placeholder="Ingrese observaciones sobre la revisión..."><?= htmlspecialchars($revision['observaciones_contingencias'] ?? '') ?></textarea>
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