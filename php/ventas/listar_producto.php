<?php
require_once "../conexion.php";

header('Content-Type: application/json');

$sql = "SELECT idinventario, descripcion, precio FROM inventario";
$result = $conn->query($sql);

$productos = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
}

$conn->close();

// Devuelve JSON
header('Content-Type: application/json');
echo json_encode($productos);
?>
