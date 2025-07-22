<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../usuarios/funciones.php'; // Para usar esAdministrador()

// --- Constantes para rutas de subida ---
define('UPLOAD_DIR_FOTOS', __DIR__ . '/../uploads/fotos_perfil/');
define('UPLOAD_DIR_PDFS', __DIR__ . '/../uploads/historiales_academicos/');
define('URL_BASE_FOTOS', '../uploads/fotos_perfil/'); // Ruta URL relativa para mostrar
define('URL_BASE_PDFS', '../uploads/historiales_academicos/'); // Ruta URL relativa para mostrar

// --- Constantes para redimensionamiento de imágenes ---
define('THUMBNAIL_WIDTH', 100);  // Ancho de la miniatura
define('THUMBNAIL_HEIGHT', 100); // Alto de la miniatura
define('ORIGINAL_PHOTO_WIDTH', 500); // Ancho deseado para la foto original (si es muy grande)
define('ORIGINAL_PHOTO_HEIGHT', 500); // Alto deseado para la foto original (si es muy grande)

// Función para obtener todos los colaboradores
// AÑADIDO: $mostrar_inactivos para controlar si se muestran todos o solo activos
function getColaboradores($link, $mostrar_inactivos = false) {
    $colaboradores = [];
    $sql = "SELECT * FROM colaboradores";
    if (!$mostrar_inactivos) {
        $sql .= " WHERE activo = 1"; // Solo activos por defecto
    }
    $sql .= " ORDER BY primer_apellido ASC, primer_nombre ASC";

    if ($result = mysqli_query($link, $sql)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $colaboradores[] = $row;
        }
        mysqli_free_result($result);
    }
    return $colaboradores;
}

// Función para obtener un colaborador por su ID
function getColaboradorById($link, $id) {
    $colaborador = null;
    $sql = "SELECT * FROM colaboradores WHERE id_colaborador = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($result) == 1) {
                $colaborador = mysqli_fetch_assoc($result);
            }
        }
        mysqli_stmt_close($stmt);
    }
    return $colaborador;
}

// Función para subir y redimensionar una imagen de perfil
function subirYRedimensionarFotoPerfil($file_input_name, $existing_photo_url = null) {
    // Si no se sube un nuevo archivo, mantener el existente
    if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] == UPLOAD_ERR_NO_FILE) {
        return ['success' => $existing_photo_url];
    }

    $file = $_FILES[$file_input_name];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($file_extension, $allowed_extensions)) {
        return ['error' => 'Tipo de archivo no permitido para la foto (solo JPG, JPEG, PNG, GIF).'];
    }

    // Generar un nombre de archivo único
    $file_name_unique = uniqid('foto_') . '.' . $file_extension;
    $temp_original_path = UPLOAD_DIR_FOTOS . $file_name_unique; // Path temporal para el archivo subido sin redimensionar

    // Mover el archivo subido al directorio temporal
    if (!move_uploaded_file($file['tmp_name'], $temp_original_path)) {
        return ['error' => 'Error al mover el archivo subido. Verifique permisos de escritura.'];
    }

    // --- Redimensionar y Guardar la foto original (ajustada a un tamaño máximo) y miniatura ---
    list($width, $height, $type) = @getimagesize($temp_original_path); // Usar @ para suprimir warnings si no es imagen válida
    if (!$type || !in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF])) {
        // Eliminar el archivo temporal si no es una imagen válida
        if (file_exists($temp_original_path)) {
            unlink($temp_original_path);
        }
        return ['error' => 'El archivo subido no es una imagen válida o soportada.'];
    }

    $source_image = null;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($temp_original_path);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($temp_original_path);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($temp_original_path);
            break;
    }

    if (!$source_image) {
        // Eliminar el archivo temporal si no se pudo crear la imagen
        if (file_exists($temp_original_path)) {
            unlink($temp_original_path);
        }
        return ['error' => 'Error al procesar la imagen subida.'];
    }

    // Redimensionar para la foto "original" (tamaño uniforme)
    $final_original_url_path = URL_BASE_FOTOS . 'original_' . $file_name_unique;
    $final_original_file_path = UPLOAD_DIR_FOTOS . 'original_' . $file_name_unique;
    redimensionarImagen($source_image, $final_original_file_path, ORIGINAL_PHOTO_WIDTH, ORIGINAL_PHOTO_HEIGHT, $file_extension, $type);

    // Redimensionar para la miniatura
    $final_thumbnail_file_path = UPLOAD_DIR_FOTOS . 'thumb_' . $file_name_unique;
    redimensionarImagen($source_image, $final_thumbnail_file_path, THUMBNAIL_WIDTH, THUMBNAIL_HEIGHT, $file_extension, $type);

    imagedestroy($source_image); // Liberar memoria de la imagen fuente
    
    // Eliminar el archivo original temporal que se subió
    if (file_exists($temp_original_path)) {
        unlink($temp_original_path);
    }

    // Si ya existía una foto, borrar las versiones antiguas (original y miniatura)
    if ($existing_photo_url) {
        $old_base_name = basename($existing_photo_url);
        // Construir rutas de archivo para las versiones originales y miniatura antiguas
        $old_original_path = UPLOAD_DIR_FOTOS . 'original_' . $old_base_name;
        $old_thumbnail_path = UPLOAD_DIR_FOTOS . 'thumb_' . $old_base_name;

        if (file_exists($old_original_path)) {
            unlink($old_original_path);
        }
        if (file_exists($old_thumbnail_path)) {
            unlink($old_thumbnail_path);
        }
    }
    
    return ['success' => $final_original_url_path]; // Devolver la URL de la foto redimensionada "original" para guardar en BD
}

