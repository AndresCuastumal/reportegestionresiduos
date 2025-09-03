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
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        Plan de Contingencias - <?= htmlspecialchars($generador['nom_generador']) ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" action="../../procesos/generador/procesar_contingencias.php">
                        <input type="hidden" name="generador_id" value="<?= $generador_id ?>">
                        <input type="hidden" name="anio" value="<?= $anio_actual ?>">
                        
                        <!-- SECCIÓN CONTINGENCIAS -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 text-primary">
                                <i class="bi bi-exclamation-octagon"></i> Registro de Contingencias
                            </h5>
                            
                            <!-- Incendios -->
                            <div class="contingencia-item mb-4 p-3 border rounded">
                                <h6 class="text-danger">
                                    <i class="bi bi-fire"></i> Incendios en las áreas de almacenamiento de residuos
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Número de contingencias</label>
                                            <input type="number" class="form-control" 
                                                   name="incendios_numero" 
                                                   min="0" value="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Acciones implementadas</label>
                                            <div class="border p-3 rounded">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="incendios_acciones[]" 
                                                           value="instalacion_extintor" id="incendio1">
                                                    <label class="form-check-label" for="incendio1">
                                                        Instalación de extintor, detector de humo, aspersor u otro sistema similar exclusivo para el area
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="incendios_acciones[]" 
                                                           value="redisenio_area" id="incendio2">
                                                    <label class="form-check-label" for="incendio2">
                                                        Rediseño/reubicación del area
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="incendios_acciones[]" 
                                                           value="verificacion_origen" id="incendio3">
                                                    <label class="form-check-label" for="incendio3">
                                                        Verificación de origen del fuego (instalaciones electricas, reactivos, etc)
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="incendios_acciones[]" 
                                                           value="otro" id="incendio_otro">
                                                    <label class="form-check-label" for="incendio_otro">
                                                        Otro (especifique cual)
                                                    </label>
                                                </div>
                                                <div class="mt-2" id="incendio_otro_container" style="display: none;">
                                                    <input type="text" class="form-control" 
                                                           name="incendios_otra_accion" 
                                                           placeholder="Especifique la acción">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Inundaciones -->
                            <div class="contingencia-item mb-4 p-3 border rounded">
                                <h6 class="text-primary">
                                    <i class="bi bi-water"></i> Inundación en las áreas de almacenamiento de residuos
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Número de contingencias</label>
                                            <input type="number" class="form-control" 
                                                   name="inundaciones_numero" 
                                                   min="0" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Acciones implementadas</label>
                                            <textarea class="form-control" 
                                                      name="inundaciones_acciones" 
                                                      rows="2" placeholder="Describa las acciones tomadas"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Interrupción suministro de agua -->
                            <div class="contingencia-item mb-4 p-3 border rounded">
                                <h6 class="text-info">
                                    <i class="bi bi-droplet"></i> Interrupción del suministro de agua para  las actividades de limpieza y desinfección
                                    dentro del marco de la gestión de interna de residuos.
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Número de contingencias</label>
                                            <input type="number" class="form-control" 
                                                   name="agua_numero" 
                                                   min="0" value="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Acciones implementadas</label>
                                            <div class="border p-3 rounded">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="agua_acciones[]" 
                                                           value="tanque_abastecimiento" id="agua1">
                                                    <label class="form-check-label" for="agua1">
                                                        Instalación o aumento de capacidad del tanque de abastacimiento
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="agua_acciones[]" 
                                                           value="sistema_alternativo" id="agua2">
                                                    <label class="form-check-label" for="agua2">
                                                        Implementación de sistema de suministro alternativo
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="agua_acciones[]" 
                                                           value="limpieza_seco" id="agua3">
                                                    <label class="form-check-label" for="agua3">
                                                        Implementación de sistemas de limpieza y/o desinfección en seco
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="agua_acciones[]" 
                                                           value="otro" id="agua_otro">
                                                    <label class="form-check-label" for="agua_otro">
                                                        Otro (especifique cual)
                                                    </label>
                                                </div>
                                                <div class="mt-2" id="agua_otro_container" style="display: none;">
                                                    <input type="text" class="form-control" 
                                                           name="agua_otra_accion" 
                                                           placeholder="Especifique la acción">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Interrupción suministro de energía -->
                            <div class="contingencia-item mb-4 p-3 border rounded">
                                <h6 class="text-warning">
                                    <i class="bi bi-lightning-charge"></i> Interrupción del suministro de energía en las unidades de almacenamiento
                                    de residuos y sistemas de refrigeración.
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Número de contingencias</label>
                                            <input type="number" class="form-control" 
                                                   name="energia_numero" 
                                                   min="0" value="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Acciones implementadas</label>
                                            <div class="border p-3 rounded">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="energia_acciones[]" 
                                                           value="planta_electrica" id="energia1">
                                                    <label class="form-check-label" for="energia1">
                                                        Instalación de planta electrica o sistema de suministro alternativo
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="energia_acciones[]" 
                                                           value="otro" id="energia_otro">
                                                    <label class="form-check-label" for="energia_otro">
                                                        Otro (especifique cual)
                                                    </label>
                                                </div>
                                                <div class="mt-2" id="energia_otro_container" style="display: none;">
                                                    <input type="text" class="form-control" 
                                                           name="energia_otra_accion" 
                                                           placeholder="Especifique la acción">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Derrames -->
                            <div class="contingencia-item mb-4 p-3 border rounded">
                                <h6 class="text-danger">
                                    <i class="bi bi-exclamation-triangle"></i> Derrame de residuos con características corrosivas, reactivas, explosivas, tóxicas,
                                    inflamables y con riesgo biológico/infeccioso según lo evidenciado en el diagnóstico.
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Número de contingencias</label>
                                            <input type="number" class="form-control" 
                                                   name="derrames_numero" 
                                                   min="0" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Tipo de residuo derramado</label>
                                            <select class="form-select" name="derrames_tipo">
                                                <option value="">Seleccione el tipo</option>
                                                <option value="corrosivo">Corrosivo</option>
                                                <option value="reactivo">Reactivo</option>
                                                <option value="explosivo">Explosivo</option>
                                                <option value="toxico">Tóxico</option>
                                                <option value="inflamable">Inflamable</option>
                                                <option value="biologico">Biológico/Infeccioso</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Acciones implementadas</label>
                                            <div class="border p-3 rounded">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="derrames_acciones[]" 
                                                           value="kit_derrame" id="derrame1">
                                                    <label class="form-check-label" for="derrame1">
                                                        Utilización del kit de derrame
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="derrames_acciones[]" 
                                                           value="limpieza_manual" id="derrame2">
                                                    <label class="form-check-label" for="derrame2">
                                                        Limpieza manual
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="derrames_acciones[]" 
                                                           value="apoyo_tercero" id="derrame3">
                                                    <label class="form-check-label" for="derrame3">
                                                        Solicitud de apoyo de un tercero
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="derrames_acciones[]" 
                                                           value="otro" id="derrame_otro">
                                                    <label class="form-check-label" for="derrame_otro">
                                                        Otro (especifique cual)
                                                    </label>
                                                </div>
                                                <div class="mt-2" id="derrame_otro_container" style="display: none;">
                                                    <input type="text" class="form-control" 
                                                           name="derrames_otra_accion" 
                                                           placeholder="Especifique la acción">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Interrupción recolección -->
                            <div class="contingencia-item mb-4 p-3 border rounded">
                                <h6 class="text-secondary">
                                    <i class="bi bi-truck"></i> Interrupción temporal del servicio de recolección
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Número de contingencias</label>
                                            <input type="number" class="form-control" 
                                                   name="recoleccion_numero" 
                                                   min="0" value="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Acciones implementadas</label>
                                            <div class="border p-3 rounded">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="recoleccion_acciones[]" 
                                                           value="gestor_alternativo" id="recoleccion1">
                                                    <label class="form-check-label" for="recoleccion1">
                                                        Contratación de un gestor alternativo
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="recoleccion_acciones[]" 
                                                           value="ampliacion_almacenamiento" id="recoleccion2">
                                                    <label class="form-check-label" for="recoleccion2">
                                                        Ampliación del area de almacenamiento
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="recoleccion_acciones[]" 
                                                           value="otro" id="recoleccion_otro">
                                                    <label class="form-check-label" for="recoleccion_otro">
                                                        Otro (especifique cual)
                                                    </label>
                                                </div>
                                                <div class="mt-2" id="recoleccion_otro_container" style="display: none;">
                                                    <input type="text" class="form-control" 
                                                           name="recoleccion_otra_accion" 
                                                           placeholder="Especifique la acción">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Alteración condiciones operativas -->
                            <div class="contingencia-item mb-4 p-3 border rounded">
                                <h6 class="text-success">
                                    <i class="bi bi-gear"></i> Alteración de condiciones operativas que incrementen la generación de residuos.
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Número de contingencias</label>
                                            <input type="number" class="form-control" 
                                                   name="operativas_numero" 
                                                   min="0" value="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Acciones implementadas</label>
                                            <div class="border p-3 rounded">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="operativas_acciones[]" 
                                                           value="gestion_personal" id="operativa1">
                                                    <label class="form-check-label" for="operativa1">
                                                        Gestión de personal externo
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="operativas_acciones[]" 
                                                           value="ampliacion_areas" id="operativa2">
                                                    <label class="form-check-label" for="operativa2">
                                                        Ampliación de las areas de almacenamiento
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="operativas_acciones[]" 
                                                           value="otro" id="operativa_otro">
                                                    <label class="form-check-label" for="operativa_otro">
                                                        Otro (especifique cual)
                                                    </label>
                                                </div>
                                                <div class="mt-2" id="operativa_otro_container" style="display: none;">
                                                    <input type="text" class="form-control" 
                                                           name="operativas_otra_accion" 
                                                           placeholder="Especifique la acción">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>         
                        <div class="alert alert-info mt-4">
                            <h6><i class="bi bi-info-circle"></i> Información:</h6>
                            <ul class="mb-0">
                                <li>Complete la información de todas las contingencias presentadas durante el período</li>
                                <li>Si no se presentó ninguna contingencia, deje el valor 0 en el número de contingencias</li>                                
                            </ul>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="reporte_adicional_view.php?id=<?= $generador_id ?>" 
                               class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-check-circle"></i> Guardar Contingencias
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Funcionalidad para mostrar campos de texto cuando se selecciona "Otro"
    document.addEventListener('DOMContentLoaded', function() {
        // Incendios
        document.getElementById('incendio_otro').addEventListener('change', function() {
            document.getElementById('incendio_otro_container').style.display = 
                this.checked ? 'block' : 'none';
        });
        
        // Agua
        document.getElementById('agua_otro').addEventListener('change', function() {
            document.getElementById('agua_otro_container').style.display = 
                this.checked ? 'block' : 'none';
        });
        
        // Energía
        document.getElementById('energia_otro').addEventListener('change', function() {
            document.getElementById('energia_otro_container').style.display = 
                this.checked ? 'block' : 'none';
        });
        
        // Derrames
        document.getElementById('derrame_otro').addEventListener('change', function() {
            document.getElementById('derrame_otro_container').style.display = 
                this.checked ? 'block' : 'none';
        });
        
        // Recolección
        document.getElementById('recoleccion_otro').addEventListener('change', function() {
            document.getElementById('recoleccion_otro_container').style.display = 
                this.checked ? 'block' : 'none';
        });
        
        // Operativas
        document.getElementById('operativa_otro').addEventListener('change', function() {
            document.getElementById('operativa_otro_container').style.display = 
                this.checked ? 'block' : 'none';
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>