<?php
session_start();


if (!isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

$emailUsuario = $_SESSION['email'];

require_once('../database/conexion.php');
require_once('../funciones/controller.php');
$db = conectar();

$datosUsuario = getDatosUsuario($emailUsuario, $db);

if (!$datosUsuario) {
    echo "Usuario no encontrado.";
    exit();
}

$id_usuario = $datosUsuario['ID'];
$saldoActualOrigen = $datosUsuario['saldo'];

$saldoDisponible = $saldoActualOrigen;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $idUsuarioDestinatario = $_POST['cuenta'];
    $montoTransferencia = $_POST['valort'];

    if (realizarTransferencia($id_usuario, $saldoDisponible, $idUsuarioDestinatario, $montoTransferencia, $db)) {
        header("Location: ../views/dashboard2.php");
        exit();
    } else {
        echo "Transferencia no válida.";
    }
}
?>
