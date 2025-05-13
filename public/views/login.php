<?php
session_start();
// Carga Twig
require_once __DIR__ . '/../../config/twig.php';
require_once __DIR__ . '/../../src/controller/apiController/SessionController.php';

// Verificación de usuario conectado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars($_POST["username"]);
    $password = htmlspecialchars($_POST["password"]);

    // Verificar si es un email o un nombre de usuario
    if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
        // Si es un email, lo utilizamos para login
        $userIdentifier = 'email';
    } else {
        // Si no es un email, utilizamos el nombre de usuario
        $userIdentifier = 'username';
    }

    // Verificación del login
    $loginSuccess = SessionController::userLogIn($username, $password, $userIdentifier);

    if ($loginSuccess) {
        // Aquí necesitarías obtener el rol del usuario de la sesión o del resultado del login.
        // Esto depende de cómo se maneje el login en la función userLogIn.
        
        // Suponiendo que el rol se almacene en la sesión, puedes accederlo así:
        if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'mod')) {
            // Si el rol es 'admin', redirigimos a la página del administrador
            header("Location: /admin");
            exit(); // No olvides poner exit después de un header para evitar que el código continúe ejecutándose
        } else {
            // Si no es admin, redirigimos al inicio
            header("Location: /");
            exit();
        }
    } else {
        // Si el login falla, muestra un error
        $_SESSION['error'] = "Nombre de usuario o contraseña incorrectos.";
    }
}

// Recuperar el mensaje de éxito, si existe
$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']); // Limpiar el mensaje de la sesión

// Renderizar la plantilla, pasando el error y el mensaje de éxito
echo $twig->render('login.html', [
    'error' => isset($_SESSION['error']) ? $_SESSION['error'] : '',
    'success_message' => $successMessage
]);
