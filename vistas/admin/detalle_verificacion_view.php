<?php
session_start();
require_once '../includes/header.php';

// Verificar que el usuario es técnico/admin
if ($_SESSION['usuario_rol'] != 'admin' && $_SESSION['usuario_rol'] != 'tecnico') {
    header("Location: listado_generadores_view.php");
    exit();
}

// Incluir el procesador que obtendrá los datos
require_once '../procesos/procesar_detalle_verificacion.php';

// Ahora las variables $generador, $reporte_mensual, $reporte_adicional, $contingencias
// están disponibles desde el procesador
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-clipboard-check"></i>
                        Verificación Detallada - <?= htmlspecialchars($generador['nom_generador']) ?> (Año: <?= $anio ?>)
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="../procesos/procesar_verificacion.php">
                        <input type="hidden" name="generador_id" value="<?= $generador_id ?>">
                        <input type="hidden" name="anio" value="<?= $anio ?>">
                        <input type="hidden" name="email_usuario" value="<?= $generador['email'] ?>">
                        <input type="hidden" name="nombre_usuario" value="<?= $generador['nom_responsable'] ?>">
                        <input type="hidden" name="nombre_generador" value="<?= $generador['nom_generador'] ?>">
                        
                        <!-- Pestañas para los tres formularios -->
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="mensual-tab" data-bs-toggle="tab" 
                                        data-bs-target="#mensual" type="button" role="tab">
                                    Reporte Mensual
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="adicional-tab" data-bs-toggle="tab" 
                                        data-bs-target="#adicional" type="button" role="tab">
                                    Información Adicional
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="contingencias-tab" data-bs-toggle="tab" 
                                        data-bs-target="#contingencias" type="button" role="tab">
                                    Plan de Contingencias
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content p-3 border border-top-0" id="myTabContent">
                            <!-- Reporte Mensual -->
                            <div class="tab-pane fade show active" id="mensual" role="tabpanel">
                                <?php if ($reporte_mensual): ?>
                                    <h5>Datos del Reporte Mensual</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Total de Residuos:</strong> <?= $reporte_mensual['total_residuos'] ?> kg</p>
                                            <p><strong>Residuos Aprovechables:</strong> <?= $reporte_mensual['residuos_aprovechables'] ?> kg</p>
                                            <p><strong>Residuos No Aprovechables:</strong> <?= $reporte_mensual['residuos_no_aprovechables'] ?> kg</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Residuos Peligrosos:</strong> <?= $reporte_mensual['residuos_peligrosos'] ?> kg</p>
                                            <p><strong>Fecha Creación:</strong> <?= date('d/m/Y', strtotime($reporte_mensual['fecha_creacion'])) ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Estado del Reporte Mensual</label>
                                        <select class="form-select" name="estado_mensual" required>
                                            <option value="aprobado" <?= ($reporte_mensual['estado'] == 'aprobado') ? 'selected' : '' ?>>Aprobado</option>
                                            <option value="rechazado" <?= ($reporte_mensual['estado'] == 'rechazado') ? 'selected' : '' ?>>Rechazado</option>
                                            <option value="pendiente" <?= ($reporte_mensual['estado'] == 'pendiente' || !$reporte_mensual['estado']) ? 'selected' : '' ?>>Pendiente</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Observaciones</label>
                                        <textarea class="form-control" name="observaciones_mensual" 
                                                  rows="3" placeholder="Observaciones sobre el reporte mensual"><?= $reporte_mensual['observaciones'] ?? '' ?></textarea>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning">No se encontró reporte mensual para este período.</div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Información Adicional -->
                            <div class="tab-pane fade" id="adicional" role="tabpanel">
                                <?php if ($reporte_adicional): ?>
                                    <h5>Datos del Reporte Adicional</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Capacitaciones Programadas:</strong> <?= $reporte_adicional['num_capacitaciones_programadas'] ?></p>
                                            <p><strong>Capacitaciones Ejecutadas:</strong> <?= $reporte_adicional['num_capacitaciones_ejecutadas'] ?></p>
                                            <p><strong>¿Hubo Accidentes?:</strong> <?= ucfirst($reporte_adicional['tiene_accidentes']) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Número de Accidentes:</strong> <?= $reporte_adicional['num_accidentes'] ?></p>
                                            <p><strong>Número de Auditorías:</strong> <?= $reporte_adicional['num_auditorias'] ?></p>
                                            <p><strong>Fecha Creación:</strong> <?= date('d/m/Y', strtotime($reporte_adicional['fecha_creacion'])) ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Estado del Reporte Adicional</label>
                                        <select class="form-select" name="estado_adicional" required>
                                            <option value="aprobado" <?= ($reporte_adicional['estado'] == 'aprobado') ? 'selected' : '' ?>>Aprobado</option>
                                            <option value="rechazado" <?= ($reporte_adicional['estado'] == 'rechazado') ? 'selected' : '' ?>>Rechazado</option>
                                            <option value="pendiente" <?= ($reporte_adicional['estado'] == 'pendiente' || !$reporte_adicional['estado']) ? 'selected' : '' ?>>Pendiente</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Observaciones</label>
                                        <textarea class="form-control" name="observaciones_adicional" 
                                                  rows="3" placeholder="Observaciones sobre el reporte adicional"><?= $reporte_adicional['observaciones'] ?? '' ?></textarea>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning">No se encontró reporte adicional para este período.</div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Plan de Contingencias -->
                            <div class="tab-pane fade" id="contingencias" role="tabpanel">
                                <?php if ($contingencias): ?>
                                    <h5>Datos del Plan de Contingencias</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Persona que Reporta:</strong> <?= htmlspecialchars($contingencias['persona_reporta']) ?></p>
                                            <p><strong>Área/Localización:</strong> <?= htmlspecialchars($contingencias['area_localizacion']) ?></p>
                                            <p><strong>Incendios:</strong> <?= $contingencias['incendios_numero'] ?> contingencias</p>
                                            <p><strong>Inundaciones:</strong> <?= $contingencias['inundaciones_numero'] ?> contingencias</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Interrupción Agua:</strong> <?= $contingencias['agua_numero'] ?> contingencias</p>
                                            <p><strong>Interrupción Energía:</strong> <?= $contingencias['energia_numero'] ?> contingencias</p>
                                            <p><strong>Fecha Creación:</strong> <?= date('d/m/Y', strtotime($contingencias['fecha_creacion'])) ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Estado del Plan de Contingencias</label>
                                        <select class="form-select" name="estado_contingencias" required>
                                            <option value="aprobado" <?= ($contingencias['estado'] == 'aprobado') ? 'selected' : '' ?>>Aprobado</option>
                                            <option value="rechazado" <?= ($contingencias['estado'] == 'rechazado') ? 'selected' : '' ?>>Rechazado</option>
                                            <option value="pendiente" <?= ($contingencias['estado'] == 'pendiente' || !$contingencias['estado']) ? 'selected' : '' ?>>Pendiente</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Observaciones</label>
                                        <textarea class="form-control" name="observaciones_contingencias" 
                                                  rows="3" placeholder="Observaciones sobre el plan de contingencias"><?= $contingencias['observaciones'] ?? '' ?></textarea>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning">No se encontró plan de contingencias para este período.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Decisión final -->
                        <div class="card mt-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">Decisión Final</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Decisión Final</label>
                                    <select class="form-select" name="decision_final" id="decision_final" required>
                                        <option value="">Seleccione una opción</option>
                                        <option value="aprobado">Aprobar Todos los Reportes</option>
                                        <option value="rechazado">Rechazar Reportes</option>
                                        <option value="parcial">Aprobación Parcial</option>
                                    </select>
                                </div>
                                <div class="mb-3" id="razon_rechazo_container" style="display: none;">
                                    <label class="form-label">Razón detallada del rechazo</label>
                                    <textarea class="form-control" name="razon_rechazo" 
                                              rows="3" placeholder="Detalle las razones del rechazo"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="verificacion_reportes_view.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Guardar Verificación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('decision_final').addEventListener('change', function() {
        document.getElementById('razon_rechazo_container').style.display = 
            this.value === 'rechazado' ? 'block' : 'none';
    });
</script>

<?php include '../includes/footer.php'; ?>