// Función auxiliar para redimensionar imágenes (pequeña corrección de tipo)
function redimensionarImagen($source_gd_image, $target_path, $target_width, $target_height, $extension, $type) {
    $width = imagesx($source_gd_image);
    $height = imagesy($source_gd_image);

    // Calcular nuevas dimensiones manteniendo la proporción
    $ratio_orig = $width / $height;
    if ($target_width / $target_height > $ratio_orig) {
        $target_width = $target_height * $ratio_orig;
    } else {
        $target_height = $target_width / $ratio_orig;
    }

    $resized_image = imagecreatetruecolor(round($target_width), round($target_height)); // Usar round() para evitar float a int warnings

    // Conservar transparencia para PNG y GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($resized_image, false);
        imagesavealpha($resized_image, true);
        $transparent = imagecolorallocatealpha($resized_image, 255, 255, 255, 127);
        imagefilledrectangle($resized_image, 0, 0, round($target_width), round($target_height), $transparent);
    }

    imagecopyresampled($resized_image, $source_gd_image, 0, 0, 0, 0, round($target_width), round($target_height), $width, $height);

    switch ($extension) {
        case 'jpg':
        case 'jpeg':
            imagejpeg($resized_image, $target_path, 90); // Calidad 90
            break;
        case 'png':
            imagepng($resized_image, $target_path);
            break;
        case 'gif':
            imagegif($resized_image, $target_path);
            break;
    }
    imagedestroy($resized_image); // Liberar memoria
}

// Función para subir PDF
function subirPDF($file_input_name, $existing_pdf_url = null) {
    // Si no se sube un nuevo archivo, mantener el existente
    if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] == UPLOAD_ERR_NO_FILE) {
        return ['success' => $existing_pdf_url];
    }

    $file = $_FILES[$file_input_name];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file_extension != 'pdf') {
        return ['error' => 'Solo se permiten archivos PDF.'];
    }

    $file_name = uniqid('pdf_') . '.' . $file_extension;
    $target_path = UPLOAD_DIR_PDFS . $file_name;

    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['error' => 'Error al subir el archivo PDF. Verifique permisos de escritura.'];
    }

    // Si ya existía un PDF, borrar el archivo antiguo
    if ($existing_pdf_url) {
        $old_pdf_file_name = basename($existing_pdf_url);
        if (file_exists(UPLOAD_DIR_PDFS . $old_pdf_file_name)) {
            unlink(UPLOAD_DIR_PDFS . $old_pdf_file_name);
        }
    }
    return ['success' => URL_BASE_PDFS . $file_name];
}

