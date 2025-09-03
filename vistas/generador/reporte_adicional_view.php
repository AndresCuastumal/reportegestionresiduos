<?php
session_start();
require_once '../../includes/header.php';

// Verificar que venga del formulario anterior
if (!isset($_SESSION['generador_id_reportando']) || $_SESSION['generador_id_reportando'] != $_GET['id']) {
    header("Location: listado_generadores_view.php");
    exit();
}

$generador_id = $_GET['id'];
$anio_actual = $_SESSION['anio_reportando'];

// Obtener datos del generador
require_once '../../includes/conexion.php';
$stmt = $conn->prepare("SELECT * FROM generador WHERE id = ?");
$stmt->execute([$generador_id]);
$generador = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-clipboard-check"></i>
                        Información sobre capacitaciones, accidentes laborales y auditorías - <?= htmlspecialchars($generador['nom_generador']) ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" action="../../procesos/generador/procesar_reporte_adicional.php">
                        <input type="hidden" name="anio" value="<?= $anio_actual ?>">
                        
                        <!-- SECCIÓN CAPACITACIONES -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 text-primary">
                                <i class="bi bi-mortarboard"></i> Capacitaciones
                            </h5>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            Número de capacitaciones programadas sobre manejo de residuos
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control" 
                                               name="num_capacitaciones_programadas" 
                                               min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            Cronograma de capacitaciones (PDF)
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" class="form-control" 
                                               name="archivo_cronograma" 
                                               accept=".pdf" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            Número de capacitaciones ejecutadas sobre manejo de residuos
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control" 
                                               name="num_capacitaciones_ejecutadas" 
                                               min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            Soportes de capacitaciones (PDF)
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" class="form-control" 
                                               name="archivo_soportes_capacitaciones" 
                                               accept=".pdf" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- SECCIÓN ACCIDENTES -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 text-primary">
                                <i class="bi bi-exclamation-triangle"></i> Accidentes Laborales
                            </h5>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            ¿Se han presentado accidentes ocurridos por manejo de residuos?
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" name="tiene_accidentes" id="tiene_accidentes" required>
                                            <option value="no">No</option>
                                            <option value="si">Sí</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3" id="numero_accidentes_container" style="display: none;">
                                        <label class="form-label">
                                            Número de accidentes ocurridos por manejo de residuos
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control" 
                                               name="num_accidentes" 
                                               min="0" value="0">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">
                                    Acciones preventivas y/o correctivas sobre accidentes ocurridos por manejo de residuos
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="border p-3 rounded">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                               name="acciones_preventivas[]" 
                                               value="remision_salud" id="accion1">
                                        <label class="form-check-label" for="accion1">
                                            Remisión a servicios de salud
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                               name="acciones_preventivas[]" 
                                               value="capacitacion_primeros_auxilios" id="accion2">
                                        <label class="form-check-label" for="accion2">
                                            Capacitación en primeros auxilios
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                               name="acciones_preventivas[]" 
                                               value="investigacion_accidente" id="accion3">
                                        <label class="form-check-label" for="accion3">
                                            Investigación del accidente
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                               name="acciones_preventivas[]" 
                                               value="actualizacion_procedimientos" id="accion4">
                                        <label class="form-check-label" for="accion4">
                                            Actualización de procedimientos
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                               name="acciones_preventivas[]" 
                                               value="otra" id="accion_otra">
                                        <label class="form-check-label" for="accion_otra">
                                            Otra
                                        </label>
                                    </div>
                                    <div class="mt-2" id="otra_accion_container" style="display: none;">
                                        <input type="text" class="form-control" 
                                               name="otra_accion_preventiva" 
                                               placeholder="Especifique cuál">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- SECCIÓN AUDITORIAS -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 text-primary">
                                <i class="bi bi-search"></i> Auditorías Internas
                            </h5>
                            <p class="text-muted mb-3">
                                Recuerde que las auditorías internas son obligatorias según la normatividad vigente.
                                Asegúrese de haber realizado al menos una auditoría interna sobre la gestión de residuos
                                durante el año <?= $anio_actual ?>.
                            </p>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            Número de auditorías internas realizadas sobre el manejo de residuos sólidos
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control" 
                                               name="num_auditorias" 
                                               min="0" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            Resultados de las auditorías (PDF)
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" class="form-control" 
                                               name="archivo_resultados_auditorias" 
                                               accept=".pdf" required>
                                        <div class="form-text">Acta(s) de auditorías realizadas</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            Acciones correctivas y de mejoramiento (PDF)
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" class="form-control" 
                                               name="archivo_plan_mejoramiento" 
                                               accept=".pdf" required>
                                        <div class="form-text">Plan de mejoramiento para el año evaluado</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- SECCIÓN SOPORTE ALERTAS --> 
                        <div class="alert alert-warning mt-4">
                            <h6><i class="bi bi-exclamation-triangle"></i> Importante:</h6>
                            <ul class="mb-0">
                                <li>El PDF de <strong>Soportes de capacitaciones</strong> deben ser relacionados únicamente 
                                con la temática de manejo de residuos</li>
                                <li>Los archivos de auditoría deben corresponder a las realizadas durante el año <?= $anio_actual ?></li>
                                <li>El estado de su reporte cambiará a "Pendiente de revisión"</li>
                                <li>Recibirá una notificación cuando sea aprobado o rechazado</li>
                            </ul>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="reporte_mensual_view.php?id=<?= $generador_id ?>" 
                               class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Guardar reporte y continuar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('tiene_accidentes').addEventListener('change', function() {
        document.getElementById('numero_accidentes_container').style.display = 
            this.value === 'si' ? 'block' : 'none';
    });
    
    document.getElementById('accion_otra').addEventListener('change', function() {
        document.getElementById('otra_accion_container').style.display = 
            this.checked ? 'block' : 'none';
    });
</script>

<?php include '../../includes/footer.php'; ?>