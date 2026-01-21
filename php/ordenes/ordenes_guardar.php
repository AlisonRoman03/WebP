<?php
require_once "../conexion.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(['ok' => false, 'msg' => 'JSON inválido']);
    exit;
}

$idorden   = $data['idorden'] ?? null;   // NUEVO (para editar)
$idmenu    = $data['idmenu'] ?? null;
$cliente   = trim($data['cliente'] ?? '');
$hora      = $data['hora'] ?? '';
$cantidad  = (int)($data['cantidad'] ?? 0);
$idcliente = $data['idcliente'] ?? null;

if (!$idmenu || !$cliente || !$hora || $cantidad <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Datos incompletos']);
    exit;
}

$conn->begin_transaction();

try {

    /* ==========================
       INSERT O UPDATE ORDEN
       ========================== */

    if ($idorden) {

        // UPDATE
        $stmt = $conn->prepare("
            UPDATE ordenes
            SET cliente = ?, cantidad = ?, hora = ?
            WHERE idorden = ?
        ");
        $stmt->bind_param("sisi", $cliente, $cantidad, $hora, $idorden);
        $stmt->execute();

    } else {

        // INSERT
        $fecpro = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("
            INSERT INTO ordenes
            (idmenu, cliente, cantidad, hora, fecpro, estado)
            VALUES (?, ?, ?, ?, ?, 0)
        ");
        $stmt->bind_param("isiss", $idmenu, $cliente, $cantidad, $hora, $fecpro);
        $stmt->execute();

        $idorden = $conn->insert_id;
    }

    /* ==========================
       ACTUALIZAR CLIENTE (SI EXISTE)
       ========================== */

    if (!empty($idcliente)) {

        // Obtener precio del menú
        $stmtPrecio = $conn->prepare("
            SELECT precio 
            FROM menu 
            WHERE idmenu = ?
        ");
        $stmtPrecio->bind_param("i", $idmenu);
        $stmtPrecio->execute();
        $precio = $stmtPrecio->get_result()->fetch_assoc()['precio'] ?? 0;

        $ganancia = $precio * $cantidad;

        $stmtUpd = $conn->prepare("
            UPDATE clientes
            SET pedidos = pedidos + ?,
                ganancia = ganancia + ?
            WHERE idcliente = ?
        ");
        $stmtUpd->bind_param("idi", $cantidad, $ganancia, $idcliente);
        $stmtUpd->execute();
    }

    $conn->commit();

    echo json_encode([
        'ok' => true,
        'idorden' => $idorden
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        'ok' => false,
        'msg' => 'Error al guardar la orden'
    ]);
}
