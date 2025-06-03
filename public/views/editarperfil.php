<?php

// Sesión y dependencias
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/twig.php';
require_once __DIR__ . '/../../src/controller/apiController/SessionController.php';
require_once __DIR__ . '/../../src/controller/apiController/RankUtils.php';


// Comprobar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$error  = null;
$success= null;

// PETICIÓN POST – guardar cambios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rutaAvatar = null;
    if (!empty($_FILES['avatar']['name'])) {


        if ($_FILES['avatar']['error'] === UPLOAD_ERR_OK) {

            $tmp       = $_FILES['avatar']['tmp_name'];
            $ext       = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $permitidas= ['jpg','jpeg','png','gif','webp'];

            if (!in_array($ext, $permitidas)) {
                $error = 'Formato de imagen no permitido.';
            } else {
                $nombreSeguro   = "avatar_{$userId}.".$ext;
                $carpetaDestino = __DIR__ . '/../img/profile/';
                
                if (!is_dir($carpetaDestino)) {
                    mkdir($carpetaDestino, 0755, true);
                }

                if (move_uploaded_file($tmp, $carpetaDestino . $nombreSeguro)) {
                    $rutaAvatar = 'img/profile/' . $nombreSeguro;

                    // Opcional: eliminar avatar anterior
                    $userData = SessionController::getUserById($userId);
                    if (!empty($userData['avatar']) && file_exists(__DIR__ . '/../' . $userData['avatar'])) {
                        unlink(__DIR__ . '/../' . $userData['avatar']);
                    }

                } else {
                    $error = 'Error al guardar la imagen.';
                }
            }
        } else {
            $error = 'Hubo un error al subir la imagen.';
        }
    }

    $data = [
        'name'      => trim($_POST['name'] ?? ''),
        'surname1'  => trim($_POST['surname1'] ?? ''),
        'surname2'  => trim($_POST['surname2'] ?? ''),
        'birthdate' => $_POST['birthdate'] ?? '',
        'genre'     => $_POST['genre'] ?? '',
        'country'   => trim($_POST['country'] ?? ''),
        'lang'      => $_POST['lang'] ?? 'es',
        'avatar'    => $rutaAvatar
    ];

    /* Validación mínima */
    $generosValidos = ['Hombre','Mujer','Prefiero no contestar'];
    if (!in_array($data['genre'], $generosValidos)) {
        $error = 'Género no válido.';
    }

    if (empty($data['name']) || strlen($data['name']) > 100) {
        $error = 'Nombre inválido.';
    }

    if (!$error) {
        $ok = SessionController::updateUserById($userId, $data);
        if ($ok) {
            $success = 'Perfil actualizado correctamente.';
        } else {
            $error = 'Ocurrió un error al guardar los cambios.';
        }
    }
}

// PETICIÓN GET o POST con resultado – mostrar formulario

$userData = SessionController::getUserById($userId);
$rank     = RankUtils::obtenerRangoPorPuntos($userData['points']);

echo $twig->render('editarperfil.html', [
    'user'    => $userData,
    'rank'    => $rank,
    'error'   => $error,
    'success' => $success
]);