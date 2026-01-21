<?php
require_once "../conexion.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$id = (int) ($data['id'] ?? 0);

$sql = "UPDATE inventario
        SET estado = 1, fecmod = NOW()
        WHERE idinventario = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

echo json_encode([
    "success" => $stmt->execute()
]);
