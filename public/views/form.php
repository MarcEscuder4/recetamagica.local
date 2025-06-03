<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/twig.php';
require_once __DIR__ . '/../../src/controller/apiController/SessionController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rutaAvatar = null;

    if (!empty($_FILES['avatar']['name'])) {
        if ($_FILES['avatar']['error'] === UPLOAD_ERR_OK) {

            $tmp       = $_FILES['avatar']['tmp_name'];
            $extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));

            /*  Solo permitimos imágenes  */
            $extPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($extension, $extPermitidas)) {
                $_SESSION['error'] = 'Formato de imagen no permitido.';
                header('Location: /registro');
                exit;
            }

            $nombreSeguro   = uniqid('avatar_', true) . ".$extension";
            $carpetaDestino = __DIR__ . '/../img/profile/';   //  public/img/profile

            if (!is_dir($carpetaDestino)) {
                mkdir($carpetaDestino, 0755, true);
            }

            $rutaCompleta = $carpetaDestino . $nombreSeguro;

            if (!move_uploaded_file($tmp, $rutaCompleta)) {
                $_SESSION['error'] = 'Error al guardar la imagen.';
                header('Location: /registro');
                exit;
            }

            /*  Ruta relativa que guardaremos en la BD  */
            $rutaAvatar = 'img/profile/' . $nombreSeguro;
        } else {
            $_SESSION['error'] = 'Hubo un error al subir la imagen.';
            header('Location: /registro');
            exit;
        }
    }

    /* ---------- 2-B  Construir el array de datos ---------------------- */
    $data = [
        'username'  => trim($_POST['username']  ?? ''),
        'name'      => trim($_POST['name']      ?? ''),
        'surname1'  => trim($_POST['surname1']  ?? ''),
        'surname2'  => trim($_POST['surname2']  ?? ''),
        'email'     => trim($_POST['email']     ?? ''),
        'password'  => $_POST['password']       ?? '',
        'birthdate' => $_POST['birthdate']      ?? '',
        'country'   => trim($_POST['country']   ?? 'España'),
        'genre'     => $_POST['genre']          ?? 'Prefiero no contestar',
        'lang'      => $_POST['lang']           ?? 'es',
        'avatar'    => $rutaAvatar                              //  puede ser null
    ];

    /* ---------- 2-C  Validaciones simples ----------------------------- */
    $generosValidos = ['Hombre', 'Mujer', 'Prefiero no contestar'];
    if (!in_array($data['genre'], $generosValidos)) {
        $_SESSION['error'] = 'Género no válido.';
        header('Location: /registro');
        exit;
    }

    /* ---------- 2-D  Registro en la BD -------------------------------- */
    $ok = SessionController::userSignUp($data);

    if ($ok === true) {
        $_SESSION['success_message'] = '¡Registro exitoso! Ahora puedes iniciar sesión.';
        header('Location: /login');
        exit;
    }

    /*  Si falló, $ok contendrá el mensaje de error devuelto por userSignUp */
    $_SESSION['error'] = $ok ?: 'Ocurrió un error al registrarte.';
    header('Location: /registro');
    exit;
}

/* ─── GET ─────────────────────────────────────────── */
echo $twig->render('form.html', [
    'error'            => $_SESSION['error']           ?? '',
    'success_message'  => $_SESSION['success_message'] ?? ''
]);

unset($_SESSION['error'], $_SESSION['success_message']);
