<?php
require_once '../../includes/conexion.php';
require_once '../../procesos/admin/revisiones_controller.php';

// Verificar sesión y permisos de admin
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$controller = new RevisionesController($conn);

// Obtener parámetros de filtro
$filtro_tipo = $_GET['tipo_sujeto'] ?? '';
$filtro_estado = $_GET['estado_general'] ?? '';
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$registros_por_pagina = 5;

// Obtener revisiones con filtros
$revisiones = $controller->obtenerRevisionesConFiltros($filtro_tipo, $filtro_estado);

// Obtener tipos de sujeto para el filtro
$tipos_sujeto = $controller->obtenerTiposSujeto();

// Calcular paginación
$total_registros = count($revisiones);
$total_paginas = ceil($total_registros / $registros_por_pagina);
$inicio = ($pagina_actual - 1) * $registros_por_pagina;
$revisiones_paginadas = array_slice($revisiones, $inicio, $registros_por_pagina);

// Función para determinar el estado general basado en los tres formularios
function determinarEstadoGeneral($formulario_mensual, $formulario_accidentes, $formulario_contingencias) {
    // Si alguno está rechazado, estado general es "rechazado"
    if ($formulario_mensual === 'rechazado' || 
        $formulario_accidentes === 'rechazado' || 
        $formulario_contingencias === 'rechazado') {
        return 'rechazado';
    }
    
    // Si todos están aprobados, estado general es "aprobado"
    if ($formulario_mensual === 'aprobado' && 
        $formulario_accidentes === 'aprobado' && 
        $formulario_contingencias === 'aprobado') {
        return 'aprobado';
    }
    
    // En cualquier otro caso, está "pendiente"
    return 'pendiente';
}

// Función para obtener la clase CSS del badge según el estado
function obtenerClaseEstado($estado) {
    switch ($estado) {
        case 'aprobado': return 'success';
        case 'rechazado': return 'danger';
        case 'pendiente': return 'warning';
        default: return 'secondary';
    }
}

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="row">
        <div class="col-12">
            <!-- Header con botón de regreso -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-clipboard-check"></i>
                    Revisiones - Reporte Anual
                </h2>
                <a href="../dashboard.php" class="btn btn-primary">
                    <i class="bi bi-house"></i> Inicio
                </a>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="tipo_sujeto" class="form-label">Tipo de Sujeto</label>
                            <select name="tipo_sujeto" id="tipo_sujeto" class="form-select">
                                <option value="">Todos los tipos</option>
                                <?php foreach ($tipos_sujeto as $id => $nombre): ?>
                                    <option value="<?= $id ?>" <?= $filtro_tipo == $id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>              
                        <div class="col-md-4">
                            <label for="estado_general" class="form-label">Estado General</label>
                            <select name="estado_general" id="estado_general" class="form-select">
                                <option value="">Todos los estados</option>
                                <option value="pendiente" <?= $filtro_estado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="aprobado" <?= $filtro_estado === 'aprobado' ? 'selected' : '' ?>>Aprobado</option>
                                <option value="rechazado" <?= $filtro_estado === 'rechazado' ? 'selected' : '' ?>>Rechazado</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-filter"></i> Filtrar
                            </button>
                            <a href="listado_revisiones_view.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Contador de resultados -->
            <div class="alert alert-info mb-3">
                <i class="bi bi-info-circle"></i>
                Mostrando <?= count($revisiones_paginadas) ?> de <?= $total_registros ?> revisiones
                <?php if ($filtro_tipo || $filtro_estado): ?>
                    (filtradas)
                <?php endif; ?>
            </div>
            
            <?php if (empty($revisiones_paginadas)): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    No se encontraron revisiones con los filtros seleccionados.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Generador</th>
                                <th>Tipo sujeto</th>
                                <th>Año</th>
                                <th>Formularios</th>
                                <th>Estado General</th>                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($revisiones_paginadas as $revision): 
                                // Determinar el estado general
                                $estado_general = determinarEstadoGeneral(
                                    $revision['formulario_mensual'],
                                    $revision['formulario_accidentes'],
                                    $revision['formulario_contingencias']
                                );
                                
                                // Actualizar el estado general en la base de datos si es diferente
                                if ($revision['estado_general'] !== $estado_general) {
                                    $controller->actualizarEstadoGeneral(
                                        $revision['generador_id'], 
                                        $revision['anio'], 
                                        $estado_general
                                    );
                                    $revision['estado_general'] = $estado_general;
                                }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($revision['nom_generador']) ?></td>
                                <td><?= htmlspecialchars($revision['nom_tipo']) ?></td>                                
                                <td><?= $revision['anio'] ?></td>
                                <td>
                                    <div class="btn-group-vertical" role="group">
                                        <!-- Botón para Reporte Mensual -->
                                        <a href="revisar_formulario_mensual.php?generador_id=<?= $revision['generador_id'] ?>&anio=<?= $revision['anio'] ?>" 
                                           class="btn btn-sm mb-1 <?= $revision['formulario_mensual'] === 'aprobado' ? 'btn-success' : ($revision['formulario_mensual'] === 'rechazado' ? 'btn-danger' : 'btn-warning') ?>">
                                            <i class="bi bi-clipboard-data"></i>
                                            Reporte Mensual: <?= ucfirst($revision['formulario_mensual']) ?>
                                        </a>
                                        
                                        <!-- Botón para Capacitaciones, Accidentes y Auditorías -->
                                        <a href="revisar_formulario_accidentes.php?generador_id=<?= $revision['generador_id'] ?>&anio=<?= $revision['anio'] ?>" 
                                           class="btn btn-sm mb-1 <?= $revision['formulario_accidentes'] === 'aprobado' ? 'btn-success' : ($revision['formulario_accidentes'] === 'rechazado' ? 'btn-danger' : 'btn-info') ?>">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            Capacitaciones, Accidentes y Auditorías: <?= ucfirst($revision['formulario_accidentes']) ?>
                                        </a>
                                        
                                        <!-- Botón para Plan de Contingencias -->
                                        <a href="revisar_formulario_contingencias.php?generador_id=<?= $revision['generador_id'] ?>&anio=<?= $revision['anio'] ?>" 
                                           class="btn btn-sm <?= $revision['formulario_contingencias'] === 'aprobado' ? 'btn-success' : ($revision['formulario_contingencias'] === 'rechazado' ? 'btn-danger' : 'btn-secondary') ?>">
                                            <i class="bi bi-shield-exclamation"></i>
                                            Plan de Contingencias: <?= ucfirst($revision['formulario_contingencias']) ?>
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-estado bg-<?= obtenerClaseEstado($revision['estado_general']) ?>">
                                        <?= ucfirst($revision['estado_general']) ?>
                                    </span>
                                </td>                                
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                <nav aria-label="Paginación de revisiones">
                    <ul class="pagination justify-content-center">
                        <!-- Botón Anterior -->
                        <li class="page-item <?= $pagina_actual <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" 
                               href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina_actual - 1])) ?>">
                                <i class="bi bi-chevron-left"></i> Anterior
                            </a>
                        </li>

                        <!-- Números de página -->
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="page-item <?= $i === $pagina_actual ? 'active' : '' ?>">
                                <a class="page-link" 
                                   href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- Botón Siguiente -->
                        <li class="page-item <?= $pagina_actual >= $total_paginas ? 'disabled' : '' ?>">
                            <a class="page-link" 
                               href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina_actual + 1])) ?>">
                                Siguiente <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>

                <div class="mt-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        El estado general se actualiza automáticamente según la revisión de los formularios.    
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>