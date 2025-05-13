<?php
session_start();
require_once __DIR__ . '/../../config/twig.php';
require_once __DIR__ . '/../../src/controller/apiController/SessionController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => $_POST['username'] ?? '',
        'name' => $_POST['name'] ?? '',
        'surname1' => $_POST['surname1'] ?? '',
        'surname2' => $_POST['surname2'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'birthdate' => $_POST['birthdate'] ?? '',
        'country' => $_POST['country'] ?? 'España',
        'genre' => $_POST['genre'] ?? 'Prefiero no contestar'
    ];

    $validGenres = ['Hombre', 'Mujer', 'Prefiero no contestar'];
    if (!in_array($data['genre'], $validGenres)) {
        echo $twig->render('form.html', ['error' => 'Género no válido.']);
        exit;
    }

    $result = SessionController::userSignUp($data);

    if ($result === true) {
        $_SESSION['success_message'] = '¡Registro exitoso! Ahora puedes iniciar sesión.';
        header('Location: /login');
        exit;
    } else {
        echo $twig->render('form.html', ['error' => $result]);
        exit;
    }
} else {
    echo $twig->render('form.html', ['error' => '']);
}
