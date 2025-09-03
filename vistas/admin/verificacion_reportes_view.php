<?php
require_once '../includes/conexion.php';
require_once '../procesos/procesar_listado_revisiones.php';

// Verificar sesión y permisos de admin
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$controller = new RevisionesController($conn);
$revisiones = $controller->obtenerRevisionesPendientes();

include '../includes/header.php';
?>

<div class="container my-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="bi bi-clipboard-check"></i>
                Revisiones Pendientes - Reporte Anual
            </h2>
            
            <?php if (empty($revisiones)): ?>
                <div class="alert alert-info">
                    No hay revisiones pendientes en este momento.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Generador</th>
                                <th>NIT</th>
                                <th>Municipio</th>
                                <th>Año</th>
                                <th>Formularios</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($revisiones as $revision): ?>
                            <tr>
                                <td><?= htmlspecialchars($revision['nom_generador']) ?></td>
                                <td><?= htmlspecialchars($revision['nit']) ?></td>
                                <td><?= htmlspecialchars($revision['municipio']) ?></td>
                                <td><?= $revision['anio'] ?></td>
                                <td>
                                    <span class="badge bg-<?= $revision['formulario_mensual'] === 'aprobado' ? 'success' : ($revision['formulario_mensual'] === 'rechazado' ? 'danger' : 'warning') ?>">
                                        Mensual: <?= ucfirst($revision['formulario_mensual']) ?>
                                    </span>
                                    <span class="badge bg-<?= $revision['formulario_caracterizacion'] === 'aprobado' ? 'success' : ($revision['formulario_caracterizacion'] === 'rechazado' ? 'danger' : 'secondary') ?>">
                                        Caracterización: <?= ucfirst($revision['formulario_caracterizacion']) ?>
                                    </span>
                                    <span class="badge bg-<?= $revision['formulario_planificacion'] === 'aprobado' ? 'success' : ($revision['formulario_planificacion'] === 'rechazado' ? 'danger' : 'secondary') ?>">
                                        Planificación: <?= ucfirst($revision['formulario_planificacion']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $revision['estado_general'] === 'completo' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($revision['estado_general']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="revisar_formulario_mensual.php?generador_id=<?= $revision['generador_id'] ?>&anio=<?= $revision['anio'] ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Revisar
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>