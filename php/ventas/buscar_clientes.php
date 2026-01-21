<?php
include "../conexion.php"; 

$q = $_GET['q'] ?? '';

$sql = "SELECT idcliente, nombre, saldo
        FROM clientes
        WHERE nombre LIKE ?
        ORDER BY nombre
        LIMIT 10";

$stmt = $conn->prepare($sql);
$like = "%".$q."%";
$stmt->bind_param("s", $like);
$stmt->execute();

$result = $stmt->get_result();

$clientes = [];

while ($row = $result->fetch_assoc()) {
    $clientes[] = [
        'idcliente' => $row['idcliente'],
        'nombre' => $row['nombre'],
        'saldo'  => $row['saldo']
    ];
}

header('Content-Type: application/json');
echo json_encode($clientes);