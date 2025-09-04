<?php
class ReporteContingenciasController {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Obtener datos del formulario de contingencias
    public function obtenerDatosContingencias($generador_id, $anio) {
        $stmt = $this->conn->prepare("
            SELECT c.*, u.email as nombre_persona_reporta
            FROM contingencias c
            LEFT JOIN usuarios u ON c.persona_reporta = u.id
            WHERE c.generador_id = ? AND c.anio = ?
        ");
        $stmt->execute([$generador_id, $anio]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Verificar si existe registro para este año
    public function existeRegistro($generador_id, $anio) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) FROM contingencias 
            WHERE generador_id = ? AND anio = ?
        ");
        $stmt->execute([$generador_id, $anio]);
        return $stmt->fetchColumn() > 0;
    }
    
    // Obtener acciones como array desde JSON
    public function obtenerAccionesJSON($jsonData) {
        if (empty($jsonData)) {
            return [];
        }
        
        $acciones = json_decode($jsonData, true);
        return is_array($acciones) ? $acciones : [];
    }
    
    // Lista de acciones posibles para incendios
    public function obtenerAccionesIncendios() {
        return [
            'activacion_protocolo' => 'Activación del protocolo de emergencia',
            'evacuacion' => 'Procedimiento de evacuación',
            'uso_extintores' => 'Uso de extintores',
            'llamada_bomberos' => 'Llamada a bomberos',
            'otra' => 'Otra acción'
        ];
    }
    
    // Lista de acciones posibles para agua
    public function obtenerAccionesAgua() {
        return [
            'reserva_agua' => 'Uso de reserva de agua',
            'racionamiento' => 'Racionamiento del agua',
            'busqueda_fuente' => 'Búsqueda de fuente alternativa',
            'reparacion' => 'Reparación del sistema',
            'otra' => 'Otra acción'
        ];
    }
    
    // Lista de acciones posibles para energía
    public function obtenerAccionesEnergia() {
        return [
            'generador' => 'Uso de generador eléctrico',
            'racionamiento_energia' => 'Racionamiento de energía',
            'reparacion_electrica' => 'Reparación del sistema eléctrico',
            'protocolo_ahorro' => 'Protocolo de ahorro energético',
            'otra' => 'Otra acción'
        ];
    }
    
    // Lista de acciones posibles para derrames
    public function obtenerAccionesDerrames() {
        return [
            'contencion' => 'Contención del derrame',
            'limpieza' => 'Limpieza y recolección',
            'neutralizacion' => 'Neutralización del material',
            'evacuacion_area' => 'Evacuación del área',
            'reporte_autoridad' => 'Reporte a autoridad ambiental',
            'otra' => 'Otra acción'
        ];
    }
    
    // Lista de acciones posibles para recolección
    public function obtenerAccionesRecoleccion() {
        return [
            'almacenamiento_temporal' => 'Almacenamiento temporal',
            'busqueda_recolector' => 'Búsqueda de recolector alternativo',
            'negociacion_urgencia' => 'Negociación de urgencia',
            'transporte_propio' => 'Transporte propio',
            'otra' => 'Otra acción'
        ];
    }
    
    // Lista de acciones posibles para operativas
    public function obtenerAccionesOperativas() {
        return [
            'protocolo_contingencia' => 'Activación protocolo de contingencia',
            'reprogramacion' => 'Reprogramación de actividades',
            'personal_adicional' => 'Contratación de personal adicional',
            'equipos_alternativos' => 'Uso de equipos alternativos',
            'otra' => 'Otra acción'
        ];
    }
    
    // Tipos de derrames
    public function obtenerTiposDerrames() {
        return [
            'quimico' => 'Químico',
            'petroleo' => 'Petróleo o derivados',
            'biologico' => 'Biológico',
            'otros' => 'Otros'
        ];
    }
}
?>