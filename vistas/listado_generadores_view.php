

<?php
require_once '../procesos/listado_generadores_controller.php';
include '../includes/header.php'; // Incluye el encabezado HTML
?>
<style>
/* ===== ESTILOS PARA CATEGORÍAS DE GENERADORES ===== */
.badge-micro { 
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white !important;
    border: 1px solid #545b62;
}
.badge-pequeno-generador { 
    background: linear-gradient(135deg, #28a745 0%, #218838 100%);
    color: white !important;
    border: 1px solid #1e7e34;
}
.badge-mediano-generador { 
    background: linear-gradient(135deg, #fd7e14 0%, #e36209 100%);
    color: white !important;
    border: 1px solid #d85c08;
}
.badge-gran-generador { 
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white !important;
    border: 1px solid #bd2130;
}
/* Estilos base para todos los badges */
.badge {
    font-size: 0.85em;
    padding: 0.5em 0.75em;
    border-radius: 0.375rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(8, 42, 231, 0.5);
    transition: all 0.2s ease;
}

.badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}        
</style>
<style>
    /* Solo estilos ESPECÍFICOS de esta página */
    .table-hover tbody tr:hover {
        background-color: rgba(74, 106, 209, 0.1);
    }
    
    /* Estilos temporales para debugging */
    .test-debug {
        border: 1px solid red;
    }

    /* ===== ESTILOS PARA ESTADOS DE REVISIÓN ===== */

.badge-estado-pendiente-gradient { 
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    color: #000 !important;
    border: 1px solid #d39e00;
}
.badge-estado-aprobado-gradient { 
    background: linear-gradient(135deg, #198754 0%, #0f6848 100%);
    color: #fff !important;
    border: 1px solid #0c573b;
}
.badge-estado-rechazado-gradient { 
    background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
    color: #fff !important;
    border: 1px solid #a71e2a;
}
.badge-estado-sin-revision-gradient { 
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: #fff !important;
    border: 1px solid #545b62;
}

.badge-estado {
    font-size: 0.8em;
    padding: 0.6em 0.9em;
    border-radius: 0.5rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

.badge-estado:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
.table-hover tbody tr:hover {
            background-color: rgba(74, 106, 209, 0.1);
}

</style>
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-biohazard me-2"></i>Reporte de Residuos
            </a>
            <span class="navbar-text ms-auto">
                <?php echo $_SESSION['usuario_email']; ?>
                <a href="dashboard.php" class="btn btn-sm btn-outline-light ms-3">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </span>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container my-5">
        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['mensaje_exito']; unset($_SESSION['mensaje_exito']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-building me-2"></i>Mis Establecimientos</h2>
            <a href="generador_view.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Añadir Nuevo
            </a>
        </div>

        <!-- Tabla de generadores -->
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Dirección</th>
                        <th>Categoría</th>
                        <th>Estado <?= date('Y', strtotime('-1 year') ) ?></th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($generadores as $generador): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($generador['nom_generador']); ?></td>
                            <td><?php echo htmlspecialchars($generador['tipo_sujeto']); ?></td>
                            <td><?php echo htmlspecialchars($generador['dir_establecimiento']); ?></td>
                            <td>
                                <?php if ($generador['categoria']): ?>
                                    <?php
                                    // Mapear categorías a clases CSS
                                    $clases_badge = [
                                        'Micro generador' => 'badge-micro',
                                        'Pequeño generador' => 'badge-pequeno-generador',
                                        'Mediano generador' => 'badge-mediano-generador',
                                        'Gran generador' => 'badge-gran-generador'
                                    ];
                                    
                                    $clase = $clases_badge[$generador['categoria']] ?? 'badge-secondary';
                                    ?>
                                    <span class="badge <?php echo $clase; ?>">
                                        <?php echo htmlspecialchars($generador['categoria']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Sin datos</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $estado = $estados_revision[$generador['id']] ?? 'sin_revision';
                                
                                // Configuración de estados
                                $estados_config = [
                                    'pendiente' => [
                                        'clase' => 'badge-estado-pendiente-gradient',
                                        'texto' => 'PENDIENTE',
                                        'icono' => 'bi bi-clock'
                                    ],
                                    'aprobado' => [
                                        'clase' => 'badge-estado-aprobado-gradient', 
                                        'texto' => 'APROBADO',
                                        'icono' => 'bi bi-check-circle'
                                    ],
                                    'rechazado' => [
                                        'clase' => 'badge-estado-rechazado-gradient',
                                        'texto' => 'RECHAZADO',
                                        'icono' => 'bi bi-x-circle'
                                    ],
                                    'sin_revision' => [
                                        'clase' => 'badge-estado-sin-revision-gradient',
                                        'texto' => 'SIN REVISIÓN',
                                        'icono' => 'bi bi-dash-circle'
                                    ]
                                ];
                                
                                $config = $estados_config[$estado] ?? $estados_config['sin_revision'];
                                ?>
                                
                                <span class="badge badge-estado <?= $config['clase'] ?>" 
                                    data-bs-toggle="tooltip" 
                                    title="Estado de revisión <?= date('Y') ?>">
                                    <i class="<?= $config['icono'] ?> me-1"></i>
                                    <?= $config['texto'] ?>
                                </span>
                            </td>
                               
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="reporte_mensual_view.php?id=<?php echo $generador['id']; ?>" 
                                       class="btn btn-sm btn-success" title="Reportar residuos">
                                        <i class="bi bi-clipboard-data"></i>
                                    </a>
                                    <a href="generador_view.php?id=<?php echo $generador['id']; ?>" 
                                       class="btn btn-sm btn-warning" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button onclick="confirmarEliminacion(<?php echo $generador['id']; ?>)" 
                                            class="btn btn-sm btn-danger" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($generadores)): ?>
            <div class="alert alert-info text-center mt-5">
                <i class="bi bi-info-circle me-2"></i>No tienes establecimientos registrados.
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de eliminar este establecimiento? Todos sus reportes mensuales también se eliminarán.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a id="eliminarBtn" href="#" class="btn btn-danger">Eliminar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarEliminacion(id) {
            const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
            document.getElementById('eliminarBtn').href = `listado_generadores_view.php?eliminar=${id}`;
            modal.show();
        }
    </script>
</body>
</html>
