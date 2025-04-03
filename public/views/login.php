<?php
// Carga Twig
require_once __DIR__ . '/../../config/twig.php';

//Verificació d'usuari connectat
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars($_POST["username"]);
    $password = htmlspecialchars($_POST["password"]);

    // Verificar si es un email o un nom d'usuari
    if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
        // Si es un email, l'utilitza per fer login
        $userIdentifier = 'email';
    } else {
        // Si no és un email, utilitza el nom d'usuari per fer login
        $userIdentifier = 'username';
    }

    // Verificació del login
    $user = SessionController::userLogIn($username, $password, $userIdentifier);

    if ($user) {
        // Verificar el rol del usuari
        if ($user['role'] == 'admin') {
            redirect("/admin");
        } else {
            redirect("");
        }
    } else {
        // Si el login falla, mostra un error
        $_SESSION['error'] = "Nombre de usuario o contraseña incorrectos.";
    }

}

// Renderitzar la plantilla
echo $twig->render('login.html');