// Función para crear un nuevo colaborador
function crearColaborador($link, $data, $foto_file_input_name, $pdf_file_input_name) {
    // Manejo de la subida de foto
    $foto_result = subirYRedimensionarFotoPerfil($foto_file_input_name);
    if (isset($foto_result['error'])) {
        return ['error' => $foto_result['error']];
    }
    $ruta_foto_perfil = $foto_result['success'];

    // Manejo de la subida de PDF
    $pdf_result = subirPDF($pdf_file_input_name);
    if (isset($pdf_result['error'])) {
        // Opcional: borrar la foto si el PDF falla y la foto se subió
        if (!empty($ruta_foto_perfil) && strpos($ruta_foto_perfil, 'original_') !== false) { // Solo borrar si es una nueva subida
            $uploaded_photo_name = basename($ruta_foto_perfil);
            if (file_exists(UPLOAD_DIR_FOTOS . $uploaded_photo_name)) {
                unlink(UPLOAD_DIR_FOTOS . $uploaded_photo_name);
            }
            if (file_exists(UPLOAD_DIR_FOTOS . 'thumb_' . $uploaded_photo_name)) {
                unlink(UPLOAD_DIR_FOTOS . 'thumb_' . $uploaded_photo_name);
            }
        }
        return ['error' => $pdf_result['error']];
    }
    $ruta_historial_academico_pdf = $pdf_result['success'];

    $sql = "INSERT INTO colaboradores (primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, sexo, identificacion, fecha_nacimiento, correo_personal, telefono, celular, direccion, ruta_foto_perfil, ruta_historial_academico_pdf, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)"; // AÑADIDO: 'activo' por defecto a 1
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssssssssssssss", // sssssssssssssi -> ssssssssssssss (activo es BOOLEAN/TINYINT, pero lo pasamos como string '1')
            $data['primer_nombre'], $data['segundo_nombre'], $data['primer_apellido'],
            $data['segundo_apellido'], $data['sexo'], $data['identificacion'],
            $data['fecha_nacimiento'], $data['correo_personal'], $data['telefono'],
            $data['celular'], $data['direccion'], $ruta_foto_perfil,
            $ruta_historial_academico_pdf
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt); // Cerrar aquí para evitar código inalcanzable
            return ['success' => true];
        } else {
            // Si la inserción en la BD falla, intenta borrar los archivos subidos
            if (!empty($ruta_foto_perfil) && strpos($ruta_foto_perfil, 'original_') !== false) {
                $uploaded_photo_name = basename($ruta_foto_perfil);
                if (file_exists(UPLOAD_DIR_FOTOS . $uploaded_photo_name)) {
                    unlink(UPLOAD_DIR_FOTOS . $uploaded_photo_name);
                }
                if (file_exists(UPLOAD_DIR_FOTOS . 'thumb_' . $uploaded_photo_name)) {
                    unlink(UPLOAD_DIR_FOTOS . 'thumb_' . $uploaded_photo_name);
                }
            }
            if (!empty($ruta_historial_academico_pdf) && strpos($ruta_historial_academico_pdf, 'pdf_') !== false) {
                if (file_exists(UPLOAD_DIR_PDFS . basename($ruta_historial_academico_pdf))) {
                    unlink(UPLOAD_DIR_PDFS . basename($ruta_historial_academico_pdf));
                }
            }
            // Manejo de error de duplicidad de identificación
            if (mysqli_errno($link) == 1062) {
                mysqli_stmt_close($stmt); // Cerrar stmt antes de retornar error
                return ['error' => 'La identificación (cédula) ya existe para otro colaborador.'];
            }
            mysqli_stmt_close($stmt); // Cerrar stmt antes de retornar error
            return ['error' => 'Error al guardar el colaborador en la base de datos.'];
        }
    }
    return ['error' => 'Error en la preparación de la consulta SQL.'];
}

