<?php
require_once "../conexion.php";
header('Content-Type: application/json');

$id = (int) ($_GET['id'] ?? 0);

$sql = "SELECT idinventario, descripcion, stock, precio, idcategoria
        FROM inventario
        WHERE idinventario = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        "success" => true,
        "data" => $row
    ]);
} else {
    echo json_encode([
        "success" => false
    ]);
}
