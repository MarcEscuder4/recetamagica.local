<?php 
// Carga Twig
$twig = require_once __DIR__ . '/../../config/twig.php';
require_once __DIR__ . '/../../src/controller/apiController/RecipeController.php';

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $recipes = RecipeController::getRecipesByName(trim($_GET['search']));
} else {
    $recipes = RecipeController::getAllRecipes();
}

// Renderizar la plantilla
echo $twig->render('recipes.html', [
    'recipes' => $recipes,
    'searchQuery' => $_GET['search'] ?? '' // para mantener el valor en el input
]);
