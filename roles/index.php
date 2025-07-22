<?php
session_start();

require_once '../config.php';
require_once 'funciones.php'; 
require_once '../classes/Footer.php'; 

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !esAdministrador()) {
    header("location: ../index.php"); // Redirigir al login si no tiene permisos
    exit;
}

$roles = getTodosLosRoles($link); // Obtener todos los roles

// Mostrar mensaje de éxito/error después de una operación
$mensaje_confirmacion = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'creado') {
        $mensaje_confirmacion = '<div class="alert alert-success" role="alert">Rol creado exitosamente.</div>';
    } elseif ($_GET['msg'] == 'actualizado') {
        $mensaje_confirmacion = '<div class="alert alert-success" role="alert">Rol actualizado exitosamente.</div>';
    } elseif ($_GET['msg'] == 'eliminado') {
        $mensaje_confirmacion = '<div class="alert alert-success" role="alert">Rol eliminado exitosamente.</div>';
    } elseif ($_GET['msg'] == 'error_eliminar_usuarios') {
        $mensaje_confirmacion = '<div class="alert alert-danger" role="alert">No se puede eliminar el rol: hay usuarios asignados a este rol. Reasígnelos primero.</div>';
    } elseif ($_GET['msg'] == 'error') {
        $mensaje_confirmacion = '<div class="alert alert-danger" role="alert">Ocurrió un error en la operación.</div>';
    }
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Roles - Capital Humano</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { display: flex; flex-direction: column; min-height: 100vh; }
        .content-wrapper { flex: 1; padding-bottom: 50px; }
        .table-responsive { margin-top: 20px; }
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
                        <a class="nav-link" href="../usuarios/index.php">Módulo de Usuarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">Módulo de Roles</a>
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
        <h2>Gestión de Roles</h2>
        <p>Administra los roles del sistema y sus descripciones.</p>

        <?php echo $mensaje_confirmacion; ?>

        <a href="crear.php" class="btn btn-success mb-3">Crear Nuevo Rol</a>

        <?php if (!empty($roles)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Rol</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $rol): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($rol['id_rol']); ?></td>
                                <td><?php echo htmlspecialchars($rol['nombre_rol']); ?></td>
                                <td><?php echo htmlspecialchars($rol['descripcion']); ?></td>
                                <td>
                                    <a href="editar.php?id_rol=<?php echo $rol['id_rol']; ?>" class="btn btn-info btn-sm">Editar</a>
                                    <a href="eliminar.php?id_rol=<?php echo $rol['id_rol']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar este rol? ¡No se puede eliminar si hay usuarios asignados!');">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No hay roles registrados en el sistema.</div>
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