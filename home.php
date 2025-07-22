<?php
// Iniciar la sesión
session_start();

require_once 'classes/Footer.php';

// Verificar si el usuario ha iniciado sesión, si no, redirigirlo a la página de login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Principal - Capital Humano</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos para que el footer se mantenga abajo y el contenido principal ocupe espacio */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .content {
            flex: 1; /* Esto hará que el contenido principal ocupe el espacio restante */
            padding-bottom: 50px; /* Para asegurar espacio encima del footer */
        }
        /* Estilos para el footer en home.php, si FooterView no los trae */
        .footer {
            background-color: #f8f9fa; /* Un fondo gris claro para el footer */
            border-top: 1px solid #e9ecef; /* Un borde superior sutil */
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 0.9rem;
            width: 100%;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="home.php">Capital Humano</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="usuarios/index.php">Módulo de Usuarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="roles/index.php">Módulo de Roles</a> 
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Módulo de Colaboradores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Módulo de Cargos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Reportes</a>
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
                            <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4 content">
        <div class="alert alert-success" role="alert">
            Bienvenido al sistema, **<?php echo htmlspecialchars($_SESSION["username"]); ?>**!
        </div>
        <h3>Módulos Disponibles</h3>
        <p>Selecciona un módulo del menú de navegación para empezar a trabajar.</p>

        </div>

    <?php
    // Instanciar y renderizar el footer si existe la clase FooterView
    if (class_exists('Footer')) {
        $footer = new Footer();
        $footer->render();
    } else {
        // Fallback si la clase FooterView no está definida
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