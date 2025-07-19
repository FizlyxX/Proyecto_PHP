<?php
session_start();

// Si hay un mensaje de error redirigido de login.php
$error_message = null;
if (isset($_GET['error']) && $_GET['error'] == 'invalid_credentials') {
    $error_message = "Usuario o contraseña incorrectos.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Capital Humano</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="left-panel">
        <div class="slider-dots">
            <span class="dot active"></span>
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="dot"></span>
        </div>
    </div>
    <div class="right-panel">
        <div class="login-content">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e0/Coat_of_arms_of_Uganda.svg/1200px-Coat_of_arms_of_Uganda.svg.png" alt="Logo" class="logo">
            <h2>Bienvenido al Sistema de Capital Humano</h2>
            <p>Por favor inicie sesión con sus credenciales</p>

            <form action="login.php" method="post">
                <div class="form-group mb-3">
                    <input type="text" id="username" name="username" class="form-control" placeholder="Usuario" required>
                </div>
                <div class="form-group mb-4">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Contraseña" required>
                </div>
                <div class="form-group d-grid">
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                </div>
                <?php
                if ($error_message) {
                    echo '<p class="error-message">' . htmlspecialchars($error_message) . '</p>';
                }
                ?>
                </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>