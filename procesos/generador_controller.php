<?php
session_start();
require_once '../includes/conexion.php';

class GeneradorController {
    private $conn;
    public $error;
    public $success;

    public function getTiposGenerador() {
        try {
            $stmt = $this->conn->query("SELECT id, nom_tipo FROM tipo_generador ORDER BY nom_tipo");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = "Error al cargar tipos de generador: " . $e->getMessage();
            return [];
        }
    }

    public function obtenerGeneradorPorId($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM GENERADOR WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = "Error al obtener generador: " . $e->getMessage();
            return [];
        }
    }

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function checkAccess() {
        if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin', 'generador'])) {
            header("Location: acceso_denegado.php");
            exit();
        }
    }

    public function handleRequest() {
        $this->checkAccess();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        }
    }

    private function processForm() {
    try {
        // Verificar si estamos actualizando un generador existente
        if (isset($_POST['id_generador']) && is_numeric($_POST['id_generador'])) {
            // Actualizar generador existente
            $stmt = $this->conn->prepare("UPDATE GENERADOR SET
                periodo_reporte = ?, 
                nom_generador = ?, 
                tipo_sujeto = ?, 
                dir_establecimiento = ?, 
                tel_establecimiento = ?, 
                nom_responsable = ?, 
                cargo_responsable = ?
                WHERE id = ?");
            
            $stmt->execute([
                $_POST['periodo_reporte'],
                $_POST['nom_generador'],
                $_POST['tipo_sujeto'],
                $_POST['dir_establecimiento'],
                $_POST['tel_establecimiento'],
                $_POST['nom_responsable'],
                $_POST['cargo_responsable'],
                $_POST['id_generador']
            ]);

            $_SESSION['mensaje_exito'] = "Generador actualizado exitosamente!";
        } else {
            // Crear nuevo generador
            $stmt = $this->conn->prepare("INSERT INTO GENERADOR (
                periodo_reporte, 
                nom_generador, 
                tipo_sujeto, 
                dir_establecimiento, 
                tel_establecimiento, 
                nom_responsable, 
                cargo_responsable
            ) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $_POST['periodo_reporte'],
                $_POST['nom_generador'],
                $_POST['tipo_sujeto'],
                $_POST['dir_establecimiento'],
                $_POST['tel_establecimiento'],
                $_POST['nom_responsable'],
                $_POST['cargo_responsable']
            ]);

            $id_generador = $this->conn->lastInsertId();

            // === CAMBIO IMPORTANTE AQUÍ ===
            // Asociar el generador al usuario en la NUEVA tabla de relación
            if ($_SESSION['usuario_rol'] === 'generador') {
                $stmt = $this->conn->prepare("INSERT INTO usuario_generador (usuario_id, generador_id) VALUES (?, ?)");
                $stmt->execute([$_SESSION['usuario_id'], $id_generador]);
            }
            // =============================

            $_SESSION['mensaje_exito'] = "Generador registrado exitosamente!";
        }

        header("Location: listado_generadores_view.php");
        exit();

    } catch (PDOException $e) {
        $this->error = "Error al procesar el formulario: " . $e->getMessage();
    }
}
}

// Uso del controlador
$controller = new GeneradorController($conn);
$controller->handleRequest();
?>