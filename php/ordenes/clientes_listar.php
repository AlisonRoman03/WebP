<?php
require_once "../conexion.php";

$sql = "SELECT idcliente, nombre FROM clientes WHERE estado = 0 AND tipo = 'PLANILLA' ORDER BY nombre";
$result = $conn->query($sql);

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        "id"   => $row["idcliente"],
        "text" => $row["nombre"]
    ];
}

echo json_encode($data);
