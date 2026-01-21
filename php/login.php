<?php
session_start();
require_once "conexion.php";

$data = json_decode(file_get_contents("php://input"), true);

$usuario = $data['usuario'] ?? '';
$clave   = $data['clave'] ?? '';

$sql = "SELECT idusuario, usuario, clave, idrol, nombre, apellidos
        FROM usuarios 
        WHERE usuario = ? 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && $clave === $user['clave']) {

    $_SESSION['idusuario'] = $user['idusuario'];
    $_SESSION['usuario']   = $user['usuario'];
    $_SESSION['idrol']     = $user['idrol'];
    $_SESSION['nombre']     = $user['nombre'];
    $_SESSION['apellidos']     = $user['apellidos'];

    echo json_encode(["success" => true]);

} else {
    echo json_encode(["success" => false]);
}
