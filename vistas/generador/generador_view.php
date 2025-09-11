<?php
require_once '../../procesos/generador/generador_controller.php';

// Verificar si estamos editando (si viene un ID por GET)
$generadorExistente = [];
if (isset($_GET['id'])) {
    $idGenerador = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($idGenerador) {
        $generadorExistente = $controller->obtenerGeneradorPorId($idGenerador);
        
        // Si no encontramos el generador, redirigir
        if (empty($generadorExistente)) {
            $_SESSION['mensaje_error'] = "El generador solicitado no existe";
            header("Location: listado_generadores_view.php");
            exit();
        }
    }
}

include '../../includes/header.php'; // Incluye el encabezado HTML
?>
    <!-- Contenedor principal -->
    <div class="container my-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="listado_generadores_view.php">Mis Establecimientos</a></li>
                <li class="breadcrumb-item active"><?= isset($generadorExistente['id']) ? 'Editar' : 'Nuevo' ?> Establecimiento</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-building me-2"></i><?= isset($generadorExistente['id']) ? 'Editar' : 'Registrar' ?> Establecimiento</h2>
            <a href="<?= isset($generadorExistente['id']) ? 'listado_generadores_view.php' : '../dashboard.php' ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Cancelar
            </a>
        </div>

        <!-- Tarjeta informativa -->
        <div class="card mb-4" style="background-color: #f8f4ceff;">
            <div class="card-body">
                <p class="card-text" style="text-align: justify; text-justify: inter-word;">
                    Complete la información del establecimiento donde se generan residuos peligrosos. 
                    Todos los campos marcados con <span class="text-danger">*</span> son obligatorios.
                </p>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información del Establecimiento</h5>
            </div>
            <div class="card-body">
                <?php if (isset($controller->error)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($controller->error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['mensaje_exito'])): ?>
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: '<?= addslashes($_SESSION['mensaje_exito']) ?>',
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#28a745'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'listado_generadores_view.php';
                            }
                        });
                    });
                    </script>
                    <?php unset($_SESSION['mensaje_exito']); ?>
                <?php endif; ?>

                <form method="POST">
                    <!-- Campo oculto para ID si estamos editando -->
                    <?php if (isset($generadorExistente['id'])): ?>
                        <input type="hidden" name="id_generador" value="<?= htmlspecialchars($generadorExistente['id']) ?>">
                    <?php endif; ?>

                    <!-- Sección 1: Datos del Establecimiento -->
                    <div class="mb-4">
                        <h6 class="text-muted border-bottom pb-2 mb-3">Datos del Establecimiento</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nom_generador" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nom_generador" name="nom_generador" 
                                       value="<?= htmlspecialchars($generadorExistente['nom_generador'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nit" class="form-label">NIT <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nit" name="nit" 
                                       value="<?= htmlspecialchars($generadorExistente['nit'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tipo_sujeto" class="form-label">Tipo <span class="text-danger">*</span></label>
                                <select class="form-select" id="tipo_sujeto" name="tipo_sujeto" required>
                                    <option value="">Seleccione...</option>
                                    <?php 
                                    $tiposGenerador = $controller->getTiposGenerador();
                                    $selectedValue = $_POST['tipo_sujeto'] ?? ($generadorExistente['tipo_sujeto'] ?? '');
                                    
                                    foreach ($tiposGenerador as $tipo): 
                                        $selected = ($selectedValue == $tipo['id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= htmlspecialchars($tipo['id']) ?>" <?= $selected ?>>
                                            <?= htmlspecialchars($tipo['nom_tipo']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="dir_establecimiento" class="form-label">Dirección <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="dir_establecimiento" name="dir_establecimiento" 
                                       value="<?= htmlspecialchars($generadorExistente['dir_establecimiento'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tel_establecimiento" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="tel_establecimiento" name="tel_establecimiento"
                                       value="<?= htmlspecialchars($generadorExistente['tel_establecimiento'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Sección 2: Datos del Responsable -->
                    <div class="mb-4">
                        <h6 class="text-muted border-bottom pb-2 mb-3">Datos del Responsable</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nom_responsable" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nom_responsable" name="nom_responsable" 
                                       value="<?= htmlspecialchars($generadorExistente['nom_responsable'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cargo_responsable" class="form-label">Cargo</label>
                                <input type="text" class="form-control" id="cargo_responsable" name="cargo_responsable"
                                       value="<?= htmlspecialchars($generadorExistente['cargo_responsable'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="periodo_reporte" class="form-label">Fecha de reporte <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="date" class="form-control bg-light text-muted" id="periodo_reporte" name="periodo_reporte" 
                                        value="<?= htmlspecialchars($generadorExistente['periodo_reporte'] ?? date('Y-m-d')) ?>" readonly
                                        style="cursor: not-allowed; opacity: 0.8;">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-lock text-muted"></i>
                                    </span>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="bi bi-info-circle"></i> Campo de solo lectura
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?= isset($generadorExistente['id']) ? 'listado_generadores_view.php' : '../dashboard.php' ?>" class="btn btn-outline btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Cancelar
                        </a>
                        <div>
                            <?php if (isset($generadorExistente['id'])): ?>
                                <a href="reporte_mensual_view.php?id=<?= $generadorExistente['id'] ?>" class="btn btn-outline btn-outline-success me-2">
                                    <i class="bi bi-clipboard-data me-2"></i>Ver Reportes
                                </a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-outline btn-outline-primary">
                                <i class="bi bi-check-circle me-2"></i><?= isset($generadorExistente['id']) ? 'Actualizar' : 'Guardar' ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"></script>