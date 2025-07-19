<?php
session_start();

require_once 'config.php';

$username = $password = "";
$username_err = $password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar nombre de usuario
    if (empty(trim($_POST["username"]))) {
        $username_err = "Por favor ingrese su usuario.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validar contraseña
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor ingrese su contraseña.";
    } else {
        $password = trim($_POST["password"]);
    }
    if (empty($username_err) && empty($password_err)) {

        $sql = "SELECT id, nombre_usuario, contrasena, id_rol FROM usuarios WHERE nombre_usuario = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {

            mysqli_stmt_bind_param($stmt, "s", $param_username);

            $param_username = $username;

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_store_result($stmt);

                // Si se encontró un usuario con ese nombre de usuario
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Vincular las variables de resultado
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $id_rol);
                    if (mysqli_stmt_fetch($stmt)) {
                        
                        if (password_verify($password, $hashed_password)) {
                            
                            session_regenerate_id(); // Regenerar ID de sesión por seguridad
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["id_rol"] = $id_rol;

                            // Redirigir al usuario a la página de inicio (home.php)
                            header("location: home.php");
                            exit();
                        } else {
                            // Contraseña incorrecta
                            header("location: index.php?error=invalid_credentials");
                            exit();
                        }
                    }
                } else {
                    // Nombre de usuario no encontrado
                    header("location: index.php?error=invalid_credentials");
                    exit();
                }
            } else {
                echo "¡Ups! Algo salió mal. Por favor, inténtelo de nuevo más tarde.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($link);
}
?>