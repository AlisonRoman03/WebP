<?php
require_once "../conexion.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$id          = (int) $data['idInventario'];
$descripcion = trim($data['descripcion']);
$stock       = (int) $data['stock'];
$precio      = (float) $data['precio'];
$idcategoria = (int) $data['categoria'];

$sql = "UPDATE inventario
        SET descripcion = ?, 
            stock = ?, 
            precio = ?, 
            idcategoria = ?, 
            fecmod = NOW()
        WHERE idinventario = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sidii", $descripcion, $stock, $precio, $idcategoria, $id);

echo json_encode([
    "success" => $stmt->execute()
]);
