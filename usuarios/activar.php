<?php
session_start();

require_once '../config.php';
require_once 'funciones.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !esAdministrador()) {
    header("location: ../index.php");
    exit;
}

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);

    // Usar la función activarUsuario (que está en funciones.php)
    if (activarUsuario($link, $id)) {
        header("location: index.php?msg=activado");
        exit();
    } else {
        echo "¡Ups! Algo salió mal al intentar activar el usuario.";
    }
} else {
    header("location: index.php");
    exit();
}
mysqli_close($link);
?>