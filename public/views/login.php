<?php

// echo "Estoy en login.php<br>";
// var_dump($_SESSION);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/twig.php';
require_once __DIR__ . '/../../src/controller/apiController/SessionController.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars($_POST["username"]);
    $password = htmlspecialchars($_POST["password"]);

    $userIdentifier = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    $loginSuccess = SessionController::userLogIn($username, $password, $userIdentifier);

    if ($loginSuccess) {
        $role = $_SESSION['user_role'] ?? null;
        if (in_array($role, ['admin', 'mod'])) {
            header("Location: /admin");
        } else {
            header("Location: /");
        }
        exit();
    } else {
        $_SESSION['error'] = "Nombre de usuario o contraseña incorrectos.";
        header("Location: /login"); // Evita que el formulario se reenvíe al refrescar
        exit();
    }
}

// var_dump($_SESSION);

// Recuperar y limpiar mensajes
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

echo $twig->render('login.html', [
    'error' => $errorMessage,
    'success_message' => $successMessage
]);
