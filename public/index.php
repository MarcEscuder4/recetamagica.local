<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->safeLoad();
require_once __DIR__ . '/../config/locale.php';

/* ───────────────────── Helpers ───────────────────── */
function cleanPath(string $uri): string {
    $path = rawurldecode(parse_url($uri, PHP_URL_PATH));
    $path = rtrim($path, '/');
    return $path === '' ? '/' : strtolower($path);
}

function view(string $file, array $data = []): void {
    extract($data); // Extrae variables como $recipeId
    require __DIR__ . '/views/' . $file;
    exit;
}


function auth(string $file): void {
    require __DIR__ . '/../' . $file; 
}



/* ───────────────────── Routing ───────────────────── */
$request = cleanPath($_SERVER['REQUEST_URI']);

/* 1) Rutas con parámetro vía RegEx */
if (preg_match('#^/recetas/(\d+)$#', $request, $m)) {
    $recipeId = (int)$m[1];
$recipe = RecipeController::getRecipeById($recipeId);

if (!$recipe) {
    echo "Receta no encontrada.";
    exit;
}


    view('recipe_show.php', [
        'recipeId' => $recipeId,
        'recipe' => $recipe
    ]);
}


if (preg_match('#^/admin/receta/(\d+)$#', $request, $m)) {
    $recipeId = (int)$m[1];
    auth('adminRM.php');
}

/* 2) Rutas simples */
switch ($request) {
    case '/':                      view('home.php');               break;
    case '/registro':              view('form.php');               break;
    case '/login':                 view('login.php');              break;
    case '/perfil':                view('profile.php');            break;
    case '/editar-perfil':         view('editarperfil.php');       break;
    case '/logout':
        SessionController::userLogout();
        header('Location: /login?logout=1'); exit;
    case '/recetas':               view('recipes.php');            break;

    /* ── Wizard Crear Receta ───────────────────────── */
    case '/crear-receta/formulario-1':  view('firstformstep.php');  break;
    case '/crear-receta/formulario-2':
        auth('src/controller/apiController/secondformstep.php');
                                                                    break;

    case '/crear-receta/confirmar':     view('stepsend.php');       break;
    case '/crear-receta/guardar':       view('save_recipe.php');    break;

    /* ── Otros ─────────────────────────────────────── */
    case '/blog':        view('blog.php');                break;
    case '/blog/tendencias-culinarias':        view('blogtc.php');                break;
    case '/contacto':    view('contact.php');             break;
    case '/desafios':    view('desafios.php');            break;
    case '/terminos':    view('terminoscondiciones.php'); break;
    case '/privacidad':  view('pprivacidad.php');         break;
    case '/test':        view('test.php');                break;
    case '/example':     view('example.php');             break;

    default:
        http_response_code(404);
        view('404.php');
}
