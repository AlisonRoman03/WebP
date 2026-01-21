<?php
require_once "../conexion.php";
header('Content-Type: application/json');

// 1. Intentar leer JSON
$data = json_decode(file_get_contents("php://input"), true);

// 2. Fallback a POST o GET
$idorden = $data['idorden']
        ?? $_POST['idorden']
        ?? $_GET['idorden']
        ?? null;

if (!$idorden || !is_numeric($idorden)) {
    echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
    exit;
}

$conn->begin_transaction();

try {

    // Obtener datos de la orden
    $stmt = $conn->prepare("
        SELECT o.idmenu, o.cantidad, o.cliente, m.precio
        FROM ordenes o
        LEFT JOIN menu m ON m.idmenu = o.idmenu
        WHERE o.idorden = ? AND o.estado = 0
    ");
    $stmt->bind_param("i", $idorden);
    $stmt->execute();
    $orden = $stmt->get_result()->fetch_assoc();

    if (!$orden) {
        throw new Exception("Orden no encontrada");
    }

    // Eliminación lógica
    $stmtDel = $conn->prepare("
        UPDATE ordenes
        SET estado = 1
        WHERE idorden = ?
    ");
    $stmtDel->bind_param("i", $idorden);
    $stmtDel->execute();

    // Revertir cliente si aplica
    if (!empty($orden['idcliente'])) {

        $cantidad = (int)$orden['cantidad'];
        $precio   = (float)$orden['precio'];
        $ganancia = $cantidad * $precio;

        $stmtUpd = $conn->prepare("
            UPDATE clientes
            SET pedidos = pedidos - ?,
                ganancia = ganancia - ?
            WHERE idcliente = ?
        ");
        $stmtUpd->bind_param(
            "idi",
            $cantidad,
            $ganancia,
            $orden['idcliente']
        );
        $stmtUpd->execute();
    }

    $conn->commit();
    echo json_encode(['ok' => true]);

} catch (Exception $e) {

    $conn->rollback();
    echo json_encode([
        'ok' => false,
        'msg' => $e->getMessage()   // 👈 VER ERROR REAL
    ]);
}

