<?php
header('Content-Type: application/json');
require '../conexion.php'; // $conn (mysqli)

try {

    // =========================
    // Leer JSON de entrada
    // =========================
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        throw new Exception("Datos inválidos");
    }

    $idcliente = intval($data['idcliente'] ?? 0);
    $monto     = floatval($data['monto'] ?? 0);   // TOTAL DE LA VENTA
    $fiado     = floatval($data['fiado'] ?? 0);   // MONTO FIADO
    $productos = $data['productos'] ?? [];
    $tipo      = $data['tipo'] ?? '';             // SALDO | PLANILLA | DIRECTO | FIADO

    if ($idcliente <= 0 || empty($productos) || !$tipo || $monto <= 0) {
        throw new Exception("Datos incompletos");
    }

    // =========================
    // Iniciar transacción
    // =========================
    $conn->begin_transaction();

    // =========================
    // Obtener cliente
    // =========================
    $sql = "SELECT saldo, tipo, IFNULL(fiado,0) AS fiado FROM clientes WHERE idcliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idcliente);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        throw new Exception("Cliente no encontrado");
    }

    $cliente = $res->fetch_assoc();
    $saldoCliente = floatval($cliente['saldo']);
    $tipoCliente  = $cliente['tipo'];

    // =========================
    // Variables de pago
    // =========================
    $idtipoPago = 0;
    $estadoPago = 'CANCELADO';

    // =========================
    // SALDO
    // =========================
    if ($tipo === 'SALDO') {

        if ($saldoCliente < $monto) {
            throw new Exception("Saldo insuficiente, por favor recargue");
        }

        $sql = "UPDATE clientes SET saldo = saldo - ? WHERE idcliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $monto, $idcliente);
        $stmt->execute();

        $idtipoPago = 2;
    }

    // =========================
    // PLANILLA
    // =========================
    elseif ($tipo === 'PLANILLA') {

        if (stripos($tipoCliente, 'PLANILLA') === false) {
            throw new Exception("El cliente seleccionado no pertenece a planilla");
        }

        $idtipoPago = 1;
    }

    // =========================
    // DIRECTO
    // =========================
    elseif ($tipo === 'DIRECTO') {

        $idtipoPago = 3;
    }

    // =========================
    // FIADO
    // =========================
    elseif ($tipo === 'FIADO') {

        if ($fiado <= 0) {
            throw new Exception("Monto fiado inválido");
        }

        // sumar fiado al cliente
        $sql = "UPDATE clientes SET fiado = IFNULL(fiado,0) + ? WHERE idcliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $fiado, $idcliente);
        $stmt->execute();

        $idtipoPago = 4;
        $estadoPago = 'FALTANTE';
    }

    else {
        throw new Exception("Tipo de operación no válido");
    }

    // =========================
    // Generar código correlativo
    // =========================
    $sql = "SELECT IFNULL(MAX(idpago),0) + 1 AS correlativo FROM pagos";
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();

    $codigo = 'C' . str_pad($row['correlativo'], 6, '0', STR_PAD_LEFT);

    // =========================
    // Insertar ventas (una fila por producto)
    // =========================
    $sqlVenta = "
        INSERT INTO ventas (idcliente, idinventario, fecpro, codigo)
        VALUES (?, ?, NOW(), ?)
    ";
    $stmtVenta = $conn->prepare($sqlVenta);

    foreach ($productos as $idinventario) {
        $idinventario = intval($idinventario);
        $stmtVenta->bind_param("iis", $idcliente, $idinventario, $codigo);
        $stmtVenta->execute();
    }

    // =========================
    // Insertar pago (1 sola fila)
    // =========================
    $sqlPago = "
        INSERT INTO pagos (idcliente, codigo, idtipo, monto, estado, fecpro)
        VALUES (?, ?, ?, ?, ?, NOW())
    ";
    $stmtPago = $conn->prepare($sqlPago);
    $stmtPago->bind_param(
        "isids",
        $idcliente,
        $codigo,
        $idtipoPago,
        $monto,      
        $estadoPago
    );
    $stmtPago->execute();

    // =========================
    // Commit
    // =========================
    $conn->commit();

    echo json_encode([
        'ok'     => true,
        'msg'    => 'Venta registrada correctamente',
        'codigo' => $codigo
    ]);

} catch (Exception $e) {

    if ($conn->errno === 0) {
        $conn->rollback();
    }

    http_response_code(400);
    echo json_encode([
        'ok'  => false,
        'msg' => $e->getMessage()
    ]);
    exit;
}
