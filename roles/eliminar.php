<?php
session_start();

require_once '../config.php';
require_once 'funciones.php';
require_once '../classes/Footer.php';

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !esAdministrador()) {
    header("location: ../index.php");
    exit;
}

if (isset($_GET["id_rol"]) && !empty(trim($_GET["id_rol"]))) {
    $id_rol = trim($_GET["id_rol"]);

    // Intentar eliminar el rol. La función ya tiene la lógica para verificar usuarios asignados.
    if (eliminarRol($link, $id_rol)) {
        header("location: index.php?msg=eliminado");
        exit();
    } else {
        // Error al eliminar, posiblemente por usuarios asignados
        header("location: index.php?msg=error_eliminar_usuarios"); // Mensaje más específico
        exit();
    }
} else {
    // Si no se proporcionó un ID, redirigir
    header("location: index.php");
    exit();
}
mysqli_close($link);
?>