// Función para actualizar un colaborador existente
function actualizarColaborador($link, $id_colaborador, $data, $foto_file_input_name, $pdf_file_input_name) {
    // Obtener las rutas actuales de foto y PDF del colaborador para pasarlas a las funciones de subida
    $colaborador_existente = getColaboradorById($link, $id_colaborador);
    if (!$colaborador_existente) {
        return ['error' => 'Colaborador no encontrado para actualizar.'];
    }
    $old_foto_url = $colaborador_existente['ruta_foto_perfil'];
    $old_pdf_url = $colaborador_existente['ruta_historial_academico_pdf'];

    // Manejo de la subida de foto (se pasa la ruta antigua para posible eliminación si se sube una nueva)
    $foto_result = subirYRedimensionarFotoPerfil($foto_file_input_name, $old_foto_url);
    if (isset($foto_result['error'])) {
        return ['error' => $foto_result['error']];
    }
    $ruta_foto_perfil = $foto_result['success'];

    // Manejo de la subida de PDF (se pasa la ruta antigua para posible eliminación si se sube uno nuevo)
    $pdf_result = subirPDF($pdf_file_input_name, $old_pdf_url);
    if (isset($pdf_result['error'])) {
        return ['error' => $pdf_result['error']];
    }
    $ruta_historial_academico_pdf = $pdf_result['success'];

    $sql = "UPDATE colaboradores SET
                primer_nombre = ?, segundo_nombre = ?, primer_apellido = ?, segundo_apellido = ?,
                sexo = ?, identificacion = ?, fecha_nacimiento = ?, correo_personal = ?,
                telefono = ?, celular = ?, direccion = ?, ruta_foto_perfil = ?,
                ruta_historial_academico_pdf = ?
            WHERE id_colaborador = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssssssssssssi",
            $data['primer_nombre'], $data['segundo_nombre'], $data['primer_apellido'],
            $data['segundo_apellido'], $data['sexo'], $data['identificacion'],
            $data['fecha_nacimiento'], $data['correo_personal'], $data['telefono'],
            $data['celular'], $data['direccion'], $ruta_foto_perfil,
            $ruta_historial_academico_pdf, $id_colaborador
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt); // Cerrar aquí para evitar código inalcanzable
            return ['success' => true];
        } else {
            // Manejo de error de duplicidad de identificación
            if (mysqli_errno($link) == 1062) {
                mysqli_stmt_close($stmt); // Cerrar stmt antes de retornar error
                return ['error' => 'La identificación (cédula) ya existe para otro colaborador.'];
            }
            mysqli_stmt_close($stmt); // Cerrar stmt antes de retornar error
            return ['error' => 'Error al actualizar el colaborador en la base de datos.'];
        }
    }
    return ['error' => 'Error en la preparación de la consulta SQL para actualización.'];
}

// Función para desactivar un colaborador (en lugar de eliminarlo físicamente)
// AÑADIDO: Nuevo criterio para "desactivar" en lugar de "eliminar"
function desactivarColaborador($link, $id_colaborador) {
    $sql = "UPDATE colaboradores SET activo = 0 WHERE id_colaborador = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id_colaborador);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt); // Cerrar stmt antes de retornar
            return true;
        }
        mysqli_stmt_close($stmt); // Cerrar stmt antes de retornar
    }
    return false;
}

// Función para activar un colaborador (complemento de desactivar)
// AÑADIDO: Nueva función para activar
function activarColaborador($link, $id_colaborador) {
    $sql = "UPDATE colaboradores SET activo = 1 WHERE id_colaborador = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id_colaborador);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt); // Cerrar stmt antes de retornar
            return true;
        }
        mysqli_stmt_close($stmt); // Cerrar stmt antes de retornar
    }
    return false;
}

// Función para eliminar archivos físicos (usar con precaución, solo si no se va a activar/desactivar)
// Si estamos desactivando, no deberíamos llamar a esta función en la práctica.
// Pero la mantengo si en algún escenario excepcional se necesitara la eliminación física de un archivo.
function eliminarArchivosColaborador($colaborador_data) {
    if (is_array($colaborador_data)) {
        $foto_url = $colaborador_data['ruta_foto_perfil'];
        $pdf_url = $colaborador_data['ruta_historial_academico_pdf'];

        if (!empty($foto_url)) {
            $base_foto_name = basename($foto_url);
            if (file_exists(UPLOAD_DIR_FOTOS . 'original_' . $base_foto_name)) {
                unlink(UPLOAD_DIR_FOTOS . 'original_' . $base_foto_name);
            }
            if (file_exists(UPLOAD_DIR_FOTOS . 'thumb_' . $base_foto_name)) {
                unlink(UPLOAD_DIR_FOTOS . 'thumb_' . $base_foto_name);
            }
        }
        if (!empty($pdf_url)) {
            $base_pdf_name = basename($pdf_url);
            if (file_exists(UPLOAD_DIR_PDFS . $base_pdf_name)) {
                unlink(UPLOAD_DIR_PDFS . $base_pdf_name);
            }
        }
        return true;
    }
    return false;
}

?>