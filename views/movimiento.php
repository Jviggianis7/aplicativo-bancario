<?php
require_once('../database/conexion.php');
$db = conectar();

session_start();

if (!isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

$emailUsuario = $_SESSION['email'];

try {
    $listadoTransacciones = $db->prepare("SELECT * FROM transaccion WHERE fk_usuario = (SELECT id_usuario FROM usuario WHERE email = :email)");
    $listadoTransacciones->bindParam(':email', $emailUsuario);
    $listadoTransacciones->execute();
    $resultados = $listadoTransacciones->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

if (isset($_POST['cerrar_sesion'])) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>cuentaBanco</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
</head>

<body>
    <form class="modal-content animate" action="bancovalida.php" method="POST">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content" class="mb-5">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-7">
                                <div class="card border rounded-lg mt-5">
                                    <div class="card-header">
                                        <h3 class="text-center font-weight-light my-4">Bienvenido</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="card bg-light text-center mb-4">
                                            <div class="card-header">
                                                <i class="fa-solid fa-receipt"></i>
                                                Movimientos
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">Movimiento</th>
                                                            <th scope="col">Monto</th>
                                                            <th scope="col">Fecha</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($resultados as $row) : ?>
                                                            <tr>
                                                                <td>
                                                                    <?php if ($row['tipo'] == 'consignacion') : ?>
                                                                        <span class='badge bg-success rounded-pill w-25'>
                                                                            <i class='fa-solid fa-circle-arrow-up'></i>
                                                                        </span>
                                                                    <?php elseif ($row['tipo'] == 'retiro') : ?>
                                                                        <span class='badge bg-danger rounded-pill w-25'>
                                                                            <i class='fa-solid fa-circle-arrow-down'></i>
                                                                        </span>
                                                                    <?php elseif ($row['tipo'] == 'transferencia') : ?>
                                                                        <span class='badge bg-info rounded-pill w-25'>
                                                                            <i class='fa-solid fa-circle-arrow-right'></i>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?= htmlspecialchars($row['monto'], ENT_QUOTES, 'UTF-8') ?></td>
                                                                <td><?= htmlspecialchars($row['fecha'], ENT_QUOTES, 'UTF-8') ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>

                                                    </tbody>

                                                </table>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-center">
                                            <a href="dashboard2.php" type="button" class="btn btn-outline-danger shadow-sm" style="width: 110px;">
                                                <i class="fa-solid fa-xmark"></i> Salir
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutAuthentication_footer">
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; Your Website 2023</div>
                            <div>
                                <a href="#">Privacy Policy</a>
                                &middot;
                                <a href="#">Terms &amp; Conditions</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </form>
</body>

</html>