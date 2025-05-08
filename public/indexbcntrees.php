<?php
session_start();

//Cargamos el archivo gettext
require_once __DIR__ . '/../config/locale.php';

// SessionController::userSignUp("usuario", "mescuder@elpuig.xeill.net", "password");
// die();
// SessionController::userLogin("usuario", "password");
// print_r($_SESSION);


//Instanciamos Routing
$request = strtok($_SERVER['REQUEST_URI'], '?');
$viewDir = '/views/';

// Funcion para redirigir
function redirect($url) {
    header("Location: $url");
    exit();
}

// Registro via código
// SessionController::userSignUp("mescuder", "mescuder@elpuig.xeill.net", "password");
// die();

//Switch para configurar las rutas
if (preg_match('/^\/arbre\/(\d+)$/', $request, $matches)) {
    $idArbol = $matches[1]; // Obtienes el ID del árbol
    require __DIR__ . $viewDir . 'tree.php';
} 
elseif (preg_match('/^\/admin\/arbre\/(\d+)$/', $request, $matches)) {
    $idArbol = $matches[1];
    if (SessionController::isLoggedIn()) {
        require __DIR__ . $viewDir . 'adminTree.php';
    } else {
        redirect("/");
    }

}else {
    switch ($request) {
        case '':
        case '/':
            require __DIR__ . $viewDir . 'home.php';
            break;
        case '/news':
            require __DIR__ . $viewDir . 'news.php';
            break;
        case '/barcelona':
            require __DIR__ . $viewDir . 'barcelona.php';
            break;
        case '/filter':
            require __DIR__ . $viewDir . 'filter.php';
            break;
        case '/test':
            require __DIR__ . $viewDir . 'tmpt.php';
            break;
        case '/perfil':
            if (SessionController::isLoggedIn()) {
                require __DIR__ . $viewDir . 'perfil.php';
            } else {
                redirect("/login");
            }
            break;
        case '/news/especies-predominants-a-barcelona':
            require __DIR__ . $viewDir . 'news1.php';
            break;
        case '/news/laberint-horta':
            require __DIR__ . $viewDir . 'news2.php';
            break;
        case '/news/taronjers-a-barcelona':
            require __DIR__ . $viewDir . 'news3.php';
            break;
        case '/news/extra-news':
            require __DIR__ . $viewDir . 'newsextra.php';
            break;
        case '/cercador':
            require __DIR__ . $viewDir . 'trees.php';
                break;
        case '/formulari':
            require __DIR__ . $viewDir . 'form.php';
            break;
        case '/login':
            if (SessionController::isLoggedIn()) {
                redirect("/admin");
                break;
            } else {
                require __DIR__ . $viewDir . 'login.php';
                break;
            }
        case '/logout':
                require __DIR__ . $viewDir . 'logout.php';
                break;
        case '/forgot_password':
                require __DIR__ . $viewDir . 'forgotPassword.php';
                break;   
        case '/admin':
            if (SessionController::isLoggedIn()) {
                require __DIR__ . $viewDir . 'adminDashboard.php';
            } else {
                redirect("/login");
            }
            break;
        case 'not-found':
        default:
            http_response_code(404);
            require __DIR__ . $viewDir . '404.php';
            break;
    }
}