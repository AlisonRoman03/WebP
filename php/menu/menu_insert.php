<?php
require_once "../conexion.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$descripcion = trim($data['descripcion']);
$precio      = (float)$data['precio'];
$unidad      = (int)$data['unidad'];
$fecha       = $data['fecha'];
$hora        = $data['hora'];

$sql = "INSERT INTO menu
        (descripcion, unidad, precio, fecha, hora, fecpro, estado)
        VALUES (?, ?, ?, ?, ?, NOW(), 0)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sidss", $descripcion, $unidad, $precio, $fecha, $hora);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "idmenu"  => $conn->insert_id
    ]);
} else {
    echo json_encode([
        "success" => false
    ]);
}
