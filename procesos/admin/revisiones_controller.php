<?php
class RevisionesController {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Obtener todas las revisiones pendientes
    public function obtenerRevisionesPendientes() {
        $stmt = $this->conn->prepare("
            SELECT r.*, g.nom_generador, g.dir_establecimiento, g.tipo_sujeto, s.nom_tipo
            FROM revisiones_anuales r
            JOIN generador g ON r.generador_id = g.id
            JOIN tipo_generador s ON g.tipo_sujeto = s.id
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener revisión específica
    public function obtenerRevision($generador_id, $anio) {
        $stmt = $this->conn->prepare("
            SELECT r.*, g.nom_generador, g.dir_establecimiento, g.tipo_sujeto,
                   u.email as nombre_revisor
            FROM revisiones_anuales r
            JOIN generador g ON r.generador_id = g.id
            LEFT JOIN usuarios u ON r.revisado_por = u.id
            WHERE r.generador_id = ? AND r.anio = ?
        ");
        $stmt->execute([$generador_id, $anio]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Actualizar estado de revisión
    public function actualizarRevision($data) {
        $stmt = $this->conn->prepare("
            UPDATE revisiones_anuales 
            SET formulario_mensual = ?, 
                observaciones_mensual = ?,
                fecha_revision = NOW(),
                revisado_por = ?,
                estado_general = ?
            WHERE generador_id = ? AND anio = ?
        ");
        
        return $stmt->execute([
            $data['formulario_mensual'],
            $data['observaciones_mensual'],
            $data['revisado_por'],
            $data['estado_general'],
            $data['generador_id'],
            $data['anio']
        ]);
    }

     // Actualizar estado de revisión de accidentes
    public function actualizarRevisionAccidentes($data) {
        $stmt = $this->conn->prepare("
            UPDATE revisiones_anuales 
            SET formulario_accidentes = ?, 
                observaciones_accidentes = ?,
                fecha_revision = NOW(),
                revisado_por = ?,
                estado_general = ?
            WHERE generador_id = ? AND anio = ?
        ");
        
        return $stmt->execute([
            $data['formulario_accidentes'],
            $data['observaciones_accidentes'],
            $data['revisado_por'],
            $data['estado_general'],
            $data['generador_id'],
            $data['anio']
        ]);
    }
        // Actualizar estado de revisión de contingencias
    public function actualizarRevisionContingencias($data) {
        $stmt = $this->conn->prepare("
            UPDATE revisiones_anuales 
            SET formulario_contingencias = ?, 
                observaciones_contingencias = ?,
                fecha_revision = NOW(),
                revisado_por = ?,
                estado_general = ?
            WHERE generador_id = ? AND anio = ?
        ");
        
        return $stmt->execute([
            $data['formulario_contingencias'],
            $data['observaciones_contingencias'],
            $data['revisado_por'],
            $data['estado_general'],
            $data['generador_id'],
            $data['anio']
        ]);
    }
    
    // Verificar si todos los formularios están aprobados
    public function verificarFormulariosCompletos($generador_id, $anio) {
        $stmt = $this->conn->prepare("
            SELECT formulario_mensual, formulario_accidentes, formulario_contingencias
            FROM revisiones_anuales
            WHERE generador_id = ? AND anio = ?
        ");
        $stmt->execute([$generador_id, $anio]);
        $revision = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ($revision['formulario_mensual'] === 'aprobado' &&
                $revision['formulario_accidentes'] === 'aprobado' &&
                $revision['formulario_contingencias'] === 'aprobado');
    }
    // ... después de los métodos existentes ...

    // Actualizar el estado general de la revisión
    public function actualizarEstadoGeneral($generador_id, $anio, $estado_general) {
        $stmt = $this->conn->prepare("
            UPDATE revisiones_anuales 
            SET estado_general = ?,
                fecha_revision = NOW()
            WHERE generador_id = ? AND anio = ?
        ");
        
        return $stmt->execute([
            $estado_general,
            $generador_id,
            $anio
        ]);
    }
    // Obtener revisiones con filtros
    public function obtenerRevisionesConFiltros($tipo_sujeto = '', $estado_general = '') {
        $sql = "
            SELECT r.*, g.nom_generador, g.tipo_sujeto, s.nom_tipo
            FROM revisiones_anuales r
            JOIN generador g ON r.generador_id = g.id
            JOIN tipo_generador s ON g.tipo_sujeto = s.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($tipo_sujeto)) {
            $sql .= " AND g.tipo_sujeto = ?";
            $params[] = $tipo_sujeto;
        }
        
        if (!empty($estado_general)) {
            $sql .= " AND r.estado_general = ?";
            $params[] = $estado_general;
        }
        
        $sql .= " ORDER BY r.fecha_revision DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtener tipos de sujeto únicos para el filtro
    public function obtenerTiposSujeto() {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT g.tipo_sujeto, s.nom_tipo 
            FROM generador g
            JOIN tipo_generador s ON g.tipo_sujeto = s.id 
            WHERE g.tipo_sujeto IS NOT NULL 
            ORDER BY s.nom_tipo ASC
        ");
        $stmt->execute();
        
        // Devolver un array asociativo id => nombre
        $tipos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tipos[$row['tipo_sujeto']] = $row['nom_tipo'];
        }
        return $tipos;
    }

}
?>