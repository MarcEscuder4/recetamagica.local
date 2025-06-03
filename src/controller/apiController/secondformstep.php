<?php
declare(strict_types=1);

/* ───────────────────────── Sesión ───────────────────────── */
if (session_status() === PHP_SESSION_NONE) {
    session_start();          // solo si aún no existe
}

/* ────────────────── Carga helpers y Twig ────────────────── */
require_once __DIR__ . '/../../../config/twig.php';

/* ─────────────────────────  GET  ──────────────────────────
   Muestra el formulario de pasos (segundo paso del wizard) */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (empty($_SESSION['recipe_draft'])) {
        header('Location: /crear-receta/formulario-1');
        exit;
    }

    echo $twig->render('steps2.html', [
        'ingredients' => $_SESSION['recipe_draft']['ingredients'] ?? [],
        'recipeDraft' => $_SESSION['recipe_draft'] ?? [],
        'numSteps'    => $_SESSION['recipe_draft']['numSteps'] ?? 1,
        'error'       => $_SESSION['error'] ?? '',
    ]);
    unset($_SESSION['error']);
    exit;
}



/* ─────────────────────────  POST  ─────────────────────────
   Procesa los pasos recibidos desde el formulario 2        */

   echo '<pre>';
var_dump($_POST['steps']);
exit;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['recipe_draft'])) {
    header('Location: /crear-receta/formulario-1');
    exit;
}

if (empty($_POST['steps']) || count($_POST['steps']) < 1) {
    $_SESSION['error'] = 'Debes incluir al menos un paso en la receta.';
    header('Location: /crear-receta/formulario-2');
    exit;
}

/* ─── Actualizar número real de pasos ───────────────────── */
$_SESSION['recipe_draft']['numSteps'] = count($_POST['steps']);

/* ─── Procesar cada paso ────────────────────────────────── */
$extOk   = ['jpg','jpeg','png','gif','webp'];
$tmpBase = $_SERVER['DOCUMENT_ROOT'].'/uploads/tmp/steps/';
if (!is_dir($tmpBase)) { mkdir($tmpBase, 0755, true); }

$steps = [];

foreach ($_POST['steps'] as $i => $step) {

    $steps[$i] = [
        'title'       => trim($step['title']),
        'description' => trim($step['description']),
        'minutes'     => isset($step['minutes']) ? (int)$step['minutes'] : null,
        'seconds'     => isset($step['seconds']) ? (int)$step['seconds'] : null,
        'ingredients' => $step['ingredients'] ?? [],
        'photo'       => null,
    ];

    /* ─── Foto opcional ─────────────────────────────────── */
    if (
        isset($_FILES['steps']['name'][$i]['photo']) &&
        $_FILES['steps']['error'][$i]['photo'] === UPLOAD_ERR_OK
    ) {
        $ext = strtolower(pathinfo($_FILES['steps']['name'][$i]['photo'], PATHINFO_EXTENSION));
        if (!in_array($ext, $extOk, true)) {
            $_SESSION['error'] = 'Formato de imagen en paso '.($i+1).' no permitido.';
            header('Location: /crear-receta/formulario-2');
            exit;
        }

        $tmpFile  = $_FILES['steps']['tmp_name'][$i]['photo'];
        $fileName = uniqid("step{$i}_", true).".$ext";

        if (!move_uploaded_file($tmpFile, $tmpBase.$fileName)) {
            $_SESSION['error'] = 'Error al subir la imagen del paso '.($i+1).'.';
            header('Location: /crear-receta/formulario-2');
            exit;
        }

        $steps[$i]['photo'] = '/uploads/tmp/steps/'.$fileName; // ruta relativa
    }
}

/* ─── Guardar en sesión y avanzar ───────────────────────── */
$_SESSION['recipe_draft']['steps'] = $steps;

header('Location: /crear-receta/confirmar');
exit;
