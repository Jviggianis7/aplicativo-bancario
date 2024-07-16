<?php
require_once('../database/conexion.php');
require_once('../funciones/controller.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($nombre) && !empty($email) && !empty($password)) {
        registrarUsuario($nombre, $email, $password);
        header("Location: ../views/login.php");
        exit();
    } else {
        echo "No puede dejar campos vacios";
    }
} else {
    echo "ERROR";
}
?>