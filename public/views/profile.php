<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/twig.php';
require_once __DIR__ . '/../../src/controller/apiController/SessionController.php';
require_once __DIR__ . '/../../src/controller/apiController/RankUtils.php';

// Redirigir si no hay sesión activa
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// Obtener datos del usuario desde la base de datos
$userId = $_SESSION['user_id'];
$userData = SessionController::getUserById($userId);
$rank = RankUtils::obtenerRangoPorPuntos($userData['points']);
$recipes = RecipeController::getRecipesById($userId);

echo $twig->render('profile.html', [
    'user' => $userData,
    'rank' => $rank,
    'recipes' => $recipes
]);
