<?php
require_once('../database/conexion.php');
require_once('../funciones/controller.php');
$db = conectar();

session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

$emailUsuario = $_SESSION['email'];

$datosUsuario = getDatosUsuario($emailUsuario, $db);

if (!$datosUsuario) {
    echo "Usuario no encontrado.";
    exit();
}

$id_usuario = $datosUsuario['ID'];
$saldoActual = $datosUsuario['saldo'];


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $tipoTransaccion = 'consignacion';
    $montoDeposito = $_POST['valorc'];

    if (realizarConsignacion($id_usuario, $saldoActual, $montoDeposito, $db, $tipoTransaccion)) {
        header("Location: ../views/dashboard2.php");
        exit();
    } else {
        echo "Monto de depósito no válido.";
    }
}
?>
