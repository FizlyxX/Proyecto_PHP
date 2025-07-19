<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["username"] !== 'admin') {
    header("location: ../index.php");
    exit;
}

require_once '../config.php';
require_once 'funciones.php';

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);

    if (eliminarUsuario($link, $id)) {
        header("location: index.php?msg=eliminado");
        exit();
    } else {
        echo "¡Ups! Algo salió mal al intentar eliminar el usuario.";
    }
} else {
    // Si no se proporcionó un ID, redirigir
    header("location: index.php");
    exit();
}
mysqli_close($link);
?>