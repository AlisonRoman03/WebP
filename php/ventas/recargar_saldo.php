<?php
include "../conexion.php"; 

$idcliente = $_POST['idcliente'] ?? 0;
$monto     = $_POST['monto'] ?? 0;

if ($idcliente <= 0 || $monto <= 0) {
    echo json_encode([
        'ok' => false,
        'msg' => 'Datos inválidos'
    ]);
    exit;
}

// Actualizar saldo (suma)
$sql = "UPDATE clientes 
        SET saldo = saldo + ? 
        WHERE idcliente = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("di", $monto, $idcliente);

if ($stmt->execute()) {
    // Obtener nuevo saldo
    $sqlSaldo = "SELECT saldo FROM clientes WHERE idcliente = ?";
    $stmtSaldo = $conn->prepare($sqlSaldo);
    $stmtSaldo->bind_param("i", $idcliente);
    $stmtSaldo->execute();
    $result = $stmtSaldo->get_result();
    $row = $result->fetch_assoc();

    echo json_encode([
        'ok' => true,
        'saldo' => $row['saldo']
    ]);
} else {
    echo json_encode([
        'ok' => false,
        'msg' => 'Error al actualizar saldo'
    ]);
}
