<?php
require_once "../conexion.php";

$sql = "SELECT 
            i.idinventario,
            i.descripcion,
            i.stock,
            i.precio,
            c.descripcion AS categoria,
            DATE(i.fecpro) AS fecpro
        FROM inventario i
        INNER JOIN categoria c ON i.idcategoria = c.idcategoria
        WHERE i.estado = 0
        ORDER BY i.idinventario DESC";

$result = $conn->query($sql);

$data = [];

$contador = 1;

while ($row = $result->fetch_assoc()) {

    $acciones = '
        <span class="text-primary btnEditar" 
              data-id="'.$row['idinventario'].'" 
              style="cursor:pointer;">
            <iconify-icon icon="solar:pen-new-square-line-duotone" width="24" height="24"></iconify-icon>
        </span>

        <span class="text-danger btnEliminar ms-2" 
              data-id="'.$row['idinventario'].'" 
              style="cursor:pointer;">
            <iconify-icon icon="solar:trash-bin-minimalistic-line-duotone" width="24" height="24"></iconify-icon>
        </span>
    ';

    $data[] = [
        $contador++,
        $row['descripcion'],
        $row['stock'],
        number_format($row['precio'], 2),
        $row['categoria'],
        $row['fecpro'],
        $acciones
    ];
}

echo json_encode([
    "data" => $data
]);
