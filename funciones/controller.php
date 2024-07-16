<?php

require_once('../database/conexion.php');

function registrarUsuario($nombre, $email, $password)
{
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $db = conectar();
    $db->beginTransaction();

    $sql = "INSERT INTO usuario (nombre, email, password) VALUES (:nombre, :email, :password)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $passwordHash);

    $stmt->execute();

    $userId = $db->lastInsertId();
    $sql = "INSERT INTO cuenta (fk_usuario, saldo) VALUES (:fk_usuario, 0.00)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':fk_usuario', $userId);
    $stmt->execute();

    $db->commit();
}

function validarInicioSesion($email, $password) {
    $db = conectar();

    $sql = "SELECT password FROM usuario WHERE email = :email";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $storedPasswordHash = $result['password'];

    return ($result && password_verify($password, $storedPasswordHash));
}

function getDatosUsuario($emailUsuario, $db) {
    $consultaUsuario = $db->prepare("
        SELECT usuario.id_usuario AS ID, cuenta.saldo
        FROM usuario
        JOIN cuenta ON usuario.id_usuario = cuenta.fk_usuario
        WHERE usuario.email = :email
    ");
    $consultaUsuario->bindParam(':email', $emailUsuario);
    $consultaUsuario->execute();
    return $consultaUsuario->fetch(PDO::FETCH_ASSOC);
}

function realizarConsignacion($id_usuario, $saldoDisponible, $montoConsignacion, $db, $tipoTransaccion) {
    if ($montoConsignacion > 0) {
        $nuevoSaldo = $saldoDisponible + $montoConsignacion;
        $db->beginTransaction();

        try {
            $actualizarSaldo = $db->prepare("UPDATE cuenta SET saldo = :nuevoSaldo WHERE fk_usuario = :id_usuario");
            $actualizarSaldo->bindParam(':nuevoSaldo', $nuevoSaldo);
            $actualizarSaldo->bindParam(':id_usuario', $id_usuario);
            $actualizarSaldo->execute();
            $registrarTransaccion = $db->prepare("
                INSERT INTO transaccion (fk_usuario, fecha, tipo, monto)
                VALUES (:fk_usuario, NOW(), 'consignacion', :monto)
            ");
            $registrarTransaccion->bindParam(':fk_usuario', $id_usuario);
            $registrarTransaccion->bindParam(':monto', $montoConsignacion);
            $registrarTransaccion->execute();
            $db->commit();

            return true;
        } catch (PDOException $e) {
            $db->rollBack();

            echo "No se pudo realizar la consignacion: " . $e->getMessage();

            return false;
        }
    } else {
        return false;
    }
}

function realizarRetiro($id_usuario, $saldoDisponible, $montoRetiro, $db) {
    if ($montoRetiro > 0 && $montoRetiro <= $saldoDisponible) {
        $nuevoSaldo = $saldoDisponible - $montoRetiro;
        $db->beginTransaction();

        try {
            $actualizarSaldo = $db->prepare("UPDATE cuenta SET saldo = :nuevoSaldo WHERE fk_usuario = :id_usuario");
            $actualizarSaldo->bindParam(':nuevoSaldo', $nuevoSaldo);
            $actualizarSaldo->bindParam(':id_usuario', $id_usuario);
            $actualizarSaldo->execute();
            $registrarTransaccion = $db->prepare("
                INSERT INTO transaccion (fk_usuario, fecha, tipo, monto)
                VALUES (:fk_usuario, NOW(), 'retiro', :monto)
            ");
            $registrarTransaccion->bindParam(':fk_usuario', $id_usuario);
            $registrarTransaccion->bindParam(':monto', $montoRetiro);
            $registrarTransaccion->execute();
            $db->commit();

            return true;
        } catch (PDOException $e) {

            $db->rollBack();

            echo "No se pudo realizar el retiro: " . $e->getMessage();

            return false;
        }
    } else {
        return false;
    }
}

function getSaldoUsuario($id_usuario, $db) {
    try {
        $consultaSaldo = $db->prepare("SELECT saldo FROM cuenta WHERE fk_usuario = :id_usuario");
        $consultaSaldo->bindParam(':id_usuario', $id_usuario);
        $consultaSaldo->execute();

        $resultado = $consultaSaldo->fetch(PDO::FETCH_ASSOC);

        return ($resultado) ? $resultado['saldo'] : 0;
    } catch (PDOException $e) {
        echo "No se pudo obtener el saldo del usuario: " . $e->getMessage();
        return 0;
    }
}

function realizarTransferencia($id_usuario, $saldoDisponible, $idUsuarioDestinatario, $montoTransferencia, $db) {
    if ($montoTransferencia > 0 && $montoTransferencia <= $saldoDisponible) {
        $nuevoSaldoOrigen = $saldoDisponible - $montoTransferencia;
        $db->beginTransaction();

        try {
            $actualizarSaldoOrigen = $db->prepare("UPDATE cuenta SET saldo = :nuevoSaldo WHERE fk_usuario = :id_usuario");
            $actualizarSaldoOrigen->bindParam(':nuevoSaldo', $nuevoSaldoOrigen);
            $actualizarSaldoOrigen->bindParam(':id_usuario', $id_usuario);
            $actualizarSaldoOrigen->execute();

            $saldoDestinatario = getSaldoUsuario($idUsuarioDestinatario, $db);

            $nuevoSaldoDestinatario = $saldoDestinatario + $montoTransferencia;

            $actualizarSaldoDestinatario = $db->prepare("UPDATE cuenta SET saldo = :nuevoSaldo WHERE fk_usuario = :id_usuario");
            $actualizarSaldoDestinatario->bindParam(':nuevoSaldo', $nuevoSaldoDestinatario);
            $actualizarSaldoDestinatario->bindParam(':id_usuario', $idUsuarioDestinatario);
            $actualizarSaldoDestinatario->execute();

            $registrarTransaccion = $db->prepare("
                INSERT INTO transaccion (fk_usuario, fecha, tipo, monto)
                VALUES (:fk_usuario, NOW(), 'transferencia', :monto)
            ");
            $registrarTransaccion->bindParam(':fk_usuario', $id_usuario);
            $registrarTransaccion->bindParam(':monto', $montoTransferencia);
            $registrarTransaccion->execute();
            $db->commit();

            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            echo "Error al realizar la transferencia: " . $e->getMessage();

            return false;
        }
    } else {
        return false;
    }
}

function getTransferencias($id_usuario, $db){
    try {
        $consultaTransaccion = $db->prepare("SELECT usuario.id_usuario, transaccion.*
        FROM usuario INNER JOIN (cuenta INNER JOIN transaccion ON cuenta.fk_usuario = transaccion.fk_usuario) 
        ON usuario.id_usuario = cuenta.fk_usuario
        WHERE (((usuario.id_usuario)=:id_usuario));
        ");
        $consultaTransaccion->bindParam(':id_usuario', $id_usuario);
        $consultaTransaccion->execute();

        $resultado = $consultaTransaccion->fetch(PDO::FETCH_ASSOC);

        return ($resultado) ? $resultado['tipo'] : 0;
    } catch (PDOException $e) {
        echo "No se pudo obtener el historial del usuario: " . $e->getMessage();
        return 0;
    }
}


?>

 

