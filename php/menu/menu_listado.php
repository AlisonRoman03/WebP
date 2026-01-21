<?php
require_once "../conexion.php";
header('Content-Type: application/json');

$sql = "SELECT 
            idmenu,
            descripcion,
            unidad,
            precio,
            fecha,
            hora
        FROM menu
        WHERE estado = 0
        ORDER BY fecha, hora";

$result = $conn->query($sql);

$eventos = [];

while ($row = $result->fetch_assoc()) {

    $eventos[] = [
        "id"    => $row['idmenu'],
        "title" => $row['descripcion'],
        "start" => $row['fecha'] . "T" . $row['hora'],
        "extendedProps" => [
            "precio" => (float) $row['precio'],
            "unidad" => (int) $row['unidad'],
            "hora"   => $row['hora']
        ]
    ];
}

echo json_encode($eventos);
