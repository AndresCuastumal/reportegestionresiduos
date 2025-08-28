<?php
session_start();
echo "Test de redirección<br>";
echo "Session ID: " . session_id() . "<br>";
echo "Generador ID: " . ($_GET['id'] ?? 'NO_ID') . "<br>";

// Simular procesamiento exitoso
$_SESSION['generador_id_reportando'] = $_GET['id'];
$_SESSION['anio_reportando'] = 2023;

echo "Redirigiendo en 3 segundos...";
header("Refresh: 3; URL=../vistas/reporte_adicional_view.php?id=" . $_GET['id']);
?>