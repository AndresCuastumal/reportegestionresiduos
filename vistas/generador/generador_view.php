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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($generadorExistente['id']) ? 'Editar' : 'Nuevo' ?> Generador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="">
                <i class="fas fa-biohazard me-2"></i>Reporte de Residuos
            </a>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container my-5">
        <div class="form-container bg-white">
            <h2 class="mb-4"><i class="fas fa-hospital me-2"></i><?= isset($generadorExistente['id']) ? 'Editar' : 'Registrar' ?> Establecimiento</h2>
            
            <?php if (isset($controller->error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($controller->error); ?></div>
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
                            window.location.href = 'dashboard.php';
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
                    <h5 class="border-bottom pb-2">Datos del Establecimiento</h5>
                    <div class="row mt-3">
                        <div class="col-md-6 mb-3">
                            <label for="nom_generador" class="form-label required-field">Nombre</label>
                            <input type="text" class="form-control" id="nom_generador" name="nom_generador" 
                                   value="<?= htmlspecialchars($generadorExistente['nom_generador'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nit" class="form-label required-field">NIT</label>
                            <input type="text" class="form-control" id="nit" name="nit" 
                                   value="<?= htmlspecialchars($generadorExistente['nit'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tipo_sujeto" class="form-label required-field">Tipo</label>
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
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="dir_establecimiento" class="form-label required-field">Dirección</label>
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
                    <h5 class="border-bottom pb-2">Datos del Responsable</h5>
                    <div class="row mt-3">
                        <div class="col-md-6 mb-3">
                            <label for="nom_responsable" class="form-label required-field">Nombre Completo</label>
                            <input type="text" class="form-control" id="nom_responsable" name="nom_responsable" 
                                   value="<?= htmlspecialchars($generadorExistente['nom_responsable'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cargo_responsable" class="form-label">Cargo</label>
                            <input type="text" class="form-control" id="cargo_responsable" name="cargo_responsable"
                                   value="<?= htmlspecialchars($generadorExistente['cargo_responsable'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="periodo_reporte" class="form-label required-field">Periodo de Reporte Inicial</label>
                            <input type="date" class="form-control" id="periodo_reporte" name="periodo_reporte" 
                                   value="<?= htmlspecialchars($generadorExistente['periodo_reporte'] ?? date('Y-m-d')) ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="<?= isset($generadorExistente['id']) ? 'listado_generadores_view.php' : '../dashboard.php' ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i><?= isset($generadorExistente['id']) ? 'Actualizar' : 'Guardar' ?> Generador
                    </button>
                    
                    <?php if (isset($generadorExistente['id'])): ?>
                        <a href="reporte_mensual.php?id=<?= $generadorExistente['id'] ?>" class="btn btn-success">
                            <i class="fas fa-clipboard-list me-2"></i>Ver Reportes
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
<?php
include '../../includes/footer.php'; // Incluye el pie de página