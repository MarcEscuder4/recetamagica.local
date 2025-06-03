<?php
require_once __DIR__ . '/../../config/twig.php';
require_once __DIR__ . '/../../src/controller/apiController/RecipeController.php';

// Obtener el ID de la receta desde la URL
$id = $_GET['id'] ?? null;

if ($recipeId) {
    $recipe = RecipeController::getRecipeById($recipeId);

    echo $twig->render('receta.html', [
        'recipe' => $recipe
    ]);
} else {
    // Si no hay ID, redirigir o mostrar error
    echo "Receta no encontrada.";
}
