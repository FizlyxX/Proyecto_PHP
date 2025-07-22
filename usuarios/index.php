<?php
session_start();

require_once '../config.php';
require_once 'funciones.php';

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !esAdministrador()) {
    header("location: ../index.php"); // Redirigir al login si no tiene permisos
    exit;
}

// Obtener usuarios.
// Por defecto, getUsuarios() solo trae activos.
$usuarios = getUsuarios($link, isset($_GET['mostrar_todos'])); 

// Mostrar mensaje de éxito/error después de una operación
$mensaje_confirmacion = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'creado') {
        $mensaje_confirmacion = '<div class="alert alert-success" role="alert">Usuario creado exitosamente.</div>';
    } elseif ($_GET['msg'] == 'actualizado') {
        $mensaje_confirmacion = '<div class="alert alert-success" role="alert">Usuario actualizado exitosamente.</div>';
    } elseif ($_GET['msg'] == 'desactivado') {
        $mensaje_confirmacion = '<div class="alert alert-warning" role="alert">Usuario desactivado exitosamente.</div>';
    } elseif ($_GET['msg'] == 'activado') {
        $mensaje_confirmacion = '<div class="alert alert-success" role="alert">Usuario activado exitosamente.</div>';
    }
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Usuarios - Capital Humano</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .content-wrapper {
            flex: 1;
            padding-bottom: 50px;
        }
        .table-responsive {
            margin-top: 20px;
        }
        /* Badges para el estado Activo/Inactivo */
        .status-badge {
            padding: .3em .6em;
            border-radius: .25rem;
            font-size: 0.85em;
            font-weight: bold;
        }
        .status-badge.active {
            background-color: #28a745; /* Verde Bootstrap */
            color: white;
        }
        .status-badge.inactive {
            background-color: #dc3545; /* Rojo Bootstrap */
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../home.php">Capital Humano</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="../home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">Módulo de Usuarios</a>
                    </li>
                    </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($_SESSION["username"]); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="#">Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4 content-wrapper">
        <h2>Gestión de Usuarios</h2>
        <p>Administra los usuarios del sistema.</p>

        <?php echo $mensaje_confirmacion; ?>

        <div class="d-flex justify-content-between mb-3">
            <a href="crear.php" class="btn btn-success">Crear Nuevo Usuario</a>
            <?php if (isset($_GET['mostrar_todos'])): ?>
                <a href="index.php" class="btn btn-info">Mostrar Solo Activos</a>
            <?php else: ?>
                <a href="index.php?mostrar_todos=true" class="btn btn-info">Mostrar Todos (Activos/Inactivos)</a>
            <?php endif; ?>
        </div>

        <?php if (!empty($usuarios)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombre_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombre_rol']); ?></td>
                                <td>
                                    <?php if ($usuario['activo'] == 1): ?>
                                        <span class="status-badge active">Activo</span>
                                    <?php else: ?>
                                        <span class="status-badge inactive">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="editar.php?id=<?php echo $usuario['id']; ?>" class="btn btn-info btn-sm">Editar</a>
                                    <?php if ($usuario['activo'] == 1): ?>
                                        <a href="eliminar.php?id=<?php echo $usuario['id']; ?>" class="btn btn-warning btn-sm" onclick="return confirm('¿Está seguro de DESACTIVAR este usuario?');">Desactivar</a>
                                    <?php else: ?>
                                        <a href="activar.php?id=<?php echo $usuario['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('¿Está seguro de ACTIVAR este usuario?');">Activar</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No hay usuarios registrados en el sistema.</div>
        <?php endif; ?>
    </div>

    <?php
    if (class_exists('FooterView')) {
        $footer = new FooterView();
        $footer->render();
    } else {
        echo '<footer class="footer">';
        echo '  <div class="container">';
        echo '      <p>&copy; ' . date("Y") . ' Proyecto PHP Capital Humano. Todos los derechos reservados.</p>';
        echo '  </div>';
        echo '</footer>';
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>