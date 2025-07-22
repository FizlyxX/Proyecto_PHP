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

$id_rol = $nombre_rol = $descripcion = "";
$nombre_rol_err = $descripcion_err = "";

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener el ID del rol a editar
    $id_rol = $_POST["id_rol"];

    // Validar nombre del rol
    if (empty(trim($_POST["nombre_rol"]))) {
        $nombre_rol_err = "Por favor ingrese un nombre para el rol.";
    } else {
        // Verificar si el nombre del rol ya existe para otro rol
        $sql = "SELECT id_rol FROM roles WHERE nombre_rol = ? AND id_rol != ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $param_nombre_rol, $param_id_rol);
            $param_nombre_rol = trim($_POST["nombre_rol"]);
            $param_id_rol = $id_rol;
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $nombre_rol_err = "Este nombre de rol ya existe para otro rol.";
                } else {
                    $nombre_rol = trim($_POST["nombre_rol"]);
                }
            } else {
                echo "¡Ups! Algo salió mal al verificar el rol.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Validar descripción (opcional)
    $descripcion = trim($_POST["descripcion"]);

    // Si no hay errores, intentar actualizar el rol
    if (empty($nombre_rol_err) && empty($descripcion_err)) {
        if (actualizarRol($link, $id_rol, $nombre_rol, $descripcion)) {
            header("location: index.php?msg=actualizado");
            exit();
        } else {
            echo '<div class="alert alert-danger">Error al actualizar el rol. Por favor, inténtelo de nuevo.</div>';
        }
    }
    mysqli_close($link);

} else { // Si no es un POST, cargar los datos del rol para edición
    if (isset($_GET["id_rol"]) && !empty(trim($_GET["id_rol"]))) {
        $id_rol = trim($_GET["id_rol"]);
        $rol_data = getRolById($link, $id_rol);

        if ($rol_data) {
            $nombre_rol = $rol_data['nombre_rol'];
            $descripcion = $rol_data['descripcion'];
        } else {
            // Rol no encontrado, redirigir
            header("location: index.php");
            exit();
        }
    } else {
        // ID no proporcionado, redirigir
        header("location: index.php");
        exit();
    }
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Rol - Capital Humano</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { display: flex; flex-direction: column; min-height: 100vh; }
        .content-wrapper { flex: 1; padding-bottom: 50px; }
        .form-group { margin-bottom: 1rem; }
        .invalid-feedback { display: block; }
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
                    <li class="nav-item"><a class="nav-link" href="../home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="index.php">Módulo de Roles</a></li>
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
        <h2>Editar Rol</h2>
        <p>Modifique los datos del rol.</p>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="id_rol" value="<?php echo htmlspecialchars($id_rol); ?>">
            <div class="mb-3 <?php echo (!empty($nombre_rol_err)) ? 'has-error' : ''; ?>">
                <label for="nombre_rol" class="form-label">Nombre del Rol:</label>
                <input type="text" name="nombre_rol" id="nombre_rol" class="form-control" value="<?php echo htmlspecialchars($nombre_rol); ?>">
                <span class="invalid-feedback text-danger"><?php echo $nombre_rol_err; ?></span>
            </div>
            <div class="mb-3 <?php echo (!empty($descripcion_err)) ? 'has-error' : ''; ?>">
                <label for="descripcion" class="form-label">Descripción:</label>
                <textarea name="descripcion" id="descripcion" class="form-control" rows="3"><?php echo htmlspecialchars($descripcion); ?></textarea>
                <span class="invalid-feedback text-danger"><?php echo $descripcion_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Actualizar Rol">
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
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