<?php
session_start();

// Cargamos el archivo gettext
require_once __DIR__ . '/../config/locale.php';


// Instància del Routing
$request = strtok($_SERVER['REQUEST_URI'], '?');
$viewDir = '/views/';

// Funció per redirigir
function redirect($url) {
    header("Location: $url");
    exit();
}


// Definició del Routing
if (preg_match('/^\/recetas\/(\d+)$/', $request, $matches)) {

    $idPajaro = $matches[1]; // Obtienes el ID de la receta
    require __DIR__ . $viewDir . 'receta.php';

}  elseif (preg_match('/^\/admin\/receta\/(\d+)$/', $request, $matches)) {

    $idPajaro = $matches[1];
    if (SessionController::isLoggedIn()) {
        require __DIR__ . $viewDir . 'adminRM.php';

    } else {
        redirect("/");
    }

} else {
    switch ($request) {
        case '':
            require __DIR__ . $viewDir . 'home.php';
            break;
        case '/':
            require __DIR__ . $viewDir . 'home.php';
            break;
        case '/form':
            require __DIR__ . $viewDir . 'form.php';
            break;
        case '/login':
            require __DIR__ . $viewDir . 'login.php';
            break;
        case '/perfil':
            if (SessionController::isLoggedIn()) {
                require __DIR__ . $viewDir . 'perfil.php';
            } else {
                redirect("/login");
            }
            break;
        case '/blog':
            require __DIR__ . $viewDir . 'blog.php';
            break;
        case '/contacto':
            require __DIR__ . $viewDir . 'contact.php';
            break; 
        case '/desafios':
            require __DIR__ . $viewDir . 'desafios.php';
            break;
        case '/crear-receta/formulario1':
            require __DIR__ . $viewDir . 'steps.php';
            break;
        case '/crear-receta/formulario2':
            require __DIR__ . $viewDir . 'steps2.php';
            break;
        case '/test':
            require __DIR__ . $viewDir . 'test.php';
            break;
        case '/example':
            require __DIR__ . $viewDir . 'example.php';
            break;
        case 'not-found':
            default:
                http_response_code(404);
                require __DIR__ . $viewDir . '404.php';
            break;
    }
}