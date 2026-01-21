<?php
require_once "../conexion.php";

header('Content-Type: application/json');

$idmenu = $_GET['idmenu'] ?? null;

if (!$idmenu) {
    echo json_encode([]);
    exit;
}

$sql = "
    SELECT idorden, cliente, cantidad, hora
    FROM ordenes
    WHERE idmenu = ?
      AND estado = 0
    ORDER BY hora
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idmenu);
$stmt->execute();

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
