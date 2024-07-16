<?php
require_once('../database/conexion.php');
require_once('../funciones/controller.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (validarInicioSesion($email, $password)) {
        session_start();
        $_SESSION['email'] = $email;
        header("Location: ../views/dashboard2.php");
        exit();
    } else {
        echo "Revise la informacion ingresada";
    }
} else {
    echo "ERROR";
}
?>
