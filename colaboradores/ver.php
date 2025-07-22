<?php
session_start();

require_once '../config.php';
require_once 'funciones.php';
require_once '../classes/Footer.php';
require_once '../includes/navbar.php';

// Verificar si el usuario ha iniciado sesión y tiene permisos (ej. RRHH o Administrador)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !esAdministrador()) { // O !esRRHH()
    header("location: ../index.php");
    exit;
}

// Definir la página actual para que el navbar la resalte
$current_page = 'colaboradores';

$colaborador = null;

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id_colaborador = trim($_GET["id"]);
    $colaborador = getColaboradorById($link, $id_colaborador);

    if (!$colaborador) {
        header("location: index.php?msg=error_notfound");
        exit();
    }
} else {
    header("location: index.php?msg=error");
    exit();
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Colaborador - Capital Humano</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { display: flex; flex-direction: column; min-height: 100vh; }
        .content-wrapper { flex: 1; padding-bottom: 50px; }
        .detail-photo {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .detail-card {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            background-color: #fff;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
        }
         .footer { 
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 0.9rem;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container mt-4 content-wrapper">
        <h2>Detalles del Colaborador</h2>
        <p>Información completa del colaborador.</p>

        <?php if ($colaborador): ?>
            <div class="detail-card">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <?php if (!empty($colaborador['ruta_foto_perfil'])): ?>
                            <?php
                                // Generar la URL para la foto original
                                $base_foto_name = basename($colaborador['ruta_foto_perfil']);
                                $original_url = URL_BASE_FOTOS . 'original_' . $base_foto_name;
                            ?>
                            <img src="<?php echo htmlspecialchars($original_url); ?>" alt="Foto de Perfil" class="detail-photo">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/200?text=No+Foto" alt="Sin Foto" class="detail-photo">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <p><span class="detail-label">Nombre Completo:</span> <?php echo htmlspecialchars($colaborador['primer_nombre'] . ' ' . $colaborador['segundo_nombre'] . ' ' . $colaborador['primer_apellido'] . ' ' . $colaborador['segundo_apellido']); ?></p>
                        <p><span class="detail-label">Identificación (Cédula):</span> <?php echo htmlspecialchars($colaborador['identificacion']); ?></p>
                        <p><span class="detail-label">Sexo:</span> <?php echo htmlspecialchars($colaborador['sexo']); ?></p>
                        <p><span class="detail-label">Fecha de Nacimiento:</span> <?php echo htmlspecialchars($colaborador['fecha_nacimiento']); ?></p>
                        <p><span class="detail-label">Correo Personal:</span> <?php echo htmlspecialchars($colaborador['correo_personal']); ?></p>
                        <p><span class="detail-label">Teléfono:</span> <?php echo htmlspecialchars($colaborador['telefono']); ?></p>
                        <p><span class="detail-label">Celular:</span> <?php echo htmlspecialchars($colaborador['celular']); ?></p>
                        <p><span class="detail-label">Dirección:</span> <?php echo nl2br(htmlspecialchars($colaborador['direccion'])); ?></p>
                        
                        <?php if (!empty($colaborador['ruta_historial_academico_pdf'])): ?>
                            <p><span class="detail-label">Historial Académico:</span> <a href="<?php echo htmlspecialchars(URL_BASE_PDFS . basename($colaborador['ruta_historial_academico_pdf'])); ?>" target="_blank" class="btn btn-sm btn-info">Ver PDF</a></p>
                        <?php endif; ?>
                        <p><span class="detail-label">Estado:</span> 
                            <?php if ($colaborador['activo'] == 1): ?>
                                <span class="status-badge active">Activo</span>
                            <?php else: ?>
                                <span class="status-badge inactive">Inactivo</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <a href="index.php" class="btn btn-secondary">Volver al Listado</a>
                <a href="editar.php?id=<?php echo $colaborador['id_colaborador']; ?>" class="btn btn-primary">Editar Colaborador</a>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">No se encontró el colaborador.</div>
        <?php endif; ?>
    </div>

    <?php
    if (class_exists('Footer')) {
        $footer = new Footer();
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