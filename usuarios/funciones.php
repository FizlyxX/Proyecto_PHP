<?php

require_once __DIR__ . '/../config.php';

// Función para obtener todos los usuarios
function getUsuarios($link) {
    $usuarios = [];
    $sql = "SELECT u.id, u.nombre_usuario, r.nombre_rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol ORDER BY u.nombre_usuario ASC";
    if ($result = mysqli_query($link, $sql)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $usuarios[] = $row;
        }
        mysqli_free_result($result);
    }
    return $usuarios;
}

// Función para obtener un usuario por su ID
function getUsuarioById($link, $id) {
    $usuario = null;
    $sql = "SELECT id, nombre_usuario, contrasena, id_rol FROM usuarios WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($result) == 1) {
                $usuario = mysqli_fetch_assoc($result);
            }
        }
        mysqli_stmt_close($stmt);
    }
    return $usuario;
}

// Función para obtener todos los roles
function getRoles($link) {
    $roles = [];
    $sql = "SELECT id_rol, nombre_rol FROM roles ORDER BY nombre_rol ASC";
    if ($result = mysqli_query($link, $sql)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $roles[] = $row;
        }
        mysqli_free_result($result);
    }
    return $roles;
}

// Función para crear un nuevo usuario
function crearUsuario($link, $nombre_usuario, $contrasena, $id_rol) {
    // Hashear la contraseña antes de guardarla
    $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nombre_usuario, contrasena, id_rol) VALUES (?, ?, ?)";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssi", $nombre_usuario, $hashed_password, $id_rol);
        if (mysqli_stmt_execute($stmt)) {
            return true;
        } else {
            
            return false;
        }
        mysqli_stmt_close($stmt);
    }
    return false;
}

// Función para actualizar un usuario existente
function actualizarUsuario($link, $id, $nombre_usuario, $contrasena, $id_rol) {
    if (!empty($contrasena)) {
        // Si se proporciona una nueva contraseña, hashearla
        $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET nombre_usuario = ?, contrasena = ?, id_rol = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssii", $nombre_usuario, $hashed_password, $id_rol, $id);
        } else {
            return false;
        }
    } else {
        // Si no se proporciona una nueva contraseña, no actualizarla
        $sql = "UPDATE usuarios SET nombre_usuario = ?, id_rol = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sii", $nombre_usuario, $id_rol, $id);
        } else {
            return false;
        }
    }

    if (mysqli_stmt_execute($stmt)) {
        return true;
    } else {
    
        return false;
    }
    mysqli_stmt_close($stmt);
    return false;
}

// Función para eliminar un usuario
function eliminarUsuario($link, $id) {
    $sql = "DELETE FROM usuarios WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            return true;
        }
        mysqli_stmt_close($stmt);
    }
    return false;
}
?>