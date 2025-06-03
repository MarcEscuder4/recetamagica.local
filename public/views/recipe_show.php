<?php
// Carga Twig
$twig = require_once __DIR__ . '/../../config/twig.php';

// Aquí ya no necesitas llamar a RecipeController ni hacer más lógica.
// La variable $recipe ya fue pasada desde index.php mediante `view()` (con extract).

$recipe = RecipeController::getRecipeById($recipeId);
echo "<pre>Resultado getRecipeById(): ";
var_dump($recipe);
echo "</pre>";


// Renderiza Twig
echo $twig->render('receta.html', [
    'recipe' => $recipe
]);
