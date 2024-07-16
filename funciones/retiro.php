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
    echo "El usuario no existe";
    exit();
}

$id_usuario = $datosUsuario['ID'];
$saldoActual = $datosUsuario['saldo'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipoTransaccion = 'retiro';
    $montoRetiro = $_POST['valorr'];

    if (realizarRetiro($id_usuario, $saldoActual, $montoRetiro, $db)) {
        header("Location: ../views/dashboard2.php");
        exit();
    } else {
        echo "Monto de retiro no válido.";
    }
}
?>
