<?php
session_start();
require_once "../conexion.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$descripcion = $data['descripcion'] ?? '';
$stock       = $data['stock'] ?? 0;
$precio      = $data['precio'] ?? 0;
$idcategoria = $data['categoria'] ?? 0;

if ($descripcion === '' || $idcategoria == 0) {
    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos"
    ]);
    exit;
}

$sql = "INSERT INTO inventario (descripcion, stock, precio, idcategoria, fecpro)
        VALUES (?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sidi", $descripcion, $stock, $precio, $idcategoria);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Error al insertar"
    ]);
}
