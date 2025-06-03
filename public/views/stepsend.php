<?php
declare(strict_types=1);

/* ─── Sesión ─────────────────────────────────────────── */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ─── Seguridad básica ───────────────────────────────── */
$draft = $_SESSION['recipe_draft']  ?? null;
$steps = $_SESSION['recipe_steps'] ?? null;

if (!$draft || !$steps) {
    $_SESSION['error'] = 'No se encontró la receta en progreso.';
    header('Location: /crear-receta/formulario-1');
    exit;
}

/* ─── Twig ───────────────────────────────────────────── */
require_once __DIR__ . '/../../config/twig.php';

echo '<pre>';
var_dump($_SESSION['recipe_steps']);
echo '</pre>';
exit;

/* ─── Render ─────────────────────────────────────────── */
echo $twig->render('stepsend.html', [
    'recipeDraft' => $draft,
    'recipeSteps' => $steps,        // << ¡el array correcto!
    'error'       => $_SESSION['error'] ?? ''
]);

unset($_SESSION['error']);
